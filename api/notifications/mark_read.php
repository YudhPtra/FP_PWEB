<?php
// api/notifications/mark_read.php
session_start();
require_once '../../config/database.php';
header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    http_response_code(403);
    exit;
}

$user_id = $_SESSION['user_id'];
$input = json_decode(file_get_contents('php://input'), true);
$notif_id = isset($input['id']) ? $input['id'] : null;

if ($notif_id) {
    // Update status jadi '1' (Sudah dibaca)
    // Pastikan cuma bisa update notifikasi milik user yang login (Security)
    $query = "UPDATE notifications SET is_read = 1 WHERE id = '$notif_id' AND user_id = '$user_id'";
    
    if (mysqli_query($conn, $query)) {
        echo json_encode(['status' => 'success']);
    } else {
        echo json_encode(['status' => 'error']);
    }
} else {
    echo json_encode(['status' => 'error', 'message' => 'ID kosong']);
}
?>