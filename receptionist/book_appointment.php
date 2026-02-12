<?php
$pageTitle = "Book Appointment";
$roleCss = true;
require_once __DIR__ . '/../middleware/check_login.php';
require_once __DIR__ . '/../middleware/check_role.php';
checkRole(['receptionist']);

/**
 * Validate appointment time is within consulting hours
 */
function validateConsultingTime($appointmentTime, $consultingHours) {
    $consultingHours = trim($consultingHours);
    $parts = preg_split('/\s*[-–—to]+\s*/i', $consultingHours);
    if (count($parts) < 2) return true;
    
    $startTime = strtotime(trim($parts[0]));
    $endTime = strtotime(trim($parts[1]));
    $apptTime = strtotime($appointmentTime);
    
    if ($startTime === false || $endTime === false || $apptTime === false) return true;
    
    $startMinutes = (int)date('H', $startTime) * 60 + (int)date('i', $startTime);
    $endMinutes = (int)date('H', $endTime) * 60 + (int)date('i', $endTime);
    $apptMinutes = (int)date('H', $apptTime) * 60 + (int)date('i', $apptTime);
    
    return ($apptMinutes >= $startMinutes && $apptMinutes <= $endMinutes);
}

// Handle booking
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'book') {
    $patientId = (int)$_POST['patient_id'];
    $doctorId = (int)$_POST['doctor_id'];
    $date = $_POST['appointment_date'];
    $time = $_POST['appointment_time'];
    $reason = trim($_POST['reason']);
    
    // Validate doctor availability
    $docCheck = $conn->prepare("SELECT consulting_days, consulting_hours, consulting_fee, full_name FROM users WHERE id=? AND role='doctor' AND is_active=1");
    $docCheck->bind_param("i", $doctorId);
    $docCheck->execute();
    $docInfo = $docCheck->get_result()->fetch_assoc();
    
    if (!$docInfo) {
        $_SESSION['error'] = "Invalid doctor selected.";
    } else {
        $hasError = false;
        $selectedDay = date('l', strtotime($date));
        $availableDays = explode(',', $docInfo['consulting_days'] ?? '');
        
        // Validate day
        if (!empty($docInfo['consulting_days']) && !in_array($selectedDay, $availableDays)) {
            $_SESSION['error'] = "Dr. " . $docInfo['full_name'] . " is not available on " . $selectedDay . ".";
            $hasError = true;
        }
        
        // Validate time
        if (!$hasError && !empty($docInfo['consulting_hours'])) {
            if (!validateConsultingTime($time, $docInfo['consulting_hours'])) {
                $_SESSION['error'] = "The selected time is outside Dr. " . $docInfo['full_name'] . "'s consulting hours (" . $docInfo['consulting_hours'] . ").";
                $hasError = true;
            }
        }
        
        if (!$hasError) {
            $stmt = $conn->prepare("INSERT INTO appointments (patient_id, doctor_id, appointment_date, appointment_time, reason, status) VALUES (?, ?, ?, ?, ?, 'approved')");
            $stmt->bind_param("iisss", $patientId, $doctorId, $date, $time, $reason);
            
            if ($stmt->execute()) {
                $appointmentId = $conn->insert_id;
                $fee = $docInfo['consulting_fee'] ?? 0;
                if ($fee > 0) {
                    $desc = "Consultation fee - Dr. " . $docInfo['full_name'];
                    $billStmt = $conn->prepare("INSERT INTO billing (patient_id, appointment_id, amount, description) VALUES (?, ?, ?, ?)");
                    $billStmt->bind_param("iids", $patientId, $appointmentId, $fee, $desc);
                    $billStmt->execute();
                }
                $_SESSION['success'] = "Appointment booked and approved successfully!";
            } else {
                $_SESSION['error'] = "Failed to book appointment.";
            }
        }
    }
    header("Location: " . APP_URL . "/receptionist/book_appointment.php");
    exit();
}

$patients = $conn->query("SELECT id, full_name, phone FROM users WHERE role='patient' AND is_active=1 ORDER BY full_name");
$doctors = $conn->query("SELECT id, full_name, specialization, consulting_hours, consulting_days, consulting_fee FROM users WHERE role='doctor' AND is_active=1 ORDER BY full_name");

