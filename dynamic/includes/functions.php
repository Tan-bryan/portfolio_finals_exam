<?php
// Helper Functions

function sanitize($data) {
    return htmlspecialchars(strip_tags(trim($data)));
}

function uploadImage($file, $folder = 'profiles') {
    if (!isset($file) || $file['error'] !== UPLOAD_ERR_OK) {
        return ['success' => false, 'message' => 'No file uploaded or upload error.'];
    }
    
    // Check file size
    if ($file['size'] > MAX_FILE_SIZE) {
        return ['success' => false, 'message' => 'File size exceeds 5MB limit.'];
    }
    
    // Check file extension
    $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    if (!in_array($extension, ALLOWED_IMAGES)) {
        return ['success' => false, 'message' => 'Only JPG, PNG, and GIF files are allowed.'];
    }
    
    // Create upload directory if it doesn't exist
    $uploadDir = UPLOAD_PATH . $folder . '/';
    if (!file_exists($uploadDir)) {
        mkdir($uploadDir, 0777, true);
    }
    
    // Generate unique filename
    $filename = uniqid() . '_' . time() . '.' . $extension;
    $destination = $uploadDir . $filename;
    
    // Move uploaded file
    if (move_uploaded_file($file['tmp_name'], $destination)) {
        return ['success' => true, 'filename' => $folder . '/' . $filename];
    }
    
    return ['success' => false, 'message' => 'Failed to upload file.'];
}

function deleteImage($filename) {
    $filepath = UPLOAD_PATH . $filename;
    if (file_exists($filepath)) {
        return unlink($filepath);
    }
    return false;
}

function formatDate($date) {
    return date('F Y', strtotime($date));
}

function escape($string) {
    return htmlspecialchars($string, ENT_QUOTES, 'UTF-8');
}

function redirect($url) {
    header('Location: ' . $url);
    exit();
}

function showAlert($type, $message) {
    $alertClass = $type === 'success' ? 'alert-success' : 'alert-danger';
    return "<div class='alert $alertClass alert-dismissible fade show' role='alert'>
                $message
                <button type='button' class='btn-close' data-bs-dismiss='alert'></button>
            </div>";
}

function getImageUrl($filename) {
    if (empty($filename)) {
        return BASE_URL . 'assets/images/default-profile.png';
    }
    return BASE_URL . 'uploads/' . $filename;
}
?>