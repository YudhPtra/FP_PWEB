<?php
// pages/user/export_pdf.php
session_start();
require_once '../../config/database.php';
// Panggil library FPDF yang tadi kamu taruh
require_once '../../libraries/fpdf/fpdf.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: ../auth/login.php");
    exit;
}

$user_id = $_SESSION['user_id'];

class PDF extends FPDF {
    // Header Halaman
    function Header() {
        $this->SetFont('Arial','B',14);
        $this->Cell(0,10,'LAPORAN TUGAS SAYA',0,1,'C');
        $this->SetFont('Arial','I',10);
        $this->Cell(0,10,'Task Manager App',0,1,'C');
        $this->Ln(5);
        
        // Header Tabel
        $this->SetFont('Arial','B',10);
        $this->SetFillColor(200,220,255);
        $this->Cell(10,10,'No',1,0,'C',true);
        $this->Cell(60,10,'Judul Tugas',1,0,'C',true);
        $this->Cell(30,10,'Kategori',1,0,'C',true);
        $this->Cell(40,10,'Deadline',1,0,'C',true);
        $this->Cell(30,10,'Status',1,1,'C',true);
    }

    // Footer Halaman
    function Footer() {
        $this->SetY(-15);
        $this->SetFont('Arial','I',8);
        $this->Cell(0,10,'Halaman '.$this->PageNo().'/{nb}',0,0,'C');
    }
}

// Inisialisasi PDF
$pdf = new PDF();
$pdf->AliasNbPages();
$pdf->AddPage();
$pdf->SetFont('Arial','',10);

// Ambil Data
$query = "SELECT t.*, c.nama_kategori 
          FROM tasks t
          JOIN categories c ON t.category_id = c.id
          WHERE t.user_id = '$user_id' 
          ORDER BY t.deadline ASC";
$result = mysqli_query($conn, $query);

$no = 1;
while($row = mysqli_fetch_assoc($result)) {
    $pdf->Cell(10,10,$no++,1,0,'C');
    $pdf->Cell(60,10,substr($row['judul'], 0, 30),1,0); // Cut text biar gak kepanjangan
    $pdf->Cell(30,10,$row['nama_kategori'],1,0);
    $pdf->Cell(40,10,substr($row['deadline'], 0, 16),1,0);
    $pdf->Cell(30,10,$row['status'],1,1,'C');
}

$pdf->Output();
?>