<?php
// api/admin/read_all.php
session_start();
require_once '../../config/database.php';
header('Content-Type: application/json');

// 1. Cek Apakah dia ADMIN?
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    http_response_code(403);
    echo json_encode(['status' => 'error', 'message' => 'Bukan Admin!']);
    exit;
}

// 2. Query JOIN 3 Tabel (Tasks + Categories + Users)
$query = "SELECT t.*, c.nama_kategori, c.warna_label, u.nama as nama_user 
          FROM tasks t
          JOIN categories c ON t.category_id = c.id
          JOIN users u ON t.user_id = u.id
          ORDER BY t.created_at DESC";

$result = mysqli_query($conn, $query);
$data = [];

while ($row = mysqli_fetch_assoc($result)) {
    $data[] = $row;
}

echo json_encode($data);
?>