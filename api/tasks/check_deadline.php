<?php
// api/tasks/check_deadline.php
session_start();
require_once '../../config/database.php';
header('Content-Type: application/json');

// 1. ATUR ZONA WAKTU (Wajib biar NOW() akurat sesuai jam Indonesia)
date_default_timezone_set('Asia/Jakarta');

if (!isset($_SESSION['user_id'])) {
    exit;
}

$user_id = $_SESSION['user_id'];

// 2. QUERY CARI TUGAS H-3
// Kriteria:
// - Milik User ini
// - Status belum selesai
// - Belum pernah dikirim notifikasi (reminder_sent = 0)
// - Deadline <= 3 hari ke depan DAN Deadline > Sekarang
$query = "SELECT * FROM tasks 
          WHERE user_id = '$user_id' 
          AND status = 'Belum' 
          AND reminder_sent = 0 
          AND deadline <= DATE_ADD(NOW(), INTERVAL 3 DAY)
          AND deadline > NOW()"; // Pastikan deadline belum lewat

$result = mysqli_query($conn, $query);
$count = 0;

if (!$result) {
    // Debugging: Kalau query error, tampilkan
    echo json_encode(['status' => 'error', 'message' => mysqli_error($conn)]);
    exit;
}

while ($task = mysqli_fetch_assoc($result)) {
    $judul = mysqli_real_escape_string($conn, $task['judul']);
    
    // Hitung sisa waktu (Opsional: biar pesannya lebih dramatis)
    $deadline_time = strtotime($task['deadline']);
    $now = time();
    $diff = $deadline_time - $now;
    $hours = floor($diff / (60 * 60)); // Sisa jam
    
    if ($hours < 24) {
        $pesan = "Deadline tugas '$judul' tinggal $hours jam lagi! Buruan kerjain!";
    } else {
        $pesan = "Deadline tugas '$judul' tinggal 3 hari lagi! Segera selesaikan.";
    }
    
    // Escape pesan agar aman
    $pesan = mysqli_real_escape_string($conn, $pesan);

    // 3. INSERT KE NOTIFIKASI
    $sql_notif = "INSERT INTO notifications (user_id, judul, pesan, tipe, is_read, created_at) 
                  VALUES ('$user_id', 'Reminder Deadline', '$pesan', 'warning', 0, NOW())";
    
    if (mysqli_query($conn, $sql_notif)) {
        // 4. UPDATE STATUS REMINDER (Supaya tidak spam)
        $task_id = $task['id'];
        mysqli_query($conn, "UPDATE tasks SET reminder_sent = 1 WHERE id = '$task_id'");
        $count++;
    }
}

echo json_encode([
    'status' => 'success', 
    'reminders_sent' => $count, 
    'server_time' => date('Y-m-d H:i:s') // Cek jam server bener gak?
]);
?>