<?php
$pageTitle = "Book Appointment";
$roleCss = true;
require_once __DIR__ . '/../middleware/check_login.php';
require_once __DIR__ . '/../middleware/check_role.php';
checkRole(['patient']);

$userId = $_SESSION['user_id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $doctor_id = (int)$_POST['doctor_id'];
    $date = $_POST['appointment_date'];
    $time = $_POST['appointment_time'];
    $reason = trim($_POST['reason']);
    
    // Validate the selected date and time against doctor's availability
    $docCheck = $conn->prepare("SELECT consulting_days, consulting_hours, consulting_fee, full_name FROM users WHERE id=? AND role='doctor' AND is_active=1");
    $docCheck->bind_param("i", $doctor_id);
    $docCheck->execute();
    $docInfo = $docCheck->get_result()->fetch_assoc();
    
    if (!$docInfo) {
        $error = "Invalid doctor selected.";
    } else {
        $selectedDay = date('l', strtotime($date));
        $availableDays = explode(',', $docInfo['consulting_days'] ?? '');
        
        // Validate day
        if (!empty($docInfo['consulting_days']) && !in_array($selectedDay, $availableDays)) {
            $error = "Dr. " . $docInfo['full_name'] . " is not available on " . $selectedDay . ". Please choose an available day.";
        }
        // Validate time against consulting hours
        elseif (!empty($docInfo['consulting_hours'])) {
            $timeValid = validateConsultingTime($time, $docInfo['consulting_hours']);
            if (!$timeValid) {
                $error = "The selected time is outside Dr. " . $docInfo['full_name'] . "'s consulting hours (" . $docInfo['consulting_hours'] . "). Please choose a time within consulting hours.";
            }
        }
        
        if (!isset($error)) {
            $stmt = $conn->prepare("INSERT INTO appointments (patient_id, doctor_id, appointment_date, appointment_time, reason) VALUES (?, ?, ?, ?, ?)");
            $stmt->bind_param("iisss", $userId, $doctor_id, $date, $time, $reason);
            
            if ($stmt->execute()) {
                // Create billing record with actual doctor fee
                $aptId = $conn->insert_id;
                $fee = $docInfo['consulting_fee'] ?? 0;
                if ($fee > 0) {
                    $desc = "Consultation fee - Dr. " . $docInfo['full_name'];
                    $billingStmt = $conn->prepare("INSERT INTO billing (patient_id, appointment_id, amount, description) VALUES (?, ?, ?, ?)");
                    $billingStmt->bind_param("iids", $userId, $aptId, $fee, $desc);
                    $billingStmt->execute();
                }
                
                $_SESSION['success'] = "Appointment booked successfully! Awaiting receptionist approval.";
                header("Location: " . APP_URL . "/patient/appointment_history.php");
                exit();
            } else {
                $error = "Failed to book appointment. Please try again.";
            }
        }
    }
}

/**
 * Validate appointment time is within consulting hours
 * Supports formats: "09:00 AM - 05:00 PM", "9:00AM-5:00PM", "09:00 - 17:00"
 */
function validateConsultingTime($appointmentTime, $consultingHours) {
    // Try to parse consulting hours like "09:00 AM - 05:00 PM" or "9AM - 5PM"
    $consultingHours = trim($consultingHours);
    
    // Split by common separators
    $parts = preg_split('/\s*[-–—to]+\s*/i', $consultingHours);
    if (count($parts) < 2) return true; // Can't parse, allow booking
    
    $startStr = trim($parts[0]);
    $endStr = trim($parts[1]);
    
    // Parse start and end times
    $startTime = strtotime($startStr);
    $endTime = strtotime($endStr);
    $apptTime = strtotime($appointmentTime);
    
    if ($startTime === false || $endTime === false || $apptTime === false) {
        return true; // Can't parse, allow booking
    }
    
    // Normalize all times to same day for comparison
    $startMinutes = (int)date('H', $startTime) * 60 + (int)date('i', $startTime);
    $endMinutes = (int)date('H', $endTime) * 60 + (int)date('i', $endTime);
    $apptMinutes = (int)date('H', $apptTime) * 60 + (int)date('i', $apptTime);
    
    return ($apptMinutes >= $startMinutes && $apptMinutes <= $endMinutes);
}

$doctors = $conn->query("SELECT id, full_name, specialization, consulting_hours, consulting_days, consulting_fee FROM users WHERE role='doctor' AND is_active=1 ORDER BY full_name");

