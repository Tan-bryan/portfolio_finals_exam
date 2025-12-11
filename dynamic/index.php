<?php
require_once 'includes/config.php';
require_once 'includes/auth.php';
require_once 'includes/functions.php';

// If already logged in, redirect to dashboard
if (isLoggedIn()) {
    header('Location: dashboard.php');
    exit();
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';
    
    if (login($username, $password, $pdo)) {
        header('Location: dashboard.php');
        exit();
    } else {
        $error = 'Invalid username or password.';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Portfolio Builder</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .login-container {
            max-width: 450px;
            width: 100%;
        }
        .login-card {
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
        <div class="login-container mx-auto">
            <div class="login-card">
                <div class="logo-section">
                    <i class="bi bi-person-circle"></i>
                    <h2>Portfolio Builder</h2>
                    <p class="text-muted">Sign in to manage your portfolio</p>
                </div>
                
                <?php if ($error): ?>
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <?php echo $error; ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>
                
                <form method="POST" action="">
                    <div class="mb-3">
                        <label for="username" class="form-label">Username</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="bi bi-person"></i></span>
                            <input type="text" class="form-control" id="username" name="username" required>
                        </div>
                    </div>
                    
                    <div class="mb-4">
                        <label for="password" class="form-label">Password</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="bi bi-lock"></i></span>
                            <input type="password" class="form-control" id="password" name="password" required>
                        </div>
                    </div>
                    
                    <button type="submit" class="btn btn-primary w-100 mb-3">
                        <i class="bi bi-box-arrow-in-right me-2"></i>Sign In
                    </button>
                    
                    <div class="text-center">
                        <p class="mb-0">Don't have an account? <a href="register.php">Create one</a></p>
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