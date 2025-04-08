<?php
// Include database connection
require_once 'db_connect.php';

// Initialize variables
$errors = [];
$success = false;

// Process form when submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Validate and sanitize inputs
    $fullName = trim(htmlspecialchars($_POST['fullName']));
    $email = trim(filter_var($_POST['email'], FILTER_SANITIZE_EMAIL));
    $phone = trim(htmlspecialchars($_POST['phone']));
    $password = $_POST['password'];
    $confirmPassword = $_POST['confirmPassword'];
    $address = trim(htmlspecialchars($_POST['address']));
    $dob = $_POST['dob'];
    $gender = trim(htmlspecialchars($_POST['gender']));
    $idType = trim(htmlspecialchars($_POST['idType']));
    $idNumber = trim(htmlspecialchars($_POST['idNumber']));
    $terms = isset($_POST['terms']) ? true : false;

    // Validate inputs
    if (empty($fullName)) {
        $errors['fullName'] = "Full name is required";
    }

    if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors['email'] = "Valid email is required";
    }

    if (empty($phone) || !preg_match("/^[0-9]{10,15}$/", $phone)) {
        $errors['phone'] = "Valid phone number is required";
    }

    if (empty($password) || strlen($password) < 8) {
        $errors['password'] = "Password must be at least 8 characters";
    }

    if ($password !== $confirmPassword) {
        $errors['confirmPassword'] = "Passwords do not match";
    }

    if (empty($address)) {
        $errors['address'] = "Address is required";
    }

    if (empty($dob)) {
        $errors['dob'] = "Date of birth is required";
    } else {
        $dobDate = new DateTime($dob);
        $today = new DateTime();
        $age = $today->diff($dobDate)->y;
        
        if ($age < 18) {
            $errors['dob'] = "You must be at least 18 years old";
        }
    }

    if (empty($gender)) {
        $errors['gender'] = "Gender is required";
    }

    if (empty($idType)) {
        $errors['idType'] = "ID type is required";
    }

    if (empty($idNumber)) {
        $errors['idNumber'] = "ID number is required";
    }

    if (!$terms) {
        $errors['terms'] = "You must accept the terms and conditions";
    }

    // Check if email already exists
    if (empty($errors['email'])) {
        $stmt = $conn->prepare("SELECT member_id FROM members WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $stmt->store_result();
        
        if ($stmt->num_rows > 0) {
            $errors['email'] = "Email already registered";
        }
        $stmt->close();
    }

    // Check if ID number already exists
    if (empty($errors['idNumber'])) {
        $stmt = $conn->prepare("SELECT member_id FROM members WHERE id_number = ?");
        $stmt->bind_param("s", $idNumber);
        $stmt->execute();
        $stmt->store_result();
        
        if ($stmt->num_rows > 0) {
            $errors['idNumber'] = "ID number already registered";
        }
        $stmt->close();
    }

    // If no errors, proceed with registration
    if (empty($errors)) {
        // Hash password
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
        
        // Generate member number
        $memberNumber = 'FSC' . date('Y') . str_pad(rand(0, 9999), 4, '0', STR_PAD_LEFT);
        
        // Start transaction
        $conn->begin_transaction();
        
        try {
            // Insert into members table
            $stmt = $conn->prepare("INSERT INTO members 
                                   (full_name, email, phone, password, address, 
                                    dob, gender, id_type, id_number, member_number, 
                                    registration_date) 
                                   VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())");
            
            $stmt->bind_param("ssssssssss", $fullName, $email, $phone, $hashedPassword, 
                             $address, $dob, $gender, $idType, $idNumber, $memberNumber);
            
            if (!$stmt->execute()) {
                throw new Exception("Failed to register member");
            }
            
            $memberId = $stmt->insert_id;
            $stmt->close();
            
            // Create savings account
            $accountNumber = 'SAV' . $memberNumber;
            $stmt = $conn->prepare("INSERT INTO savings_accounts 
                                   (member_id, account_type, account_number, 
                                    interest_rate, opened_date) 
                                   VALUES (?, 'regular', ?, 5.0, NOW())");
            $stmt->bind_param("is", $memberId, $accountNumber);
            
            if (!$stmt->execute()) {
                throw new Exception("Failed to create savings account");
            }
            $stmt->close();
            
            // Record initial deposit transaction (if any)
            if (isset($_POST['initialDeposit']) && is_numeric($_POST['initialDeposit']) && $_POST['initialDeposit'] > 0) {
                $initialDeposit = $_POST['initialDeposit'];
                $accountId = $conn->insert_id;
                
                $stmt = $conn->prepare("INSERT INTO transactions 
                                       (member_id, account_id, amount, 
                                        transaction_type, description, 
                                        transaction_date, balance_after) 
                                       VALUES (?, ?, ?, 'deposit', 'Initial deposit', NOW(), ?)");
                $stmt->bind_param("iidd", $memberId, $accountId, $initialDeposit, $initialDeposit);
                
                if (!$stmt->execute()) {
                    throw new Exception("Failed to record initial deposit");
                }
                $stmt->close();
                
                // Update account balance
                $stmt = $conn->prepare("UPDATE savings_accounts SET balance = ? WHERE account_id = ?");
                $stmt->bind_param("di", $initialDeposit, $accountId);
                
                if (!$stmt->execute()) {
                    throw new Exception("Failed to update account balance");
                }
                $stmt->close();
            }
            
            // Commit transaction
            $conn->commit();
            $success = true;
            
            // Send confirmation email (in a real implementation)
            // $to = $email;
            // $subject = "Welcome to Fayda SACCO";
            // $message = "Dear $fullName,\n\nThank you for registering with Fayda SACCO.\n\n";
            // $message .= "Your member number is: $memberNumber\n";
            // $message .= "Your savings account number is: $accountNumber\n\n";
            // $message .= "Please visit any branch to complete your registration.\n\n";
            // $message .= "Best regards,\nFayda SACCO Team";
            // mail($to, $subject, $message);
            
            // Redirect to success page
            header("Location: registration-success.php?member=" . urlencode($memberNumber));
            exit();
            
        } catch (Exception $e) {
            // Rollback transaction on error
            $conn->rollback();
            $errors['database'] = "Registration failed. Please try again.";
        }
    }
}

// Close connection
$conn->close();

// If we get here, display the form again with errors
// In a real implementation, you would redirect back to the form with error messages
// For this example, we'll include the registration form
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - Fayda SACCO</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="css/style.css">
    <link rel="icon" type="image/x-icon" href="images/favicon.ico">
</head>
<body>
    <!-- Top Bar -->
    <div class="top-bar bg-primary text-white py-2">
        <div class="container">
            <div class="row">
                <div class="col-md-8">
                    <marquee>Welcome to Fayda SACCO! Together to The Future!</marquee>
                </div>
                <div class="col-md-2 text-end">
                    <select id="language" class="form-select form-select-sm d-inline-block w-auto">
                        <option value="en">English</option>
                        <option value="am">አማርኛ</option>
                        <option value="om">Afaan Oromo</option>
                    </select>
                </div>
                <div class="col-md-2 text-end">
                    <span id="datetime"></span>
                    <a href="login.html" class="btn btn-sm btn-outline-light">Login</a>
                </div>
            </div>
        </div>
    </div>

    <!-- Header -->
    <header class="sticky-top">
        <nav class="navbar navbar-expand-lg navbar-light bg-white shadow-sm">
            <div class="container">
                <a class="navbar-brand" href="index.html">
                    <img src="images/logo.png" alt="Fayda SACCO Logo" width="80" height="75" class="d-inline-block align-top">
                    <span class="logo-text">Fayda SACCO</span><br>
                    <small class="logo-slogan">Together to The Future!</small>
                </a>
                <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                    <span class="navbar-toggler-icon"></span>
                </button>
                <div class="collapse navbar-collapse" id="navbarNav">
                    <ul class="navbar-nav ms-auto">
                        <li class="nav-item"><a class="nav-link" href="index.html">Home</a></li>
                        <li class="nav-item"><a class="nav-link" href="about.html">About Us</a></li>
                        <li class="nav-item"><a class="nav-link" href="services.html">Services</a></li>
                        <li class="nav-item"><a class="nav-link" href="contact.html">Contact</a></li>
                        <li class="nav-item"><a class="nav-link active" href="register.html">Register</a></li>
                        <li class="nav-item"><a class="nav-link" href="login.html">Login</a></li>
                    </ul>
                </div>
            </div>
        </nav>
    </header>

    <main class="container my-5">
        <div class="row justify-content-center">
            <div class="col-md-8 col-lg-6">
                <div class="card shadow">
                    <div class="card-header bg-primary text-white">
                        <h2 class="mb-0">Member Registration</h2>
                    </div>
                    <div class="card-body p-4">
                        <?php if (!empty($errors['database'])): ?>
                            <div class="alert alert-danger">
                                <?php echo $errors['database']; ?>
                            </div>
                        <?php endif; ?>
                        
                        <form id="registrationForm" action="register.php" method="POST">
                            <div class="mb-3">
                                <label for="fullName" class="form-label">Full Name *</label>
                                <input type="text" class="form-control <?php echo isset($errors['fullName']) ? 'is-invalid' : ''; ?>" 
                                       id="fullName" name="fullName" value="<?php echo isset($fullName) ? htmlspecialchars($fullName) : ''; ?>" required>
                                <?php if (isset($errors['fullName'])): ?>
                                    <div class="invalid-feedback">
                                        <?php echo $errors['fullName']; ?>
                                    </div>
                                <?php endif; ?>
                            </div>
                            
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="email" class="form-label">Email Address *</label>
                                    <input type="email" class="form-control <?php echo isset($errors['email']) ? 'is-invalid' : ''; ?>" 
                                           id="email" name="email" value="<?php echo isset($email) ? htmlspecialchars($email) : ''; ?>" required>
                                    <?php if (isset($errors['email'])): ?>
                                        <div class="invalid-feedback">
                                            <?php echo $errors['email']; ?>
                                        </div>
                                    <?php endif; ?>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="phone" class="form-label">Phone Number *</label>
                                    <input type="tel" class="form-control <?php echo isset($errors['phone']) ? 'is-invalid' : ''; ?>" 
                                           id="phone" name="phone" value="<?php echo isset($phone) ? htmlspecialchars($phone) : ''; ?>" required>
                                    <?php if (isset($errors['phone'])): ?>
                                        <div class="invalid-feedback">
                                            <?php echo $errors['phone']; ?>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                            
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="password" class="form-label">Password *</label>
                                    <input type="password" class="form-control <?php echo isset($errors['password']) ? 'is-invalid' : ''; ?>" 
                                           id="password" name="password" required>
                                    <?php if (isset($errors['password'])): ?>
                                        <div class="invalid-feedback">
                                            <?php echo $errors['password']; ?>
                                        </div>
                                    <?php endif; ?>
                                    <div class="form-text">At least 8 characters</div>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="confirmPassword" class="form-label">Confirm Password *</label>
                                    <input type="password" class="form-control <?php echo isset($errors['confirmPassword']) ? 'is-invalid' : ''; ?>" 
                                           id="confirmPassword" name="confirmPassword" required>
                                    <?php if (isset($errors['confirmPassword'])): ?>
                                        <div class="invalid-feedback">
                                            <?php echo $errors['confirmPassword']; ?>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                            
                            <div class="mb-3">
                                <label for="address" class="form-label">Address *</label>
                                <textarea class="form-control <?php echo isset($errors['address']) ? 'is-invalid' : ''; ?>" 
                                          id="address" name="address" rows="2" required><?php echo isset($address) ? htmlspecialchars($address) : ''; ?></textarea>
                                <?php if (isset($errors['address'])): ?>
                                    <div class="invalid-feedback">
                                        <?php echo $errors['address']; ?>
                                    </div>
                                <?php endif; ?>
                            </div>
                            
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="dob" class="form-label">Date of Birth *</label>
                                    <input type="date" class="form-control <?php echo isset($errors['dob']) ? 'is-invalid' : ''; ?>" 
                                           id="dob" name="dob" value="<?php echo isset($dob) ? htmlspecialchars($dob) : ''; ?>" required>
                                    <?php if (isset($errors['dob'])): ?>
                                        <div class="invalid-feedback">
                                            <?php echo $errors['dob']; ?>
                                        </div>
                                    <?php endif; ?>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="gender" class="form-label">Gender *</label>
                                    <select class="form-select <?php echo isset($errors['gender']) ? 'is-invalid' : ''; ?>" 
                                            id="gender" name="gender" required>
                                        <option value="" selected disabled>Select Gender</option>
                                        <option value="male" <?php echo (isset($gender) && $gender === 'male') ? 'selected' : ''; ?>>Male</option>
                                        <option value="female" <?php echo (isset($gender) && $gender === 'female') ? 'selected' : ''; ?>>Female</option>
                                        <option value="other" <?php echo (isset($gender) && $gender === 'other') ? 'selected' : ''; ?>>Other</option>
                                    </select>
                                    <?php if (isset($errors['gender'])): ?>
                                        <div class="invalid-feedback">
                                            <?php echo $errors['gender']; ?>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                            
                            <div class="mb-3">
                                <label for="idType" class="form-label">ID Type *</label>
                                <select class="form-select <?php echo isset($errors['idType']) ? 'is-invalid' : ''; ?>" 
                                        id="idType" name="idType" required>
                                    <option value="" selected disabled>Select ID Type</option>
                                    <option value="national" <?php echo (isset($idType) && $idType === 'national') ? 'selected' : ''; ?>>National ID</option>
                                    <option value="passport" <?php echo (isset($idType) && $idType === 'passport') ? 'selected' : ''; ?>>Passport</option>
                                    <option value="driving" <?php echo (isset($idType) && $idType === 'driving') ? 'selected' : ''; ?>>Driving License</option>
                                </select>
                                <?php if (isset($errors['idType'])): ?>
                                    <div class="invalid-feedback">
                                        <?php echo $errors['idType']; ?>
                                    </div>
                                <?php endif; ?>
                            </div>
                            
                            <div class="mb-3">
                                <label for="idNumber" class="form-label">ID Number *</label>
                                <input type="text" class="form-control <?php echo isset($errors['idNumber']) ? 'is-invalid' : ''; ?>" 
                                       id="idNumber" name="idNumber" value="<?php echo isset($idNumber) ? htmlspecialchars($idNumber) : ''; ?>" required>
                                <?php if (isset($errors['idNumber'])): ?>
                                    <div class="invalid-feedback">
                                        <?php echo $errors['idNumber']; ?>
                                    </div>
                                <?php endif; ?>
                            </div>
                            
                            <div class="mb-3">
                                <label for="initialDeposit" class="form-label">Initial Deposit (Optional)</label>
                                <input type="number" class="form-control" 
                                       id="initialDeposit" name="initialDeposit" min="0" step="0.01" 
                                       value="<?php echo isset($_POST['initialDeposit']) ? htmlspecialchars($_POST['initialDeposit']) : ''; ?>">
                                <div class="form-text">Minimum 100 ETB. Can be paid at branch if not deposited now.</div>
                            </div>
                            
                            <div class="mb-4">
                                <div class="form-check">
                                    <input class="form-check-input <?php echo isset($errors['terms']) ? 'is-invalid' : ''; ?>" 
                                           type="checkbox" id="terms" name="terms" required>
                                    <label class="form-check-label" for="terms">
                                        I agree to the <a href="#" data-bs-toggle="modal" data-bs-target="#termsModal">Terms and Conditions</a> *
                                    </label>
                                    <?php if (isset($errors['terms'])): ?>
                                        <div class="invalid-feedback">
                                            <?php echo $errors['terms']; ?>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                            
                            <div class="d-grid gap-2">
                                <button type="submit" class="btn btn-primary btn-lg">Register</button>
                                <a href="login.html" class="btn btn-outline-secondary">Already have an account? Login</a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <!-- Terms Modal -->
    <div class="modal fade" id="termsModal" tabindex="-1" aria-labelledby="termsModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="termsModalLabel">Terms and Conditions</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <h6>Membership Requirements</h6>
                    <p>To become a member of Fayda SACCO, you must:</p>
                    <ul>
                        <li>Be at least 18 years old</li>
                        <li>Provide valid identification documents</li>
                        <li>Pay the required membership fee of 200 ETB</li>
                        <li>Agree to abide by the SACCO's bylaws</li>
                    </ul>
                    
                    <h6 class="mt-4">Member Responsibilities</h6>
                    <p>As a member, you agree to:</p>
                    <ul>
                        <li>Maintain regular savings as required</li>
                        <li>Repay loans according to the agreed terms</li>
                        <li>Attend general meetings when possible</li>
                        <li>Keep your account information up to date</li>
                    </ul>
                    
                    <h6 class="mt-4">Data Protection</h6>
                    <p>We are committed to protecting your personal information in accordance with applicable data protection laws.</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-primary" data-bs-dismiss="modal">I Understand</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Footer -->
    <footer class="bg-dark text-white pt-4 pb-2">
        <div class="container">
            <div class="row g-4">
                <div class="col-lg-4">
                    <h5 class="mb-3">About Fayda SACCO</h5>
                    <p>Fayda SACCO is a member-owned financial cooperative providing savings, loans, and investment services to our community.</p>
                    <div class="social-links mt-3">
                        <a href="#" class="text-white me-2"><i class="fab fa-facebook-f"></i></a>
                        <a href="#" class="text-white me-2"><i class="fab fa-twitter"></i></a>
                        <a href="#" class="text-white me-2"><i class="fab fa-telegram"></i></a>
                    </div>
                </div>
                <div class="col-lg-2 col-md-4">
                    <h5 class="mb-3">Quick Links</h5>
                    <ul class="list-unstyled">
                        <li class="mb-2"><a href="index.html" class="text-white">Home</a></li>
                        <li class="mb-2"><a href="about.html" class="text-white">About Us</a></li>
                        <li class="mb-2"><a href="services.html" class="text-white">Services</a></li>
                        <li class="mb-2"><a href="contact.html" class="text-white">Contact</a></li>
                    </ul>
                </div>
                <div class="col-lg-3 col-md-4">
                    <h5 class="mb-3">Contact Us</h5>
                    <ul class="list-unstyled">
                        <li class="mb-2"><i class="fas fa-map-marker-alt me-2"></i> Addis Ababa, Ethiopia</li>
                        <li class="mb-2"><i class="fas fa-phone me-2"></i> +251 123 456 789</li>
                        <li class="mb-2"><i class="fas fa-envelope me-2"></i> info@faydasacco.com</li>
                    </ul>
                </div>
                <div class="col-lg-3 col-md-4">
                    <h5 class="mb-3">Newsletter</h5>
                    <p>Subscribe to our newsletter for updates.</p>
                    <form class="mb-3">
                        <div class="input-group">
                            <input type="email" class="form-control" placeholder="Your Email">
                            <button class="btn btn-primary" type="submit">Subscribe</button>
                        </div>
                    </form>
                </div>
            </div>
            <hr class="my-4">
            <div class="row">
                <div class="col-md-6">
                    <p class="mb-0">&copy; 2023 Fayda SACCO. All rights reserved.</p>
                </div>
                <div class="col-md-6 text-md-end">
                    <a href="#" class="text-white me-3">Privacy Policy</a>
                    <a href="#" class="text-white">Terms of Service</a>
                </div>
            </div>
        </div>
    </footer>

    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="js/script.js"></script>
    
    <script>
        // Date and time display
        function updateDateTime() {
            const now = new Date();
            const options = { 
                weekday: 'long', 
                year: 'numeric', 
                month: 'long', 
                day: 'numeric',
                hour: '2-digit',
                minute: '2-digit'
            };
            document.getElementById('datetime').textContent = now.toLocaleDateString('en-US', options);
        }
        
        setInterval(updateDateTime, 60000);
        updateDateTime();
        
        // Password strength check
        document.getElementById('password').addEventListener('input', function() {
            const password = this.value;
            const strengthText = document.getElementById('passwordStrength');
            
            if (password.length === 0) {
                return;
            }
            
            // Simple strength check (in a real app, use more robust checks)
            if (password.length < 8) {
                this.setCustomValidity("Password must be at least 8 characters");
            } else {
                this.setCustomValidity("");
            }
        });
        
        // Confirm password match
        document.getElementById('confirmPassword').addEventListener('input', function() {
            const password = document.getElementById('password').value;
            const confirmPassword = this.value;
            
            if (confirmPassword !== password) {
                this.setCustomValidity("Passwords do not match");
            } else {
                this.setCustomValidity("");
            }
        });
        
        // Age verification
        document.getElementById('dob').addEventListener('change', function() {
            const dob = new Date(this.value);
            const today = new Date();
            const age = today.getFullYear() - dob.getFullYear();
            const monthDiff = today.getMonth() - dob.getMonth();
            
            if (monthDiff < 0 || (monthDiff === 0 && today.getDate() < dob.getDate())) {
                age--;
            }
            
            if (age < 18) {
                this.setCustomValidity("You must be at least 18 years old");
            } else {
                this.setCustomValidity("");
            }
        });
    </script>
</body>
</html>