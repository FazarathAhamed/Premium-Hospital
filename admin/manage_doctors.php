<?php
$pageTitle = "Manage Doctors";
$roleCss = true;
require_once __DIR__ . '/../middleware/check_login.php';
require_once __DIR__ . '/../middleware/check_role.php';
checkRole(['admin']);

$allDays = ['Monday','Tuesday','Wednesday','Thursday','Friday','Saturday','Sunday'];

// Handle Add Doctor
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    if ($_POST['action'] === 'add') {
        $name = trim($_POST['full_name']);
        $email = trim($_POST['email']);
        $phone = trim($_POST['phone']);
        $gender = $_POST['gender'];
        $specialization = trim($_POST['specialization']);
        $consultingHours = trim($_POST['consulting_hours']);
        $consultingDays = isset($_POST['consulting_days']) ? implode(',', $_POST['consulting_days']) : '';
        $consultingFee = (float)$_POST['consulting_fee'];
        $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
        
        $check = $conn->prepare("SELECT id FROM users WHERE email = ?");
        $check->bind_param("s", $email);
        $check->execute();
        if ($check->get_result()->num_rows > 0) {
            $_SESSION['error'] = "Email already exists.";
        } else {
            $stmt = $conn->prepare("INSERT INTO users (full_name, email, password, role, phone, gender, specialization, consulting_hours, consulting_days, consulting_fee) VALUES (?, ?, ?, 'doctor', ?, ?, ?, ?, ?, ?)");
            $stmt->bind_param("ssssssssd", $name, $email, $password, $phone, $gender, $specialization, $consultingHours, $consultingDays, $consultingFee);
            $stmt->execute();
            $_SESSION['success'] = "Doctor added successfully!";
        }
        header("Location: " . APP_URL . "/admin/manage_doctors.php");
        exit();
    }
    
    if ($_POST['action'] === 'edit') {
        $id = (int)$_POST['doctor_id'];
        $name = trim($_POST['full_name']);
        $email = trim($_POST['email']);
        $phone = trim($_POST['phone']);
        $gender = $_POST['gender'];
        $specialization = trim($_POST['specialization']);
        $consultingHours = trim($_POST['consulting_hours']);
        $consultingDays = isset($_POST['consulting_days']) ? implode(',', $_POST['consulting_days']) : '';
        $consultingFee = (float)$_POST['consulting_fee'];
        
        $stmt = $conn->prepare("UPDATE users SET full_name=?, email=?, phone=?, gender=?, specialization=?, consulting_hours=?, consulting_days=?, consulting_fee=? WHERE id=? AND role='doctor'");
        $stmt->bind_param("sssssssdi", $name, $email, $phone, $gender, $specialization, $consultingHours, $consultingDays, $consultingFee, $id);
        $stmt->execute();
        $_SESSION['success'] = "Doctor updated successfully!";
        header("Location: " . APP_URL . "/admin/manage_doctors.php");
        exit();
    }
    
    if ($_POST['action'] === 'delete') {
        $id = (int)$_POST['doctor_id'];
        $stmt = $conn->prepare("DELETE FROM users WHERE id=? AND role='doctor'");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $_SESSION['success'] = "Doctor removed successfully!";
        header("Location: " . APP_URL . "/admin/manage_doctors.php");
        exit();
    }
    
    if ($_POST['action'] === 'toggle_status') {
        $id = (int)$_POST['doctor_id'];
        $conn->query("UPDATE users SET is_active = NOT is_active WHERE id = $id AND role='doctor'");
        $_SESSION['success'] = "Doctor status updated!";
        header("Location: " . APP_URL . "/admin/manage_doctors.php");
        exit();
    }
}

$doctors = $conn->query("SELECT * FROM users WHERE role='doctor' ORDER BY created_at DESC");

require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/sidebar.php';
?>

<?php if (isset($_SESSION['success'])): ?>
    <div class="alert alert-success"><i class="fas fa-check-circle"></i> <?php echo $_SESSION['success']; unset($_SESSION['success']); ?></div>
<?php endif; ?>
<?php if (isset($_SESSION['error'])): ?>
    <div class="alert alert-error"><i class="fas fa-exclamation-circle"></i> <?php echo $_SESSION['error']; unset($_SESSION['error']); ?></div>
<?php endif; ?>

