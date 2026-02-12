<?php
$pageTitle = "Assign Rooms";
$roleCss = true;
require_once __DIR__ . '/../middleware/check_login.php';
require_once __DIR__ . '/../middleware/check_role.php';
checkRole(['receptionist']);

// Handle room actions
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    if ($_POST['action'] === 'assign') {
        $roomId = (int)$_POST['room_id'];
        $patientId = (int)$_POST['patient_id'];
        
        // Get doctor assigned to this room
        $room = $conn->query("SELECT doctor_id FROM rooms WHERE id = $roomId")->fetch_assoc();
        $doctorId = $room['doctor_id'] ?? null;
        
        // Assign patient to room (allow multiple patients)
        $stmt = $conn->prepare("INSERT INTO room_assignments (room_id, patient_id, doctor_id) VALUES (?, ?, ?)");
        $stmt->bind_param("iii", $roomId, $patientId, $doctorId);
        $stmt->execute();
        
        // Update room status to occupied
        $conn->query("UPDATE rooms SET status='occupied' WHERE id = $roomId");
        
        $_SESSION['success'] = "Patient assigned to room successfully!";
        header("Location: " . APP_URL . "/receptionist/assign_rooms.php");
        exit();
    }
    
    if ($_POST['action'] === 'release_patient') {
        $assignmentId = (int)$_POST['assignment_id'];
        $roomId = (int)$_POST['room_id'];
        
        // Release specific patient
        $conn->query("UPDATE room_assignments SET is_active=0, released_at=NOW() WHERE id = $assignmentId");
        
        // Check if any patients still in the room
        $remaining = $conn->query("SELECT COUNT(*) as c FROM room_assignments WHERE room_id = $roomId AND is_active = 1")->fetch_assoc()['c'];
        if ($remaining == 0) {
            $conn->query("UPDATE rooms SET status='available' WHERE id = $roomId");
        }
        
        $_SESSION['success'] = "Patient released from room!";
        header("Location: " . APP_URL . "/receptionist/assign_rooms.php");
        exit();
    }
    
    if ($_POST['action'] === 'assign_doctor') {
        $roomId = (int)$_POST['room_id'];
        $doctorId = (int)$_POST['doctor_id'];
        
        $stmt = $conn->prepare("UPDATE rooms SET doctor_id=? WHERE id=?");
        $stmt->bind_param("ii", $doctorId, $roomId);
        $stmt->execute();
        
        $_SESSION['success'] = "Doctor assigned to room!";
        header("Location: " . APP_URL . "/receptionist/assign_rooms.php");
        exit();
    }
}

