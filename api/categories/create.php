<?php
// api/categories/create.php
session_start();
require_once '../../config/database.php';
header('Content-Type: application/json');

// Cek Admin (Wajib)
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    http_response_code(403); exit;
}

$nama = mysqli_real_escape_string($conn, $_POST['nama_kategori']);
// Warna label bootstrap (primary, warning, danger, success, info, secondary)
$warna = mysqli_real_escape_string($conn, $_POST['warna_label']); 

$query = "INSERT INTO categories (nama_kategori, warna_label) VALUES ('$nama', '$warna')";

if (mysqli_query($conn, $query)) {
    echo json_encode(['status' => 'success', 'message' => 'Kategori berhasil ditambah']);
} else {
    echo json_encode(['status' => 'error', 'message' => 'Gagal tambah kategori']);
}
?>