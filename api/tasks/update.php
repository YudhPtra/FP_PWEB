<?php
session_start();
require_once '../../config/database.php';
header('Content-Type: application/json');

// Cek method harus POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);
$task_id = $input['id'];
$user_id = $_SESSION['user_id'];

// Update status jadi 'Selesai'
$query = "UPDATE tasks SET status = 'Selesai', updated_at = NOW() 
          WHERE id = '$task_id' AND user_id = '$user_id'";

if (mysqli_query($conn, $query)) {
    echo json_encode(['status' => 'success', 'message' => 'Tugas ditandai selesai!']);
} else {
    echo json_encode(['status' => 'error', 'message' => 'Gagal update database']);
}
?>