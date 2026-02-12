<?php
$pageTitle = "Manage Staff";
$roleCss = true;
require_once __DIR__ . '/../middleware/check_login.php';
require_once __DIR__ . '/../middleware/check_role.php';
checkRole(['admin']);

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    if ($_POST['action'] === 'add') {
        $name = trim($_POST['full_name']);
        $email = trim($_POST['email']);
        $phone = trim($_POST['phone']);
        $gender = $_POST['gender'];
        $role = $_POST['staff_role'];
        $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
        
        $check = $conn->prepare("SELECT id FROM users WHERE email = ?");
        $check->bind_param("s", $email);
        $check->execute();
        if ($check->get_result()->num_rows > 0) {
            $_SESSION['error'] = "Email already exists.";
        } else {
            $stmt = $conn->prepare("INSERT INTO users (full_name, email, password, role, phone, gender) VALUES (?, ?, ?, ?, ?, ?)");
            $stmt->bind_param("ssssss", $name, $email, $password, $role, $phone, $gender);
            $stmt->execute();
            $_SESSION['success'] = ucfirst($role) . " added successfully!";
        }
        header("Location: " . APP_URL . "/admin/manage_staff.php");
        exit();
    }
    
    if ($_POST['action'] === 'edit') {
        $id = (int)$_POST['staff_id'];
        $name = trim($_POST['full_name']);
        $email = trim($_POST['email']);
        $phone = trim($_POST['phone']);
        $gender = $_POST['gender'];
        
        $stmt = $conn->prepare("UPDATE users SET full_name=?, email=?, phone=?, gender=? WHERE id=? AND role IN ('receptionist','admin')");
        $stmt->bind_param("ssssi", $name, $email, $phone, $gender, $id);
        $stmt->execute();
        $_SESSION['success'] = "Staff updated successfully!";
        header("Location: " . APP_URL . "/admin/manage_staff.php");
        exit();
    }
    
    if ($_POST['action'] === 'delete') {
        $id = (int)$_POST['staff_id'];
        // Don't allow deleting yourself
        if ($id == $_SESSION['user_id']) {
            $_SESSION['error'] = "You cannot delete your own account!";
        } else {
            $stmt = $conn->prepare("DELETE FROM users WHERE id=? AND role IN ('receptionist','admin')");
            $stmt->bind_param("i", $id);
            $stmt->execute();
            $_SESSION['success'] = "Staff removed successfully!";
        }
        header("Location: " . APP_URL . "/admin/manage_staff.php");
        exit();
    }
    
    if ($_POST['action'] === 'toggle_status') {
        $id = (int)$_POST['staff_id'];
        if ($id == $_SESSION['user_id']) {
            $_SESSION['error'] = "You cannot deactivate your own account!";
        } else {
            $conn->query("UPDATE users SET is_active = NOT is_active WHERE id = $id AND role IN ('receptionist','admin')");
            $_SESSION['success'] = "Staff status updated!";
        }
        header("Location: " . APP_URL . "/admin/manage_staff.php");
        exit();
    }
}

// Both admins and receptionists
$staff = $conn->query("SELECT * FROM users WHERE role IN ('receptionist','admin') ORDER BY role ASC, created_at DESC");

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
        <h2><i class="fas fa-user-tie"></i> Staff Members (Admins & Receptionists)</h2>
        <button class="btn btn-sm" onclick="document.getElementById('addStaffModal').classList.add('active')" style="background:#c8a951;color:#1a1f3d;font-weight:700;">
            <i class="fas fa-plus"></i> Add Staff
        </button>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table>
                <thead>
                    <tr><th>#</th><th>Name</th><th>Email</th><th>Phone</th><th>Gender</th><th>Role</th><th>Status</th><th>Actions</th></tr>
                </thead>
                <tbody>
                    <?php $n=1; while ($s = $staff->fetch_assoc()): ?>
                    <tr>
                        <td data-label="#"><?php echo $n++; ?></td>
                        <td data-label="Name">
                            <strong><?php echo htmlspecialchars($s['full_name']); ?></strong>
                            <?php if ($s['id'] == $_SESSION['user_id']): ?>
                                <span class="badge badge-completed" style="font-size:10px;padding:2px 6px;">You</span>
                            <?php endif; ?>
                        </td>
                        <td data-label="Email"><?php echo htmlspecialchars($s['email']); ?></td>
                        <td data-label="Phone"><?php echo htmlspecialchars($s['phone'] ?? '-'); ?></td>
                        <td data-label="Gender"><?php echo ucfirst($s['gender'] ?? '-'); ?></td>
                        <td data-label="Role">
                            <span class="badge <?php echo $s['role'] === 'admin' ? 'badge-completed' : 'badge-pending'; ?>">
                                <?php echo ucfirst($s['role']); ?>
                            </span>
                        </td>
                        <td data-label="Status"><span class="badge <?php echo $s['is_active'] ? 'badge-approved' : 'badge-cancelled'; ?>"><?php echo $s['is_active'] ? 'Active' : 'Inactive'; ?></span></td>
                        <td data-label="Actions">
                            <div class="action-btns">
                                <button class="btn btn-info btn-sm" onclick='editStaff(<?php echo json_encode($s, JSON_HEX_APOS | JSON_HEX_QUOT); ?>)'><i class="fas fa-edit"></i></button>
                                <?php if ($s['id'] != $_SESSION['user_id']): ?>
                                <form method="POST" style="display:inline;">
                                    <input type="hidden" name="action" value="toggle_status">
                                    <input type="hidden" name="staff_id" value="<?php echo $s['id']; ?>">
                                    <button type="submit" class="btn btn-warning btn-sm"><i class="fas fa-power-off"></i></button>
                                </form>
                                <form method="POST" style="display:inline;" onsubmit="return confirm('Delete this staff member?');">
                                    <input type="hidden" name="action" value="delete">
                                    <input type="hidden" name="staff_id" value="<?php echo $s['id']; ?>">
                                    <button type="submit" class="btn btn-danger btn-sm"><i class="fas fa-trash"></i></button>
                                </form>
                                <?php endif; ?>
                            </div>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Add Staff Modal -->
