<?php
$pageTitle = "Patient Feedback";
$roleCss = true;
require_once __DIR__ . '/../middleware/check_login.php';
require_once __DIR__ . '/../middleware/check_role.php';
checkRole(['admin']);

// Handle reply
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'reply') {
    $feedbackId = (int)$_POST['feedback_id'];
    $reply = trim($_POST['reply']);
    $repliedBy = $_SESSION['user_id'];
    
    $stmt = $conn->prepare("UPDATE feedback SET reply=?, replied_by=?, replied_at=NOW() WHERE id=?");
    $stmt->bind_param("sii", $reply, $repliedBy, $feedbackId);
    $stmt->execute();
    $_SESSION['success'] = "Reply sent successfully!";
    header("Location: " . APP_URL . "/admin/view_feedback.php");
    exit();
}

// Filter
$typeFilter = isset($_GET['type']) ? trim($_GET['type']) : '';
$conditions = "";
if ($typeFilter !== '') {
    $conditions = " AND f.type = '" . $conn->real_escape_string($typeFilter) . "'";
}

$feedbacks = $conn->query("
    SELECT f.*, p.full_name as patient_name, p.email as patient_email,
           r.full_name as replied_by_name
    FROM feedback f
    JOIN users p ON f.patient_id = p.id
    LEFT JOIN users r ON f.replied_by = r.id
    WHERE 1=1 $conditions
    ORDER BY f.created_at DESC
");

// Stats
$totalFeedback = $conn->query("SELECT COUNT(*) as c FROM feedback WHERE type='feedback'")->fetch_assoc()['c'];
$totalInquiries = $conn->query("SELECT COUNT(*) as c FROM feedback WHERE type='inquiry'")->fetch_assoc()['c'];
$unanswered = $conn->query("SELECT COUNT(*) as c FROM feedback WHERE reply IS NULL")->fetch_assoc()['c'];

require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/sidebar.php';
?>

<?php if (isset($_SESSION['success'])): ?>
    <div class="alert alert-success"><i class="fas fa-check-circle"></i> <?php echo $_SESSION['success']; unset($_SESSION['success']); ?></div>
<?php endif; ?>

<!-- Stats -->
<div class="stats-grid" style="margin-bottom:20px;">
    <div class="stat-card blue">
        <div class="stat-icon blue"><i class="fas fa-star"></i></div>
        <div class="stat-details"><h3><?php echo $totalFeedback; ?></h3><p>Feedback</p></div>
    </div>
    <div class="stat-card orange">
        <div class="stat-icon orange"><i class="fas fa-question-circle"></i></div>
        <div class="stat-details"><h3><?php echo $totalInquiries; ?></h3><p>Inquiries</p></div>
    </div>
    <div class="stat-card red">
        <div class="stat-icon red"><i class="fas fa-reply"></i></div>
        <div class="stat-details"><h3><?php echo $unanswered; ?></h3><p>Unanswered</p></div>
    </div>
</div>

<!-- Filter -->
<div style="margin-bottom:20px;display:flex;gap:8px;">
    <a href="<?php echo APP_URL; ?>/admin/view_feedback.php" class="btn btn-sm <?php echo $typeFilter === '' ? 'btn-primary' : 'btn-outline'; ?>">All</a>
    <a href="<?php echo APP_URL; ?>/admin/view_feedback.php?type=feedback" class="btn btn-sm <?php echo $typeFilter === 'feedback' ? 'btn-primary' : 'btn-outline'; ?>">Feedback</a>
    <a href="<?php echo APP_URL; ?>/admin/view_feedback.php?type=inquiry" class="btn btn-sm <?php echo $typeFilter === 'inquiry' ? 'btn-primary' : 'btn-outline'; ?>">Inquiries</a>
</div>

<?php if ($feedbacks->num_rows > 0): ?>
    <?php while ($f = $feedbacks->fetch_assoc()): ?>
    <div class="card" style="margin-bottom:16px;border-left:4px solid <?php echo $f['type'] === 'feedback' ? 'var(--color-info)' : 'var(--color-warning)'; ?>;">
        <div class="card-body">
            <div style="display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:8px;margin-bottom:12px;">
                <div>
                    <h3 style="font-size:16px;font-weight:700;color:var(--text-primary);margin-bottom:4px;">
                        <?php echo htmlspecialchars($f['subject']); ?>
                    </h3>
                    <div style="font-size:12px;color:var(--text-muted);display:flex;gap:12px;flex-wrap:wrap;">
                        <span><i class="fas fa-user"></i> <?php echo htmlspecialchars($f['patient_name']); ?></span>
                        <span><i class="fas fa-envelope"></i> <?php echo htmlspecialchars($f['patient_email']); ?></span>
                        <span><i class="fas fa-calendar"></i> <?php echo date('M d, Y h:i A', strtotime($f['created_at'])); ?></span>
                    </div>
                </div>
                <div style="display:flex;gap:6px;">
                    <span class="badge <?php echo $f['type'] === 'feedback' ? 'badge-approved' : 'badge-pending'; ?>"><?php echo ucfirst($f['type']); ?></span>
                    <span class="badge <?php echo $f['reply'] ? 'badge-completed' : 'badge-cancelled'; ?>"><?php echo $f['reply'] ? 'Replied' : 'Unanswered'; ?></span>
                </div>
            </div>
            
            <p style="font-size:14px;color:var(--text-secondary);line-height:1.6;margin-bottom:14px;padding:10px 14px;background:var(--bg-light);border-radius:8px;">
                <?php echo nl2br(htmlspecialchars($f['message'])); ?>
            </p>
            
            <?php if ($f['reply']): ?>
            <div style="background:#f0fdf4;padding:12px 16px;border-radius:8px;border-left:3px solid var(--color-success);">
                <strong style="display:block;font-size:12px;text-transform:uppercase;letter-spacing:0.5px;color:#065f46;margin-bottom:4px;">
                    <i class="fas fa-reply"></i> Reply by <?php echo htmlspecialchars($f['replied_by_name'] ?? 'Admin'); ?>
                    <span style="font-weight:400;"> — <?php echo $f['replied_at'] ? date('M d, Y h:i A', strtotime($f['replied_at'])) : ''; ?></span>
                </strong>
                <p style="font-size:14px;color:#065f46;margin:0;"><?php echo nl2br(htmlspecialchars($f['reply'])); ?></p>
            </div>
            <?php else: ?>
            <form method="POST" style="border-top:1px solid var(--border-color);padding-top:12px;">
                <input type="hidden" name="action" value="reply">
                <input type="hidden" name="feedback_id" value="<?php echo $f['id']; ?>">
                <div class="form-group" style="margin-bottom:10px;">
                    <textarea name="reply" class="form-control" rows="2" placeholder="Write your reply..." required></textarea>
                </div>
                <button type="submit" class="btn btn-primary btn-sm"><i class="fas fa-reply"></i> Send Reply</button>
            </form>
            <?php endif; ?>
        </div>
    </div>
    <?php endwhile; ?>
<?php else: ?>
    <div class="empty-state"><i class="fas fa-comments"></i><p>No feedback or inquiries found</p></div>
<?php endif; ?>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
