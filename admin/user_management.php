<?php
$pageTitle = "User Management";
$roleCss = true;
require_once __DIR__ . '/../middleware/check_login.php';
require_once __DIR__ . '/../middleware/check_role.php';
checkRole(['admin']);

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    if ($_POST['action'] === 'change_password') {
        $userId = (int)$_POST['user_id'];
        $newPassword = trim($_POST['new_password']);
        
        if (strlen($newPassword) < 6) {
            $_SESSION['error'] = "Password must be at least 6 characters long.";
        } else {
            $hashed = password_hash($newPassword, PASSWORD_DEFAULT);
            $stmt = $conn->prepare("UPDATE users SET password=? WHERE id=?");
            $stmt->bind_param("si", $hashed, $userId);
            $stmt->execute();
            $_SESSION['success'] = "Password changed successfully!";
        }
        header("Location: " . APP_URL . "/admin/user_management.php");
        exit();
    }
    
    if ($_POST['action'] === 'change_role') {
        $userId = (int)$_POST['user_id'];
        $newRole = $_POST['new_role'];
        
        // Don't allow changing your own role
        if ($userId == $_SESSION['user_id']) {
            $_SESSION['error'] = "You cannot change your own role!";
        } else {
            // Only allow changing between admin and receptionist
            $allowedRoles = ['admin', 'receptionist'];
            if (in_array($newRole, $allowedRoles)) {
                $stmt = $conn->prepare("UPDATE users SET role=? WHERE id=? AND role IN ('admin','receptionist')");
                $stmt->bind_param("si", $newRole, $userId);
                $stmt->execute();
                if ($stmt->affected_rows > 0) {
                    $_SESSION['success'] = "User role changed to " . ucfirst($newRole) . "!";
                } else {
                    $_SESSION['error'] = "Role can only be changed for admin/receptionist accounts.";
                }
            } else {
                $_SESSION['error'] = "Invalid role selected.";
            }
        }
        header("Location: " . APP_URL . "/admin/user_management.php");
        exit();
    }
}

// Search functionality
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$roleFilter = isset($_GET['role']) ? trim($_GET['role']) : '';
$conditions = "WHERE 1=1";
if ($search !== '') {
    $searchEsc = $conn->real_escape_string($search);
    $conditions .= " AND (full_name LIKE '%$searchEsc%' OR email LIKE '%$searchEsc%')";
}
if ($roleFilter !== '') {
    $conditions .= " AND role = '" . $conn->real_escape_string($roleFilter) . "'";
}

$users = $conn->query("SELECT * FROM users $conditions ORDER BY role ASC, full_name ASC");

require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/sidebar.php';
?>

<?php if (isset($_SESSION['success'])): ?>
    <div class="alert alert-success"><i class="fas fa-check-circle"></i> <?php echo $_SESSION['success']; unset($_SESSION['success']); ?></div>
<?php endif; ?>
<?php if (isset($_SESSION['error'])): ?>
    <div class="alert alert-error"><i class="fas fa-exclamation-circle"></i> <?php echo $_SESSION['error']; unset($_SESSION['error']); ?></div>
<?php endif; ?>

<!-- Search & Filter -->
<div class="card" style="margin-bottom:20px;">
    <div class="card-body" style="padding:16px 24px;">
        <form method="GET" style="display:flex;gap:10px;flex-wrap:wrap;align-items:center;">
            <input type="text" name="search" class="form-control" style="max-width:280px;" placeholder="Search by name or email..." value="<?php echo htmlspecialchars($search); ?>">
            <select name="role" class="form-control" style="max-width:180px;">
                <option value="">All Roles</option>
                <option value="admin" <?php echo $roleFilter === 'admin' ? 'selected' : ''; ?>>Admin</option>
                <option value="receptionist" <?php echo $roleFilter === 'receptionist' ? 'selected' : ''; ?>>Receptionist</option>
                <option value="doctor" <?php echo $roleFilter === 'doctor' ? 'selected' : ''; ?>>Doctor</option>
                <option value="patient" <?php echo $roleFilter === 'patient' ? 'selected' : ''; ?>>Patient</option>
            </select>
            <button type="submit" class="btn btn-primary btn-sm"><i class="fas fa-search"></i> Search</button>
            <?php if ($search || $roleFilter): ?>
                <a href="<?php echo APP_URL; ?>/admin/user_management.php" class="btn btn-outline btn-sm"><i class="fas fa-times"></i> Clear</a>
            <?php endif; ?>
        </form>
    </div>