// Fetch rooms with assigned doctor
$rooms = $conn->query("
    SELECT r.*, d.full_name as doctor_name, d.specialization
    FROM rooms r 
    LEFT JOIN users d ON r.doctor_id = d.id 
    ORDER BY r.room_number
");

// Fetch active room assignments
$assignments = $conn->query("
    SELECT ra.*, p.full_name as patient_name, r.room_number
    FROM room_assignments ra
    JOIN users p ON ra.patient_id = p.id
    JOIN rooms r ON ra.room_id = r.id
    WHERE ra.is_active = 1
    ORDER BY ra.assigned_at DESC
");

// Build assignments array keyed by room_id
$roomAssignments = [];
if ($assignments && $assignments->num_rows > 0) {
    while ($a = $assignments->fetch_assoc()) {
        $roomAssignments[$a['room_id']][] = $a;
    }
}

$patients = $conn->query("SELECT id, full_name FROM users WHERE role='patient' AND is_active=1 ORDER BY full_name");
$doctorsList = $conn->query("SELECT id, full_name, specialization FROM users WHERE role='doctor' AND is_active=1 ORDER BY full_name");

require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/sidebar.php';
?>

<?php if (isset($_SESSION['success'])): ?>
    <div class="alert alert-success"><i class="fas fa-check-circle"></i> <?php echo $_SESSION['success']; unset($_SESSION['success']); ?></div>
<?php endif; ?>

<div class="card">
    <div class="card-header">
        <h2><i class="fas fa-bed"></i> Room Management</h2>
        <span class="badge badge-approved">Multiple patients per doctor room supported</span>
    </div>
    <div class="card-body">
        <?php while ($r = $rooms->fetch_assoc()): ?>
        <div class="room-card" style="flex-direction:column;align-items:stretch;">
            <div style="display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:10px;">
                <div class="room-info">
                    <h4>Room <?php echo htmlspecialchars($r['room_number']); ?></h4>
                    <div class="room-meta">
                        <span><i class="fas fa-door-open"></i> <?php echo ucfirst($r['room_type']); ?></span>
                        <span class="badge badge-<?php echo $r['status']; ?>"><?php echo ucfirst($r['status']); ?></span>
                        <?php if ($r['doctor_name']): ?>
                            <span><i class="fas fa-user-md"></i> Dr. <?php echo htmlspecialchars($r['doctor_name']); ?> (<?php echo htmlspecialchars($r['specialization']); ?>)</span>
                        <?php else: ?>
                            <span style="color:var(--color-warning);"><i class="fas fa-exclamation-triangle"></i> No doctor assigned</span>
                        <?php endif; ?>
                    </div>
                </div>
                <div class="room-actions">
                    <?php if ($r['doctor_id']): ?>
                        <button class="btn btn-success btn-sm" onclick="openAssignModal(<?php echo $r['id']; ?>, '<?php echo htmlspecialchars($r['room_number']); ?>', '<?php echo htmlspecialchars($r['doctor_name'] ?? ''); ?>')">
                            <i class="fas fa-user-plus"></i> Add Patient
                        </button>
                    <?php else: ?>
                        <button class="btn btn-info btn-sm" onclick="openDoctorAssignModal(<?php echo $r['id']; ?>, '<?php echo htmlspecialchars($r['room_number']); ?>')">
                            <i class="fas fa-user-md"></i> Assign Doctor
                        </button>
                    <?php endif; ?>
                </div>
            </div>
            
            <!-- Show assigned patients -->
            <?php if (isset($roomAssignments[$r['id']]) && count($roomAssignments[$r['id']]) > 0): ?>
            <div class="room-patients">
                <h5><i class="fas fa-users"></i> Assigned Patients (<?php echo count($roomAssignments[$r['id']]); ?>)</h5>
                <?php foreach ($roomAssignments[$r['id']] as $assign): ?>
                <div class="room-patient-item">
                    <span><i class="fas fa-user"></i> <?php echo htmlspecialchars($assign['patient_name']); ?></span>
                    <div style="display:flex;gap:8px;align-items:center;">
                        <span style="font-size:12px;color:var(--text-muted);">Since <?php echo date('M d, h:i A', strtotime($assign['assigned_at'])); ?></span>
                        <form method="POST" style="display:inline;" onsubmit="return confirm('Release this patient from the room?');">
                            <input type="hidden" name="action" value="release_patient">
                            <input type="hidden" name="assignment_id" value="<?php echo $assign['id']; ?>">
                            <input type="hidden" name="room_id" value="<?php echo $r['id']; ?>">
                            <button type="submit" class="btn btn-warning btn-sm" style="padding:4px 8px;font-size:11px;">
                                <i class="fas fa-sign-out-alt"></i> Release
                            </button>
                        </form>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>
        </div>
        <?php endwhile; ?>
    </div>
</div>

<!-- Assign Patient Modal -->
<div class="modal-overlay" id="assignModal">
    <div class="modal">
        <div class="modal-header">
            <h2>Add Patient to Room <span id="modal_room_number"></span></h2>
            <button class="modal-close" onclick="this.closest('.modal-overlay').classList.remove('active')">&times;</button>
        </div>
        <form method="POST">
            <div class="modal-body">
                <input type="hidden" name="action" value="assign">
                <input type="hidden" name="room_id" id="assign_room_id">
                <p style="font-size:13px;color:var(--text-secondary);margin-bottom:14px;">
                    <i class="fas fa-info-circle"></i> Doctor: <strong id="modal_doctor_name"></strong>
                    <br><small>Multiple patients can be assigned to the same doctor's room.</small>
                </p>
                <div class="form-group">
                    <label><i class="fas fa-user"></i> Select Patient</label>
                    <select name="patient_id" class="form-control" required>
                        <option value="">-- Choose Patient --</option>
                        <?php 
                        $patients->data_seek(0);
                        while ($p = $patients->fetch_assoc()): ?>
                            <option value="<?php echo $p['id']; ?>"><?php echo htmlspecialchars($p['full_name']); ?></option>
                        <?php endwhile; ?>
                    </select>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline" onclick="this.closest('.modal-overlay').classList.remove('active')">Cancel</button>
                <button type="submit" class="btn btn-success">Assign Patient</button>
            </div>
        </form>
    </div>
</div>

<!-- Assign Doctor Modal -->
<div class="modal-overlay" id="doctorAssignModal">
    <div class="modal">
        <div class="modal-header">
            <h2>Assign Doctor to Room <span id="da_room_number"></span></h2>
            <button class="modal-close" onclick="this.closest('.modal-overlay').classList.remove('active')">&times;</button>
        </div>
        <form method="POST">
            <div class="modal-body">
                <input type="hidden" name="action" value="assign_doctor">
                <input type="hidden" name="room_id" id="da_room_id">
                <div class="form-group">
                    <label><i class="fas fa-user-md"></i> Select Doctor</label>
                    <select name="doctor_id" class="form-control" required>
                        <option value="">-- Choose Doctor --</option>
                        <?php 
                        $doctorsList->data_seek(0);
                        while ($d = $doctorsList->fetch_assoc()): ?>
                            <option value="<?php echo $d['id']; ?>">Dr. <?php echo htmlspecialchars($d['full_name']); ?> - <?php echo htmlspecialchars($d['specialization']); ?></option>
                        <?php endwhile; ?>
                    </select>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline" onclick="this.closest('.modal-overlay').classList.remove('active')">Cancel</button>
                <button type="submit" class="btn btn-primary">Assign Doctor</button>
            </div>
        </form>
    </div>
</div>

<script>
function openAssignModal(roomId, roomNumber, doctorName) {
    document.getElementById('assign_room_id').value = roomId;
    document.getElementById('modal_room_number').textContent = roomNumber;
    document.getElementById('modal_doctor_name').textContent = doctorName || 'N/A';
    document.getElementById('assignModal').classList.add('active');
}

function openDoctorAssignModal(roomId, roomNumber) {
    document.getElementById('da_room_id').value = roomId;
    document.getElementById('da_room_number').textContent = roomNumber;
    document.getElementById('doctorAssignModal').classList.add('active');
}
</script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
