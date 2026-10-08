<?php
session_start();

// Perbaikan: Ubah $_SESSION['level'] menjadi $_SESSION['role'] agar cocok dengan sistem login
if (!isset($_SESSION['status']) || $_SESSION['status'] != "login" || $_SESSION['role'] != "admin") {
    header("location:../login.php?pesan=belum_login");
    exit;
}

include '../koneksi.php';
include 'header.php'; 

// Logika Sapaan
date_default_timezone_set('Asia/Jakarta');
$jam = date('H');
if ($jam >= 3 && $jam < 11) {
    $sapaan = "Selamat Pagi";
} else if ($jam >= 11 && $jam < 15) {
    $sapaan = "Selamat Siang";
} else if ($jam >= 15 && $jam < 18) {
    $sapaan = "Selamat Sore";
} else {
    $sapaan = "Selamat Malam";
}

// Ambil Statistik
$q_produk = mysqli_query($koneksi, "SELECT COUNT(*) AS total FROM produk");
$total_produk = ($q_produk) ? mysqli_fetch_assoc($q_produk)['total'] : 0;

$q_pelanggan = mysqli_query($koneksi, "SELECT COUNT(*) AS total FROM pelanggan");
$total_pelanggan = ($q_pelanggan) ? mysqli_fetch_assoc($q_pelanggan)['total'] : 0;

$q_transaksi = mysqli_query($koneksi, "SELECT COUNT(*) AS total FROM penjualan");
$total_transaksi = ($q_transaksi) ? mysqli_fetch_assoc($q_transaksi)['total'] : 0;

$q_pendapatan = mysqli_query($koneksi, "SELECT SUM(TotalHarga) AS total FROM penjualan");
$total_pendapatan = ($q_pendapatan) ? (mysqli_fetch_assoc($q_pendapatan)['total'] ?? 0) : 0;
?>

<!-- Import Google Fonts: Poppins & Inter -->
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700;800&family=Poppins:wght@600;700;800&display=swap" rel="stylesheet">

<style>
/* Background Image Utama Admin */
body {
    background: url('../assets/img/bgadmin.jfif') no-repeat center center fixed;
    background-size: cover;
    font-family: 'Inter', sans-serif;
}

/* --- ANIMASI SANTAI, LEMBUT, & ELEGAN --- */
@keyframes fadeInUpSlow {
    0% { opacity: 0; transform: translateY(30px); }
    100% { opacity: 1; transform: translateY(0); }
}

@keyframes popScaleSlow {
    0% { opacity: 0; transform: scale(0.94); }
    100% { opacity: 1; transform: scale(1); }
}

.animate-hero {
    animation: fadeInUpSlow 1.2s cubic-bezier(0.25, 1, 0.5, 1) forwards;
}

.animate-grid {
    animation: popScaleSlow 1.2s cubic-bezier(0.25, 1, 0.5, 1) 0.25s forwards;
    opacity: 0;
}

.animate-table {
    animation: fadeInUpSlow 1.2s cubic-bezier(0.25, 1, 0.5, 1) 0.45s forwards;
    opacity: 0;
}

.admin-container {
    max-width: 1200px;
    margin: 30px auto;
    padding: 0 20px;
}

/* Header Hero Sapaan */
.header-hero {
    display: flex;
    justify-content: space-between;
    align-items: flex-end;
    margin-bottom: 25px;
    padding: 0 10px;
}

.header-hero h1 {
    margin: 0;
    font-family: 'Poppins', sans-serif;
    font-size: 32px;
    font-weight: 800;
    color: #112211;
    letter-spacing: -0.5px;
    display: flex;
    align-items: center;
    gap: 14px;
    text-shadow: 0 2px 10px rgba(255, 255, 255, 0.6);
}

.header-hero p {
    margin: 6px 0 0 0;
    font-family: 'Inter', sans-serif;
    color: #2d422a;
    font-size: 14.5px;
    font-weight: 600;
    letter-spacing: 0.2px;
}

.badge-date {
    background: rgba(28, 46, 27, 0.88);
    backdrop-filter: blur(10px);
    color: #ffffff;
    padding: 9px 20px;
    border-radius: 30px;
    font-size: 13.5px;
    font-weight: 600;
    box-shadow: 0 4px 15px rgba(0,0,0,0.15);
    display: flex;
    align-items: center;
    gap: 8px;
    transition: transform 0.4s ease;
}

.badge-date:hover {
    transform: translateY(-2px);
}

