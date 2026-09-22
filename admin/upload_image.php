<?php
/**
 * Admin AJAX Image & Media Upload Handler for Summernote Editor
 */
require_once __DIR__ . '/includes/auth.php';

header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['status' => 'error', 'message' => 'Invalid request method']);
    exit;
}

$fileKey = null;
if (isset($_FILES['file']) && $_FILES['file']['error'] === UPLOAD_ERR_OK) {
    $fileKey = 'file';
} elseif (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
    $fileKey = 'image';
}

if (!$fileKey) {
    echo json_encode(['status' => 'error', 'message' => 'No file uploaded or file upload error.']);
    exit;
}

$file = $_FILES[$fileKey];
$file_ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
$allowed_extensions = ['jpg', 'jpeg', 'png', 'webp', 'gif', 'avif', 'jfif', 'svg', 'bmp'];

if (!in_array($file_ext, $allowed_extensions)) {
    echo json_encode(['status' => 'error', 'message' => 'अमान्य फ़ोटो फ़ॉर्मेट! केवल JPG, PNG, WEBP, GIF मान्य हैं।']);
    exit;
}

// 20MB Max size check
if ($file['size'] > 20 * 1024 * 1024) {
    echo json_encode(['status' => 'error', 'message' => 'फ़ाइल साइज़ 20MB से अधिक नहीं होना चाहिए।']);
    exit;
}

if (!is_dir(UPLOAD_DIR)) {
    mkdir(UPLOAD_DIR, 0777, true);
}

$new_filename = 'content_' . time() . '_' . rand(1000, 9999) . '.' . $file_ext;
$target_path = UPLOAD_DIR . $new_filename;

if (move_uploaded_file($file['tmp_name'], $target_path)) {
    $file_url = UPLOAD_URL . $new_filename;
    echo json_encode([
        'status' => 'success',
        'url' => $file_url,
        'filename' => $new_filename
    ]);
} else {
    echo json_encode(['status' => 'error', 'message' => 'सर्वर पर फ़ाइल सहेजने में विफल।']);
}
exit;
