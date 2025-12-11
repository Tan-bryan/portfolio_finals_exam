<?php
// Authentication Functions

function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

function requireLogin() {
    if (!isLoggedIn()) {
        header('Location: ' . BASE_URL . 'index.php');
        exit();
    }
}

function getUserId() {
    return $_SESSION['user_id'] ?? null;
}

function getUsername() {
    return $_SESSION['username'] ?? null;
}

function login($username, $password, $pdo) {
    $stmt = $pdo->prepare("SELECT id, username, password FROM users WHERE username = ?");
    $stmt->execute([$username]);
    $user = $stmt->fetch();
    
    if ($user && password_verify($password, $user['password'])) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['username'] = $user['username'];
        return true;
    }
    return false;
}

function register($username, $email, $password, $pdo) {
    // Check if username already exists
    $stmt = $pdo->prepare("SELECT id FROM users WHERE username = ? OR email = ?");
    $stmt->execute([$username, $email]);
    if ($stmt->fetch()) {
        return "Username or email already exists.";
    }
    
    // Hash password
    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
    
    try {
        $stmt = $pdo->prepare("INSERT INTO users (username, email, password) VALUES (?, ?, ?)");
        $stmt->execute([$username, $email, $hashedPassword]);
        return true;
    } catch (PDOException $e) {
        return "Registration failed: " . $e->getMessage();
    }
}

function logout() {
    session_unset();
    session_destroy();
    header('Location: ' . BASE_URL . 'index.php');
    exit();
}
?>