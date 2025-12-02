<?php
// api/admin/delete_task.php
session_start();
require_once '../../config/database.php';
header('Content-Type: application/json');

// Cek Admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    http_response_code(403);
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);
$task_id = isset($input['id']) ? $input['id'] : null;
$alasan = isset($input['alasan']) ? $input['alasan'] : '';

if (!$task_id) {
    echo json_encode(['status' => 'error', 'message' => 'ID Tugas tidak valid']);
    exit;
}

// 1. Amankan ID Tugas dulu
$task_id = mysqli_real_escape_string($conn, $task_id);

// 2. Cari Data Tugas (Ambil Judul & User ID)
$check = mysqli_query($conn, "SELECT user_id, judul FROM tasks WHERE id = '$task_id'");
$task = mysqli_fetch_assoc($check);

if ($task) {
    $user_id_target = $task['user_id'];
    // Ambil judul mentah (jangan di-escape dulu disini)
    $judul_tugas = $task['judul']; 

    // 3. Hapus Tugas dari Database
    $delete = mysqli_query($conn, "DELETE FROM tasks WHERE id = '$task_id'");

    if ($delete) {
        
        // ============================================================
        // INJEKSI KODE: Log Aktivitas Admin
        // ============================================================
        $admin_id = $_SESSION['user_id']; 
        
        // Susun kalimat log
        $log_action_mentah = "ADMIN menghapus tugas: $judul_tugas (Alasan: $alasan)";
        
        // Amankan string log agar tidak error di database (terutama jika ada kutip ')
        $log_action_aman = mysqli_real_escape_string($conn, $log_action_mentah);
        
        // Simpan ke database
        mysqli_query($conn, "INSERT INTO activity_logs (user_id, action, created_at) VALUES ('$admin_id', '$log_action_aman', NOW())");
        // ============================================================


        // 4. SIAPKAN PESAN NOTIFIKASI KE USER
        // Kita susun kalimatnya dulu dengan format string biasa
        $pesan_mentah = "Tugas '$judul_tugas' dihapus oleh Admin. Alasan: $alasan";
        
        // Kita escape SELURUH kalimat pesan ini biar tanda kutip di dalamnya jadi aman
        $pesan_aman = mysqli_real_escape_string($conn, $pesan_mentah);
        
        // Masukkan pesan yang sudah diamankan ke dalam Query
        $query_notif = "INSERT INTO notifications (user_id, judul, pesan, tipe, is_read, created_at) 
                        VALUES ('$user_id_target', 'Pelanggaran Konten', '$pesan_aman', 'danger', 0, NOW())";
        
        if (mysqli_query($conn, $query_notif)) {
            echo json_encode(['status' => 'success', 'message' => 'Tugas dihapus & Notifikasi terkirim!']);
        } else {
            // Debugging: Tampilkan error biar jelas
            echo json_encode(['status' => 'error', 'message' => 'Gagal Insert Notif: ' . mysqli_error($conn)]);
        }
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Gagal Delete Task: ' . mysqli_error($conn)]);
    }

} else {
    echo json_encode(['status' => 'error', 'message' => 'Tugas tidak ditemukan']);
}
?>