require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/sidebar.php';
?>

<?php if (isset($error)): ?>
    <div class="alert alert-error"><i class="fas fa-exclamation-circle"></i> <?php echo $error; ?></div>
<?php endif; ?>

<div class="card">
    <div class="card-header">
        <h2><i class="fas fa-calendar-plus"></i> Book New Appointment</h2>
    </div>
    <div class="card-body">
        <form method="POST" class="booking-form" id="bookingForm" onsubmit="return validateBooking()">
            <div class="form-group">
                <label for="doctor_id"><i class="fas fa-user-md"></i> Select Doctor</label>
                <select name="doctor_id" id="doctor_id" class="form-control" required onchange="showDoctorDetails(this)">
                    <option value="">-- Choose a Doctor --</option>
                    <?php while ($doc = $doctors->fetch_assoc()): ?>
                        <option value="<?php echo $doc['id']; ?>"
                            data-spec="<?php echo htmlspecialchars($doc['specialization']); ?>"
                            data-hours="<?php echo htmlspecialchars($doc['consulting_hours'] ?? 'N/A'); ?>"
                            data-days="<?php echo htmlspecialchars($doc['consulting_days'] ?? ''); ?>"
                            data-fee="<?php echo $doc['consulting_fee'] ?? 0; ?>"
                            data-name="<?php echo htmlspecialchars($doc['full_name']); ?>">
                            <?php echo htmlspecialchars($doc['full_name']); ?> — <?php echo htmlspecialchars($doc['specialization']); ?>
                        </option>
                    <?php endwhile; ?>
                </select>
            </div>

            <!-- Doctor Info Panel -->
            <div id="doctorInfoPanel" style="display:none; background:linear-gradient(135deg, #f0fdf4 0%, #ecfdf5 100%); padding:20px; border-radius:12px; margin-bottom:20px; border:1px solid #bbf7d0;">
                <h4 style="margin:0 0 12px 0; color:#166534; font-size:15px;"><i class="fas fa-stethoscope"></i> Doctor Information</h4>
                <div class="doctor-info-grid">
                    <div style="background:white; padding:12px 16px; border-radius:8px; border-left:3px solid #22c55e;">
                        <small style="color:#6b7280; font-size:11px; text-transform:uppercase; letter-spacing:0.5px;">Specialization</small>
                        <div style="font-weight:600; color:#1f2937; margin-top:4px;" id="infoSpec"></div>
                    </div>
                    <div style="background:white; padding:12px 16px; border-radius:8px; border-left:3px solid #3b82f6;">
                        <small style="color:#6b7280; font-size:11px; text-transform:uppercase; letter-spacing:0.5px;">Consulting Hours</small>
                        <div style="font-weight:600; color:#1f2937; margin-top:4px;" id="infoHours"></div>
                    </div>
                    <div style="background:white; padding:12px 16px; border-radius:8px; border-left:3px solid #a855f7;">
                        <small style="color:#6b7280; font-size:11px; text-transform:uppercase; letter-spacing:0.5px;">Available Days</small>
                        <div style="font-weight:600; color:#1f2937; margin-top:4px; font-size:13px;" id="infoDays"></div>
                    </div>
                    <div style="background:white; padding:12px 16px; border-radius:8px; border-left:3px solid #f59e0b;">
                        <small style="color:#6b7280; font-size:11px; text-transform:uppercase; letter-spacing:0.5px;">Consultation Fee</small>
                        <div style="font-weight:700; color:#166534; margin-top:4px; font-size:18px;" id="infoFee"></div>
                    </div>
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label for="appointment_date"><i class="fas fa-calendar"></i> Date</label>
                    <input type="date" name="appointment_date" id="appointment_date" class="form-control" min="<?php echo date('Y-m-d'); ?>" required onchange="validateDate()">
                    <small id="dateWarning" style="color:#dc2626; display:none; margin-top:4px; font-size:12px;"><i class="fas fa-exclamation-triangle"></i> <span id="dateWarningText"></span></small>
                </div>
                <div class="form-group">
                    <label for="appointment_time"><i class="fas fa-clock"></i> Time</label>
                    <input type="time" name="appointment_time" id="appointment_time" class="form-control" required onchange="validateTime()">
                    <small id="timeWarning" style="color:#dc2626; display:none; margin-top:4px; font-size:12px;"><i class="fas fa-exclamation-triangle"></i> <span id="timeWarningText"></span></small>
                </div>
            </div>
            <div class="form-group">
                <label for="reason"><i class="fas fa-comment-medical"></i> Reason for Visit</label>
                <textarea name="reason" id="reason" class="form-control" rows="3" placeholder="Describe your symptoms or reason for visit..." required></textarea>
            </div>

            <!-- Booking Summary / Confirmation Panel -->
            <div id="bookingSummary" style="display:none; background:linear-gradient(135deg, #eff6ff 0%, #f0f9ff 100%); padding:20px; border-radius:12px; margin-bottom:20px; border:1px solid #bfdbfe;">
                <h4 style="margin:0 0 14px 0; color:#1e40af; font-size:15px;"><i class="fas fa-receipt"></i> Booking Summary</h4>
                <div style="display:grid; gap:8px; font-size:14px;">
                    <div style="display:flex; justify-content:space-between; padding:8px 0; border-bottom:1px dashed #cbd5e1; flex-wrap:wrap; gap:4px;">
                        <span style="color:#6b7280;">Doctor:</span>
                        <strong id="sumDoctor" style="color:#1f2937;"></strong>
                    </div>
                    <div style="display:flex; justify-content:space-between; padding:8px 0; border-bottom:1px dashed #cbd5e1; flex-wrap:wrap; gap:4px;">
                        <span style="color:#6b7280;">Date:</span>
                        <strong id="sumDate" style="color:#1f2937;"></strong>
                    </div>
                    <div style="display:flex; justify-content:space-between; padding:8px 0; border-bottom:1px dashed #cbd5e1; flex-wrap:wrap; gap:4px;">
                        <span style="color:#6b7280;">Time:</span>
                        <strong id="sumTime" style="color:#1f2937;"></strong>
                    </div>
                    <div style="display:flex; justify-content:space-between; padding:12px; background: #fef3c7; border-radius:8px; flex-wrap:wrap; gap:4px;">
                        <span style="color:#92400e; font-weight:600;"><i class="fas fa-money-bill-wave"></i> Consultation Fee:</span>
                        <strong id="sumFee" style="color:#166534; font-size:18px;"></strong>
                    </div>
                </div>
                <p style="font-size:12px; color:#6b7280; margin-top:12px; margin-bottom:0;">
                    <i class="fas fa-info-circle"></i> Payment can be made after your appointment is approved. You can pay via card or at the reception cashier.
                </p>
            </div>

            <button type="submit" class="btn btn-primary" id="bookBtn" style="width:100%;">
                <i class="fas fa-check"></i> Confirm & Book Appointment
            </button>
        </form>
    </div>
