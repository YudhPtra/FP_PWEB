<?php
// api/categories/delete.php
session_start();
require_once '../../config/database.php';
header('Content-Type: application/json');

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    http_response_code(403); exit;
}

$input = json_decode(file_get_contents('php://input'), true);
$id = $input['id'];

// Hapus kategori
$query = "DELETE FROM categories WHERE id = '$id'";
if (mysqli_query($conn, $query)) {
    echo json_encode(['status' => 'success', 'message' => 'Kategori dihapus']);
} else {
    echo json_encode(['status' => 'error', 'message' => 'Gagal hapus (Mungkin sedang dipakai user?)']);
}
?>