<?php
$pageTitle = "Billing Reports";
$roleCss = true;
require_once __DIR__ . '/../middleware/check_login.php';
require_once __DIR__ . '/../middleware/check_role.php';
checkRole(['admin']);

$totalRevenue = $conn->query("SELECT COALESCE(SUM(amount),0) as t FROM billing WHERE payment_status='paid'")->fetch_assoc()['t'];
$totalPending = $conn->query("SELECT COALESCE(SUM(amount),0) as t FROM billing WHERE payment_status='unpaid'")->fetch_assoc()['t'];
$totalBills = $conn->query("SELECT COUNT(*) as c FROM billing")->fetch_assoc()['c'];
$paidBills = $conn->query("SELECT COUNT(*) as c FROM billing WHERE payment_status='paid'")->fetch_assoc()['c'];

$bills = $conn->query("
    SELECT b.*, p.full_name as patient_name 
    FROM billing b 
    JOIN users p ON b.patient_id = p.id 
    ORDER BY b.created_at DESC
");

require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/sidebar.php';
?>

<div class="stats-grid">
    <div class="stat-card green">
        <div class="stat-icon green"><i class="fas fa-dollar-sign"></i></div>
        <div class="stat-details"><h3>$<?php echo number_format($totalRevenue, 2); ?></h3><p>Total Revenue</p></div>
    </div>
    <div class="stat-card orange">
        <div class="stat-icon orange"><i class="fas fa-hourglass-half"></i></div>
        <div class="stat-details"><h3>$<?php echo number_format($totalPending, 2); ?></h3><p>Pending Amount</p></div>
    </div>
    <div class="stat-card blue">
        <div class="stat-icon blue"><i class="fas fa-file-invoice"></i></div>
        <div class="stat-details"><h3><?php echo $totalBills; ?></h3><p>Total Bills</p></div>
    </div>
    <div class="stat-card teal">
        <div class="stat-icon teal"><i class="fas fa-check-double"></i></div>
        <div class="stat-details"><h3><?php echo $paidBills; ?></h3><p>Paid Bills</p></div>
    </div>
</div>

<div class="card admin-table-card">
    <div class="card-header">
        <h2><i class="fas fa-file-invoice-dollar"></i> All Billing Records</h2>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table>
                <thead>
                    <tr><th>#</th><th>Patient</th><th>Description</th><th>Amount</th><th>Method</th><th>Status</th><th>Date</th></tr>
                </thead>
                <tbody>
                    <?php $n=1; while ($b = $bills->fetch_assoc()): ?>
                    <tr>
                        <td data-label="#"><?php echo $n++; ?></td>
                        <td data-label="Patient"><strong><?php echo htmlspecialchars($b['patient_name']); ?></strong></td>
                        <td data-label="Description"><?php echo htmlspecialchars($b['description'] ?? '-'); ?></td>
                        <td data-label="Amount"><strong>$<?php echo number_format($b['amount'], 2); ?></strong></td>
                        <td data-label="Method"><?php echo $b['payment_method'] ? ucfirst($b['payment_method']) : '-'; ?></td>
                        <td data-label="Status"><span class="badge badge-<?php echo $b['payment_status']; ?>"><?php echo ucfirst($b['payment_status']); ?></span></td>
                        <td data-label="Date"><?php echo date('M d, Y', strtotime($b['created_at'])); ?></td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
