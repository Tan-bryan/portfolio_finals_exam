<?php
require_once 'includes/config.php';
require_once 'includes/auth.php';

// If already logged in, redirect to dashboard
if (isLoggedIn()) {
    header('Location: admin/dashboard.php');
    exit();
}

$message = '';
$messageType = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'] ?? '';
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';
    $confirmPassword = $_POST['confirm_password'] ?? '';
    
    // Validation
    if (strlen($username) < 3) {
        $message = 'Username must be at least 3 characters.';
        $messageType = 'danger';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $message = 'Invalid email address.';
        $messageType = 'danger';
    } elseif (strlen($password) < 6) {
        $message = 'Password must be at least 6 characters.';
        $messageType = 'danger';
    } elseif ($password !== $confirmPassword) {
        $message = 'Passwords do not match.';
        $messageType = 'danger';
    } else {
        $result = register($username, $email, $password, $pdo);
        if ($result === true) {
            $message = 'Registration successful! You can now log in.';
            $messageType = 'success';
        } else {
            $message = $result;
            $messageType = 'danger';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - Portfolio Builder</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px 0;
        }
        .register-container {
            max-width: 500px;
            width: 100%;
        }
        .register-card {
            background: white;
            border-radius: 15px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.2);
            padding: 40px;
        }
        .logo-section {
            text-align: center;
            margin-bottom: 30px;
        }
        .logo-section i {
            font-size: 60px;
            color: #667eea;
        }
        .logo-section h2 {
            margin-top: 15px;
            color: #333;
            font-weight: 700;
        }
        .btn-primary {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border: none;
            padding: 12px;
            font-weight: 600;
        }
        .btn-primary:hover {
            opacity: 0.9;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="register-container mx-auto">
            <div class="register-card">
                <div class="logo-section">
                    <i class="bi bi-person-plus-fill"></i>
                    <h2>Create Account</h2>
                    <p class="text-muted">Start building your portfolio today</p>
                </div>
                
                <?php if ($message): ?>
                    <div class="alert alert-<?php echo $messageType; ?> alert-dismissible fade show" role="alert">
                        <?php echo $message; ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>
                
                <form method="POST" action="">
                    <div class="mb-3">
                        <label for="username" class="form-label">Username</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="bi bi-person"></i></span>
                            <input type="text" class="form-control" id="username" name="username" required minlength="3">
                        </div>
                        <small class="form-text text-muted">At least 3 characters</small>
                    </div>
                    
                    <div class="mb-3">
                        <label for="email" class="form-label">Email</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="bi bi-envelope"></i></span>
                            <input type="email" class="form-control" id="email" name="email" required>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="password" class="form-label">Password</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="bi bi-lock"></i></span>
                            <input type="password" class="form-control" id="password" name="password" required minlength="6">
                        </div>
                        <small class="form-text text-muted">At least 6 characters</small>
                    </div>
                    
                    <div class="mb-4">
                        <label for="confirm_password" class="form-label">Confirm Password</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="bi bi-lock-fill"></i></span>
                            <input type="password" class="form-control" id="confirm_password" name="confirm_password" required>
                        </div>
                    </div>
                    
                    <button type="submit" class="btn btn-primary w-100 mb-3">
                        <i class="bi bi-check-circle me-2"></i>Create Account
                    </button>
                    
                    <div class="text-center">
                        <p class="mb-0">Already have an account? <a href="index.php">Sign in</a></p>
                    </div>
                </form>
            </div>
            
            <div class="text-center mt-4">
                <a href="../static/index.php" class="btn btn-outline-light">
                    <i class="bi bi-arrow-left me-2"></i>Back to Static Portfolio
                </a>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>