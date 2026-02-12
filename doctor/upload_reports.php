<?php
$pageTitle = "Upload Medical Report";
$roleCss = true;
require_once __DIR__ . '/../middleware/check_login.php';
require_once __DIR__ . '/../middleware/check_role.php';
checkRole(['doctor']);

$userId = $_SESSION['user_id'];
$selectedPatientId = isset($_GET['patient_id']) ? (int)$_GET['patient_id'] : 0;

// Handle upload
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $patientId = (int)$_POST['patient_id'];
    
    // Validate patient has consulted
    $check = $conn->query("SELECT 1 FROM appointments WHERE doctor_id = $userId AND patient_id = $patientId AND status = 'completed'");
    if ($check->num_rows === 0) {
        $_SESSION['error'] = "You can only upload reports for consulted patients.";
        header("Location: " . APP_URL . "/doctor/upload_reports.php");
        exit();
    }

    $title = trim($_POST['report_title']);
    $description = trim($_POST['report_description']);
    $filePath = null;
    
    // Handle file upload
    if (isset($_FILES['report_file']) && $_FILES['report_file']['error'] === UPLOAD_ERR_OK) {
        $uploadDir = __DIR__ . '/../uploads/reports/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }
        
        $fileName = time() . '_' . basename($_FILES['report_file']['name']);
        $targetPath = $uploadDir . $fileName;
        
        if (move_uploaded_file($_FILES['report_file']['tmp_name'], $targetPath)) {
            $filePath = 'uploads/reports/' . $fileName;
        }
    }
    
    $stmt = $conn->prepare("INSERT INTO medical_reports (patient_id, doctor_id, report_title, report_description, file_path) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("iisss", $patientId, $userId, $title, $description, $filePath);
    
    if ($stmt->execute()) {
        $_SESSION['success'] = "Medical report uploaded successfully!";
        header("Location: " . APP_URL . "/doctor/patient_details.php?patient_id=" . $patientId);
        exit();
    } else {
        $error = "Failed to upload report. Please try again.";
    }
}

// Get doctor's patients
$patients = $conn->query("
    SELECT DISTINCT p.id, p.full_name 
    FROM users p 
    JOIN appointments a ON p.id = a.patient_id 
    WHERE a.doctor_id = $userId AND p.role='patient' AND a.status = 'completed'
    ORDER BY p.full_name
");

require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/sidebar.php';
?>

<?php if (isset($error)): ?>
    <div class="alert alert-error"><i class="fas fa-exclamation-circle"></i> <?php echo $error; ?></div>
<?php endif; ?>

<div class="card">
    <div class="card-header">
        <h2><i class="fas fa-upload"></i> Upload Medical Report</h2>
    </div>
    <div class="card-body">
        <form method="POST" enctype="multipart/form-data" class="upload-form">
            <div class="form-group">
                <label for="patient_id"><i class="fas fa-user"></i> Select Patient</label>
                <select name="patient_id" id="patient_id" class="form-control" required>
                    <option value="">-- Choose Patient --</option>
                    <?php while ($p = $patients->fetch_assoc()): ?>
                        <option value="<?php echo $p['id']; ?>" <?php echo $p['id'] == $selectedPatientId ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($p['full_name']); ?>
                        </option>
                    <?php endwhile; ?>
                </select>
            </div>
            <div class="form-group">
                <label for="report_title"><i class="fas fa-heading"></i> Report Title</label>
                <input type="text" name="report_title" id="report_title" class="form-control" placeholder="e.g., Blood Test Results - Jan 2026" required>
            </div>
            <div class="form-group">
                <label for="report_description"><i class="fas fa-comment-medical"></i> Description</label>
                <textarea name="report_description" id="report_description" class="form-control" rows="3" placeholder="Brief description of the report..."></textarea>
            </div>
            <div class="form-group">
                <label><i class="fas fa-file-upload"></i> Upload Report File (Optional)</label>
                <div class="file-input-wrapper">
                    <input type="file" name="report_file" id="report_file" accept=".pdf,.jpg,.jpeg,.png,.doc,.docx" onchange="displayFileName(this)">
                    <label for="report_file" class="file-input-label">
                        <i class="fas fa-cloud-upload-alt"></i>
                        <span>Choose file or drag here</span>
                    </label>
                </div>
                <div id="file-name" class="file-name"></div>
                <small style="color:var(--text-muted);font-size:12px;">Supported: PDF, JPG, PNG, DOC, DOCX</small>
            </div>
            <button type="submit" class="btn btn-primary">
                <i class="fas fa-upload"></i> Upload Report
            </button>
        </form>
    </div>
</div>

<script>
function displayFileName(input) {
    const fileNameDiv = document.getElementById('file-name');
    if (input.files && input.files[0]) {
        fileNameDiv.innerHTML = '<i class="fas fa-check-circle"></i> ' + input.files[0].name;
    } else {
        fileNameDiv.innerHTML = '';
    }
}
</script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
