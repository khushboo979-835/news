<?php
/**
 * Admin AJAX Endpoint: Automatically saves a canvas-captured video poster/thumbnail
 * for MP4 uploaded videos to ensure WhatsApp & Social Media preview displays the real video frame.
 */
require_once __DIR__ . '/includes/auth.php';

header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['status' => 'error', 'message' => 'Invalid request method']);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);
$news_id = isset($_POST['news_id']) ? (int)$_POST['news_id'] : (int)($input['news_id'] ?? 0);
$poster_data = $_POST['poster_data'] ?? ($input['poster_data'] ?? '');

if ($news_id <= 0 || empty($poster_data)) {
    echo json_encode(['status' => 'error', 'message' => 'Missing news_id or poster_data']);
    exit;
}

if (strpos($poster_data, 'data:image/') !== 0) {
    echo json_encode(['status' => 'error', 'message' => 'Invalid image data URI format']);
    exit;
}

$parts = explode(',', $poster_data);
if (count($parts) !== 2) {
    echo json_encode(['status' => 'error', 'message' => 'Invalid data payload']);
    exit;
}

$decoded = base64_decode($parts[1]);
if ($decoded === false || strlen($decoded) < 500) {
    echo json_encode(['status' => 'error', 'message' => 'Image decode failed or payload too small']);
    exit;
}

if (!is_dir(UPLOAD_DIR)) {
    @mkdir(UPLOAD_DIR, 0777, true);
}

$filename = 'thumb_video_' . $news_id . '_' . time() . '.jpg';
$filepath = UPLOAD_DIR . $filename;

if (file_put_contents($filepath, $decoded)) {
    @chmod($filepath, 0644);
    
    // Update news record in database
    try {
        $stmt = $pdo->prepare("UPDATE news SET thumbnail_url = :thumb WHERE id = :id");
        $stmt->execute([':thumb' => $filename, ':id' => $news_id]);
        
        echo json_encode([
            'status' => 'success',
            'filename' => $filename,
            'url' => UPLOAD_URL . $filename,
            'message' => 'वीडियो का पोस्टर सफलतापूर्वक सुरक्षित कर दिया गया।'
        ]);
        exit;
    } catch (Exception $e) {
        echo json_encode(['status' => 'error', 'message' => 'Database update failed: ' . $e->getMessage()]);
        exit;
    }
} else {
    echo json_encode(['status' => 'error', 'message' => 'Failed to save poster file on disk']);
    exit;
}
