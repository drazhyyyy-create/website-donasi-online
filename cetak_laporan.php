<?php
require('fpdf/fpdf.php');
include 'Function/koneksi.php';

// Query data donasi terverifikasi
$query = "SELECT kategori, SUM(jumlah) as total FROM donasi WHERE status = 'terverifikasi' GROUP BY kategori";
$result = mysqli_query($conn, $query);

// Buat PDF
$pdf = new FPDF();
$pdf->AddPage();
$pdf->SetFont('Arial','B',16);
$pdf->Cell(0,10,'LAPORAN AKUMULASI DONASI',0,1,'C');
$pdf->Ln(5);

// Header tabel
$pdf->SetFont('Arial','B',12);
$pdf->Cell(80,10,'Kategori',1);
$pdf->Cell(100,10,'Total Donasi (Rp)',1);
$pdf->Ln();

// Isi data
$pdf->SetFont('Arial','',12);
while($row = mysqli_fetch_assoc($result)){
    $pdf->Cell(80,10, $row['kategori'],1);
    $pdf->Cell(100,10, 'Rp '.number_format($row['total'], 0, ',', '.'),1);
    $pdf->Ln();
}

// Output
$pdf->Output();
?>