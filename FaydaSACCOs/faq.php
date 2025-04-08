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
    $question = htmlspecialchars($_POST['question']);
    
    // Insert FAQ question
    $stmt = $conn->prepare("INSERT INTO faq_questions (name, email, question, submission_date) VALUES (?, ?, ?, NOW())");
    $stmt->bind_param("sss", $name, $email, $question);
    
    if ($stmt->execute()) {
        // Send email notification (in a real implementation)
        // mail('support@faydasacco.com', 'New FAQ Question', $question);
        
        // Redirect to thank you page
        header("Location: faq.html?status=success");
        exit();
    } else {
        // Error occurred
        header("Location: faq.html?status=error");
        exit();
    }
    
    $stmt->close();
}

$conn->close();
?>