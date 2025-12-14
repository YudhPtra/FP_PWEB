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
    <title>Fitur Admin - Task Manager</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <link href=".../.../assets/css/style.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    
    <style>
        body { background-color: #f4f6f9; }
        .card-header { font-weight: bold; }
    </style>
</head>
<body class="bg-light pb-5">
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
                        <a class="nav-link" href="monitoring.php">
                            <i class="bi bi-list-check"></i> Monitoring Tugas
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link active fw-bold" href="admin_features.php">
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
        
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h4 class="fw-bold text-dark">Analisis & Manajemen Sistem</h4>
            <span class="text-muted small">Update Realtime</span>
        </div>

        <div class="row mb-4">
            <div class="col-md-3">
                <div class="card shadow-sm border-0 mb-3 bg-primary text-white">
                    <div class="card-body text-center">
                        <h1 class="display-4 fw-bold" id="totalUser">0</h1>
                        <small>Total User Terdaftar</small>
                    </div>
                </div>
                <div class="card shadow-sm border-0 bg-success text-white">
                    <div class="card-body text-center">
                        <h1 class="display-4 fw-bold" id="totalTask">0</h1>
                        <small>Total Tugas Masuk</small>
                    </div>
                </div>
            </div>

            <div class="col-md-4">
                <div class="card shadow-sm border-0 h-100">
                    <div class="card-header bg-white">Statistik Status Tugas</div>
                    <div class="card-body">
                        <canvas id="chartStatus"></canvas>
                    </div>
                </div>
            </div>

            <div class="col-md-5">
                <div class="card shadow-sm border-0 h-100">
                    <div class="card-header bg-white">Distribusi Prioritas</div>
                    <div class="card-body">
                        <canvas id="chartPrioritas"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            
            <div class="col-md-6 mb-4">
                <div class="card shadow-sm border-0 h-100">
                    <div class="card-header bg-white border-bottom text-primary">
                        <i class="bi bi-tags-fill"></i> Master Data Kategori
                    </div>
                    <div class="card-body">
                        <form id="formKategori" class="row g-2 mb-4">
                            <div class="col-7">
                                <input type="text" name="nama_kategori" class="form-control" placeholder="Nama Kategori (mis: Hobi)" required>
                            </div>
                            <div class="col-3">
                                <select name="warna_label" class="form-select">
                                    <option value="primary">Biru</option>
                                    <option value="success">Hijau</option>
                                    <option value="danger">Merah</option>
                                    <option value="warning">Kuning</option>
                                    <option value="info">Cyan</option>
                                    <option value="secondary">Abu</option>
                                    <option value="dark">Hitam</option>
                                </select>
                            </div>
                            <div class="col-2">
                                <button type="submit" class="btn btn-primary w-100"><i class="bi bi-plus-lg"></i></button>
                            </div>
                        </form>

                        <h6 class="text-muted small mb-3">Daftar Kategori Tersedia:</h6>
                        <ul class="list-group list-group-flush" id="listKategori" style="max-height: 300px; overflow-y: auto;">
                            <li class="list-group-item text-center">Memuat...</li>
                        </ul>
                    </div>
                </div>
            </div>

            <div class="col-md-6 mb-4">
                <div class="card shadow-sm border-0 h-100">
                    <div class="card-header bg-white border-bottom text-danger">
                        <i class="bi bi-clock-history"></i> Log Aktivitas Sistem
                    </div>
                    <div class="card-body p-0">
                        <div style="max-height: 400px; overflow-y: auto;">
                            <table class="table table-striped table-hover mb-0 small align-middle">
                                <thead class="table-light sticky-top">
                                    <tr>
                                        <th style="width: 30%;">Waktu</th>
                                        <th style="width: 25%;">User</th>
                                        <th>Aktivitas</th>
                                    </tr>
                                </thead>
                                <tbody id="tableLogs">
                                    <tr><td colspan="3" class="text-center py-3">Memuat log...</td></tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

    <script>
        // 1. LOAD STATISTIK & GRAFIK
        function loadStats() {
            fetch('../../api/admin/get_stats.php')
            .then(res => res.json())
            .then(data => {
                // Update Kartu Angka
                document.getElementById('totalUser').innerText = data.total_user;
                document.getElementById('totalTask').innerText = data.total_task;

                // Render Chart Status (Pie Chart)
                // Hancurkan chart lama jika ada (biar gak numpuk saat refresh)
                let ctxStatus = document.getElementById('chartStatus');
                if (window.myPieChart) window.myPieChart.destroy();
                
                window.myPieChart = new Chart(ctxStatus, {
                    type: 'doughnut', // Bisa ganti 'pie'
                    data: {
                        labels: ['Selesai', 'Belum Selesai'],
                        datasets: [{
                            data: [data.by_status.Selesai || 0, data.by_status.Belum || 0],
                            backgroundColor: ['#198754', '#ffc107'],
                            borderWidth: 0
                        }]
                    },
                    options: { maintainAspectRatio: false }
                });

                // Render Chart Prioritas (Bar Chart)
                let ctxPrio = document.getElementById('chartPrioritas');
                if (window.myBarChart) window.myBarChart.destroy();

                window.myBarChart = new Chart(ctxPrio, {
                    type: 'bar',
                    data: {
                        labels: ['Tinggi', 'Sedang', 'Rendah'],
                        datasets: [{
                            label: 'Jumlah Tugas',
                            data: [
                                data.by_priority.Tinggi || 0,
                                data.by_priority.Sedang || 0,
                                data.by_priority.Rendah || 0
                            ],
                            backgroundColor: ['#dc3545', '#ffc107', '#0dcaf0'],
                            borderRadius: 5
                        }]
                    },
                    options: {
                        scales: { y: { beginAtZero: true, ticks: { stepSize: 1 } } },
                        plugins: { legend: { display: false } }
                    }
                });
            });
        }

        // 2. LOAD KATEGORI
        function loadCategories() {
            fetch('../../api/categories/read.php')
            .then(res => res.json())
            .then(data => {
                let html = '';
                data.forEach(cat => {
                    html += `
                    <li class="list-group-item d-flex justify-content-between align-items-center">
                        <span class="badge bg-${cat.warna_label} p-2">${cat.nama_kategori}</span>
                        <button class="btn btn-sm btn-outline-danger border-0" onclick="hapusKategori(${cat.id})">
                            <i class="bi bi-trash"></i>
                        </button>
                    </li>`;
                });
                document.getElementById('listKategori').innerHTML = html;
            });
        }

        // Tambah Kategori
        document.getElementById('formKategori').addEventListener('submit', function(e) {
            e.preventDefault();
            let formData = new FormData(this);
            fetch('../../api/categories/create.php', { method: 'POST', body: formData })
            .then(res => res.json())
            .then(d => {
                if(d.status === 'success') {
                    this.reset();
                    loadCategories(); // Refresh list
                } else { alert(d.message); }
            });
        });

        // Hapus Kategori
        function hapusKategori(id) {
            if(confirm('Yakin hapus kategori ini?')) {
                fetch('../../api/categories/delete.php', { 
                    method: 'POST', 
                    headers: {'Content-Type': 'application/json'},
                    body: JSON.stringify({id: id}) 
                }).then(() => loadCategories());
            }
        }

        // 3. LOAD ACTIVITY LOG
        function loadLogs() {
            fetch('../../api/admin/get_logs.php')
            .then(res => res.json())
            .then(data => {
                let html = '';
                data.forEach(log => {
                    html += `
                    <tr>
                        <td class="text-muted">${log.created_at}</td>
                        <td class="fw-bold text-primary">${log.nama}</td>
                        <td>${log.action}</td>
                    </tr>`;
                });
                document.getElementById('tableLogs').innerHTML = html;
            });
        }

        // JALANKAN SAAT HALAMAN DIBUKA
        document.addEventListener('DOMContentLoaded', () => {
            loadStats();
            loadCategories();
            loadLogs();
            
            // Auto Refresh Statistik & Log tiap 5 detik (Biar hidup)
            setInterval(() => {
                loadStats();
                loadLogs();
            }, 5000);
        });
    </script>
<script src=".../.../assets/js/script.js"></script>
</body>
</html>
