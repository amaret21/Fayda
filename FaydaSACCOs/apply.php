<?php
// Database configuration
$servername = "localhost";
$username = "your_username";
$password = "your_password";
$dbname = "faydasacco";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Process form data when form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Validate and sanitize input
    $firstName = htmlspecialchars($_POST['firstName']);
    $lastName = htmlspecialchars($_POST['lastName']);
    $email = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL);
    $phone = htmlspecialchars($_POST['phone']);
    $address = htmlspecialchars($_POST['address']);
    $education = htmlspecialchars($_POST['education']);
    $experience = htmlspecialchars($_POST['experience']);
    $coverLetter = htmlspecialchars($_POST['coverLetter']);
    $position = htmlspecialchars($_POST['position']);
    
    // File upload handling
    $uploadDir = "uploads/applications/";
    
    // Create upload directory if it doesn't exist
    if (!file_exists($uploadDir)) {
        mkdir($uploadDir, 0777, true);
    }
    
    // Process resume file
    $resumeName = basename($_FILES["resume"]["name"]);
    $resumeTmp = $_FILES["resume"]["tmp_name"];
    $resumePath = $uploadDir . uniqid() . "_" . $resumeName;
    
    // Validate file type and size
    $resumeFileType = strtolower(pathinfo($resumeName, PATHINFO_EXTENSION));
    $allowedTypes = array("pdf", "doc", "docx");
    
    if (!in_array($resumeFileType, $allowedTypes)) {
        header("Location: vacancy.html?error=invalid_file_type");
        exit();
    }
    
    if ($_FILES["resume"]["size"] > 2097152) { // 2MB
        header("Location: vacancy.html?error=file_too_large");
        exit();
    }
    
    // Move uploaded resume file
    if (!move_uploaded_file($resumeTmp, $resumePath)) {
        header("Location: vacancy.html?error=upload_failed");
        exit();
    }
    
    // Process certificates file if provided
    $certificatesPath = null;
    if (!empty($_FILES["certificates"]["name"])) {
        $certificatesName = basename($_FILES["certificates"]["name"]);
        $certificatesTmp = $_FILES["certificates"]["tmp_name"];
        $certificatesPath = $uploadDir . uniqid() . "_" . $certificatesName;
        
        $certificatesFileType = strtolower(pathinfo($certificatesName, PATHINFO_EXTENSION));
        
        if (!in_array($certificatesFileType, $allowedTypes)) {
            unlink($resumePath); // Remove already uploaded resume
            header("Location: vacancy.html?error=invalid_file_type");
            exit();
        }
        
        if ($_FILES["certificates"]["size"] > 5242880) { // 5MB
            unlink($resumePath); // Remove already uploaded resume
            header("Location: vacancy.html?error=file_too_large");
            exit();
        }
        
        if (!move_uploaded_file($certificatesTmp, $certificatesPath)) {
            unlink($resumePath); // Remove already uploaded resume
            header("Location: vacancy.html?error=upload_failed");
            exit();
        }
    }
    
    // Insert application into database
    $stmt = $conn->prepare("INSERT INTO job_applications 
                            (first_name, last_name, email, phone, address, education, 
                             experience, cover_letter, position, resume_path, certificates_path, 
                             application_date) 
                            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())");
    
    $stmt->bind_param("sssssssssss", 
                     $firstName, $lastName, $email, $phone, $address, 
                     $education, $experience, $coverLetter, $position, 
                     $resumePath, $certificatesPath);
    
    if ($stmt->execute()) {
        // Send email notification (in a real implementation)
        $to = "careers@faydasacco.com";
        $subject = "New Job Application: " . $position;
        $message = "A new application has been received:\n\n";
        $message .= "Name: $firstName $lastName\n";
        $message .= "Email: $email\n";
        $message .= "Phone: $phone\n";
        $message .= "Position: $position\n\n";
        $message .= "Login to the admin panel to view the complete application.";
        
        // mail($to, $subject, $message);
        
        // Redirect to thank you page
        header("Location: application-thank-you.html");
        exit();
    } else {
        // Clean up uploaded files if database insert failed
        if (file_exists($resumePath)) {
            unlink($resumePath);
        }
        if ($certificatesPath && file_exists($certificatesPath)) {
            unlink($certificatesPath);
        }
        
        header("Location: vacancy.html?error=application_failed");
        exit();
    }
    
    $stmt->close();
}

$conn->close();
?>