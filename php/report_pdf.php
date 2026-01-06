<?php
session_start();
require_once 'middleware/auth.php';
require_once 'koneksi.php';

// Cek parameter
if (!isset($_GET['start_date']) || !isset($_GET['end_date'])) {
    die('Parameter tidak lengkap');
}

$start_date = $_GET['start_date'];
$end_date = $_GET['end_date'];
$user_id = $_SESSION['user_id'];

// Ambil data user
$db = new Database();
$db->query('SELECT nama, email FROM users WHERE id = :id');
$db->bind(':id', $user_id);
$user = $db->single();

// Include TCPDF library
require_once 'tcpdf/tcpdf.php';

// Buat PDF baru
$pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);

// Set document information
$pdf->SetCreator('Finansialku System');
$pdf->SetAuthor('Finansialku');
$pdf->SetTitle('Laporan Keuangan - ' . date('Y-m-d'));
$pdf->SetSubject('Laporan Keuangan');

// Set default header data
$pdf->SetHeaderData(PDF_HEADER_LOGO, PDF_HEADER_LOGO_WIDTH, 'LAPORAN KEUANGAN', "Periode: " . date('d M Y', strtotime($start_date)) . " - " . date('d M Y', strtotime($end_date)));

// Set header and footer fonts
$pdf->setHeaderFont(Array(PDF_FONT_NAME_MAIN, '', PDF_FONT_SIZE_MAIN));
$pdf->setFooterFont(Array(PDF_FONT_NAME_DATA, '', PDF_FONT_SIZE_DATA));

// Set margins
$pdf->SetMargins(15, 25, 15);
$pdf->SetHeaderMargin(10);
$pdf->SetFooterMargin(10);

// Set auto page breaks
$pdf->SetAutoPageBreak(TRUE, 15);

// Set image scale factor
$pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);

// Add a page
$pdf->AddPage();

// Set font
$pdf->SetFont('helvetica', '', 10);

// Judul
$pdf->SetFont('helvetica', 'B', 16);
$pdf->Cell(0, 10, 'LAPORAN KEUANGAN', 0, 1, 'C');
$pdf->SetFont('helvetica', '', 11);
$pdf->Cell(0, 5, 'Periode: ' . date('d M Y', strtotime($start_date)) . ' - ' . date('d M Y', strtotime($end_date)), 0, 1, 'C');
$pdf->Cell(0, 5, 'Dibuat oleh: ' . $user['nama'], 0, 1, 'C');
$pdf->Cell(0, 5, 'Tanggal Cetak: ' . date('d M Y H:i:s'), 0, 1, 'C');
$pdf->Ln(10);