</div>

<style>
.doctor-info-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 12px;
}
@media (max-width: 600px) {
    .doctor-info-grid {
        grid-template-columns: 1fr;
    }
    .booking-form {
        max-width: 100% !important;
    }
}
</style>

<script>
var selectedDoctorDays = [];
var selectedDoctorFee = 0;
var selectedDoctorHours = '';
var isDayValid = true;
var isTimeValid = true;

function showDoctorDetails(select) {
    var opt = select.options[select.selectedIndex];
    var panel = document.getElementById('doctorInfoPanel');
    if (opt.value) {
        document.getElementById('infoSpec').textContent = opt.dataset.spec;
        document.getElementById('infoHours').textContent = opt.dataset.hours;
        selectedDoctorHours = opt.dataset.hours;
        
        var days = opt.dataset.days;
        selectedDoctorDays = days ? days.split(',') : [];
        document.getElementById('infoDays').textContent = days ? days.replace(/,/g, ', ') : 'All days';
        
        selectedDoctorFee = parseFloat(opt.dataset.fee);
        document.getElementById('infoFee').textContent = '$' + selectedDoctorFee.toFixed(2);
        panel.style.display = 'block';
        
        validateDate();
        validateTime();
        updateSummary();
    } else {
        panel.style.display = 'none';
        selectedDoctorDays = [];
        selectedDoctorFee = 0;
        selectedDoctorHours = '';
        document.getElementById('bookingSummary').style.display = 'none';
        document.getElementById('timeWarning').style.display = 'none';
    }
}

