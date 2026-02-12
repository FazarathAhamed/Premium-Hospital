<?php
$pageTitle = "Verify Payments";
$roleCss = true;
require_once __DIR__ . '/../middleware/check_login.php';
require_once __DIR__ . '/../middleware/check_role.php';
checkRole(['receptionist']);

$userId = $_SESSION['user_id'];

// Handle verification
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    if ($_POST['action'] === 'verify') {
        $billId = (int)$_POST['bill_id'];
        $stmt = $conn->prepare("UPDATE billing SET payment_status='paid', verified_by=?, verified_at=NOW(), paid_at=NOW() WHERE id=?");
        $stmt->bind_param("ii", $userId, $billId);
        $stmt->execute();
        $_SESSION['success'] = "Payment verified and confirmed!";
        header("Location: " . APP_URL . "/receptionist/verify_payments.php");
        exit();
    }
    
    if ($_POST['action'] === 'reject') {
        $billId = (int)$_POST['bill_id'];
        $stmt = $conn->prepare("UPDATE billing SET payment_status='unpaid', payment_method=NULL, payment_code=NULL WHERE id=?");
        $stmt->bind_param("i", $billId);
        $stmt->execute();
        $_SESSION['success'] = "Payment rejected. Patient needs to pay again.";
        header("Location: " . APP_URL . "/receptionist/verify_payments.php");
        exit();
    }
    
    // Search by payment code
    if ($_POST['action'] === 'search_code') {
        $searchCode = trim($_POST['payment_code']);
    }
}

$searchCode = $searchCode ?? (isset($_GET['code']) ? trim($_GET['code']) : '');