// Ambil data summary
$db->query('SELECT 
            COALESCE(SUM(CASE WHEN jenis = "pemasukan" THEN jumlah ELSE 0 END), 0) as total_pemasukan,
            COALESCE(SUM(CASE WHEN jenis = "pengeluaran" THEN jumlah ELSE 0 END), 0) as total_pengeluaran,
            COUNT(*) as total_transaksi
            FROM transaksi 
            WHERE user_id = :user_id 
            AND tanggal BETWEEN :start_date AND :end_date');
$db->bind(':user_id', $user_id);
$db->bind(':start_date', $start_date);
$db->bind(':end_date', $end_date);
$summary = $db->single();

$saldo = $summary['total_pemasukan'] - $summary['total_pengeluaran'];

// Ringkasan
$pdf->SetFont('helvetica', 'B', 12);
$pdf->Cell(0, 10, 'Ringkasan Keuangan', 0, 1);
$pdf->SetFont('helvetica', '', 10);

// Tabel ringkasan
$summary_html = '
<table border="1" cellpadding="4" style="border-collapse: collapse;">
    <thead>
        <tr style="background-color:#f2f2f2;">
            <th width="25%">Item</th>
            <th width="25%">Jumlah</th>
            <th width="25%">Item</th>
            <th width="25%">Jumlah</th>
        </tr>
    </thead>
    <tbody>
        <tr>
            <td><strong>Total Pemasukan</strong></td>
            <td>Rp ' . number_format($summary['total_pemasukan'], 0, ',', '.') . '</td>
            <td><strong>Total Transaksi</strong></td>
            <td>' . number_format($summary['total_transaksi'], 0, ',', '.') . '</td>
        </tr>
        <tr>
            <td><strong>Total Pengeluaran</strong></td>
            <td>Rp ' . number_format($summary['total_pengeluaran'], 0, ',', '.') . '</td>
            <td><strong>Saldo Periode</strong></td>
            <td>Rp ' . number_format($saldo, 0, ',', '.') . '</td>
        </tr>
    </tbody>
</table>';

$pdf->writeHTML($summary_html, true, false, true, false, '');
$pdf->Ln(10);

// Ambil transaksi
$db->query('SELECT * FROM transaksi 
            WHERE user_id = :user_id 
            AND tanggal BETWEEN :start_date AND :end_date
            ORDER BY tanggal DESC');
$db->bind(':user_id', $user_id);
$db->bind(':start_date', $start_date);
$db->bind(':end_date', $end_date);
$transactions = $db->resultSet();

// Detail Transaksi
if (!empty($transactions)) {
    $pdf->SetFont('helvetica', 'B', 12);
    $pdf->Cell(0, 10, 'Detail Transaksi (' . count($transactions) . ' transaksi)', 0, 1);
    $pdf->SetFont('helvetica', '', 9);
    
    $detail_html = '
    <table border="1" cellpadding="4" style="border-collapse: collapse;">
        <thead>
            <tr style="background-color:#f2f2f2;">
                <th width="15%">Tanggal</th>
                <th width="20%">Kategori</th>
                <th width="25%">Deskripsi</th>
                <th width="15%">Jenis</th>
                <th width="25%">Jumlah</th>
            </tr>
        </thead>
        <tbody>';
    
    foreach ($transactions as $transaction) {
        $jenis = $transaction['jenis'] == 'pemasukan' ? 'Pemasukan' : 'Pengeluaran';
        $warna = $transaction['jenis'] == 'pemasukan' ? '#d4edda' : '#f8d7da';
        $warna_teks = $transaction['jenis'] == 'pemasukan' ? '#155724' : '#721c24';
        
        $detail_html .= '
        <tr>
            <td>' . date('d M Y', strtotime($transaction['tanggal'])) . '</td>
            <td>' . htmlspecialchars($transaction['kategori']) . '</td>
            <td>' . htmlspecialchars($transaction['deskripsi']) . '</td>
            <td style="background-color:' . $warna . ';color:' . $warna_teks . ';font-weight:bold;">' . $jenis . '</td>
            <td align="right" style="font-weight:bold;color:' . $warna_teks . ';">' . 
              ($transaction['jenis'] == 'pemasukan' ? '+' : '-') . ' Rp ' . 
              number_format($transaction['jumlah'], 0, ',', '.') . '</td>
        </tr>';
    }
    
    $detail_html .= '
        </tbody>
        <tfoot>
            <tr style="background-color:#f2f2f2;">
                <td colspan="3" align="right"><strong>TOTAL:</strong></td>
                <td><strong>Pemasukan</strong></td>
                <td align="right" style="font-weight:bold;color:#155724;"><strong>+ Rp ' . number_format($summary['total_pemasukan'], 0, ',', '.') . '</strong></td>
            </tr>
            <tr style="background-color:#f2f2f2;">
                <td colspan="3" align="right"></td>
                <td><strong>Pengeluaran</strong></td>
                <td align="right" style="font-weight:bold;color:#721c24;"><strong>- Rp ' . number_format($summary['total_pengeluaran'], 0, ',', '.') . '</strong></td>
            </tr>
            <tr style="background-color:#e8f4f8;">
                <td colspan="3" align="right"><strong>SALDO AKHIR:</strong></td>
                <td colspan="2" align="center" style="font-weight:bold;"><strong>Rp ' . 
                  number_format($saldo, 0, ',', '.') . '</strong></td>
            </tr>
        </tfoot>
    </table>';
    
    $pdf->writeHTML($detail_html, true, false, true, false, '');
} else {
    $pdf->SetFont('helvetica', '', 10);
    $pdf->Cell(0, 10, 'Tidak ada transaksi pada periode ini.', 0, 1);
}

$pdf->Ln(10);

// Footer
$pdf->SetFont('helvetica', 'I', 8);
$pdf->Cell(0, 10, 'Dokumen ini dibuat otomatis oleh Sistem Finansialku. Hak Cipta © ' . date('Y') . ' Finansialku.', 0, 1, 'C');
$pdf->Cell(0, 5, 'Dokumen bersifat rahasia dan hanya untuk penggunaan pribadi.', 0, 1, 'C');

// Output PDF
$pdf->Output('Laporan-Keuangan-' . date('Y-m-d') . '.pdf', 'D');
?>