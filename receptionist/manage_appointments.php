<?php
$pageTitle = "Manage Appointments";
$roleCss = true;
require_once __DIR__ . '/../middleware/check_login.php';
require_once __DIR__ . '/../middleware/check_role.php';
checkRole(['receptionist']);

// Handle status update - Receptionist can only approve or cancel
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    if ($_POST['action'] === 'approve') {
        $id = (int)$_POST['appointment_id'];
        $stmt = $conn->prepare("UPDATE appointments SET status='approved' WHERE id=? AND status='pending'");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $_SESSION['success'] = "Appointment approved!";
        header("Location: " . APP_URL . "/receptionist/manage_appointments.php");
        exit();
    }
    if ($_POST['action'] === 'cancel') {
        $id = (int)$_POST['appointment_id'];
        $stmt = $conn->prepare("UPDATE appointments SET status='cancelled' WHERE id=?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $_SESSION['success'] = "Appointment cancelled!";
        header("Location: " . APP_URL . "/receptionist/manage_appointments.php");
        exit();
    }
}

// Get filter
$filter = $_GET['filter'] ?? 'all';
$whereClause = "";
if ($filter === 'pending') $whereClause = " AND a.status = 'pending'";
elseif ($filter === 'approved') $whereClause = " AND a.status = 'approved'";
elseif ($filter === 'completed') $whereClause = " AND a.status = 'completed'";
elseif ($filter === 'cancelled') $whereClause = " AND a.status = 'cancelled'";

$appointments = $conn->query("
    SELECT a.*, p.full_name as patient_name, p.phone as patient_phone, d.full_name as doctor_name, d.specialization
    FROM appointments a 
    JOIN users p ON a.patient_id = p.id 
    JOIN users d ON a.doctor_id = d.id 
    WHERE 1=1 $whereClause
    ORDER BY a.appointment_date DESC, a.appointment_time DESC
");

// Count by status
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

<!-- Status Filter Tabs -->
<div style="display:flex; gap:8px; margin-bottom:20px; flex-wrap:wrap; overflow-x:auto; -webkit-overflow-scrolling:touch; padding-bottom:4px;">
    <a href="?filter=all" class="btn btn-sm <?php echo $filter==='all' ? 'btn-primary' : 'btn-outline'; ?>" style="border-radius:20px; padding:8px 16px;">
        <i class="fas fa-list"></i> All
    </a>
    <a href="?filter=pending" class="btn btn-sm <?php echo $filter==='pending' ? 'btn-primary' : 'btn-outline'; ?>" style="border-radius:20px; padding:8px 16px;">
        <i class="fas fa-clock"></i> Pending <span class="badge badge-pending" style="margin-left:4px;"><?php echo $countPending; ?></span>
    </a>
    <a href="?filter=approved" class="btn btn-sm <?php echo $filter==='approved' ? 'btn-primary' : 'btn-outline'; ?>" style="border-radius:20px; padding:8px 16px;">
        <i class="fas fa-check"></i> Approved <span class="badge badge-approved" style="margin-left:4px;"><?php echo $countApproved; ?></span>
    </a>
    <a href="?filter=completed" class="btn btn-sm <?php echo $filter==='completed' ? 'btn-primary' : 'btn-outline'; ?>" style="border-radius:20px; padding:8px 16px;">
        <i class="fas fa-check-double"></i> Done <span class="badge badge-completed" style="margin-left:4px;"><?php echo $countCompleted; ?></span>
    </a>
    <a href="?filter=cancelled" class="btn btn-sm <?php echo $filter==='cancelled' ? 'btn-primary' : 'btn-outline'; ?>" style="border-radius:20px; padding:8px 16px;">
        <i class="fas fa-ban"></i> Cancel <span class="badge badge-cancelled" style="margin-left:4px;"><?php echo $countCancelled; ?></span>
    </a>
</div>

<div class="card">
    <div class="card-header">
        <h2><i class="fas fa-calendar-alt"></i> <?php echo $filter === 'all' ? 'All' : ucfirst($filter); ?> Appointments</h2>
        <span class="badge badge-approved"><?php echo $appointments->num_rows; ?> Total</span>
    </div>
    <div class="card-body">
        <?php if ($appointments->num_rows > 0): ?>
        <div class="table-responsive">
            <table>
                <thead>
                    <tr><th>#</th><th>Patient</th><th>Doctor</th><th>Date</th><th>Time</th><th>Reason</th><th>Status</th><th>Actions</th></tr>
                </thead>
                <tbody>
                    <?php $n=1; while ($a = $appointments->fetch_assoc()): ?>
                    <tr>
                        <td data-label="#"><?php echo $n++; ?></td>
                        <td data-label="Patient"><strong><?php echo htmlspecialchars($a['patient_name']); ?></strong></td>
                        <td data-label="Doctor"><?php echo htmlspecialchars($a['doctor_name']); ?> <small style="color:var(--text-muted);">(<?php echo htmlspecialchars($a['specialization']); ?>)</small></td>
                        <td data-label="Date"><?php echo date('M d, Y', strtotime($a['appointment_date'])); ?></td>
                        <td data-label="Time"><?php echo date('h:i A', strtotime($a['appointment_time'])); ?></td>
                        <td data-label="Reason"><?php echo htmlspecialchars(substr($a['reason'] ?? '', 0, 40)); ?></td>
                        <td data-label="Status"><span class="badge badge-<?php echo $a['status']; ?>"><?php echo ucfirst($a['status']); ?></span></td>
                        <td data-label="Actions">
                            <div class="action-btns">
                                <?php if ($a['status'] === 'pending'): ?>
                                    <form method="POST" style="display:inline;">
                                        <input type="hidden" name="action" value="approve">
                                        <input type="hidden" name="appointment_id" value="<?php echo $a['id']; ?>">
                                        <button type="submit" class="btn btn-success btn-sm" title="Approve" onclick="return confirm('Approve this appointment?');">
                                            <i class="fas fa-check"></i> Approve
                                        </button>
                                    </form>
                                    <form method="POST" style="display:inline;" onsubmit="return confirm('Cancel this appointment?');">
                                        <input type="hidden" name="action" value="cancel">
                                        <input type="hidden" name="appointment_id" value="<?php echo $a['id']; ?>">
                                        <button type="submit" class="btn btn-danger btn-sm" title="Cancel">
                                            <i class="fas fa-times"></i> Cancel
                                        </button>
                                    </form>
                                <?php elseif ($a['status'] === 'approved'): ?>
                                    <form method="POST" style="display:inline;" onsubmit="return confirm('Cancel this appointment?');">
                                        <input type="hidden" name="action" value="cancel">
                                        <input type="hidden" name="appointment_id" value="<?php echo $a['id']; ?>">
                                        <button type="submit" class="btn btn-danger btn-sm" title="Cancel">
                                            <i class="fas fa-times"></i> Cancel
                                        </button>
                                    </form>
                                    <span style="font-size:11px; color:#22c55e; font-weight:600;"><i class="fas fa-check-circle"></i> Approved</span>
                                <?php elseif ($a['status'] === 'completed'): ?>
                                    <span style="font-size:11px; color:#3b82f6; font-weight:600;"><i class="fas fa-check-double"></i> Completed</span>
                                <?php else: ?>
                                    <span style="font-size:12px; color:var(--text-muted);">—</span>
                                <?php endif; ?>
                            </div>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
        <?php else: ?>
            <div class="empty-state"><i class="fas fa-calendar-times"></i><p>No <?php echo $filter !== 'all' ? $filter : ''; ?> appointments found</p></div>
        <?php endif; ?>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
