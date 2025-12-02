<?php
// api/admin/check_updates.php
session_start();
require_once '../../config/database.php';
header('Content-Type: application/json');

// Cek Admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    http_response_code(403);
    exit;
}

// REVISI QUERY: Tambahkan MAX(updated_at)
// Ini berguna untuk mendeteksi perubahan status (Update), bukan cuma insert baru.
$query = "SELECT 
            COUNT(*) as total_tasks, 
            MAX(id) as last_id, 
            MAX(updated_at) as last_update 
          FROM tasks";

$result = mysqli_query($conn, $query);
$data = mysqli_fetch_assoc($result);

echo json_encode($data);
?>