// Today's bookings
$todayBookings = $conn->query("
    SELECT a.*, p.full_name as patient_name, d.full_name as doctor_name, d.specialization
    FROM appointments a 
    JOIN users p ON a.patient_id = p.id 
    JOIN users d ON a.doctor_id = d.id 
    WHERE a.appointment_date = CURDATE()
    ORDER BY a.appointment_time ASC
");

require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/sidebar.php';
?>

<?php if (isset($_SESSION['success'])): ?>
    <div class="alert alert-success"><i class="fas fa-check-circle"></i> <?php echo $_SESSION['success']; unset($_SESSION['success']); ?></div>
<?php endif; ?>
<?php if (isset($_SESSION['error'])): ?>
    <div class="alert alert-error"><i class="fas fa-exclamation-circle"></i> <?php echo $_SESSION['error']; unset($_SESSION['error']); ?></div>
<?php endif; ?>

<div class="grid-2">
    <div class="card register-form-card">
        <div class="card-header">
            <h2><i class="fas fa-calendar-plus"></i> Book New Appointment</h2>
        </div>
        <div class="card-body">
            <form method="POST" onsubmit="return validateRecBooking()">
                <input type="hidden" name="action" value="book">
                <div class="form-group">
                    <label><i class="fas fa-user"></i> Select Patient</label>
                    <select name="patient_id" class="form-control" required>
                        <option value="">-- Choose Patient --</option>
                        <?php while ($p = $patients->fetch_assoc()): ?>
                            <option value="<?php echo $p['id']; ?>"><?php echo htmlspecialchars($p['full_name']); ?> (<?php echo htmlspecialchars($p['phone'] ?? 'No phone'); ?>)</option>
                        <?php endwhile; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label><i class="fas fa-user-md"></i> Select Doctor</label>
                    <select name="doctor_id" class="form-control" id="doctorSelect" required onchange="showDoctorInfo(this)">
                        <option value="">-- Choose Doctor --</option>
                        <?php while ($d = $doctors->fetch_assoc()): ?>
                            <option value="<?php echo $d['id']; ?>" 
                                data-spec="<?php echo htmlspecialchars($d['specialization']); ?>"
                                data-hours="<?php echo htmlspecialchars($d['consulting_hours'] ?? 'N/A'); ?>"
                                data-days="<?php echo htmlspecialchars($d['consulting_days'] ?? ''); ?>"
                                data-fee="<?php echo $d['consulting_fee'] ?? 0; ?>"
                                data-name="<?php echo htmlspecialchars($d['full_name']); ?>">
                                Dr. <?php echo htmlspecialchars($d['full_name']); ?> - <?php echo htmlspecialchars($d['specialization']); ?>
                            </option>
                        <?php endwhile; ?>
                    </select>
                </div>
                <div id="doctorInfoBox" style="display:none; background:linear-gradient(135deg, #f5f3ff 0%, #ede9fe 100%); padding:16px; border-radius:10px; margin-bottom:16px; border:1px solid #c4b5fd;">
                    <div style="display:grid; gap:8px; font-size:13px;">
                        <div style="display:flex; justify-content:space-between; align-items:center; padding:6px 0; border-bottom:1px dashed #ddd6fe; flex-wrap:wrap; gap:4px;">
                            <span style="color:#6b7280;"><i class="fas fa-stethoscope" style="width:16px;"></i> Specialization:</span>
                            <strong id="diSpec" style="color:#1f2937;"></strong>
                        </div>
                        <div style="display:flex; justify-content:space-between; align-items:center; padding:6px 0; border-bottom:1px dashed #ddd6fe; flex-wrap:wrap; gap:4px;">
                            <span style="color:#6b7280;"><i class="fas fa-clock" style="width:16px;"></i> Consulting Hours:</span>
                            <strong id="diHours" style="color:#1f2937;"></strong>
                        </div>
                        <div style="display:flex; justify-content:space-between; align-items:center; padding:6px 0; border-bottom:1px dashed #ddd6fe; flex-wrap:wrap; gap:4px;">
                            <span style="color:#6b7280;"><i class="fas fa-calendar-week" style="width:16px;"></i> Available Days:</span>
                            <strong id="diDays" style="color:#1f2937; font-size:12px;"></strong>
                        </div>
                        <div style="display:flex; justify-content:space-between; align-items:center; padding:8px 12px; background:#fef3c7; border-radius:6px; margin-top:4px; flex-wrap:wrap; gap:4px;">
                            <span style="color:#92400e; font-weight:600;"><i class="fas fa-money-bill-wave" style="width:16px;"></i> Consultation Fee:</span>
                            <strong id="diFee" style="color:#166534; font-size:16px;"></strong>
                        </div>
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label><i class="fas fa-calendar"></i> Appointment Date</label>
                        <input type="date" name="appointment_date" class="form-control" id="recDateInput" min="<?php echo date('Y-m-d'); ?>" required onchange="validateRecDate()">
                        <small id="recDateWarning" style="color:#dc2626; display:none; margin-top:4px; font-size:12px;"><i class="fas fa-exclamation-triangle"></i> <span id="recDateWarningText"></span></small>
                    </div>
                    <div class="form-group">
                        <label><i class="fas fa-clock"></i> Appointment Time</label>
                        <input type="time" name="appointment_time" class="form-control" id="recTimeInput" required onchange="validateRecTime()">
                        <small id="recTimeWarning" style="color:#dc2626; display:none; margin-top:4px; font-size:12px;"><i class="fas fa-exclamation-triangle"></i> <span id="recTimeWarningText"></span></small>
                    </div>
                </div>
                <div class="form-group">
                    <label><i class="fas fa-comment-medical"></i> Reason for Visit</label>
                    <textarea name="reason" class="form-control" rows="2" placeholder="Brief reason for the appointment"></textarea>
                </div>
                <button type="submit" class="btn btn-primary" style="background:var(--receptionist-accent); width:100%;">
                    <i class="fas fa-calendar-check"></i> Book Appointment (Auto-Approved)
                </button>
            </form>
        </div>
    </div>

    <!-- Today's Bookings -->
    <div>
        <h3 class="admin-section-title"><i class="fas fa-calendar-day"></i> Today's Appointments</h3>
        <?php if ($todayBookings->num_rows > 0): ?>
            <?php while ($b = $todayBookings->fetch_assoc()): ?>
            <div class="room-card">
                <div class="room-info">
                    <h4><?php echo htmlspecialchars($b['patient_name']); ?></h4>
                    <div class="room-meta">
                        <span><i class="fas fa-user-md"></i> <?php echo htmlspecialchars($b['doctor_name']); ?></span>
                        <span><i class="fas fa-clock"></i> <?php echo date('h:i A', strtotime($b['appointment_time'])); ?></span>
                        <span class="badge badge-<?php echo $b['status']; ?>"><?php echo ucfirst($b['status']); ?></span>
                    </div>
                </div>
            </div>
            <?php endwhile; ?>
        <?php else: ?>
            <div class="empty-state"><i class="fas fa-calendar-times"></i><p>No appointments for today</p></div>
        <?php endif; ?>
    </div>
</div>

<script>
var recSelectedDays = [];
var recSelectedHours = '';
var recDayValid = true;
var recTimeValid = true;

function showDoctorInfo(select) {
    var opt = select.options[select.selectedIndex];
    var box = document.getElementById('doctorInfoBox');
    if (opt.value) {
        document.getElementById('diSpec').textContent = opt.dataset.spec;
        document.getElementById('diHours').textContent = opt.dataset.hours;
        recSelectedHours = opt.dataset.hours;
        
        var days = opt.dataset.days;
        recSelectedDays = days ? days.split(',') : [];
        document.getElementById('diDays').textContent = days ? days.replace(/,/g, ', ') : 'All days';
        
        document.getElementById('diFee').textContent = '$' + parseFloat(opt.dataset.fee).toFixed(2);
        box.style.display = 'block';
        validateRecDate();
        validateRecTime();
    } else {
        box.style.display = 'none';
        recSelectedDays = [];
        recSelectedHours = '';
        document.getElementById('recTimeWarning').style.display = 'none';
    }
}

function parseTimeStr(str) {
    str = str.trim().toUpperCase();
    var match = str.match(/(\d{1,2}):?(\d{2})?\s*(AM|PM)?/i);
    if (!match) return null;
    var hours = parseInt(match[1]);
    var minutes = parseInt(match[2] || '0');
    var ampm = match[3] ? match[3].toUpperCase() : null;
    if (ampm === 'PM' && hours !== 12) hours += 12;
    if (ampm === 'AM' && hours === 12) hours = 0;
    return hours * 60 + minutes;
}

function validateRecDate() {
    var dateInput = document.getElementById('recDateInput');
    var warning = document.getElementById('recDateWarning');
    recDayValid = true;
    
    if (dateInput.value && recSelectedDays.length > 0) {
        var date = new Date(dateInput.value + 'T00:00:00');
        var dayNames = ['Sunday','Monday','Tuesday','Wednesday','Thursday','Friday','Saturday'];
        var dayName = dayNames[date.getDay()];
        
        if (!recSelectedDays.includes(dayName)) {
            document.getElementById('recDateWarningText').textContent = 
                'Doctor not available on ' + dayName + '. Available: ' + recSelectedDays.join(', ');
            warning.style.display = 'block';
            recDayValid = false;
        } else {
            warning.style.display = 'none';
        }
    } else {
        warning.style.display = 'none';
    }
}

function validateRecTime() {
    var timeInput = document.getElementById('recTimeInput');
    var warning = document.getElementById('recTimeWarning');
    recTimeValid = true;
    
    if (!timeInput.value || !recSelectedHours || recSelectedHours === 'N/A') {
        warning.style.display = 'none';
        return;
    }
    
    var parts = recSelectedHours.split(/\s*[-–—]\s*/);
    if (parts.length < 2) { warning.style.display = 'none'; return; }
    
    var startMin = parseTimeStr(parts[0]);
    var endMin = parseTimeStr(parts[1]);
    if (startMin === null || endMin === null) { warning.style.display = 'none'; return; }
    
    var timeParts = timeInput.value.split(':');
    var apptMin = parseInt(timeParts[0]) * 60 + parseInt(timeParts[1]);
    
    if (apptMin < startMin || apptMin > endMin) {
        document.getElementById('recTimeWarningText').textContent = 
            'Selected time is outside consulting hours (' + recSelectedHours + ').';
        warning.style.display = 'block';
        recTimeValid = false;
    } else {
        warning.style.display = 'none';
    }
}

function validateRecBooking() {
    validateRecDate();
    validateRecTime();
    if (!recDayValid) {
        alert('Please select a day when the doctor is available.');
        return false;
    }
    if (!recTimeValid) {
        alert('Please select a time within the doctor\'s consulting hours (' + recSelectedHours + ').');
        return false;
    }
    return true;
}
</script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
