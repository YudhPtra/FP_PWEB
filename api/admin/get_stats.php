<?php
// api/admin/get_stats.php
session_start();
require_once '../../config/database.php';
header('Content-Type: application/json');

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    http_response_code(403); exit;
}

// 1. Hitung Status (Selesai vs Belum)
$q_status = mysqli_query($conn, "SELECT status, COUNT(*) as jumlah FROM tasks GROUP BY status");
$stats_status = [];
while($row = mysqli_fetch_assoc($q_status)) {
    $stats_status[$row['status']] = $row['jumlah'];
}

// 2. Hitung Prioritas
$q_prio = mysqli_query($conn, "SELECT prioritas, COUNT(*) as jumlah FROM tasks GROUP BY prioritas");
$stats_prio = [];
while($row = mysqli_fetch_assoc($q_prio)) {
    $stats_prio[$row['prioritas']] = $row['jumlah'];
}

// 3. Total User & Tugas
$total_user = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as c FROM users WHERE role='user'"))['c'];
$total_task = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as c FROM tasks"))['c'];

echo json_encode([
    'by_status' => $stats_status,
    'by_priority' => $stats_prio,
    'total_user' => $total_user,
    'total_task' => $total_task
]);
?>