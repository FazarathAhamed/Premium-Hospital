<?php
$pageTitle = "Inquiries & Feedback";
$roleCss = true;
require_once __DIR__ . '/../middleware/check_login.php';
require_once __DIR__ . '/../middleware/check_role.php';
checkRole(['receptionist']);

$userId = $_SESSION['user_id'];

// Handle reply
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'reply') {
    $feedbackId = (int)$_POST['feedback_id'];
    $reply = trim($_POST['reply']);
    
    $stmt = $conn->prepare("UPDATE feedback SET reply=?, replied_by=?, replied_at=NOW() WHERE id=?");
    $stmt->bind_param("sii", $reply, $userId, $feedbackId);
    $stmt->execute();
    $_SESSION['success'] = "Reply sent successfully!";
    header("Location: " . APP_URL . "/receptionist/inquiries.php");
    exit();
}

$inquiries = $conn->query("
    SELECT f.*, p.full_name as patient_name, u.full_name as replied_by_name 
    FROM feedback f 
    JOIN users p ON f.patient_id = p.id 
    LEFT JOIN users u ON f.replied_by = u.id 
    ORDER BY f.created_at DESC
");

require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/sidebar.php';
?>

<?php if (isset($_SESSION['success'])): ?>
    <div class="alert alert-success"><i class="fas fa-check-circle"></i> <?php echo $_SESSION['success']; unset($_SESSION['success']); ?></div>
<?php endif; ?>

<div class="card">
    <div class="card-header">
        <h2><i class="fas fa-envelope"></i> Patient Inquiries & Feedback</h2>
        <span class="badge badge-approved"><?php echo $inquiries->num_rows; ?> Total</span>
    </div>
    <div class="card-body">
        <?php if ($inquiries->num_rows > 0): ?>
            <?php while ($inq = $inquiries->fetch_assoc()): ?>
            <div class="inquiry-card">
                <div class="inquiry-header">
                    <h4><?php echo htmlspecialchars($inq['subject']); ?></h4>
                    <div class="inquiry-meta">
                        <span><i class="fas fa-user"></i> <?php echo htmlspecialchars($inq['patient_name']); ?></span>
                        <span class="badge <?php echo $inq['type'] === 'feedback' ? 'badge-approved' : 'badge-pending'; ?>">
                            <?php echo ucfirst($inq['type']); ?>
                        </span>
                        <span><i class="fas fa-calendar"></i> <?php echo date('M d, Y', strtotime($inq['created_at'])); ?></span>
                    </div>
                </div>
                <p class="inquiry-message"><?php echo htmlspecialchars($inq['message']); ?></p>
                
                <?php if ($inq['reply']): ?>
                    <div class="inquiry-reply-display">
                        <strong><i class="fas fa-reply"></i> Reply from <?php echo htmlspecialchars($inq['replied_by_name'] ?? 'Staff'); ?></strong>
                        <?php echo htmlspecialchars($inq['reply']); ?>
                    </div>
                <?php else: ?>
                    <div class="inquiry-reply-form">
                        <form method="POST">
                            <input type="hidden" name="action" value="reply">
                            <input type="hidden" name="feedback_id" value="<?php echo $inq['id']; ?>">
                            <div class="form-group">
                                <label><i class="fas fa-reply"></i> Your Reply</label>
                                <textarea name="reply" class="form-control" rows="2" placeholder="Type your reply here..." required></textarea>
                            </div>
                            <button type="submit" class="btn btn-primary btn-sm">
                                <i class="fas fa-paper-plane"></i> Send Reply
                            </button>
                        </form>
                    </div>
                <?php endif; ?>
            </div>
            <?php endwhile; ?>
        <?php else: ?>
            <div class="empty-state"><i class="fas fa-inbox"></i><p>No inquiries or feedback yet</p></div>
        <?php endif; ?>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
