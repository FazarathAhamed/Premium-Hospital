<?php
$pageTitle = "Appointments";
$roleCss = true;
require_once __DIR__ . '/../middleware/check_login.php';
require_once __DIR__ . '/../middleware/check_role.php';
checkRole(['admin']);

// Handle delete
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'delete') {
    $id = (int)$_POST['appointment_id'];
    $stmt = $conn->prepare("DELETE FROM appointments WHERE id=?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $_SESSION['success'] = "Appointment deleted!";
    header("Location: " . APP_URL . "/admin/appointments.php");
    exit();
}

// Get filter
$filter = $_GET['filter'] ?? 'all';
$whereClause = "";
if ($filter === 'pending') $whereClause = " WHERE a.status = 'pending'";
elseif ($filter === 'approved') $whereClause = " WHERE a.status = 'approved'";
elseif ($filter === 'completed') $whereClause = " WHERE a.status = 'completed'";
elseif ($filter === 'cancelled') $whereClause = " WHERE a.status = 'cancelled'";

$appointments = $conn->query("
    SELECT a.*, p.full_name as patient_name, d.full_name as doctor_name, d.specialization
    FROM appointments a 
    JOIN users p ON a.patient_id = p.id 
    JOIN users d ON a.doctor_id = d.id 
    $whereClause
    ORDER BY a.appointment_date DESC, a.appointment_time DESC
");

// Count by status
$countAll = $conn->query("SELECT COUNT(*) as c FROM appointments")->fetch_assoc()['c'];
$countPending = $conn->query("SELECT COUNT(*) as c FROM appointments WHERE status='pending'")->fetch_assoc()['c'];
$countApproved = $conn->query("SELECT COUNT(*) as c FROM appointments WHERE status='approved'")->fetch_assoc()['c'];
$countCompleted = $conn->query("SELECT COUNT(*) as c FROM appointments WHERE status='completed'")->fetch_assoc()['c'];
$countCancelled = $conn->query("SELECT COUNT(*) as c FROM appointments WHERE status='cancelled'")->fetch_assoc()['c'];

require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/sidebar.php';
?>

<?php if (isset($_SESSION['success'])): ?>
    <div class="alert alert-success"><i class="fas fa-check-circle"></i> <?php echo $_SESSION['success']; unset($_SESSION['success']); ?></div>
<?php endif; ?>

<!-- Summary Stats -->
<div class="stats-grid">
    <div class="stat-card blue">
        <div class="stat-icon blue"><i class="fas fa-calendar-check"></i></div>
        <div class="stat-details"><h3><?php echo $countAll; ?></h3><p>Total</p></div>
    </div>
    <div class="stat-card orange">
        <div class="stat-icon orange"><i class="fas fa-clock"></i></div>
        <div class="stat-details"><h3><?php echo $countPending; ?></h3><p>Pending</p></div>
    </div>
    <div class="stat-card green">
        <div class="stat-icon green"><i class="fas fa-check-circle"></i></div>
        <div class="stat-details"><h3><?php echo $countApproved; ?></h3><p>Approved</p></div>
    </div>
    <div class="stat-card purple">
        <div class="stat-icon purple"><i class="fas fa-check-double"></i></div>
        <div class="stat-details"><h3><?php echo $countCompleted; ?></h3><p>Completed</p></div>
    </div>
</div>

<!-- Filter Tabs -->
<div style="display:flex; gap:8px; margin-bottom:20px; flex-wrap:wrap; overflow-x:auto; -webkit-overflow-scrolling:touch; padding-bottom:4px;">
    <a href="?filter=all" class="btn btn-sm <?php echo $filter==='all' ? 'btn-primary' : 'btn-outline'; ?>" style="border-radius:20px; padding:8px 16px;">All</a>
    <a href="?filter=pending" class="btn btn-sm <?php echo $filter==='pending' ? 'btn-primary' : 'btn-outline'; ?>" style="border-radius:20px; padding:8px 16px;">Pending (<?php echo $countPending; ?>)</a>
    <a href="?filter=approved" class="btn btn-sm <?php echo $filter==='approved' ? 'btn-primary' : 'btn-outline'; ?>" style="border-radius:20px; padding:8px 16px;">Approved (<?php echo $countApproved; ?>)</a>
    <a href="?filter=completed" class="btn btn-sm <?php echo $filter==='completed' ? 'btn-primary' : 'btn-outline'; ?>" style="border-radius:20px; padding:8px 16px;">Completed (<?php echo $countCompleted; ?>)</a>
    <a href="?filter=cancelled" class="btn btn-sm <?php echo $filter==='cancelled' ? 'btn-primary' : 'btn-outline'; ?>" style="border-radius:20px; padding:8px 16px;">Cancelled (<?php echo $countCancelled; ?>)</a>
</div>

<div class="card">
    <div class="card-header">
        <h2><i class="fas fa-calendar-alt"></i> <?php echo $filter === 'all' ? 'All' : ucfirst($filter); ?> Appointments</h2>
        <span style="font-size:12px; color:var(--text-muted);"><i class="fas fa-info-circle"></i> Read-only — Receptionists approve, Doctors complete</span>
    </div>
    <div class="card-body">
        <?php if ($appointments->num_rows > 0): ?>
        <div class="table-responsive">
            <table>
                <thead>
                    <tr><th>#</th><th>Patient</th><th>Doctor</th><th>Date</th><th>Time</th><th>Status</th><th>Notes</th><th>Action</th></tr>
                </thead>
                <tbody>
                    <?php $n=1; while ($a = $appointments->fetch_assoc()): ?>
                    <tr>
                        <td data-label="#"><?php echo $n++; ?></td>
                        <td data-label="Patient"><strong><?php echo htmlspecialchars($a['patient_name']); ?></strong></td>
                        <td data-label="Doctor"><?php echo htmlspecialchars($a['doctor_name']); ?></td>
                        <td data-label="Date"><?php echo date('M d, Y', strtotime($a['appointment_date'])); ?></td>
                        <td data-label="Time"><?php echo date('h:i A', strtotime($a['appointment_time'])); ?></td>
                        <td data-label="Status"><span class="badge badge-<?php echo $a['status']; ?>"><?php echo ucfirst($a['status']); ?></span></td>
                        <td data-label="Notes"><?php echo htmlspecialchars(substr($a['notes'] ?? '—', 0, 50)); ?></td>
                        <td data-label="Action">
                            <form method="POST" onsubmit="return confirm('Delete this appointment permanently?');">
                                <input type="hidden" name="action" value="delete">
                                <input type="hidden" name="appointment_id" value="<?php echo $a['id']; ?>">
                                <button type="submit" class="btn btn-danger btn-sm"><i class="fas fa-trash"></i></button>
                            </form>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
        <?php else: ?>
            <div class="empty-state"><i class="fas fa-calendar-times"></i><p>No appointments found</p></div>
        <?php endif; ?>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
