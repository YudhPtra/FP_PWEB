<?php
session_start();
// Cek Admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    header("Location: ../auth/login.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Admin Monitoring - Task Manager</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <link href="FP_PWEB/assets/css/style.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    
    <style>
        /* Animasi kedip halus saat data baru masuk/berubah */
        @keyframes highlight {
            0% { background-color: #e8f4ff; }
            100% { background-color: transparent; }
        }
        .new-data { animation: highlight 2s ease-out; }
    </style>
</head>
<body class="bg-light">
<nav class="navbar navbar-expand-lg navbar-light bg-light">
    <div class="container-fluid">
        <a class="navbar-brand" href="#"></a>
        <button id="darkModeToggle" class="btn btn-outline-secondary">🌙</button>
    </div>
</nav>
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark mb-4 shadow">
        <div class="container">
            <a class="navbar-brand fw-bold" href="#">
                <i class="bi bi-shield-lock"></i> Admin Panel
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item">
                        <a class="nav-link active fw-bold" href="monitoring.php">
                            <i class="bi bi-list-check"></i> Monitoring Tugas
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="admin_features.php">
                            <i class="bi bi-grid-1x2"></i> Dashboard & Fitur
                        </a>
                    </li>
                </ul>

                <div class="d-flex text-white align-items-center">
                    <span class="me-3 small">Halo, <?php echo htmlspecialchars($_SESSION['nama']); ?></span>
                    <a href="../auth/logout.php" class="btn btn-danger btn-sm">
                        <i class="bi bi-box-arrow-right"></i> Logout
                    </a>
                </div>
            </div>
        </div>
    </nav>

    <div class="container">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h4 class="fw-bold text-dark mb-0">Dashboard Monitoring</h4>
            <span class="badge bg-success shadow-sm p-2">
                <i class="bi bi-wifi"></i> Live Update Aktif
            </span>
        </div>

        <div class="card shadow-sm border-0">
            <div class="card-header bg-white py-3 border-bottom">
                <h5 class="mb-0 fw-bold text-primary"><i class="bi bi-activity"></i> Semua Tugas User</h5>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover table-striped align-middle mb-0">
                        <thead class="table-primary">
                            <tr>
                                <th class="ps-4">Pemilik Tugas</th>
                                <th>Judul Tugas</th>
                                <th>Kategori</th>
                                <th>Prioritas</th>
                                <th>Deadline</th>
                                <th>Status</th>
                                <th class="text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody id="table-body">
                            <tr><td colspan="7" class="text-center py-4">Menghubungkan ke server...</td></tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Variabel pelacak perubahan (Total, ID Terakhir, Waktu Update Terakhir)
        let lastCheckData = { total: 0, last_id: 0, last_update: '' };
        let isFirstLoad = true;

        // 1. FUNGSI LOAD SEMUA DATA (Render Tabel)
        function loadAllTasks() {
            fetch('../../api/admin/read_all.php?t=' + new Date().getTime())
            .then(res => res.json())
            .then(data => {
                let html = '';
                const tbody = document.getElementById('table-body');

                if(data.length === 0) {
                    tbody.innerHTML = '<tr><td colspan="7" class="text-center py-4 text-muted">Belum ada data tugas.</td></tr>';
                    return;
                }

                data.forEach(row => {
                    // Logic Badge Status (Hijau/Abu)
                    let statusBadge = row.status === 'Selesai' 
                        ? '<span class="badge bg-success"><i class="bi bi-check-circle"></i> Selesai</span>' 
                        : '<span class="badge bg-secondary text-light">Belum</span>';
                    
                    // Logic Badge Prioritas (Merah/Kuning/Biru)
                    let prioBadge = '';
                    if(row.prioritas === 'Tinggi') prioBadge = '<span class="badge bg-danger">! Tinggi</span>';
                    else if(row.prioritas === 'Sedang') prioBadge = '<span class="badge bg-warning text-dark">Sedang</span>';
                    else prioBadge = '<span class="badge bg-info text-dark">Rendah</span>';

                    // Efek animasi highlight jika bukan load pertama
                    let rowClass = isFirstLoad ? '' : 'new-data';

                    html += `
                    <tr class="${rowClass}">
                        <td class="ps-4 fw-bold text-dark">
                            <div class="d-flex align-items-center">
                                <div class="bg-primary text-white rounded-circle d-flex justify-content-center align-items-center me-2" style="width: 30px; height: 30px;">
                                    <i class="bi bi-person-fill"></i>
                                </div>
                                ${row.nama_user}
                            </div>
                        </td>
                        <td>${row.judul}</td>
                        <td><span class="badge bg-${row.warna_label}">${row.nama_kategori}</span></td>
                        <td>${prioBadge}</td>
                        <td><small class="text-muted">${row.deadline}</small></td>
                        <td>${statusBadge}</td>
                        <td class="text-center">
                            <button class="btn btn-sm btn-outline-danger" onclick="moderasiHapus(${row.id})">
                                <i class="bi bi-trash"></i> Hapus
                            </button>
                        </td>
                    </tr>`;
                });
                tbody.innerHTML = html;
                isFirstLoad = false; 
            })
            .catch(err => console.error(err));
        }

        // 2. FUNGSI CEK UPDATE (CCTV)
        function checkForUpdates() {
            fetch('../../api/admin/check_updates.php?t=' + new Date().getTime())
            .then(res => res.json())
            .then(data => {
                let currentTotal = parseInt(data.total_tasks);
                let currentLastId = parseInt(data.last_id);
                let currentLastUpdate = data.last_update; // Timestamp update terakhir

                // Cek: Apakah ada Insert baru ATAU Update status?
                if (currentTotal !== lastCheckData.total || 
                    currentLastId !== lastCheckData.last_id || 
                    currentLastUpdate !== lastCheckData.last_update) {
                    
                    console.log("Perubahan data terdeteksi! Refreshing tabel...");
                    
                    // Simpan state baru
                    lastCheckData.total = currentTotal;
                    lastCheckData.last_id = currentLastId;
                    lastCheckData.last_update = currentLastUpdate;
                    
                    // Refresh Tabel
                    loadAllTasks();
                }
            })
            .catch(err => console.error("Gagal cek update"));
        }

        // 3. FUNGSI HAPUS (MODERASI DENGAN ALASAN)
        function moderasiHapus(id) {
            Swal.fire({
                title: 'Hapus Konten Ini?',
                text: "Wajib sertakan alasan penghapusan untuk User:",
                icon: 'warning',
                input: 'text',
                inputPlaceholder: 'Contoh: Melanggar aturan / Kata kasar',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                confirmButtonText: 'Ya, Hapus',
                cancelButtonText: 'Batal',
                inputValidator: (value) => {
                    if (!value) return 'Alasan wajib diisi!'
                }
            }).then((result) => {
                if (result.isConfirmed) {
                    hapusPermanen(id, result.value);
                }
            });
        }

        function hapusPermanen(id, alasan) {
            // Tampilkan loading
            Swal.showLoading();
            
            fetch('../../api/admin/delete_task.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ id: id, alasan: alasan })
            })
            .then(res => res.json())
            .then(data => {
                if(data.status === 'success') {
                    Swal.fire('Terhapus!', data.message, 'success');
                    // loadAllTasks() akan dipanggil otomatis oleh checkForUpdates dalam 3 detik
                    // Tapi kita panggil manual juga biar instan
                    loadAllTasks();
                } else {
                    Swal.fire('Gagal!', data.message, 'error');
                }
            })
            .catch(err => Swal.fire('Error', 'Terjadi kesalahan sistem', 'error'));
        }

        // 4. INISIALISASI SAAT HALAMAN DIBUKA
        document.addEventListener('DOMContentLoaded', () => {
            // Panggil sekali langsung
            checkForUpdates();
            
            // Nyalakan Polling tiap 3 detik
            setInterval(checkForUpdates, 3000);
        });
    </script>
    <script src="FP_PWEB/assets/js/script.js"></script>
</body>
</html>
