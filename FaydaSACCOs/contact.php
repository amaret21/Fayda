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
    $name = htmlspecialchars($_POST['name']);
    $email = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL);
    $subject = htmlspecialchars($_POST['subject']);
    $message = htmlspecialchars($_POST['message']);
    
    // Insert contact message
    $stmt = $conn->prepare("INSERT INTO contact_messages (name, email, subject, message, submission_date) VALUES (?, ?, ?, ?, NOW())");
    $stmt->bind_param("ssss", $name, $email, $subject, $message);
    
    if ($stmt->execute()) {
        // Send email notification (in a real implementation)
        // mail('info@faydasacco.com', 'New Contact Form Submission', $message);
        
        // Redirect to thank you page
        header("Location: contact.html?status=success");
        exit();
    } else {
        // Error occurred
        header("Location: contact.html?status=error");
        exit();
    }
    
    $stmt->close();
}

$conn->close();
?>