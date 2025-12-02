<?php
session_start();
require_once '../../config/database.php';

// PERBAIKAN LOGIKA REDIRECT
if (isset($_SESSION['user_id'])) {
    if ($_SESSION['role'] == 'admin') {
        // Kalau Admin, arahkan ke Monitoring
        header("Location: ../admin/monitoring.php");
    } else {
        // Kalau User biasa, arahkan ke Dashboard
        header("Location: ../user/dashboard.php");
    }
    exit;
}

$error = "";

// LOGIKA LOGIN (Saat tombol ditekan)
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $password = $_POST['password'];

    // Cek email di database
    $query = "SELECT * FROM users WHERE email = '$email'";
    $result = mysqli_query($conn, $query);

    if (mysqli_num_rows($result) === 1) {
        $row = mysqli_fetch_assoc($result);
        
        // Verifikasi Password (Hash)
        if (password_verify($password, $row['password'])) {
            // Set Session
            $_SESSION['user_id'] = $row['id'];
            $_SESSION['nama'] = $row['nama'];
            $_SESSION['role'] = $row['role'];

            // ============================================================
            // INJEKSI KODE: Catat Log Aktivitas Login
            // ============================================================
            $uid = $row['id'];
            $log_action = "User Login";
            
            // Pastikan tabel 'activity_logs' sudah ada di database kamu
            $insert_log = "INSERT INTO activity_logs (user_id, action, created_at) VALUES ('$uid', '$log_action', NOW())";
            mysqli_query($conn, $insert_log);
            // ============================================================

            // Redirect sesuai Role
            if ($row['role'] == 'admin') {
                header("Location: ../admin/monitoring.php");
            } else {
                header("Location: ../user/dashboard.php");
            }
            exit;
        } else {
            $error = "Password salah!";
        }
    } else {
        $error = "Email tidak terdaftar!";
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Task Manager</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background-color: #f4f6f9; }
        .login-card { max-width: 400px; margin: 100px auto; border-radius: 15px; box-shadow: 0 4px 6px rgba(0,0,0,0.1); }
    </style>
</head>
<body>

    <div class="container">
        <div class="card login-card p-4">
            <h3 class="text-center mb-4">Login Aplikasi</h3>
            
            <?php if($error): ?>
                <div class="alert alert-danger"><?= $error ?></div>
            <?php endif; ?>

            <form method="POST" action="">
                <div class="mb-3">
                    <label class="form-label">Alamat Email</label>
                    <input type="email" name="email" class="form-control" placeholder="admin@gmail.com" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">Password</label>
                    <input type="password" name="password" class="form-control" placeholder="******" required>
                </div>
                <button type="submit" class="btn btn-primary w-100">Masuk</button>
            </form>
            
            <div class="text-center mt-3">
                <small>Belum punya akun? <a href="register.php">Daftar disini</a></small>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>