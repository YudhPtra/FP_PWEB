<?php
// api/user/upload_photo.php
session_start();
require_once '../../config/database.php';
header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    http_response_code(403);
    exit;
}

$id = $_SESSION['user_id'];

// 1. Cek apakah ada file yang diupload
if (isset($_FILES['foto_profil'])) {
    $file = $_FILES['foto_profil'];
    $nama_file = $file['name'];
    $tmp_name = $file['tmp_name'];
    $ukuran = $file['size'];
    $error = $file['error'];

    // 2. Validasi Ekstensi (Hanya JPG/PNG)
    $ekstensiValid = ['jpg', 'jpeg', 'png'];
    $ekstensiFile = strtolower(pathinfo($nama_file, PATHINFO_EXTENSION));

    if (!in_array($ekstensiFile, $ekstensiValid)) {
        echo json_encode(['status' => 'error', 'message' => 'Format file harus JPG atau PNG!']);
        exit;
    }

    // 3. Validasi Ukuran (Max 2MB)
    if ($ukuran > 2000000) {
        echo json_encode(['status' => 'error', 'message' => 'Ukuran file terlalu besar (Max 2MB)!']);
        exit;
    }

    // 4. Generate Nama Baru (Biar gak ketimpa)
    // Contoh: 5_1739281.jpg (IDUser_Timestamp.jpg)
    $namaBaru = $id . '_' . time() . '.' . $ekstensiFile;
    $tujuan = '../../assets/uploads/' . $namaBaru;

    // 5. Pindahkan File
    if (move_uploaded_file($tmp_name, $tujuan)) {
        // Update Database
        $query = "UPDATE users SET foto_profil = '$namaBaru' WHERE id = '$id'";
        if (mysqli_query($conn, $query)) {
            echo json_encode(['status' => 'success', 'message' => 'Foto berhasil diganti!', 'new_foto' => $namaBaru]);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Gagal update database']);
        }
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Gagal mengupload gambar']);
    }
} else {
    echo json_encode(['status' => 'error', 'message' => 'Tidak ada file dipilih']);
}
?>