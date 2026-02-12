<?php
$pageTitle = "Manage Patients";
$roleCss = true;
require_once __DIR__ . '/../middleware/check_login.php';
require_once __DIR__ . '/../middleware/check_role.php';
checkRole(['admin']);

// Handle Delete
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    if ($_POST['action'] === 'delete') {
        $id = (int)$_POST['patient_id'];
        $stmt = $conn->prepare("DELETE FROM users WHERE id=? AND role='patient'");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $_SESSION['success'] = "Patient removed successfully!";
        header("Location: " . APP_URL . "/admin/manage_patients.php");
        exit();
    }
    if ($_POST['action'] === 'toggle_status') {
        $id = (int)$_POST['patient_id'];
        $conn->query("UPDATE users SET is_active = NOT is_active WHERE id = $id AND role='patient'");
        $_SESSION['success'] = "Patient status updated!";
        header("Location: " . APP_URL . "/admin/manage_patients.php");
        exit();
    }
}

$patients = $conn->query("SELECT * FROM users WHERE role='patient' ORDER BY created_at DESC");

require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/sidebar.php';
?>

<?php if (isset($_SESSION['success'])): ?>
    <div class="alert alert-success"><i class="fas fa-check-circle"></i> <?php echo $_SESSION['success']; unset($_SESSION['success']); ?></div>
<?php endif; ?>

<div class="card admin-table-card">
    <div class="card-header">
        <h2><i class="fas fa-users"></i> All Patients</h2>
        <span class="badge badge-approved"><?php echo $patients->num_rows; ?> Total</span>
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
                        <th>Gender</th>
                        <th>DOB</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php $n=1; while ($patient = $patients->fetch_assoc()): ?>
                    <tr>
                        <td data-label="#"><?php echo $n++; ?></td>
                        <td data-label="Name"><strong><?php echo htmlspecialchars($patient['full_name']); ?></strong></td>
                        <td data-label="Email"><span style="word-break:break-all;"><?php echo htmlspecialchars($patient['email']); ?></span></td>
                        <td data-label="Phone"><?php echo htmlspecialchars($patient['phone'] ?? '-'); ?></td>
                        <td data-label="Gender"><?php echo ucfirst($patient['gender'] ?? '-'); ?></td>
                        <td data-label="DOB"><?php echo $patient['date_of_birth'] ? date('M d, Y', strtotime($patient['date_of_birth'])) : '-'; ?></td>
                        <td data-label="Status">
                            <span class="badge <?php echo $patient['is_active'] ? 'badge-approved' : 'badge-cancelled'; ?>">
                                <?php echo $patient['is_active'] ? 'Active' : 'Inactive'; ?>
                            </span>
                        </td>
                        <td data-label="Actions">
                            <div class="action-btns">
                                <form method="POST" style="display:inline;">
                                    <input type="hidden" name="action" value="toggle_status">
                                    <input type="hidden" name="patient_id" value="<?php echo $patient['id']; ?>">
                                    <button type="submit" class="btn btn-warning btn-sm" title="Toggle Status">
                                        <i class="fas fa-power-off"></i>
                                    </button>
                                </form>
                                <form method="POST" style="display:inline;" onsubmit="return confirm('Delete this patient?');">
                                    <input type="hidden" name="action" value="delete">
                                    <input type="hidden" name="patient_id" value="<?php echo $patient['id']; ?>">
                                    <button type="submit" class="btn btn-danger btn-sm">
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

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