/* Executive Dashboard Grid Layout */
.dashboard-grid {
    display: grid;
    grid-template-columns: 380px 1fr;
    gap: 25px;
    margin-bottom: 25px;
}

/* Panel Utama Kiri: Highlight Pendapatan */
.main-revenue-panel {
    background: rgba(28, 46, 27, 0.92);
    backdrop-filter: blur(16px);
    border-radius: 24px;
    padding: 32px 28px;
    color: #ffffff;
    border: 1px solid rgba(255, 255, 255, 0.2);
    box-shadow: 0 15px 35px rgba(0, 0, 0, 0.2);
    display: flex;
    flex-direction: column;
    justify-content: space-between;
    transition: all 0.5s cubic-bezier(0.25, 1, 0.5, 1);
}

.main-revenue-panel:hover {
    transform: translateY(-4px);
    box-shadow: 0 20px 45px rgba(28, 46, 27, 0.3);
}

.revenue-label {
    font-size: 12px;
    text-transform: uppercase;
    letter-spacing: 1.5px;
    color: #a3c2a0;
    font-weight: 700;
}

.revenue-amount {
    font-family: 'Poppins', sans-serif;
    font-size: 34px;
    font-weight: 800;
    margin: 12px 0;
    color: #ffffff;
}

/* Panel Kanan: Stack Stats */
.side-stats-panel {
    background: rgba(255, 255, 255, 0.88);
    backdrop-filter: blur(16px);
    border-radius: 24px;
    padding: 25px 30px;
    border: 1px solid rgba(255, 255, 255, 0.7);
    box-shadow: 0 12px 30px rgba(0,0,0,0.08);
    display: flex;
    flex-direction: column;
    justify-content: space-around;
}

.stat-row {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 12px 0;
    border-bottom: 1px dashed rgba(28, 46, 27, 0.15);
    transition: transform 0.4s ease;
}

.stat-row:hover {
    transform: translateX(4px);
}

.stat-row:last-child {
    border-bottom: none;
}

.stat-left {
    display: flex;
    align-items: center;
    gap: 15px;
}

.stat-icon-wrap {
    width: 42px;
    height: 42px;
    background: #1c2e1b;
    color: #ffffff;
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 16px;
    transition: transform 0.4s ease;
}

.stat-row:hover .stat-icon-wrap {
    transform: scale(1.06);
}

.stat-title {
    font-size: 13px;
    font-weight: 700;
    color: #3e523c;
    text-transform: uppercase;
}

.stat-number {
    font-family: 'Poppins', sans-serif;
    font-size: 22px;
    font-weight: 800;
    color: #1c2e1b;
}

/* Tabel Transaksi Modern Minimalis */
.table-panel {
    background: rgba(255, 255, 255, 0.9);
    backdrop-filter: blur(16px);
    border-radius: 24px;
    padding: 28px 32px;
    border: 1px solid rgba(255, 255, 255, 0.7);
    box-shadow: 0 12px 30px rgba(0,0,0,0.08);
}

.table-title {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 20px;
}

.table-title h3 {
    margin: 0;
    font-family: 'Poppins', sans-serif;
    font-size: 18px;
    color: #1c2e1b;
    font-weight: 800;
}

table {
    width: 100%;
    border-collapse: collapse;
}

table th {
    text-align: left;
    padding: 12px 15px;
    font-size: 12px;
    text-transform: uppercase;
    letter-spacing: 0.8px;
    color: #ffffff;
    background: #8b9d88;
    border: none;
}

table th:first-child { border-top-left-radius: 10px; border-bottom-left-radius: 10px; }
table th:last-child { border-top-right-radius: 10px; border-bottom-right-radius: 10px; }

table td {
    padding: 16px 15px;
    font-size: 14px;
    color: #1c2e1b;
    border-bottom: 1px solid rgba(0,0,0,0.05);
}

table tr {
    transition: background 0.3s ease;
}

table tr:hover td {
    background: rgba(139, 157, 136, 0.12);
}
</style>