function validateDate() {
    var dateInput = document.getElementById('appointment_date');
    var warning = document.getElementById('dateWarning');
    isDayValid = true;
    
    if (dateInput.value && selectedDoctorDays.length > 0) {
        var date = new Date(dateInput.value + 'T00:00:00');
        var dayNames = ['Sunday','Monday','Tuesday','Wednesday','Thursday','Friday','Saturday'];
        var dayName = dayNames[date.getDay()];
        
        if (!selectedDoctorDays.includes(dayName)) {
            document.getElementById('dateWarningText').textContent = 
                'This doctor is not available on ' + dayName + '. Available days: ' + selectedDoctorDays.join(', ');
            warning.style.display = 'block';
            isDayValid = false;
            return false;
        } else {
            warning.style.display = 'none';
        }
    } else {
        warning.style.display = 'none';
    }
    updateSummary();
    return true;
}

function parseTimeString(str) {
    str = str.trim().toUpperCase();
    // Match patterns like "09:00 AM", "9:00AM", "9AM", "17:00"  
    var match = str.match(/(\d{1,2}):?(\d{2})?\s*(AM|PM)?/i);
    if (!match) return null;
    
    var hours = parseInt(match[1]);
    var minutes = parseInt(match[2] || '0');
    var ampm = match[3] ? match[3].toUpperCase() : null;
    
    if (ampm === 'PM' && hours !== 12) hours += 12;
    if (ampm === 'AM' && hours === 12) hours = 0;
    
    return hours * 60 + minutes;
}

function validateTime() {
    var timeInput = document.getElementById('appointment_time');
    var warning = document.getElementById('timeWarning');
    isTimeValid = true;
    
    if (!timeInput.value || !selectedDoctorHours || selectedDoctorHours === 'N/A') {
        warning.style.display = 'none';
        updateSummary();
        return true;
    }
    
    // Parse consulting hours (e.g. "09:00 AM - 05:00 PM")
    var parts = selectedDoctorHours.split(/\s*[-–—]\s*/);
    if (parts.length < 2) {
        warning.style.display = 'none';
        updateSummary();
        return true;
    }
    
    var startMinutes = parseTimeString(parts[0]);
    var endMinutes = parseTimeString(parts[1]);
    
    if (startMinutes === null || endMinutes === null) {
        warning.style.display = 'none';
        updateSummary();
        return true;
    }
    
    // Parse selected time
    var timeParts = timeInput.value.split(':');
    var apptMinutes = parseInt(timeParts[0]) * 60 + parseInt(timeParts[1]);
    
    if (apptMinutes < startMinutes || apptMinutes > endMinutes) {
        document.getElementById('timeWarningText').textContent = 
            'Selected time is outside consulting hours (' + selectedDoctorHours + '). Please choose a time within the doctor\'s working hours.';
        warning.style.display = 'block';
        isTimeValid = false;
        return false;
    } else {
        warning.style.display = 'none';
    }
    
    updateSummary();
    return true;
}

function validateBooking() {
    validateDate();
    validateTime();
    
    if (!isDayValid) {
        alert('Please select a day when the doctor is available.');
        return false;
    }
    if (!isTimeValid) {
        alert('Please select a time within the doctor\'s consulting hours (' + selectedDoctorHours + ').');
        return false;
    }
    return true;
}

function updateSummary() {
    var doctor = document.getElementById('doctor_id');
    var date = document.getElementById('appointment_date');
    var time = document.getElementById('appointment_time');
    var summary = document.getElementById('bookingSummary');
    
    if (doctor.value && date.value && time.value && isDayValid && isTimeValid) {
        var opt = doctor.options[doctor.selectedIndex];
        document.getElementById('sumDoctor').textContent = 'Dr. ' + opt.dataset.name + ' (' + opt.dataset.spec + ')';
        
        var dateObj = new Date(date.value + 'T00:00:00');
        document.getElementById('sumDate').textContent = dateObj.toLocaleDateString('en-US', {weekday:'long', year:'numeric', month:'long', day:'numeric'});
        
        // Format time
        var timeParts = time.value.split(':');
        var hours = parseInt(timeParts[0]);
        var mins = timeParts[1];
        var ampm = hours >= 12 ? 'PM' : 'AM';
        hours = hours % 12 || 12;
        document.getElementById('sumTime').textContent = hours + ':' + mins + ' ' + ampm;
        
        document.getElementById('sumFee').textContent = '$' + selectedDoctorFee.toFixed(2);
        summary.style.display = 'block';
    } else {
        summary.style.display = 'none';
    }
}

// Listen for time/date changes
document.getElementById('appointment_time').addEventListener('change', function() { validateTime(); updateSummary(); });
document.getElementById('appointment_date').addEventListener('change', function() { validateDate(); updateSummary(); });
</script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
