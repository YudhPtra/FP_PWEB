<?php
// api/tasks/read.php
session_start();
require_once '../../config/database.php';
header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    http_response_code(403);
    echo json_encode([]);
    exit;
}

$user_id = $_SESSION['user_id'];

// ==========================================
// 1. TANGKAP PARAMETER FILTER (DARI FRONTEND)
// ==========================================
$search = isset($_GET['search']) ? mysqli_real_escape_string($conn, $_GET['search']) : '';
$sort   = isset($_GET['sort']) ? $_GET['sort'] : 'deadline_asc';
$cat_id = isset($_GET['category']) ? $_GET['category'] : '';

// ==========================================
// 2. BANGUN QUERY DINAMIS
// ==========================================
$query = "SELECT t.*, c.nama_kategori, c.warna_label 
          FROM tasks t 
          JOIN categories c ON t.category_id = c.id 
          WHERE t.user_id = '$user_id'";

// A. Logika Search (Judul atau Deskripsi)
if (!empty($search)) {
    $query .= " AND (t.judul LIKE '%$search%' OR t.deskripsi LIKE '%$search%')";
}

// B. Logika Filter Kategori
if (!empty($cat_id)) {
    $query .= " AND t.category_id = '$cat_id'";
}

// C. Logika Sorting
switch ($sort) {
    case 'deadline_asc':
        $query .= " ORDER BY t.deadline ASC"; // Terdekat
        break;
    case 'deadline_desc':
        $query .= " ORDER BY t.deadline DESC"; // Terlama
        break;
    case 'priority_high':
        // Trik sorting ENUM/String: Tinggi > Sedang > Rendah
        // Kita pakai FIELD() function di MySQL biar urutannya kustom
        $query .= " ORDER BY FIELD(t.prioritas, 'Tinggi', 'Sedang', 'Rendah')";
        break;
    case 'priority_low':
        $query .= " ORDER BY FIELD(t.prioritas, 'Rendah', 'Sedang', 'Tinggi')";
        break;
    default:
        $query .= " ORDER BY t.deadline ASC";
}

// ==========================================
// 3. EKSEKUSI
// ==========================================
$result = mysqli_query($conn, $query);
$tasks = [];

while ($row = mysqli_fetch_assoc($result)) {
    $tasks[] = $row;
}

echo json_encode($tasks);
?>