<?php
$pageTitle = "Billing & Payments";
$roleCss = true;
require_once __DIR__ . '/../middleware/check_login.php';
require_once __DIR__ . '/../middleware/check_role.php';
checkRole(['patient']);

$userId = $_SESSION['user_id'];

// Handle payment
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'pay') {
    $billId = (int)$_POST['bill_id'];
    $method = $_POST['payment_method'];
    
    if ($method === 'card') {
        $cardLastFour = substr(trim($_POST['card_number']), -4);
        $stmt = $conn->prepare("UPDATE billing SET payment_method='card', payment_status='paid', card_last_four=?, paid_at=NOW() WHERE id=? AND patient_id=?");
        $stmt->bind_param("sii", $cardLastFour, $billId, $userId);
        $stmt->execute();
        $_SESSION['success'] = "Payment successful via card!";
    } elseif ($method === 'cashier') {
        // Generate unique payment code
        $paymentCode = 'PAY-HL-' . date('Ymd') . '-' . str_pad(mt_rand(1, 999), 3, '0', STR_PAD_LEFT);
        $stmt = $conn->prepare("UPDATE billing SET payment_method='cashier', payment_status='pending_verification', payment_code=? WHERE id=? AND patient_id=?");
        $stmt->bind_param("sii", $paymentCode, $billId, $userId);
        $stmt->execute();
        $_SESSION['success'] = "Payment submitted! Your payment code is: <strong>" . $paymentCode . "</strong>. Please show this code at the reception for verification.";
    }
    
    header("Location: " . APP_URL . "/patient/billing.php");
    exit();
}

