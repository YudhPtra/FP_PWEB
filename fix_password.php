<?php
// fix_password.php
require_once 'config/database.php'; // Pastikan koneksi benar

// 1. Kita buat hash asli dari "123456"
$password_baru = "123456";
$hash_asli = password_hash($password_baru, PASSWORD_DEFAULT);

// 2. Update database user admin
$email_admin = "admin@gmail.com";
$query = "UPDATE users SET password = '$hash_asli' WHERE email = '$email_admin'";

if (mysqli_query($conn, $query)) {
    echo "<h1>SUKSES! ✅</h1>";
    echo "Password untuk <b>$email_admin</b> berhasil di-reset.<br>";
    echo "Password baru: <b>$password_baru</b><br>";
    echo "Hash baru di database: $hash_asli<br><br>";
    echo "<a href='pages/auth/login.php'>Klik disini untuk Login</a>";
} else {
    echo "Gagal update: " . mysqli_error($conn);
}
?>