// Pending verification payments
$pendingPayments = $conn->query("
    SELECT b.*, p.full_name as patient_name, p.phone as patient_phone, 
           a.appointment_date, d.full_name as doctor_name
    FROM billing b 
    JOIN users p ON b.patient_id = p.id 
    LEFT JOIN appointments a ON b.appointment_id = a.id 
    LEFT JOIN users d ON a.doctor_id = d.id
    WHERE b.payment_status = 'pending_verification'
    ORDER BY b.created_at DESC
");

// Search result
$searchResult = null;
if ($searchCode !== '') {
    $searchResult = $conn->query("
        SELECT b.*, p.full_name as patient_name, p.phone as patient_phone, 
               a.appointment_date, d.full_name as doctor_name
        FROM billing b 
        JOIN users p ON b.patient_id = p.id 
        LEFT JOIN appointments a ON b.appointment_id = a.id 
        LEFT JOIN users d ON a.doctor_id = d.id
        WHERE b.payment_code = '" . $conn->real_escape_string($searchCode) . "'
    ");
}

// Recently verified
$recentVerified = $conn->query("
    SELECT b.*, p.full_name as patient_name, v.full_name as verified_by_name
    FROM billing b 
    JOIN users p ON b.patient_id = p.id 
    LEFT JOIN users v ON b.verified_by = v.id
    WHERE b.payment_status = 'paid' AND b.payment_method = 'cashier' AND b.verified_by IS NOT NULL
    ORDER BY b.verified_at DESC LIMIT 10
");

require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/sidebar.php';
?>

<?php if (isset($_SESSION['success'])): ?>
    <div class="alert alert-success"><i class="fas fa-check-circle"></i> <?php echo $_SESSION['success']; unset($_SESSION['success']); ?></div>
<?php endif; ?>

<!-- Search by Payment Code -->
<div class="card" style="margin-bottom:20px;">
    <div class="card-body" style="padding:16px 24px;">
        <form method="POST" style="display:flex;gap:10px;flex-wrap:wrap;align-items:center;">
            <input type="hidden" name="action" value="search_code">
            <label style="font-weight:600;font-size:14px;display:flex;align-items:center;gap:6px;"><i class="fas fa-search"></i> Verify by Payment Code:</label>
            <input type="text" name="payment_code" class="form-control" style="max-width:280px;" placeholder="PAY-HL-XXXXXXXX-XXX" value="<?php echo htmlspecialchars($searchCode); ?>" required>
            <button type="submit" class="btn btn-primary" style="background:var(--receptionist-accent);"><i class="fas fa-search"></i> Search</button>
        </form>
    </div>
</div>

<!-- Search Result -->
<?php if ($searchResult && $searchResult->num_rows > 0): ?>
    <?php $sr = $searchResult->fetch_assoc(); ?>
    <div class="card" style="margin-bottom:20px;border-left:4px solid var(--color-info);">
        <div class="card-header"><h2><i class="fas fa-receipt"></i> Payment Found</h2></div>
        <div class="card-body">
            <div class="payment-verify-card" style="border:none;padding:0;margin:0;">
                <div class="payment-verify-header">
                    <h4><?php echo htmlspecialchars($sr['patient_name']); ?></h4>
                    <div class="payment-code-display">
                        <i class="fas fa-barcode"></i> <?php echo htmlspecialchars($sr['payment_code']); ?>
                    </div>
                </div>
                <div class="payment-verify-details">
                    <span><i class="fas fa-dollar-sign"></i> Amount: <strong>$<?php echo number_format($sr['amount'], 2); ?></strong></span>
                    <span><i class="fas fa-user-md"></i> Doctor: <?php echo htmlspecialchars($sr['doctor_name'] ?? 'N/A'); ?></span>
                    <span><i class="fas fa-calendar"></i> Date: <?php echo $sr['appointment_date'] ? date('M d, Y', strtotime($sr['appointment_date'])) : 'N/A'; ?></span>
                    <span><i class="fas fa-info-circle"></i> Status: <span class="badge badge-<?php echo $sr['payment_status']; ?>"><?php echo ucfirst(str_replace('_', ' ', $sr['payment_status'])); ?></span></span>
                </div>
                <?php if ($sr['payment_status'] === 'pending_verification'): ?>
                <div style="display:flex;gap:8px;margin-top:10px;">
                    <form method="POST" style="display:inline;" onsubmit="return confirm('Verify and confirm this payment?');">
                        <input type="hidden" name="action" value="verify">
                        <input type="hidden" name="bill_id" value="<?php echo $sr['id']; ?>">
                        <button type="submit" class="btn btn-success btn-sm"><i class="fas fa-check-circle"></i> Verify & Confirm</button>
                    </form>
                    <form method="POST" style="display:inline;" onsubmit="return confirm('Reject this payment?');">
                        <input type="hidden" name="action" value="reject">
                        <input type="hidden" name="bill_id" value="<?php echo $sr['id']; ?>">
                        <button type="submit" class="btn btn-danger btn-sm"><i class="fas fa-times-circle"></i> Reject</button>
                    </form>
                </div>
                <?php elseif ($sr['payment_status'] === 'paid'): ?>
                    <span style="color:var(--color-success);font-weight:600;"><i class="fas fa-check-double"></i> Already Verified</span>
                <?php endif; ?>
            </div>
        </div>
    </div>
<?php elseif ($searchCode !== ''): ?>
    <div class="alert alert-warning"><i class="fas fa-exclamation-triangle"></i> No payment found with code: <strong><?php echo htmlspecialchars($searchCode); ?></strong></div>
<?php endif; ?>

<!-- Pending Verification List -->
<div class="card" style="margin-bottom:20px;">
    <div class="card-header">
        <h2><i class="fas fa-clock"></i> Pending Verification</h2>
        <span class="badge badge-pending"><?php echo $pendingPayments->num_rows; ?> Pending</span>
    </div>
    <div class="card-body">
        <?php if ($pendingPayments->num_rows > 0): ?>
            <?php while ($pay = $pendingPayments->fetch_assoc()): ?>
            <div class="payment-verify-card">
                <div class="payment-verify-header">
                    <h4><?php echo htmlspecialchars($pay['patient_name']); ?></h4>
                    <div class="payment-code-display">
                        <i class="fas fa-barcode"></i> <?php echo htmlspecialchars($pay['payment_code']); ?>
                    </div>
                </div>
                <div class="payment-verify-details">
                    <span><i class="fas fa-dollar-sign"></i> $<?php echo number_format($pay['amount'], 2); ?></span>
                    <span><i class="fas fa-phone"></i> <?php echo htmlspecialchars($pay['patient_phone'] ?? 'N/A'); ?></span>
                    <span><i class="fas fa-user-md"></i> <?php echo htmlspecialchars($pay['doctor_name'] ?? 'N/A'); ?></span>
                    <span><i class="fas fa-calendar"></i> <?php echo $pay['appointment_date'] ? date('M d, Y', strtotime($pay['appointment_date'])) : 'N/A'; ?></span>
                    <span><i class="fas fa-file-alt"></i> <?php echo htmlspecialchars($pay['description'] ?? '-'); ?></span>
                </div>
                <div style="display:flex;gap:8px;">
                    <form method="POST" style="display:inline;" onsubmit="return confirm('Verify and confirm this payment?');">
                        <input type="hidden" name="action" value="verify">
                        <input type="hidden" name="bill_id" value="<?php echo $pay['id']; ?>">
                        <button type="submit" class="btn btn-success btn-sm"><i class="fas fa-check-circle"></i> Verify & Confirm</button>
                    </form>
                    <form method="POST" style="display:inline;" onsubmit="return confirm('Reject this payment?');">
                        <input type="hidden" name="action" value="reject">
                        <input type="hidden" name="bill_id" value="<?php echo $pay['id']; ?>">
                        <button type="submit" class="btn btn-danger btn-sm"><i class="fas fa-times-circle"></i> Reject</button>
                    </form>
                </div>
            </div>
            <?php endwhile; ?>
        <?php else: ?>
            <div class="empty-state"><i class="fas fa-check-double"></i><p>No pending payments to verify</p></div>
        <?php endif; ?>
    </div>
</div>

<!-- Recently Verified -->
<div class="card">
    <div class="card-header">
        <h2><i class="fas fa-history"></i> Recently Verified</h2>
    </div>
    <div class="card-body">
        <?php if ($recentVerified->num_rows > 0): ?>
        <div class="table-responsive">
            <table>
                <thead><tr><th>#</th><th>Patient</th><th>Amount</th><th>Payment Code</th><th>Verified By</th><th>Verified At</th></tr></thead>
                <tbody>
                    <?php $n=1; while ($rv = $recentVerified->fetch_assoc()): ?>
                    <tr>
                        <td data-label="#"><?php echo $n++; ?></td>
                        <td data-label="Patient"><strong><?php echo htmlspecialchars($rv['patient_name']); ?></strong></td>
                        <td data-label="Amount">$<?php echo number_format($rv['amount'], 2); ?></td>
                        <td data-label="Payment Code"><code><?php echo htmlspecialchars($rv['payment_code'] ?? '-'); ?></code></td>
                        <td data-label="Verified By"><?php echo htmlspecialchars($rv['verified_by_name'] ?? '-'); ?></td>
                        <td data-label="Verified At"><?php echo $rv['verified_at'] ? date('M d, Y h:i A', strtotime($rv['verified_at'])) : '-'; ?></td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
        <?php else: ?>
            <div class="empty-state"><i class="fas fa-history"></i><p>No verified payments yet</p></div>
        <?php endif; ?>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
