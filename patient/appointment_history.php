<?php
$pageTitle = "Appointment History";
$roleCss = true;
require_once __DIR__ . '/../middleware/check_login.php';
require_once __DIR__ . '/../middleware/check_role.php';
checkRole(['patient']);

$userId = $_SESSION['user_id'];

// Handle cancel
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'cancel') {
    $aptId = (int)$_POST['appointment_id'];
    $stmt = $conn->prepare("UPDATE appointments SET status='cancelled' WHERE id=? AND patient_id=? AND status='pending'");
    $stmt->bind_param("ii", $aptId, $userId);
    $stmt->execute();
    $_SESSION['success'] = "Appointment cancelled.";
    header("Location: " . APP_URL . "/patient/appointment_history.php");
    exit();
}

$appointments = $conn->query("
    SELECT a.*, d.full_name as doctor_name, d.specialization, d.consulting_fee,
        (SELECT b.payment_status FROM billing b WHERE b.appointment_id = a.id LIMIT 1) as payment_status,
        (SELECT b.amount FROM billing b WHERE b.appointment_id = a.id LIMIT 1) as bill_amount
    FROM appointments a 
    JOIN users d ON a.doctor_id = d.id 
    WHERE a.patient_id = $userId
    ORDER BY a.appointment_date DESC
");

require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/sidebar.php';
?>

<?php if (isset($_SESSION['success'])): ?>
    <div class="alert alert-success"><i class="fas fa-check-circle"></i> <?php echo $_SESSION['success']; unset($_SESSION['success']); ?></div>
<?php endif; ?>

<div class="card">
    <div class="card-header">
        <h2><i class="fas fa-history"></i> Appointment History</h2>
        <a href="<?php echo APP_URL; ?>/patient/book_appointment.php" class="btn btn-sm" style="background:#22c55e;color:#fff;">
            <i class="fas fa-plus"></i> Book New
        </a>
    </div>
    <div class="card-body">
        <?php if ($appointments->num_rows > 0): ?>
        <div class="table-responsive">
            <table>
                <thead>
                    <tr><th>#</th><th>Doctor</th><th>Date</th><th>Time</th><th>Fee</th><th>Payment</th><th>Status</th><th>Actions</th></tr>
                </thead>
                <tbody>
                    <?php $n=1; while ($a = $appointments->fetch_assoc()): ?>
                    <tr>
                        <td data-label="#"><?php echo $n++; ?></td>
                        <td data-label="Doctor">
                            <strong><?php echo htmlspecialchars($a['doctor_name']); ?></strong>
                            <br><small style="color:var(--text-muted);"><?php echo htmlspecialchars($a['specialization']); ?></small>
                        </td>
                        <td data-label="Date"><?php echo date('M d, Y', strtotime($a['appointment_date'])); ?></td>
                        <td data-label="Time"><?php echo date('h:i A', strtotime($a['appointment_time'])); ?></td>
                        <td data-label="Fee"><strong>$<?php echo number_format($a['bill_amount'] ?? $a['consulting_fee'] ?? 0, 2); ?></strong></td>
                        <td data-label="Payment">
                            <?php
                            $ps = $a['payment_status'] ?? 'unpaid';
                            $psClass = 'badge-unpaid';
                            if ($ps === 'paid') $psClass = 'badge-paid';
                            elseif ($ps === 'pending_verification') $psClass = 'badge-pending';
                            ?>
                            <span class="badge <?php echo $psClass; ?>"><?php echo ucfirst(str_replace('_', ' ', $ps)); ?></span>
                        </td>
                        <td data-label="Status"><span class="badge badge-<?php echo $a['status']; ?>"><?php echo ucfirst($a['status']); ?></span></td>
                        <td data-label="Actions">
                            <div class="action-btns">
                                <?php if ($a['status'] === 'pending'): ?>
                                    <form method="POST" style="display:inline;" onsubmit="return confirm('Cancel this appointment?');">
                                        <input type="hidden" name="action" value="cancel">
                                        <input type="hidden" name="appointment_id" value="<?php echo $a['id']; ?>">
                                        <button type="submit" class="btn btn-danger btn-sm"><i class="fas fa-times"></i> Cancel</button>
                                    </form>
                                <?php elseif ($ps !== 'paid' && $a['status'] !== 'cancelled'): ?>
                                    <a href="<?php echo APP_URL; ?>/patient/billing.php" class="btn btn-info btn-sm"><i class="fas fa-credit-card"></i> Pay</a>
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
            <div class="empty-state">
                <i class="fas fa-calendar-times"></i>
                <p>No appointments yet</p>
                <a href="<?php echo APP_URL; ?>/patient/book_appointment.php" class="btn btn-primary" style="margin-top:12px;"><i class="fas fa-calendar-plus"></i> Book Your First Appointment</a>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