// Fetch bills
$bills = $conn->query("
    SELECT b.*, a.appointment_date, d.full_name as doctor_name, d.specialization
    FROM billing b
    LEFT JOIN appointments a ON b.appointment_id = a.id
    LEFT JOIN users d ON a.doctor_id = d.id
    WHERE b.patient_id = $userId
    ORDER BY b.created_at DESC
");

// Calculate totals
$totalPaid = $conn->query("SELECT COALESCE(SUM(amount), 0) as t FROM billing WHERE patient_id = $userId AND payment_status='paid'")->fetch_assoc()['t'];
$totalOutstanding = $conn->query("SELECT COALESCE(SUM(amount), 0) as t FROM billing WHERE patient_id = $userId AND payment_status IN ('unpaid','pending_verification')")->fetch_assoc()['t'];
$pendingVerification = $conn->query("SELECT COUNT(*) as c FROM billing WHERE patient_id = $userId AND payment_status='pending_verification'")->fetch_assoc()['c'];

require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/sidebar.php';
?>

<?php if (isset($_SESSION['success'])): ?>
    <div class="alert alert-success"><i class="fas fa-check-circle"></i> <?php echo $_SESSION['success']; unset($_SESSION['success']); ?></div>
<?php endif; ?>

<!-- Summary Cards -->
<div class="stats-grid" style="margin-bottom:24px;">
    <div class="stat-card green">
        <div class="stat-icon green"><i class="fas fa-check-circle"></i></div>
        <div class="stat-details"><h3>$<?php echo number_format($totalPaid, 2); ?></h3><p>Total Paid</p></div>
    </div>
    <div class="stat-card red">
        <div class="stat-icon red"><i class="fas fa-exclamation-circle"></i></div>
        <div class="stat-details"><h3>$<?php echo number_format($totalOutstanding, 2); ?></h3><p>Outstanding</p></div>
    </div>
    <div class="stat-card orange">
        <div class="stat-icon orange"><i class="fas fa-clock"></i></div>
        <div class="stat-details"><h3><?php echo $pendingVerification; ?></h3><p>Pending Verification</p></div>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <h2><i class="fas fa-file-invoice-dollar"></i> My Bills</h2>
    </div>
    <div class="card-body">
        <?php if ($bills->num_rows > 0): ?>
        <div class="table-responsive">
            <table>
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Description</th>
                        <th>Doctor</th>
                        <th>Date</th>
                        <th>Amount</th>
                        <th>Method</th>
                        <th>Status</th>
                        <th>Payment Code</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php $n=1; while ($b = $bills->fetch_assoc()): ?>
                    <tr>
                        <td data-label="#"><?php echo $n++; ?></td>
                        <td data-label="Description"><?php echo htmlspecialchars($b['description'] ?? 'Consultation'); ?></td>
                        <td data-label="Doctor"><?php echo htmlspecialchars($b['doctor_name'] ?? '-'); ?></td>
                        <td data-label="Date"><?php echo $b['appointment_date'] ? date('M d, Y', strtotime($b['appointment_date'])) : '-'; ?></td>
                        <td data-label="Amount"><strong>$<?php echo number_format($b['amount'], 2); ?></strong></td>
                        <td data-label="Method"><?php echo $b['payment_method'] ? ucfirst($b['payment_method']) : '-'; ?></td>
                        <td data-label="Status">
                            <span class="badge <?php 
                                echo match($b['payment_status']) {
                                    'paid' => 'badge-approved',
                                    'unpaid' => 'badge-cancelled',
                                    'pending_verification' => 'badge-pending',
                                    default => ''
                                };
                            ?>">
                                <?php echo ucfirst(str_replace('_', ' ', $b['payment_status'])); ?>
                            </span>
                        </td>
                        <td data-label="Payment Code">
                            <?php if ($b['payment_code']): ?>
                                <code style="background:#fef3c7;padding:2px 6px;border-radius:4px;font-size:11px;color:#92400e;">
                                    <?php echo htmlspecialchars($b['payment_code']); ?>
                                </code>
                            <?php else: ?>
                                -
                            <?php endif; ?>
                        </td>
                        <td data-label="Actions">
                            <?php if ($b['payment_status'] === 'unpaid'): ?>
                                <button class="btn btn-primary btn-sm" onclick="openPayModal(<?php echo $b['id']; ?>, <?php echo $b['amount']; ?>, <?php echo htmlspecialchars(json_encode($b['description'] ?? 'Consultation'), ENT_QUOTES); ?>)">
                                    <i class="fas fa-credit-card"></i> Pay
                                </button>
                            <?php elseif ($b['payment_status'] === 'paid'): ?>
                                <a href="<?php echo APP_URL; ?>/patient/download_bill.php?id=<?php echo $b['id']; ?>" class="btn btn-success btn-sm" target="_blank">
                                    <i class="fas fa-download"></i> Receipt
                                </a>
                            <?php elseif ($b['payment_status'] === 'pending_verification'): ?>
                                <span style="font-size:12px;color:var(--color-warning);"><i class="fas fa-hourglass-half"></i> Awaiting</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
        <?php else: ?>
            <div class="empty-state"><i class="fas fa-file-invoice"></i><p>No bills found</p></div>
        <?php endif; ?>
    </div>
</div>

<!-- Pay Modal -->
<div class="modal-overlay" id="payModal">
    <div class="modal">
        <div class="modal-header">
            <h2>Make Payment</h2>
            <button class="modal-close" onclick="this.closest('.modal-overlay').classList.remove('active')">&times;</button>
        </div>
        <form method="POST">
            <div class="modal-body">
                <input type="hidden" name="action" value="pay">
                <input type="hidden" name="bill_id" id="pay_bill_id">
                <p style="font-size:14px;color:var(--text-secondary);margin-bottom:16px;">
                    <span id="pay_description"></span><br>
                    Amount: <strong>$<span id="pay_amount"></span></strong>
                </p>
                
                <div class="form-group">
                    <label><i class="fas fa-wallet"></i> Payment Method</label>
                    <div style="display:flex;gap:10px;margin-bottom:14px;">
                        <label style="flex:1;display:flex;align-items:center;gap:8px;padding:14px;border:2px solid var(--border-color);border-radius:8px;cursor:pointer;transition:all 0.3s;">
                            <input type="radio" name="payment_method" value="card" id="method_card" required onclick="togglePayFields('card')">
                            <i class="fas fa-credit-card" style="font-size:20px;color:var(--color-info);"></i>
                            <div><strong>Card</strong><br><small style="color:var(--text-muted);">Pay instantly</small></div>
                        </label>
                        <label style="flex:1;display:flex;align-items:center;gap:8px;padding:14px;border:2px solid var(--border-color);border-radius:8px;cursor:pointer;transition:all 0.3s;">
                            <input type="radio" name="payment_method" value="cashier" id="method_cashier" onclick="togglePayFields('cashier')">
                            <i class="fas fa-receipt" style="font-size:20px;color:var(--color-warning);"></i>
                            <div><strong>Cashier</strong><br><small style="color:var(--text-muted);">Pay at reception</small></div>
                        </label>
                    </div>
                </div>
                
                <!-- Card Fields -->
                <div id="cardFields" style="display:none;">
                    <div class="form-group">
                        <label>Card Number</label>
                        <input type="text" name="card_number" id="card_number_input" class="form-control" placeholder="XXXX XXXX XXXX XXXX" maxlength="19">
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label>Expiry</label>
                            <input type="text" class="form-control" placeholder="MM/YY" maxlength="5">
                        </div>
                        <div class="form-group">
                            <label>CVV</label>
                            <input type="text" class="form-control" placeholder="123" maxlength="3">
                        </div>
                    </div>
                </div>
                
                <!-- Cashier Info -->
                <div id="cashierInfo" style="display:none;">
                    <div style="background:#fffbeb;padding:16px;border-radius:8px;border-left:4px solid var(--color-warning);">
                        <h4 style="font-size:14px;color:#92400e;margin-bottom:8px;"><i class="fas fa-info-circle"></i> How Cashier Payment Works</h4>
                        <ol style="font-size:13px;color:#92400e;line-height:1.8;padding-left:18px;">
                            <li>Click "Submit" to get your unique payment code</li>
                            <li>Visit the hospital reception/cashier desk</li>
                            <li>Show your payment code to the receptionist</li>
                            <li>Pay the amount in cash</li>
                            <li>Receptionist will verify your payment</li>
                        </ol>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline" onclick="this.closest('.modal-overlay').classList.remove('active')">Cancel</button>
                <button type="submit" class="btn btn-primary"><i class="fas fa-check"></i> Submit Payment</button>
            </div>
        </form>
    </div>
</div>

<script>
function openPayModal(billId, amount, description) {
    document.getElementById('pay_bill_id').value = billId;
    document.getElementById('pay_amount').textContent = parseFloat(amount).toFixed(2);
    document.getElementById('pay_description').textContent = description;
    document.getElementById('cardFields').style.display = 'none';
    document.getElementById('cashierInfo').style.display = 'none';
    document.getElementById('payModal').classList.add('active');
}

function togglePayFields(method) {
    document.getElementById('cardFields').style.display = method === 'card' ? 'block' : 'none';
    document.getElementById('cashierInfo').style.display = method === 'cashier' ? 'block' : 'none';
    var cardInput = document.getElementById('card_number_input');
    if (method === 'card') {
        cardInput.required = true;
    } else {
        cardInput.required = false;
    }
}
</script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
