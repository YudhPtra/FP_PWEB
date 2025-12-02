<?php
// pages/user/export_excel.php
session_start();
require_once '../../config/database.php';

// Cek Login
if (!isset($_SESSION['user_id'])) {
    header("Location: ../auth/login.php");
    exit;
}

$user_id = $_SESSION['user_id'];
$nama_user = $_SESSION['nama'];

// 1. Header Khusus agar dibaca sebagai File Excel (.xls)
header("Content-Type: application/vnd.ms-excel");
header("Content-Disposition: attachment; filename=Laporan_Tugas_$nama_user.xls");

// 2. Ambil Data
$query = "SELECT t.*, c.nama_kategori 
          FROM tasks t
          JOIN categories c ON t.category_id = c.id
          WHERE t.user_id = '$user_id' 
          ORDER BY t.deadline ASC";
$result = mysqli_query($conn, $query);
?>

<h3>Laporan Daftar Tugas: <?php echo $nama_user; ?></h3>
<table border="1">
    <thead>
        <tr style="background-color: #f2f2f2;">
            <th>No</th>
            <th>Judul Tugas</th>
            <th>Kategori</th>
            <th>Deskripsi</th>
            <th>Deadline</th>
            <th>Status</th>
        </tr>
    </thead>
    <tbody>
        <?php 
        $no = 1;
        while($row = mysqli_fetch_assoc($result)): 
            $status = ($row['status'] == 'Selesai') ? 'Selesai' : 'Belum';
            $warna = ($row['status'] == 'Selesai') ? '#d4edda' : '#fff3cd';
        ?>
        <tr>
            <td><?= $no++; ?></td>
            <td><?= $row['judul']; ?></td>
            <td><?= $row['nama_kategori']; ?></td>
            <td><?= $row['deskripsi']; ?></td>
            <td><?= $row['deadline']; ?></td>
            <td style="background-color: <?= $warna ?>;"><?= $status; ?></td>
        </tr>
        <?php endwhile; ?>
    </tbody>
</table>