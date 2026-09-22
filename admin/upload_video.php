<?php
/**
 * Admin AJAX Video Upload Handler for Summernote Editor
 */
require_once __DIR__ . '/includes/auth.php';

header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['status' => 'error', 'message' => 'Invalid request method']);
    exit;
}

$fileKey = null;
if (isset($_FILES['video']) && $_FILES['video']['error'] === UPLOAD_ERR_OK) {
    $fileKey = 'video';
} elseif (isset($_FILES['file']) && $_FILES['file']['error'] === UPLOAD_ERR_OK) {
    $fileKey = 'file';
}

if (!$fileKey) {
    $errorMsg = 'कोई वीडियो फ़ाइल नहीं मिली। कृपया फ़ाइल का साइज़ चेक करें।';
    if (isset($_FILES['video']) && $_FILES['video']['error'] === UPLOAD_ERR_INI_SIZE) {
        $errorMsg = 'वीडियो फ़ाइल का साइज़ सर्वर की सीमा से बड़ा है।';
    }
    echo json_encode(['status' => 'error', 'message' => $errorMsg]);
    exit;
}

$file = $_FILES[$fileKey];
$file_ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
$allowed_extensions = ['mp4', 'webm', 'mov', 'mkv', 'avi', '3gp', 'm4v', 'ts', 'ogv'];

if (!in_array($file_ext, $allowed_extensions)) {
    echo json_encode(['status' => 'error', 'message' => 'अमान्य वीडियो फ़ॉर्मेट! केवल MP4, WEBM, MOV, MKV, AVI मान्य हैं।']);
    exit;
}

// 100MB Max size check
if ($file['size'] > 100 * 1024 * 1024) {
    echo json_encode(['status' => 'error', 'message' => 'वीडियो फ़ाइल साइज़ 100MB से अधिक नहीं होना चाहिए।']);
    exit;
}

if (!is_dir(UPLOAD_DIR)) {
    mkdir(UPLOAD_DIR, 0777, true);
}

$new_filename = 'content_vid_' . time() . '_' . rand(1000, 9999) . '.' . $file_ext;
$target_path = UPLOAD_DIR . $new_filename;

if (move_uploaded_file($file['tmp_name'], $target_path)) {
    $file_url = UPLOAD_URL . $new_filename;
    echo json_encode([
        'status' => 'success',
        'url' => $file_url,
        'filename' => $new_filename
    ]);
} else {
    echo json_encode(['status' => 'error', 'message' => 'सर्वर पर वीडियो फ़ाइल सहेजने में विफल।']);
}
exit;
