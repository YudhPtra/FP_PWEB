<?php
session_start();
require_once '../../config/database.php'; 

// 1. Cek Login & Role
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'user') {
    header("Location: ../auth/login.php");
    exit;
}

// 2. Ambil Foto Profil
$uid = $_SESSION['user_id'];
$query_user = mysqli_query($conn, "SELECT foto_profil FROM users WHERE id='$uid'");
$user_data = mysqli_fetch_assoc($query_user);
$foto_profil = !empty($user_data['foto_profil']) ? $user_data['foto_profil'] : 'default.jpg';

// 3. Ambil Kategori (Untuk Dropdown Filter & Modal)
$query_kategori = mysqli_query($conn, "SELECT * FROM categories");
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Task Manager</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    
    <style>
        body { background-color: #f4f6f9; }
        .card-task { transition: transform 0.2s; }
        .card-task:hover { transform: translateY(-5px); }
        .task-done { opacity: 0.7; background-color: #e9ecef; }
        .task-done h5 { text-decoration: line-through; color: #6c757d; }
        .dropdown-menu { box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15); border: 0; }
    </style>
</head>
<body>

    <nav class="navbar navbar-expand-lg navbar-dark bg-primary mb-4">
        <div class="container">
            <a class="navbar-brand fw-bold" href="#"><i class="bi bi-check2-square"></i> Task Manager</a>
            <div class="d-flex align-items-center gap-3">
                <div class="dropdown">
                    <button class="btn btn-light position-relative" type="button" data-bs-toggle="dropdown">
                        <i class="bi bi-bell-fill text-primary"></i>
                        <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger" id="notif-badge" style="display: none;">0</span>
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end p-0" style="width: 320px; max-height: 400px; overflow-y: auto;" id="notif-list">
                        <li class="text-center small p-3 text-muted">Memuat notifikasi...</li>
                    </ul>
                </div>
                <div class="dropdown">
                    <a href="#" class="d-flex align-items-center text-white text-decoration-none dropdown-toggle" data-bs-toggle="dropdown">
                        <img src="../../assets/uploads/<?= $foto_profil ?>" width="32" height="32" class="rounded-circle me-2 bg-light" style="object-fit: cover;">
                        <strong><?= htmlspecialchars($_SESSION['nama']) ?></strong>
                    </a>
                    <ul class="dropdown-menu dropdown-menu-end">
                        <li><a class="dropdown-item" href="profil.php">Profil Saya</a></li>
                        <li><hr class="dropdown-divider"></li>
                        <li><a class="dropdown-item text-danger" href="../auth/logout.php">Logout</a></li>
                    </ul>
                </div>
            </div>
        </div>
    </nav>

    <div class="container">
        <div class="row align-items-center mb-4">
            <div class="col-md-6">
                <h2 class="fw-bold text-dark">Daftar Tugas</h2>
                <p class="text-muted mb-0">Kelola produktivitasmu di sini.</p>
            </div>
            <div class="col-md-6 text-md-end mt-3 mt-md-0">
                <div class="btn-group me-2">
                    <a href="export_excel.php" class="btn btn-outline-success"><i class="bi bi-file-earmark-excel"></i> Excel</a>
                    <a href="export_pdf.php" target="_blank" class="btn btn-outline-danger"><i class="bi bi-file-earmark-pdf"></i> PDF</a>
                </div>
                <button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#modalTambah">
                    <i class="bi bi-plus-lg"></i> Tambah
                </button>
            </div>
        </div>

        <div class="card shadow-sm border-0 mb-4">
            <div class="card-body p-3 bg-white rounded">
                <div class="row g-2">
                    <div class="col-md-6">
                        <div class="input-group">
                            <span class="input-group-text bg-light border-end-0"><i class="bi bi-search"></i></span>
                            <input type="text" id="searchInput" class="form-control border-start-0 bg-light" placeholder="Cari judul atau deskripsi..." onkeyup="loadTasks()">
                        </div>
                    </div>
                    <div class="col-md-3">
                        <select id="filterCategory" class="form-select" onchange="loadTasks()">
                            <option value="">Semua Kategori</option>
                            <?php
                            while($c = mysqli_fetch_assoc($query_kategori)) {
                                echo "<option value='{$c['id']}'>{$c['nama_kategori']}</option>";
                            }
                            ?>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <select id="sortOrder" class="form-select" onchange="loadTasks()">
                            <option value="deadline_asc">Deadline Terdekat</option>
                            <option value="deadline_desc">Deadline Terlama</option>
                            <option value="priority_high">Prioritas Tertinggi</option>
                            <option value="priority_low">Prioritas Terendah</option>
                        </select>
                    </div>
                </div>
            </div>
        </div>

        <div id="task-container" class="row">
            <div class="col-12 text-center py-5">
                <div class="spinner-border text-primary" role="status"></div>
                <p class="mt-2 text-muted">Memuat data tugas...</p>
            </div>
        </div>
    </div>

    <div class="modal fade" id="modalTambah" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title">Buat Tugas Baru</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="formTambahTugas">
                        <div class="mb-3">
                            <label class="form-label">Judul Tugas</label>
                            <input type="text" class="form-control" name="judul" required placeholder="Contoh: Ngerjain Laporan FP">
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Kategori</label>
                                <select class="form-select" name="kategori_id">
                                    <?php
                                    mysqli_data_seek($query_kategori, 0); 
                                    while($c = mysqli_fetch_assoc($query_kategori)) {
                                        echo "<option value='{$c['id']}'>{$c['nama_kategori']}</option>";
                                    }
                                    ?>
                                </select>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Prioritas</label>
                                <select class="form-select" name="prioritas">
                                    <option value="Rendah">Rendah</option>
                                    <option value="Sedang" selected>Sedang</option>
                                    <option value="Tinggi">Tinggi (Urgent!)</option>
                                </select>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Deadline</label>
                            <input type="datetime-local" class="form-control" name="deadline" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Deskripsi</label>
                            <textarea class="form-control" name="deskripsi" rows="3"></textarea>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="button" class="btn btn-primary" id="btnSimpan">Simpan Tugas</button>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        // ==========================================
        // CONFIG & VARIABLES
        // ==========================================
        let lastNotifId = null; 

        // ==========================================
        // HELPER FUNCTIONS
        // ==========================================
        function escapeHtml(text) {
            if (!text) return "";
            return text.replace(/&/g, "&amp;").replace(/</g, "&lt;").replace(/>/g, "&gt;").replace(/"/g, "&quot;").replace(/'/g, "&#039;");
        }

        function formatTanggal(dateString) {
            const options = { weekday: 'short', year: 'numeric', month: 'short', day: 'numeric', hour: '2-digit', minute: '2-digit' };
            return new Date(dateString).toLocaleDateString('id-ID', options);
        }

        // ==========================================
        // 1. LOAD TASKS (Dengan Filter & Sort)
        // ==========================================
        function loadTasks() {
            const container = document.getElementById('task-container');
            let search = document.getElementById('searchInput').value;
            let category = document.getElementById('filterCategory').value;
            let sort = document.getElementById('sortOrder').value;

            let url = `../../api/tasks/read.php?t=${new Date().getTime()}&search=${search}&category=${category}&sort=${sort}`;

            fetch(url)
            .then(res => res.json())
            .then(data => {
                container.innerHTML = '';
                if (data.length === 0) {
                    container.innerHTML = `<div class="col-12 text-center py-5"><h5 class="text-muted">Tidak ada tugas yang cocok.</h5></div>`;
                    return;
                }
                data.forEach(task => {
                    // Google Calendar Link
                    let dateObj = new Date(task.deadline);
                    let isoStart = dateObj.toISOString().replace(/-|:|\.\d\d\d/g,"");
                    let dateEndObj = new Date(dateObj.getTime() + (3600*1000));
                    let isoEnd = dateEndObj.toISOString().replace(/-|:|\.\d\d\d/g,"");
                    let gcalLink = `https://calendar.google.com/calendar/render?action=TEMPLATE&text=${encodeURIComponent(task.judul)}&details=${encodeURIComponent(task.deskripsi)}&dates=${isoStart}/${isoEnd}`;

                    // Styling Status
                    let cardClass = task.status === 'Selesai' ? "task-done" : "";
                    let btnSelesai = task.status === 'Selesai' 
                        ? `<span class="badge bg-success">Selesai</span>` 
                        : `<button class="btn btn-sm btn-outline-success" onclick="selesaikanTugas(${task.id})"><i class="bi bi-check-lg"></i></button>`;

                    // Badge Prioritas
                    let badgePrio = '';
                    if(task.prioritas === 'Tinggi') badgePrio = '<span class="badge bg-danger ms-1">! Tinggi</span>';
                    else if(task.prioritas === 'Sedang') badgePrio = '<span class="badge bg-warning text-dark ms-1">Sedang</span>';
                    else badgePrio = '<span class="badge bg-secondary ms-1">Rendah</span>';

                    let html = `
                    <div class="col-md-4 mb-4">
                        <div class="card h-100 shadow-sm card-task border-start border-4 border-${task.warna_label} ${cardClass}">
                            <div class="card-body">
                                <div class="d-flex justify-content-between mb-2">
                                    <div>
                                        <span class="badge bg-${task.warna_label}">${escapeHtml(task.nama_kategori)}</span>
                                        ${badgePrio}
                                    </div>
                                    <small class="text-muted" style="font-size:0.8rem">${formatTanggal(task.deadline)}</small>
                                </div>
                                <h5 class="card-title fw-bold">${escapeHtml(task.judul)}</h5>
                                <p class="card-text text-muted small">${escapeHtml(task.deskripsi)}</p>
                            </div>
                            <div class="card-footer bg-white d-flex justify-content-between">
                                <a href="${gcalLink}" target="_blank" class="btn btn-sm btn-outline-warning"><i class="bi bi-calendar-plus"></i> G-Cal</a>
                                <div>${btnSelesai} <button class="btn btn-sm btn-outline-danger" onclick="hapusTugas(${task.id})"><i class="bi bi-trash"></i></button></div>
                            </div>
                        </div>
                    </div>`;
                    container.innerHTML += html;
                });
            });
        }

        // ==========================================
        // 2. CEK NOTIFIKASI
        // ==========================================
        function cekNotifikasi() {
            fetch('../../api/notifications/get.php?t=' + new Date().getTime())
            .then(res => res.json())
            .then(data => {
                const badge = document.getElementById('notif-badge');
                if (data.unread > 0) {
                    badge.style.display = 'inline-block';
                    badge.innerText = data.unread;
                } else {
                    badge.style.display = 'none';
                }
                
                // Auto Refresh Tasks jika ada notif baru masuk
                if (data.data.length > 0) {
                    let latestId = parseInt(data.data[0].id);
                    if (lastNotifId === null) lastNotifId = latestId; 
                    else if (latestId > lastNotifId) { loadTasks(); lastNotifId = latestId; }
                }

                const list = document.getElementById('notif-list');
                list.innerHTML = ''; 
                if (data.data.length === 0) list.innerHTML = '<li class="text-center small p-3 text-muted">Tidak ada notifikasi</li>';
                else {
                    data.data.forEach(notif => {
                        let bg = notif.is_read == 0 ? 'bg-light fw-bold' : '';
                        let color = notif.tipe == 'danger' ? 'text-danger' : 'text-primary';
                        let statusIcon = notif.is_read == 0 ? `<span class="position-absolute top-50 end-0 translate-middle p-1 bg-primary border border-light rounded-circle me-3"></span>` : '';
                        
                        list.innerHTML += `<li>
                            <a class="dropdown-item ${bg} border-bottom p-2 position-relative" href="#" onclick="markRead(${notif.id}, this); return false;">
                                <div class="d-flex w-100 justify-content-between pe-3">
                                    <small class="${color}">${escapeHtml(notif.judul)}</small>
                                    <small class="text-muted" style="font-size:0.7rem">${notif.created_at}</small>
                                </div>
                                <p class="mb-0 small text-wrap pe-3">${escapeHtml(notif.pesan)}</p>
                                ${statusIcon}
                            </a>
                        </li>`;
                    });
                }
            })
            .catch(err => console.log('Silent error notif'));
        }

        // ==========================================
        // 3. TRIGGER CHECK DEADLINE
        // ==========================================
        function checkDeadlines() {
            // Trigger backend untuk cek deadline & buat notifikasi jika perlu
            fetch('../../api/tasks/check_deadline.php')
                .then(() => console.log("Cek deadline background running..."))
                .catch(err => console.log("Gagal trigger cek deadline"));
        }

        // ==========================================
        // 4. ACTION FUNCTIONS (Create, Update, Delete)
        // ==========================================
        document.getElementById('btnSimpan').addEventListener('click', function() {
            let formData = new FormData(document.getElementById('formTambahTugas'));
            fetch('../../api/tasks/create.php', { method: 'POST', body: formData })
            .then(res => res.json()).then(d => { 
                if(d.status==='success') { 
                    bootstrap.Modal.getInstance(document.getElementById('modalTambah')).hide(); 
                    document.getElementById('formTambahTugas').reset(); 
                    alert(d.message); 
                    loadTasks(); 
                } 
            });
        });

        function markRead(id, element) {
            fetch('../../api/notifications/mark_read.php', { method: 'POST', headers: { 'Content-Type': 'application/json' }, body: JSON.stringify({ id: id }) })
            .then(res => res.json())
            .then(data => {
                if(data.status === 'success') {
                    element.classList.remove('bg-light', 'fw-bold');
                    let dot = element.querySelector('.rounded-circle');
                    if(dot) dot.remove();
                    const badge = document.getElementById('notif-badge');
                    let currentCount = parseInt(badge.innerText);
                    if (currentCount > 0) { currentCount--; badge.innerText = currentCount; if(currentCount === 0) badge.style.display = 'none'; }
                }
            });
        }

        function selesaikanTugas(id) { if(confirm("Selesai?")) fetch('../../api/tasks/update.php', { method: 'POST', body: JSON.stringify({id:id}) }).then(r=>r.json()).then(d=>{if(d.status==='success') loadTasks();}); }
        function hapusTugas(id) { if(confirm("Hapus?")) fetch('../../api/tasks/delete.php', { method: 'POST', body: JSON.stringify({id:id}) }).then(r=>r.json()).then(d=>{if(d.status==='success') loadTasks();}); }

        // ==========================================
        // 5. INISIALISASI & LOOPING
        // ==========================================
        document.addEventListener('DOMContentLoaded', () => {
            // 1. Jalan saat pertama kali buka
            loadTasks();        
            checkDeadlines();   
            cekNotifikasi();    
            
            // 2. Jalan BERULANG-ULANG setiap 3 detik (Auto-Check)
            setInterval(() => {
                checkDeadlines(); // Cek terus: "Ada deadline mepet gak?"
                cekNotifikasi();  // Cek terus: "Ada pesan baru gak?"
            }, 3000); 
        });
    </script>
</body>
</html>