<div class="modal-overlay" id="addStaffModal">
    <div class="modal">
        <div class="modal-header">
            <h2>Add Staff Member</h2>
            <button class="modal-close" onclick="this.closest('.modal-overlay').classList.remove('active')">&times;</button>
        </div>
        <form method="POST">
            <div class="modal-body">
                <input type="hidden" name="action" value="add">
                <div class="form-group"><label>Full Name</label><input type="text" name="full_name" class="form-control" required></div>
                <div class="form-group"><label>Email</label><input type="email" name="email" class="form-control" placeholder="staff@healthylife.com" required></div>
                <div class="form-group"><label><i class="fas fa-lock"></i> Password</label><input type="password" name="password" class="form-control" placeholder="Set initial password" required minlength="6"></div>
                <div class="form-group"><label>Phone</label><input type="text" name="phone" class="form-control"></div>
                <div class="form-group"><label>Gender</label><select name="gender" class="form-control"><option value="male">Male</option><option value="female">Female</option><option value="other">Other</option></select></div>
                <div class="form-group">
                    <label><i class="fas fa-user-tag"></i> Role</label>
                    <select name="staff_role" class="form-control" required>
                        <option value="receptionist">Receptionist</option>
                        <option value="admin">Admin</option>
                    </select>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline" onclick="this.closest('.modal-overlay').classList.remove('active')">Cancel</button>
                <button type="submit" class="btn btn-primary">Add Staff</button>
            </div>
        </form>
    </div>
</div>

<!-- Edit Staff Modal -->
<div class="modal-overlay" id="editStaffModal">
    <div class="modal">
        <div class="modal-header">
            <h2>Edit Staff</h2>
            <button class="modal-close" onclick="this.closest('.modal-overlay').classList.remove('active')">&times;</button>
        </div>
        <form method="POST">
            <div class="modal-body">
                <input type="hidden" name="action" value="edit">
                <input type="hidden" name="staff_id" id="es_id">
                <div class="form-group"><label>Full Name</label><input type="text" name="full_name" id="es_name" class="form-control" required></div>
                <div class="form-group"><label>Email</label><input type="email" name="email" id="es_email" class="form-control" required></div>
                <div class="form-group"><label>Phone</label><input type="text" name="phone" id="es_phone" class="form-control"></div>
                <div class="form-group"><label>Gender</label><select name="gender" id="es_gender" class="form-control"><option value="male">Male</option><option value="female">Female</option><option value="other">Other</option></select></div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline" onclick="this.closest('.modal-overlay').classList.remove('active')">Cancel</button>
                <button type="submit" class="btn btn-primary">Save Changes</button>
            </div>
        </form>
    </div>
</div>

<script>
function editStaff(s) {
    document.getElementById('es_id').value = s.id;
    document.getElementById('es_name').value = s.full_name;
    document.getElementById('es_email').value = s.email;
    document.getElementById('es_phone').value = s.phone || '';
    document.getElementById('es_gender').value = s.gender || 'male';
    document.getElementById('editStaffModal').classList.add('active');
}
</script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
