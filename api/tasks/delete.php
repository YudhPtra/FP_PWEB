<?php
session_start();
require_once '../../config/database.php';
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);

// Pastikan ID ada dan AMAN (Escape string)
$task_id = isset($input['id']) ? mysqli_real_escape_string($conn, $input['id']) : null;
$user_id = $_SESSION['user_id'];

if (!$task_id) {
    echo json_encode(['status' => 'error', 'message' => 'ID Tugas tidak valid']);
    exit;
}

// Hapus tugas permanen
$query = "DELETE FROM tasks WHERE id = '$task_id' AND user_id = '$user_id'";

if (mysqli_query($conn, $query)) {
    
    // ============================================================
    // INJEKSI KODE: Log Aktivitas (Hapus Tugas)
    // ============================================================
    // Karena kita cuma punya ID tugas, log-nya generik aja
    $log_action = "Menghapus tugas (ID: $task_id)";
    
    mysqli_query($conn, "INSERT INTO activity_logs (user_id, action, created_at) VALUES ('$user_id', '$log_action', NOW())");
    // ============================================================

    echo json_encode(['status' => 'success', 'message' => 'Tugas berhasil dihapus!']);
} else {
    echo json_encode(['status' => 'error', 'message' => 'Gagal menghapus data: ' . mysqli_error($conn)]);
}
?>