<?php
session_start();
require_once '../../config/database.php';

// Kalau sudah login, lempar ke dashboard
if (isset($_SESSION['user_id'])) {
    header("Location: ../user/dashboard.php");
    exit;
}

$error = "";
$success = "";

// PROSES REGISTER
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nama = mysqli_real_escape_string($conn, $_POST['nama']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];

    // 1. Validasi Password Cocok
    if ($password !== $confirm_password) {
        $error = "Konfirmasi password tidak cocok!";
    } else {
        // 2. Cek apakah Email sudah terdaftar?
        $cek_email = mysqli_query($conn, "SELECT id FROM users WHERE email = '$email'");
        
        if (mysqli_num_rows($cek_email) > 0) {
            $error = "Email sudah digunakan! Silakan login.";
        } else {
            // 3. Enkripsi Password (Hashing)
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);

            // 4. Masukkan ke Database
            // Role default = 'user', Foto default = 'default.jpg'
            $query = "INSERT INTO users (nama, email, password, role, foto_profil) 
                      VALUES ('$nama', '$email', '$hashed_password', 'user', 'default.jpg')";

            if (mysqli_query($conn, $query)) {
                $success = "Registrasi berhasil! Silakan login.";
            } else {
                $error = "Gagal mendaftar: " . mysqli_error($conn);
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daftar Akun - Task Manager</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background-color: #f4f6f9; }
        .register-card { max-width: 500px; margin: 50px auto; border-radius: 15px; box-shadow: 0 4px 6px rgba(0,0,0,0.1); }
    </style>
</head>
<body>

    <div class="container">
        <div class="card register-card p-4">
            <h3 class="text-center mb-4 fw-bold text-primary">Buat Akun Baru</h3>
            
            <?php if($error): ?>
                <div class="alert alert-danger alert-dismissible fade show">
                    <?= $error ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>

            <?php if($success): ?>
                <div class="alert alert-success">
                    <?= $success ?> <br>
                    <a href="login.php" class="fw-bold text-success text-decoration-none">Klik disini untuk Login</a>
                </div>
            <?php else: ?>

            <form method="POST" action="">
                <div class="mb-3">
                    <label class="form-label">Nama Lengkap</label>
                    <input type="text" name="nama" class="form-control" placeholder="Contoh: Budi Santoso" required>
                </div>
                
                <div class="mb-3">
                    <label class="form-label">Alamat Email</label>
                    <input type="email" name="email" class="form-control" placeholder="nama@email.com" required>
                </div>
                
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Password</label>
                        <input type="password" name="password" class="form-control" placeholder="******" required minlength="6">
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Ulangi Password</label>
                        <input type="password" name="confirm_password" class="form-control" placeholder="******" required>
                    </div>
                </div>

                <button type="submit" class="btn btn-primary w-100 py-2">Daftar Sekarang</button>
            </form>
            
            <?php endif; ?>

            <div class="text-center mt-3">
                <small>Sudah punya akun? <a href="login.php">Login disini</a></small>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>