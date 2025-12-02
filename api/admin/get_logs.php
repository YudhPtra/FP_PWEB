<?php
// api/admin/get_logs.php
session_start();
require_once '../../config/database.php';
header('Content-Type: application/json');

// Cek Admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    http_response_code(403); exit;
}

// Ambil Log + Nama Usernya
$query = "SELECT l.*, u.nama 
          FROM activity_logs l 
          JOIN users u ON l.user_id = u.id 
          ORDER BY l.created_at DESC LIMIT 50"; // Batasi 50 terakhir biar ringan

$result = mysqli_query($conn, $query);
$data = [];
while ($row = mysqli_fetch_assoc($result)) {
    $data[] = $row;
}
echo json_encode($data);
?>