</div>

<div class="card admin-table-card">
    <div class="card-header">
        <h2><i class="fas fa-users-cog"></i> User Accounts</h2>
        <span class="badge badge-approved"><?php echo $users->num_rows; ?> Users</span>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table>
                <thead>
                    <tr><th>#</th><th>Name</th><th>Email</th><th>Role</th><th>Status</th><th>Change Password</th><th>Change Role</th></tr>
                </thead>
                <tbody>
                    <?php $n=1; while ($u = $users->fetch_assoc()): ?>
                    <tr>
                        <td data-label="#"><?php echo $n++; ?></td>
                        <td data-label="Name">
                            <strong><?php echo htmlspecialchars($u['full_name']); ?></strong>
                            <?php if ($u['id'] == $_SESSION['user_id']): ?>
                                <span class="badge badge-completed" style="font-size:10px;padding:2px 6px;">You</span>
                            <?php endif; ?>
                        </td>
                        <td data-label="Email"><?php echo htmlspecialchars($u['email']); ?></td>
                        <td data-label="Role">
                            <span class="badge <?php 
                                echo match($u['role']) {
                                    'admin' => 'badge-completed',
                                    'doctor' => 'badge-approved',
                                    'receptionist' => 'badge-pending',
                                    'patient' => 'badge-available',
                                    default => ''
                                };
                            ?>"><?php echo ucfirst($u['role']); ?></span>
                        </td>
                        <td data-label="Status">
                            <span class="badge <?php echo $u['is_active'] ? 'badge-approved' : 'badge-cancelled'; ?>">
                                <?php echo $u['is_active'] ? 'Active' : 'Inactive'; ?>
                            </span>
                        </td>
                        <td data-label="Change Password">
                            <button class="btn btn-info btn-sm" onclick="openPasswordModal(<?php echo $u['id']; ?>, <?php echo htmlspecialchars(json_encode($u['full_name']), ENT_QUOTES); ?>)">
                                <i class="fas fa-key"></i> Change
                            </button>
                        </td>
                        <td data-label="Change Role">
                            <?php if (in_array($u['role'], ['admin', 'receptionist']) && $u['id'] != $_SESSION['user_id']): ?>
                            <form method="POST" style="display:inline;" onsubmit="return confirm(<?php echo htmlspecialchars(json_encode('Change role for ' . $u['full_name'] . '?'), ENT_QUOTES); ?>);">
                                <input type="hidden" name="action" value="change_role">
                                <input type="hidden" name="user_id" value="<?php echo $u['id']; ?>">
                                <select name="new_role" class="form-control" style="width:auto;padding:4px 8px;font-size:12px;display:inline-block;" onchange="this.form.submit()">
                                    <option value="receptionist" <?php echo $u['role'] === 'receptionist' ? 'selected' : ''; ?>>Receptionist</option>
                                    <option value="admin" <?php echo $u['role'] === 'admin' ? 'selected' : ''; ?>>Admin</option>
                                </select>
                            </form>
                            <?php else: ?>
                                <span style="font-size:12px;color:var(--text-muted);">—</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Change Password Modal -->
<div class="modal-overlay" id="passwordModal">
    <div class="modal">
        <div class="modal-header">
            <h2>Change Password</h2>
            <button class="modal-close" onclick="this.closest('.modal-overlay').classList.remove('active')">&times;</button>
        </div>
        <form method="POST">
            <div class="modal-body">
                <input type="hidden" name="action" value="change_password">
                <input type="hidden" name="user_id" id="pw_user_id">
                <p style="margin-bottom:16px;font-size:14px;color:var(--text-secondary);">Change password for: <strong id="pw_user_name"></strong></p>
                <div class="form-group">
                    <label><i class="fas fa-lock"></i> New Password</label>
                    <input type="password" name="new_password" class="form-control" placeholder="Enter new password" required minlength="6">
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline" onclick="this.closest('.modal-overlay').classList.remove('active')">Cancel</button>
                <button type="submit" class="btn btn-primary">Change Password</button>
            </div>
        </form>
    </div>
</div>

<script>
function openPasswordModal(userId, userName) {
    document.getElementById('pw_user_id').value = userId;
    document.getElementById('pw_user_name').textContent = userName;
    document.getElementById('passwordModal').classList.add('active');
}
</script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
