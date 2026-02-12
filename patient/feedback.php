<?php
$pageTitle = "Feedback & Inquiries";
$roleCss = true;
require_once __DIR__ . '/../middleware/check_login.php';
require_once __DIR__ . '/../middleware/check_role.php';
checkRole(['patient']);

$userId = $_SESSION['user_id'];

// Handle submit feedback
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'submit') {
    $subject = trim($_POST['subject']);
    $message = trim($_POST['message']);
    $type = $_POST['type'];
    
    $stmt = $conn->prepare("INSERT INTO feedback (patient_id, subject, message, type) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("isss", $userId, $subject, $message, $type);
    $stmt->execute();
    $_SESSION['success'] = "Your " . $type . " has been submitted successfully!";
    header("Location: " . APP_URL . "/patient/feedback.php");
    exit();
}

$feedbacks = $conn->query("
    SELECT f.*, u.full_name as replied_by_name 
    FROM feedback f 
    LEFT JOIN users u ON f.replied_by = u.id 
    WHERE f.patient_id = $userId 
    ORDER BY f.created_at DESC
");

require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/sidebar.php';
?>

<?php if (isset($_SESSION['success'])): ?>
    <div class="alert alert-success"><i class="fas fa-check-circle"></i> <?php echo $_SESSION['success']; unset($_SESSION['success']); ?></div>
<?php endif; ?>

<div class="grid-2">
    <!-- Submit Form -->
    <div class="card">
        <div class="card-header">
            <h2><i class="fas fa-paper-plane"></i> Submit Feedback / Inquiry</h2>
        </div>
        <div class="card-body">
            <form method="POST">
                <input type="hidden" name="action" value="submit">
                <div class="form-group">
                    <label for="type"><i class="fas fa-tag"></i> Type</label>
                    <select name="type" id="type" class="form-control" required>
                        <option value="feedback">Feedback</option>
                        <option value="inquiry">Inquiry</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="subject"><i class="fas fa-heading"></i> Subject</label>
                    <input type="text" name="subject" id="subject" class="form-control" placeholder="Enter subject" required>
                </div>
                <div class="form-group">
                    <label for="message"><i class="fas fa-comment"></i> Message</label>
                    <textarea name="message" id="message" class="form-control" rows="4" placeholder="Write your feedback or inquiry here..." required></textarea>
                </div>
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-paper-plane"></i> Submit
                </button>
            </form>
        </div>
    </div>

    <!-- Previous Feedback -->
    <div>
        <h3 style="font-size:16px;font-weight:700;margin-bottom:16px;"><i class="fas fa-history" style="color:var(--patient-accent);"></i> My Feedback & Inquiries</h3>
        <?php if ($feedbacks->num_rows > 0): ?>
            <?php while ($fb = $feedbacks->fetch_assoc()): ?>
            <div class="feedback-card">
                <div class="feedback-meta">
                    <h4><?php echo htmlspecialchars($fb['subject']); ?></h4>
                    <div>
                        <span class="badge <?php echo $fb['type'] === 'feedback' ? 'badge-approved' : 'badge-pending'; ?>">
                            <?php echo ucfirst($fb['type']); ?>
                        </span>
                        <span class="feedback-date"><?php echo date('M d, Y', strtotime($fb['created_at'])); ?></span>
                    </div>
                </div>
                <p class="feedback-message"><?php echo htmlspecialchars($fb['message']); ?></p>
                <?php if ($fb['reply']): ?>
                    <div class="feedback-reply">
                        <strong><i class="fas fa-reply"></i> Reply from <?php echo htmlspecialchars($fb['replied_by_name'] ?? 'Staff'); ?></strong>
                        <?php echo htmlspecialchars($fb['reply']); ?>
                    </div>
                <?php else: ?>
                    <span style="font-size:12px;color:var(--text-muted);"><i class="fas fa-clock"></i> Awaiting reply...</span>
                <?php endif; ?>
            </div>
            <?php endwhile; ?>
        <?php else: ?>
            <div class="empty-state"><i class="fas fa-comment-dots"></i><p>No feedback submitted yet</p></div>
        <?php endif; ?>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
