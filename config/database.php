<?php
$host = "localhost";
$user = "root";      // Default XAMPP user
$pass = "";          // Default XAMPP password (kosong)
$db   = "db_task_manager";

// Melakukan koneksi
$conn = mysqli_connect($host, $user, $pass, $db);

// Cek koneksi
if (!$conn) {
    die("Koneksi Database Gagal: " . mysqli_connect_error());
}
?>