<div class="admin-container">
    <!-- Header Hero Sapaan -->
    <div class="header-hero animate-hero">
        <div>
            <h1>
                <i class="fa-solid fa-user-shield" style="color: #112211; font-size: 28px;"></i>
                <?= $sapaan ?>, <?= htmlspecialchars($_SESSION['nama_user'] ?? 'Administrator Nis & Co'); ?>
            </h1>
            <p>Pusat Kendali Performa & Log Aktivitas Sistem Toko Nis & Co</p>
        </div>
        <div class="badge-date">
            <i class="fa-regular fa-calendar-days"></i>
            <?= date('d F Y'); ?>
        </div>
    </div>

    <!-- Layout Asimetris (Hero Pendapatan + Side Stats) -->
    <div class="dashboard-grid animate-grid">
        <!-- Panel Utama Pendapatan -->
        <div class="main-revenue-panel">
            <div>
                <span class="revenue-label">Akumulasi Omset</span>
                <div class="revenue-amount">Rp <?= number_format($total_pendapatan, 0, ',', '.'); ?></div>
            </div>
            <div style="display: flex; align-items: center; justify-content: space-between; background: rgba(255,255,255,0.1); padding: 12px 16px; border-radius: 14px;">
                <span style="font-size: 12px; color: #d0e3ce;">Status Keuangan</span>
                <span style="font-size: 12px; font-weight: 700; color: #ffffff;"><i class="fa-solid fa-circle-check" style="color: #82d97b;"></i> Aktif Terintegrasi</span>
            </div>
        </div>

        <!-- Panel Samping Statistik -->
        <div class="side-stats-panel">
            <div class="stat-row">
                <div class="stat-left">
                    <div class="stat-icon-wrap"><i class="fa-solid fa-boxes-stacked"></i></div>
                    <span class="stat-title">Stok Produk</span>
                </div>
                <span class="stat-number"><?= number_format($total_produk, 0, ',', '.'); ?></span>
            </div>

            <div class="stat-row">
                <div class="stat-left">
                    <div class="stat-icon-wrap"><i class="fa-solid fa-users-gear"></i></div>
                    <span class="stat-title">Pelanggan Terdaftar</span>
                </div>
                <span class="stat-number"><?= number_format($total_pelanggan, 0, ',', '.'); ?></span>
            </div>

            <div class="stat-row">
                <div class="stat-left">
                    <div class="stat-icon-wrap"><i class="fa-solid fa-receipt"></i></div>
                    <span class="stat-title">Total Transaksi</span>
                </div>
                <span class="stat-number"><?= number_format($total_transaksi, 0, ',', '.'); ?></span>
            </div>
        </div>
    </div>

    <!-- Tabel Transaksi Terbaru -->
    <div class="table-panel animate-table">
        <div class="table-title">
            <h3><i class="fa-solid fa-list-check" style="margin-right: 8px;"></i> 5 Aktivitas Penjualan Terakhir</h3>
            <a href="penjualan.php" style="color: #1c2e1b; text-decoration: none; font-size: 13px; font-weight: 700; transition: opacity 0.3s;" onmouseover="this.style.opacity='0.7'" onmouseout="this.style.opacity='1'">Lihat Seluruh Laporan &rarr;</a>
        </div>

        <table>
            <thead>
                <tr>
                    <th style="width: 50px;">No</th>
                    <th>Kode Transaksi</th>
                    <th>Tanggal</th>
                    <th>Nama Pelanggan</th>
                    <th style="text-align: right;">Nominal</th>
                </tr>
            </thead>
            <tbody>
            <?php
            $no = 1;
            $query_limit = mysqli_query($koneksi, "SELECT penjualan.*, pelanggan.NamaPelanggan FROM penjualan LEFT JOIN pelanggan ON penjualan.PelangganID = pelanggan.PelangganID ORDER BY penjualan.PenjualanID DESC LIMIT 5");
            
            if ($query_limit && mysqli_num_rows($query_limit) > 0) {
                while ($row = mysqli_fetch_assoc($query_limit)) {
                    echo "<tr>";
                    echo "<td><b>" . $no++ . "</b></td>";
                    echo "<td><span style='background: #1c2e1b; color: #ffffff; padding: 4px 10px; border-radius: 20px; font-weight: 700; font-size: 11px;'>TRX-" . str_pad($row['PenjualanID'], 4, '0', STR_PAD_LEFT) . "</span></td>";
                    echo "<td>" . date('d/m/Y', strtotime($row['TanggalPenjualan'])) . "</td>";
                    echo "<td>" . ($row['NamaPelanggan'] ? htmlspecialchars($row['NamaPelanggan']) : '<i>Pelanggan Umum</i>') . "</td>";
                    echo "<td style='text-align: right; font-weight: 800; color: #1c2e1b;'>Rp " . number_format($row['TotalHarga'], 0, ',', '.') . "</td>";
                    echo "</tr>";
                }
            } else {
                echo "<tr><td colspan='5' style='text-align: center; color: #5c705a; padding: 30px;'>Belum ada data transaksi tercatat.</td></tr>";
            }
            ?>
            </tbody>
        </table>
    </div>
</div>

</body>
</html>