<div class="card admin-table-card">
    <div class="card-header">
        <h2><i class="fas fa-user-md"></i> All Doctors</h2>
        <button class="btn btn-sm" onclick="document.getElementById('addDoctorModal').classList.add('active')" 
                style="background:#c8a951;color:#1a1f3d;font-weight:700;">
            <i class="fas fa-plus"></i> Add Doctor
        </button>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table>
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Phone</th>
                        <th>Specialization</th>
                        <th>Hours</th>
                        <th>Days</th>
                        <th>Fee</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php $n=1; while ($doc = $doctors->fetch_assoc()): ?>
                    <tr>
                        <td data-label="#"><?php echo $n++; ?></td>
                        <td data-label="Name"><strong><?php echo htmlspecialchars($doc['full_name']); ?></strong></td>
                        <td data-label="Email"><span style="word-break:break-all;"><?php echo htmlspecialchars($doc['email']); ?></span></td>
                        <td data-label="Phone"><?php echo htmlspecialchars($doc['phone']); ?></td>
                        <td data-label="Specialization"><?php echo htmlspecialchars($doc['specialization']); ?></td>
                        <td data-label="Hours"><?php echo htmlspecialchars($doc['consulting_hours'] ?? 'Not set'); ?></td>
                        <td data-label="Days">
                            <?php 
                            $days = $doc['consulting_days'] ?? '';
                            if ($days) {
                                $dayArr = explode(',', $days);
                                $shortDays = array_map(function($d) { return substr(trim($d), 0, 3); }, $dayArr);
                                echo '<span style="font-size:12px;">' . implode(', ', $shortDays) . '</span>';
                            } else {
                                echo '<span style="color:var(--text-muted);">Not set</span>';
                            }
                            ?>
                        </td>
                        <td data-label="Fee"><strong>$<?php echo number_format($doc['consulting_fee'] ?? 0, 2); ?></strong></td>
                        <td data-label="Status">
                            <span class="badge <?php echo $doc['is_active'] ? 'badge-approved' : 'badge-cancelled'; ?>">
                                <?php echo $doc['is_active'] ? 'Active' : 'Inactive'; ?>
                            </span>
                        </td>
                        <td data-label="Actions">
                            <div class="action-btns">
                                <button class="btn btn-info btn-sm" onclick='editDoctor(<?php echo json_encode($doc, JSON_HEX_APOS | JSON_HEX_QUOT); ?>)' title="Edit">
                                    <i class="fas fa-edit"></i>
                                </button>
                                <form method="POST" style="display:inline;">
                                    <input type="hidden" name="action" value="toggle_status">
                                    <input type="hidden" name="doctor_id" value="<?php echo $doc['id']; ?>">
                                    <button type="submit" class="btn btn-warning btn-sm" title="Toggle Status">
                                        <i class="fas fa-power-off"></i>
                                    </button>
                                </form>
                                <form method="POST" style="display:inline;" onsubmit="return confirm('Delete this doctor?');">
                                    <input type="hidden" name="action" value="delete">
                                    <input type="hidden" name="doctor_id" value="<?php echo $doc['id']; ?>">
                                    <button type="submit" class="btn btn-danger btn-sm" title="Delete">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Add Doctor Modal -->
<div class="modal-overlay" id="addDoctorModal">
    <div class="modal" style="max-width:560px;">
        <div class="modal-header">
            <h2><i class="fas fa-user-plus"></i> Add New Doctor</h2>
            <button class="modal-close" onclick="this.closest('.modal-overlay').classList.remove('active')">&times;</button>
        </div>
        <form method="POST">
            <div class="modal-body">
                <input type="hidden" name="action" value="add">
                <div class="form-row">
                    <div class="form-group">
                        <label><i class="fas fa-user"></i> Full Name</label>
                        <input type="text" name="full_name" class="form-control" required placeholder="Dr. John Doe">
                    </div>
                    <div class="form-group">
                        <label><i class="fas fa-envelope"></i> Email</label>
                        <input type="email" name="email" class="form-control" placeholder="doctor@healthylife.com" required>
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label><i class="fas fa-lock"></i> Password</label>
                        <input type="password" name="password" class="form-control" placeholder="Set initial password" required minlength="6">
                    </div>
                    <div class="form-group">
                        <label><i class="fas fa-phone"></i> Phone</label>
                        <input type="text" name="phone" class="form-control" placeholder="+1 234 567 890">
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label><i class="fas fa-venus-mars"></i> Gender</label>
                        <select name="gender" class="form-control">
                            <option value="male">Male</option>
                            <option value="female">Female</option>
                            <option value="other">Other</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label><i class="fas fa-briefcase-medical"></i> Specialization</label>
                        <input type="text" name="specialization" class="form-control" required placeholder="e.g. Cardiology">
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label><i class="fas fa-clock"></i> Consulting Hours</label>
                        <input type="text" name="consulting_hours" class="form-control" placeholder="e.g. 09:00 AM - 05:00 PM">
                    </div>
                    <div class="form-group">
                        <label><i class="fas fa-dollar-sign"></i> Consulting Fee ($)</label>
                        <input type="number" name="consulting_fee" class="form-control" step="0.01" min="0" placeholder="0.00">
                    </div>
                </div>
                <div class="form-group">
                    <label><i class="fas fa-calendar-week"></i> Available Days</label>
                    <div class="day-checkbox-group">
                        <?php foreach ($allDays as $day): ?>
                            <label>
                                <input type="checkbox" name="consulting_days[]" value="<?php echo $day; ?>" <?php echo in_array($day, ['Monday','Tuesday','Wednesday','Thursday','Friday']) ? 'checked' : ''; ?>>
                                <?php echo substr($day, 0, 3); ?>
                            </label>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline" onclick="this.closest('.modal-overlay').classList.remove('active')">Cancel</button>
                <button type="submit" class="btn btn-primary"><i class="fas fa-check"></i> Add Doctor</button>
            </div>
        </form>
    </div>
