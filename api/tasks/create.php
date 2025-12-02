<?php
// api/tasks/create.php
session_start();
require_once '../../config/database.php';

header('Content-Type: application/json');

// 1. Cek Login & Method Request
if (!isset($_SESSION['user_id']) || $_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(403);
    echo json_encode(['status' => 'error', 'message' => 'Akses ditolak']);
    exit;
}

// 2. Ambil Data dari Form
$user_id = $_SESSION['user_id'];

// Ambil input dan AMANKAN dari karakter aneh (seperti tanda kutip ')
$judul = mysqli_real_escape_string($conn, $_POST['judul']);
$kategori_id = mysqli_real_escape_string($conn, $_POST['kategori_id']);
$deadline = mysqli_real_escape_string($conn, $_POST['deadline']);
$deskripsi = mysqli_real_escape_string($conn, $_POST['deskripsi']);

// Ambil prioritas dari form. Kalau user iseng hapus elementnya, default ke 'Sedang'.
$prioritas = isset($_POST['prioritas']) ? mysqli_real_escape_string($conn, $_POST['prioritas']) : 'Sedang';

// 3. Validasi Sederhana (Backend Validation)
if (empty($judul) || empty($deadline)) {
    echo json_encode(['status' => 'error', 'message' => 'Judul dan Deadline wajib diisi!']);
    exit;
}

// 4. Query Insert Database
// Perhatikan urutan kolom harus sesuai dengan VALUES
$query = "INSERT INTO tasks (
            user_id, 
            category_id, 
            judul, 
            deskripsi, 
            prioritas, 
            deadline, 
            status
          ) VALUES (
            '$user_id', 
            '$kategori_id', 
            '$judul', 
            '$deskripsi', 
            '$prioritas', 
            '$deadline', 
            'Belum'
          )";

// Eksekusi Query
if (mysqli_query($conn, $query)) {
    
    // ============================================================
    // INJEKSI KODE: Catat Log Aktivitas (Membuat Tugas)
    // ============================================================
    // Kita gunakan $judul yang sudah di-escape di atas, jadi aman.
    $log_action = "Membuat tugas baru: $judul";
    
    // Pastikan tabel activity_logs ada kolom user_id, action, created_at
    $query_log = "INSERT INTO activity_logs (user_id, action, created_at) VALUES ('$user_id', '$log_action', NOW())";
    mysqli_query($conn, $query_log);
    // ============================================================

    echo json_encode(['status' => 'success', 'message' => 'Tugas berhasil disimpan!']);

} else {
    // Tampilkan error sql jika ada (untuk debugging)
    echo json_encode(['status' => 'error', 'message' => 'Gagal menyimpan: ' . mysqli_error($conn)]);
}
?>