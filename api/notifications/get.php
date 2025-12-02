<?php
// api/notifications/get.php
session_start();
require_once '../../config/database.php';
header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['unread' => 0, 'data' => []]);
    exit;
}

$user_id = $_SESSION['user_id'];

// 1. Ambil semua notifikasi user ini (Urut dari yang terbaru)
$query = "SELECT * FROM notifications WHERE user_id = '$user_id' ORDER BY created_at DESC LIMIT 10";
$result = mysqli_query($conn, $query);

$data = [];
$unread_count = 0;

while ($row = mysqli_fetch_assoc($result)) {
    $data[] = $row;
    // Hitung yang belum dibaca
    if ($row['is_read'] == 0) {
        $unread_count++;
    }
}

// 2. Kirim JSON
echo json_encode([
    'unread' => $unread_count,
    'data' => $data
]);
?>