</div>

<!-- Edit Doctor Modal -->
<div class="modal-overlay" id="editDoctorModal">
    <div class="modal" style="max-width:560px;">
        <div class="modal-header">
            <h2><i class="fas fa-edit"></i> Edit Doctor</h2>
            <button class="modal-close" onclick="this.closest('.modal-overlay').classList.remove('active')">&times;</button>
        </div>
        <form method="POST">
            <div class="modal-body">
                <input type="hidden" name="action" value="edit">
                <input type="hidden" name="doctor_id" id="edit_doctor_id">
                <div class="form-row">
                    <div class="form-group">
                        <label><i class="fas fa-user"></i> Full Name</label>
                        <input type="text" name="full_name" id="edit_full_name" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label><i class="fas fa-envelope"></i> Email</label>
                        <input type="email" name="email" id="edit_email" class="form-control" required>
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label><i class="fas fa-phone"></i> Phone</label>
                        <input type="text" name="phone" id="edit_phone" class="form-control">
                    </div>
                    <div class="form-group">
                        <label><i class="fas fa-venus-mars"></i> Gender</label>
                        <select name="gender" id="edit_gender" class="form-control">
                            <option value="male">Male</option>
                            <option value="female">Female</option>
                            <option value="other">Other</option>
                        </select>
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label><i class="fas fa-briefcase-medical"></i> Specialization</label>
                        <input type="text" name="specialization" id="edit_specialization" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label><i class="fas fa-dollar-sign"></i> Consulting Fee ($)</label>
                        <input type="number" name="consulting_fee" id="edit_consulting_fee" class="form-control" step="0.01" min="0">
                    </div>
                </div>
                <div class="form-group">
                    <label><i class="fas fa-clock"></i> Consulting Hours</label>
                    <input type="text" name="consulting_hours" id="edit_consulting_hours" class="form-control" placeholder="e.g. 09:00 AM - 05:00 PM">
                </div>
                <div class="form-group">
                    <label><i class="fas fa-calendar-week"></i> Available Days</label>
                    <div class="day-checkbox-group" id="edit_days_container">
                        <?php foreach ($allDays as $day): ?>
                            <label>
                                <input type="checkbox" name="consulting_days[]" value="<?php echo $day; ?>" id="edit_day_<?php echo strtolower($day); ?>">
                                <?php echo substr($day, 0, 3); ?>
                            </label>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline" onclick="this.closest('.modal-overlay').classList.remove('active')">Cancel</button>
                <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Save Changes</button>
            </div>
        </form>
    </div>
</div>

<script>
function editDoctor(doc) {
    document.getElementById('edit_doctor_id').value = doc.id;
    document.getElementById('edit_full_name').value = doc.full_name;
    document.getElementById('edit_email').value = doc.email;
    document.getElementById('edit_phone').value = doc.phone || '';
    document.getElementById('edit_gender').value = doc.gender || 'male';
    document.getElementById('edit_specialization').value = doc.specialization || '';
    document.getElementById('edit_consulting_hours').value = doc.consulting_hours || '';
    document.getElementById('edit_consulting_fee').value = doc.consulting_fee || '';
    
    // Set consulting days checkboxes
    var days = ['monday','tuesday','wednesday','thursday','friday','saturday','sunday'];
    var doctorDays = (doc.consulting_days || '').split(',').map(function(d) { return d.trim().toLowerCase(); });
    days.forEach(function(day) {
        var checkbox = document.getElementById('edit_day_' + day);
        if (checkbox) {
            checkbox.checked = doctorDays.includes(day);
        }
    });
    
    document.getElementById('editDoctorModal').classList.add('active');
}
</script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
