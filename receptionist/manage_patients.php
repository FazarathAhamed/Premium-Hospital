<?php
$pageTitle = "Manage Patients";
$roleCss = true;
require_once __DIR__ . '/../middleware/check_login.php';
require_once __DIR__ . '/../middleware/check_role.php';
checkRole(['receptionist']);

// Handle edit
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    if ($_POST['action'] === 'edit') {
        $id = (int)$_POST['patient_id'];
        $name = trim($_POST['full_name']);
        $email = trim($_POST['email']);
        $phone = trim($_POST['phone']);
        $gender = $_POST['gender'];
        $address = trim($_POST['address']);
        
        $stmt = $conn->prepare("UPDATE users SET full_name=?, email=?, phone=?, gender=?, address=? WHERE id=? AND role='patient'");
        $stmt->bind_param("sssssi", $name, $email, $phone, $gender, $address, $id);
        $stmt->execute();
        $_SESSION['success'] = "Patient updated successfully!";
        header("Location: " . APP_URL . "/receptionist/manage_patients.php");
        exit();
    }
    
    if ($_POST['action'] === 'toggle_status') {
        $id = (int)$_POST['patient_id'];
        $conn->query("UPDATE users SET is_active = NOT is_active WHERE id = $id AND role='patient'");
        $_SESSION['success'] = "Patient status updated!";
        header("Location: " . APP_URL . "/receptionist/manage_patients.php");
        exit();
    }
}

// Search functionality
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$searchCondition = '';
if ($search !== '') {
    $searchEsc = $conn->real_escape_string($search);
    $searchCondition = " AND (full_name LIKE '%$searchEsc%' OR email LIKE '%$searchEsc%' OR phone LIKE '%$searchEsc%')";
}

$patients = $conn->query("SELECT * FROM users WHERE role='patient' $searchCondition ORDER BY created_at DESC");

require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/sidebar.php';
?>

<?php if (isset($_SESSION['success'])): ?>
    <div class="alert alert-success"><i class="fas fa-check-circle"></i> <?php echo $_SESSION['success']; unset($_SESSION['success']); ?></div>
<?php endif; ?>
<?php if (isset($_SESSION['error'])): ?>
    <div class="alert alert-error"><i class="fas fa-exclamation-circle"></i> <?php echo $_SESSION['error']; unset($_SESSION['error']); ?></div>
<?php endif; ?>

<!-- Search Bar -->
<form method="GET" style="margin-bottom:20px;">
    <div style="display:flex;gap:10px;flex-wrap:wrap;">
        <input type="text" name="search" class="form-control" style="max-width:350px;" placeholder="Search by name, email, or phone..." value="<?php echo htmlspecialchars($search); ?>">
        <button type="submit" class="btn btn-primary" style="background:var(--receptionist-accent);"><i class="fas fa-search"></i> Search</button>
        <?php if ($search): ?>
            <a href="<?php echo APP_URL; ?>/receptionist/manage_patients.php" class="btn btn-outline"><i class="fas fa-times"></i> Clear</a>
        <?php endif; ?>
    </div>
</form>

<div class="card admin-table-card">
    <div class="card-header">
        <h2><i class="fas fa-users"></i> All Patients</h2>
        <a href="<?php echo APP_URL; ?>/receptionist/register_patient.php" class="btn btn-sm" style="background:var(--receptionist-accent);color:#fff;font-weight:700;">
            <i class="fas fa-plus"></i> Register New
        </a>
    </div>
    <div class="card-body">
        <?php if ($patients->num_rows > 0): ?>
        <div class="table-responsive">
            <table>
                <thead>
                    <tr><th>#</th><th>Name</th><th>Email</th><th>Phone</th><th>Gender</th><th>DOB</th><th>Status</th><th>Actions</th></tr>
                </thead>
                <tbody>
                    <?php $n=1; while ($p = $patients->fetch_assoc()): ?>
                    <tr>
                        <td data-label="#"><?php echo $n++; ?></td>
                        <td data-label="Name"><strong><?php echo htmlspecialchars($p['full_name']); ?></strong></td>
                        <td data-label="Email"><?php echo htmlspecialchars($p['email']); ?></td>
                        <td data-label="Phone"><?php echo htmlspecialchars($p['phone'] ?? '-'); ?></td>
                        <td data-label="Gender"><?php echo ucfirst($p['gender'] ?? '-'); ?></td>
                        <td data-label="DOB"><?php echo $p['date_of_birth'] ? date('M d, Y', strtotime($p['date_of_birth'])) : '-'; ?></td>
                        <td data-label="Status"><span class="badge <?php echo $p['is_active'] ? 'badge-approved' : 'badge-cancelled'; ?>"><?php echo $p['is_active'] ? 'Active' : 'Inactive'; ?></span></td>
                        <td data-label="Actions">
                            <div class="action-btns">
                                <button class="btn btn-info btn-sm" onclick='editPatient(<?php echo json_encode($p, JSON_HEX_APOS | JSON_HEX_QUOT); ?>)'><i class="fas fa-edit"></i></button>
                                <form method="POST" style="display:inline;">
                                    <input type="hidden" name="action" value="toggle_status">
                                    <input type="hidden" name="patient_id" value="<?php echo $p['id']; ?>">
                                    <button type="submit" class="btn btn-warning btn-sm"><i class="fas fa-power-off"></i></button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
        <?php else: ?>
            <div class="empty-state"><i class="fas fa-users"></i><p>No patients found</p></div>
        <?php endif; ?>
    </div>
</div>

<!-- Edit Patient Modal -->
<div class="modal-overlay" id="editPatientModal">
    <div class="modal">
        <div class="modal-header">
            <h2>Edit Patient</h2>
            <button class="modal-close" onclick="this.closest('.modal-overlay').classList.remove('active')">&times;</button>
        </div>
        <form method="POST">
            <div class="modal-body">
                <input type="hidden" name="action" value="edit">
                <input type="hidden" name="patient_id" id="ep_id">
                <div class="form-group"><label>Full Name</label><input type="text" name="full_name" id="ep_name" class="form-control" required></div>
                <div class="form-group"><label>Email</label><input type="email" name="email" id="ep_email" class="form-control" required></div>
                <div class="form-group"><label>Phone</label><input type="text" name="phone" id="ep_phone" class="form-control"></div>
                <div class="form-group"><label>Gender</label><select name="gender" id="ep_gender" class="form-control"><option value="male">Male</option><option value="female">Female</option><option value="other">Other</option></select></div>
                <div class="form-group"><label>Address</label><textarea name="address" id="ep_address" class="form-control" rows="2"></textarea></div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline" onclick="this.closest('.modal-overlay').classList.remove('active')">Cancel</button>
                <button type="submit" class="btn btn-primary">Save Changes</button>
            </div>
        </form>
    </div>
</div>

<script>
function editPatient(p) {
    document.getElementById('ep_id').value = p.id;
    document.getElementById('ep_name').value = p.full_name;
    document.getElementById('ep_email').value = p.email;
    document.getElementById('ep_phone').value = p.phone || '';
    document.getElementById('ep_gender').value = p.gender || 'male';
    document.getElementById('ep_address').value = p.address || '';
    document.getElementById('editPatientModal').classList.add('active');
}
</script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
