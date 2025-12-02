<?php
// api/user/update_profile.php
session_start();
require_once '../../config/database.php';
header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    http_response_code(403);
    echo json_encode(['status' => 'error', 'message' => 'Akses ditolak']);
    exit;
}

$id = $_SESSION['user_id'];
$nama = mysqli_real_escape_string($conn, $_POST['nama']);

// 1. Update Nama Dulu (Selalu jalan)
$query_nama = "UPDATE users SET nama = '$nama' WHERE id = '$id'";
if (!mysqli_query($conn, $query_nama)) {
    echo json_encode(['status' => 'error', 'message' => 'Gagal update nama']);
    exit;
}
$_SESSION['nama'] = $nama; // Update sesi

// 2. Logic Ganti Password (Hanya jika diisi)
$pass_lama = $_POST['password_lama'];
$pass_baru = $_POST['password_baru'];
$pass_konf = $_POST['konfirmasi_password'];

// Jika salah satu kolom password diisi, maka kita jalankan validasi ketat
if (!empty($pass_lama) || !empty($pass_baru) || !empty($pass_konf)) {
    
    // Validasi A: Pastikan semua kolom terisi
    if (empty($pass_lama) || empty($pass_baru) || empty($pass_konf)) {
        echo json_encode(['status' => 'error', 'message' => 'Untuk ganti password, semua kolom password wajib diisi!']);
        exit;
    }

    // Validasi B: Cek Password Lama Benar Gak?
    $query_cek = mysqli_query($conn, "SELECT password FROM users WHERE id = '$id'");
    $user_data = mysqli_fetch_assoc($query_cek);

    if (!password_verify($pass_lama, $user_data['password'])) {
        echo json_encode(['status' => 'error', 'message' => 'Password Lama SALAH!']);
        exit;
    }

    // Validasi C: Cek Konfirmasi Cocok Gak?
    if ($pass_baru !== $pass_konf) {
        echo json_encode(['status' => 'error', 'message' => 'Konfirmasi password tidak cocok!']);
        exit;
    }

    // Validasi D: Password jangan kependekan (Opsional)
    if (strlen($pass_baru) < 6) {
        echo json_encode(['status' => 'error', 'message' => 'Password baru minimal 6 karakter!']);
        exit;
    }

    // EKSEKUSI: Hash & Update
    $hash_baru = password_hash($pass_baru, PASSWORD_DEFAULT);
    $query_pass = "UPDATE users SET password = '$hash_baru' WHERE id = '$id'";
    
    if (mysqli_query($conn, $query_pass)) {
        echo json_encode(['status' => 'success', 'message' => 'Profil & Password berhasil diperbarui!']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Gagal update password database']);
    }

} else {
    // Kalau gak ganti password, cuma update nama
    echo json_encode(['status' => 'success', 'message' => 'Nama profil berhasil diperbarui!']);
}
?>