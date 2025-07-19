<?php
include("includes/session.php");
include("database/config.php");

// Ensure user is logged in
ensureLoggedIn();

$message = "";
$error = "";

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $title = trim($_POST['title']);
    $description = trim($_POST['description']);
    $payment_mode = $_POST['payment_mode'];
    $work_setup = $_POST['work_setup'];
    $deadline = $_POST['deadline'];
    $user_id = $_SESSION['user_id'];
    
    // Validate required fields
    if (empty($title) || empty($description) || empty($payment_mode) || empty($work_setup) || empty($deadline)) {
        $error = "All fields are required.";
    } else {
        $image_path = "";
        
                // Handle image upload
        if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
            $allowed_types = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif'];
            $max_size = 5 * 1024 * 1024; // 5MB
            
            if (in_array($_FILES['image']['type'], $allowed_types) && $_FILES['image']['size'] <= $max_size) {
                $upload_dir = "assests/uploads/";
                
                // Create directory if it doesn't exist
                if (!file_exists($upload_dir)) {
                    mkdir($upload_dir, 0777, true);
                }
                
                // Store temporary file path for later renaming
                $temp_upload_path = $upload_dir . uniqid() . '.' . pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
                
                if (move_uploaded_file($_FILES['image']['tmp_name'], $temp_upload_path)) {
                    $temp_image_path = $temp_upload_path;
                } else {
                    $error = "Failed to upload image. Please try again.";
                }
            } else {
                $error = "Invalid image format or size. Please upload a valid image (JPEG, PNG, GIF) under 5MB.";
            }
        }
        
        // If no errors, insert into database
        if (empty($error)) {
            $stmt = $conn->prepare("INSERT INTO jobs (user_id, title, description, payment_mode, work_setup, deadline, image_path, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, NOW())");
            $stmt->bind_param("issssss", $user_id, $title, $description, $payment_mode, $work_setup, $deadline, $temp_image_path);
            
            if ($stmt->execute()) {
                $job_id = $conn->insert_id; // Get the auto-generated job ID
                
                // Rename the image file to include the job ID
                if (isset($temp_image_path) && file_exists($temp_image_path)) {
                    $file_extension = pathinfo($temp_image_path, PATHINFO_EXTENSION);
                    $new_file_name = "imgid" . $job_id . "." . $file_extension;
                    $new_upload_path = $upload_dir . $new_file_name;
                    
                    if (rename($temp_image_path, $new_upload_path)) {
                        // Update the database with the new image path
                        $update_stmt = $conn->prepare("UPDATE jobs SET image_path = ? WHERE id = ?");
                        $update_stmt->bind_param("si", $new_upload_path, $job_id);
                        $update_stmt->execute();
                        $update_stmt->close();
                    }
                }
                
                $message = "Job posted successfully!";
                // Clear form data
                $_POST = array();
            } else {
                $error = "Error posting job: " . $conn->error;
            }
            $stmt->close();
        }
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Task Hive | Post a Job</title>
    <link rel="stylesheet" href="style.css">
    <style>
        .page-header {
            text-align: center;
            margin-bottom: 30px;
            padding-bottom: 20px;
            border-bottom: 2px solid #e9ecef;
        }
        
        .page-header h1 {
            color: #2c3e50;
            font-size: 2.5em;
            margin-bottom: 10px;
        }
        
        .page-header p {
            color: #6c757d;
            font-size: 1.1em;
        }
        
        .form-container {
            background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
            border-radius: 15px;
            padding: 30px;
            margin-bottom: 30px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.08);
        }
        
        .form-section {
            background: white;
            border-radius: 10px;
            padding: 25px;
            margin-bottom: 25px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
            border-left: 4px solid #007BFF;
        }
        
        .form-section h3 {
            color: #2c3e50;
            margin-bottom: 20px;
            font-size: 1.3em;
            display: flex;
            align-items: center;
        }
        
        .form-section h3::before {
            content: "📋";
            margin-right: 10px;
            font-size: 1.2em;
        }
        
        .form-section:nth-child(2) h3::before {
            content: "💼";
        }
        
        .form-section:nth-child(3) h3::before {
            content: "📅";
        }
        
        .form-section:nth-child(4) h3::before {
            content: "🖼️";
        }
        
        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
            margin-bottom: 20px;
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        
        .form-group label {
            display: block;
            font-weight: 600;
            color: #495057;
            margin-bottom: 8px;
            font-size: 0.95em;
        }
        
        .form-group input,
        .form-group textarea,
        .form-group select {
            width: 100%;
            padding: 12px 15px;
            border: 2px solid #e9ecef;
            border-radius: 8px;
            font-size: 1em;
            transition: all 0.3s ease;
            background-color: #fff;
        }
        
        .form-group input:focus,
        .form-group textarea:focus,
        .form-group select:focus {
            outline: none;
            border-color: #007BFF;
            box-shadow: 0 0 0 3px rgba(0, 123, 255, 0.1);
        }
        
        .form-group textarea {
            resize: vertical;
            min-height: 120px;
        }
        
        .file-upload-container {
            border: 2px dashed #dee2e6;
            border-radius: 8px;
            padding: 30px;
            text-align: center;
            background-color: #f8f9fa;
            transition: all 0.3s ease;
            cursor: pointer;
            position: relative;
        }
        
        .file-upload-container:hover {
            border-color: #007BFF;
            background-color: #f0f8ff;
        }
        
        .file-upload-container input[type="file"] {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            opacity: 0;
            cursor: pointer;
        }
        
        .file-upload-container:hover input[type="file"] {
            opacity: 0;
        }
        
        .upload-icon {
            font-size: 2em;
            color: #6c757d;
            margin-bottom: 10px;
        }
        
        .upload-text {
            color: #6c757d;
            margin-bottom: 10px;
        }
        
        .upload-hint {
            font-size: 0.85em;
            color: #868e96;
        }
        
        .alert {
            padding: 15px 20px;
            border-radius: 8px;
            margin-bottom: 25px;
            font-weight: 500;
            display: flex;
            align-items: center;
        }
        
        .alert-success {
            background-color: #d4edda;
            color: #155724;
            border-left: 4px solid #28a745;
        }
        
        .alert-error {
            background-color: #f8d7da;
            color: #721c24;
            border-left: 4px solid #dc3545;
        }
        
        .alert::before {
            margin-right: 10px;
            font-size: 1.2em;
        }
        
        .alert-success::before {
            content: "✅";
        }
        
        .alert-error::before {
            content: "❌";
        }
        
        .form-actions {
            display: flex;
            gap: 15px;
            justify-content: center;
            margin-top: 30px;
            padding-top: 25px;
            border-top: 1px solid #e9ecef;
        }
        
        .btn {
            padding: 12px 30px;
            border: none;
            border-radius: 8px;
            font-size: 1em;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            justify-content: center;
        }
        
        .btn-primary {
            background: linear-gradient(135deg, #007BFF 0%, #0056b3 100%);
            color: white;
        }
        
        .btn-primary:hover {
            background: linear-gradient(135deg, #0056b3 0%, #004085 100%);
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0, 123, 255, 0.3);
        }
        
        .btn-secondary {
            background-color: #6c757d;
            color: white;
        }
        
        .btn-secondary:hover {
            background-color: #545b62;
            transform: translateY(-2px);
        }
        
        .required {
            color: #dc3545;
            margin-left: 3px;
        }
        
        .help-text {
            font-size: 0.85em;
            color: #6c757d;
            margin-top: 5px;
            font-style: italic;
        }
        
        @media (max-width: 768px) {
            .form-row {
                grid-template-columns: 1fr;
            }
            
            .form-actions {
                flex-direction: column;
            }
            
            .container {
                padding: 20px;
            }
        }
        
        .image-preview {
            margin-top: 15px;
            text-align: center;
        }
        
        .image-preview img {
            max-width: 200px;
            max-height: 200px;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        
        .file-selected {
            border-color: #28a745;
            background-color: #d4edda;
        }
        
        .file-selected .upload-text {
            color: #155724;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="page-header">
            <h1>Post a New Job</h1>
            <p>Share your project and find the perfect freelancer for your needs</p>
        </div>
        
        <nav>
            <ul>
                <li><a href="index.php">🏠 Home</a></li>
                <li><a href="account.php">👤 Account</a></li>
                <li><a href="browse_job.php">🔍 Browse Jobs</a></li>
                <li><a href="logout.php">🚪 Logout</a></li>
            </ul>
        </nav>

        <?php if (!empty($message)): ?>
            <div class="alert alert-success">
                <?php echo $message; ?>
            </div>
        <?php endif; ?>

        <?php if (!empty($error)): ?>
            <div class="alert alert-error">
                <?php echo $error; ?>
            </div>
        <?php endif; ?>

        <div class="form-container">
            <form method="POST" enctype="multipart/form-data">
                <div class="form-section">
                    <h3>Basic Information</h3>
                    <div class="form-group">
                        <label for="title">Job Title <span class="required">*</span></label>
                        <input type="text" id="title" name="title" value="<?php echo isset($_POST['title']) ? htmlspecialchars($_POST['title']) : ''; ?>" placeholder="e.g., Web Developer Needed for E-commerce Site" required>
                        <div class="help-text">Be specific and descriptive to attract the right candidates</div>
                    </div>

                    <div class="form-group">
                        <label for="description">Job Description <span class="required">*</span></label>
                        <textarea id="description" name="description" placeholder="Describe the project requirements, skills needed, and what you're looking for..." required><?php echo isset($_POST['description']) ? htmlspecialchars($_POST['description']) : ''; ?></textarea>
                        <div class="help-text">Include project scope, required skills, deliverables, and any specific requirements</div>
                    </div>
                </div>

                <div class="form-section">
                    <h3>Job Details</h3>
                    <div class="form-row">
                        <div class="form-group">
                            <label for="payment_mode">Payment Mode <span class="required">*</span></label>
                            <select id="payment_mode" name="payment_mode" required>
                                <option value="">Select payment mode</option>
                                <option value="Hourly" <?php echo (isset($_POST['payment_mode']) && $_POST['payment_mode'] == 'Hourly') ? 'selected' : ''; ?>>💰 Hourly Rate</option>
                                <option value="Fixed" <?php echo (isset($_POST['payment_mode']) && $_POST['payment_mode'] == 'Fixed') ? 'selected' : ''; ?>>💵 Fixed Price</option>
                                <option value="Per Project" <?php echo (isset($_POST['payment_mode']) && $_POST['payment_mode'] == 'Per Project') ? 'selected' : ''; ?>>📦 Per Project</option>
                                <option value="Commission" <?php echo (isset($_POST['payment_mode']) && $_POST['payment_mode'] == 'Commission') ? 'selected' : ''; ?>>📊 Commission Based</option>
                            </select>
                        </div>

                        <div class="form-group">
                            <label for="work_setup">Work Setup <span class="required">*</span></label>
                            <select id="work_setup" name="work_setup" required>
                                <option value="">Select work setup</option>
                                <option value="Remote" <?php echo (isset($_POST['work_setup']) && $_POST['work_setup'] == 'Remote') ? 'selected' : ''; ?>>🏠 Remote Work</option>
                                <option value="On-site" <?php echo (isset($_POST['work_setup']) && $_POST['work_setup'] == 'On-site') ? 'selected' : ''; ?>>🏢 On-site Work</option>
                                <option value="Hybrid" <?php echo (isset($_POST['work_setup']) && $_POST['work_setup'] == 'Hybrid') ? 'selected' : ''; ?>>🔄 Hybrid (Remote + On-site)</option>
                            </select>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="deadline">Project Deadline <span class="required">*</span></label>
                        <input type="date" id="deadline" name="deadline" value="<?php echo isset($_POST['deadline']) ? $_POST['deadline'] : ''; ?>" required>
                        <div class="help-text">Set a realistic deadline for project completion</div>
                    </div>
                </div>

                <div class="form-section">
                    <h3>Project Visual</h3>
                    <div class="file-upload-container" id="uploadContainer">
                        <div class="upload-icon">📁</div>
                        <div class="upload-text" id="uploadText">Click here to upload a project image or reference</div>
                        <input type="file" id="image" name="image" accept="image/*">
                        <div class="upload-hint">Accepted formats: JPEG, PNG, GIF • Max size: 5MB</div>
                    </div>
                    <div class="image-preview" id="imagePreview" style="display: none;">
                        <img id="previewImg" src="" alt="Preview">
                        <p style="margin-top: 10px; color: #28a745; font-weight: 500;">✅ Image selected successfully!</p>
                    </div>
                </div>

                <div class="form-actions">
                    <button type="submit" class="btn btn-primary">
                        🚀 Post Job
                    </button>
                    <a href="index.php" class="btn btn-secondary">
                        ❌ Cancel
                    </a>
                </div>
            </form>
        </div>
    </div>
    
    <script>
        // File upload handling
        document.getElementById('image').addEventListener('change', function(e) {
            const file = e.target.files[0];
            const uploadContainer = document.getElementById('uploadContainer');
            const uploadText = document.getElementById('uploadText');
            const imagePreview = document.getElementById('imagePreview');
            const previewImg = document.getElementById('previewImg');
            
            if (file) {
                // Validate file type
                const allowedTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif'];
                if (!allowedTypes.includes(file.type)) {
                    alert('Please select a valid image file (JPEG, PNG, or GIF)');
                    this.value = '';
                    return;
                }
                
                // Validate file size (5MB)
                const maxSize = 5 * 1024 * 1024; // 5MB in bytes
                if (file.size > maxSize) {
                    alert('File size must be less than 5MB');
                    this.value = '';
                    return;
                }
                
                // Show preview
                const reader = new FileReader();
                reader.onload = function(e) {
                    previewImg.src = e.target.result;
                    imagePreview.style.display = 'block';
                };
                reader.readAsDataURL(file);
                
                // Update upload container styling
                uploadContainer.classList.add('file-selected');
                uploadText.textContent = 'File selected: ' + file.name;
                
            } else {
                // Reset if no file selected
                imagePreview.style.display = 'none';
                uploadContainer.classList.remove('file-selected');
                uploadText.textContent = 'Click here to upload a project image or reference';
            }
        });
        
        // Click anywhere in the upload container to trigger file selection
        document.getElementById('uploadContainer').addEventListener('click', function(e) {
            if (e.target !== document.getElementById('image')) {
                document.getElementById('image').click();
            }
        });
    </script>
</body>
</html>
