<?php
session_start();
require_once '../../config/database.php';

// Cek Login
if (!isset($_SESSION['user_id'])) {
    header("Location: ../auth/login.php");
    exit;
}

$id = $_SESSION['user_id'];
$query = "SELECT * FROM users WHERE id = '$id'";
$result = mysqli_query($conn, $query);
$user = mysqli_fetch_assoc($result);

// Fallback foto
$foto = !empty($user['foto_profil']) ? $user['foto_profil'] : 'default.jpg';
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <title>Profil Saya</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
</head>
<body class="bg-light">

<div class="container mt-5 mb-5">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card shadow-sm border-0">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0"><i class="bi bi-person-gear"></i> Edit Profil</h5>
                </div>
                <div class="card-body p-4">
                    
                    <div class="row">
                        <div class="col-md-4 text-center border-end">
                            <img src="../../assets/uploads/<?= $foto ?>" class="rounded-circle img-thumbnail mb-3" width="150" height="150" style="object-fit: cover;">
                            
                            <form id="formFoto">
                                <label class="form-label small text-muted">Ganti Foto (Max 2MB)</label>
                                <input type="file" name="foto_profil" class="form-control form-control-sm mb-2" accept="image/*" required>
                                <button type="submit" class="btn btn-sm btn-outline-primary w-100">
                                    <i class="bi bi-upload"></i> Upload Foto
                                </button>
                            </form>
                        </div>

                        <div class="col-md-8">
                            <form id="formProfil">
                                <h6 class="text-primary fw-bold mb-3">Data Diri</h6>
                                <div class="mb-3">
                                    <label class="form-label">Nama Lengkap</label>
                                    <input type="text" name="nama" class="form-control" value="<?= $user['nama'] ?>" required>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Email</label>
                                    <input type="text" class="form-control bg-light" value="<?= $user['email'] ?>" disabled>
                                    <small class="text-muted">Email tidak dapat diubah.</small>
                                </div>

                                <hr class="my-4">

                                <h6 class="text-danger fw-bold mb-3"><i class="bi bi-key"></i> Ganti Password</h6>
                                <div class="alert alert-info small py-2">
                                    Kosongkan bagian ini jika tidak ingin mengubah password.
                                </div>

                                <div class="mb-3">
                                    <label class="form-label">Password Lama</label>
                                    <input type="password" name="password_lama" class="form-control" placeholder="Verifikasi password saat ini">
                                </div>
                                
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Password Baru</label>
                                        <input type="password" name="password_baru" class="form-control" placeholder="Minimal 6 karakter">
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Ulangi Password Baru</label>
                                        <input type="password" name="konfirmasi_password" class="form-control" placeholder="Ketik ulang password baru">
                                    </div>
                                </div>

                                <div class="d-grid gap-2 mt-3">
                                    <button type="submit" class="btn btn-primary">
                                        <i class="bi bi-save"></i> Simpan Perubahan
                                    </button>
                                    <a href="dashboard.php" class="btn btn-outline-secondary">Kembali ke Dashboard</a>
                                </div>
                            </form>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
    // 1. Logic Upload Foto
    document.getElementById('formFoto').addEventListener('submit', function(e) {
        e.preventDefault();
        let formData = new FormData(this);
        
        // Tampilkan loading
        Swal.fire({title: 'Mengupload...', allowOutsideClick: false, didOpen: () => { Swal.showLoading() }});

        fetch('../../api/user/upload_photo.php', { method: 'POST', body: formData })
        .then(res => res.json())
        .then(data => {
            if(data.status === 'success') {
                Swal.fire('Berhasil!', data.message, 'success').then(() => location.reload());
            } else {
                Swal.fire('Gagal!', data.message, 'error');
            }
        });
    });

    // 2. Logic Update Profil & Password
    document.getElementById('formProfil').addEventListener('submit', function(e) {
        e.preventDefault();
        let formData = new FormData(this);

        Swal.fire({title: 'Menyimpan...', allowOutsideClick: false, didOpen: () => { Swal.showLoading() }});

        fetch('../../api/user/update_profile.php', { method: 'POST', body: formData })
        .then(res => res.json())
        .then(data => {
            if(data.status === 'success') {
                Swal.fire('Berhasil!', data.message, 'success').then(() => location.reload());
            } else {
                Swal.fire('Gagal!', data.message, 'error');
            }
        })
        .catch(err => Swal.fire('Error', 'Terjadi kesalahan server', 'error'));
    });
</script>

</body>
</html>