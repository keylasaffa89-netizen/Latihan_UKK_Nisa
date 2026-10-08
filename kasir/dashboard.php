<?php
session_start();

// Cek apakah user sudah login dan levelnya benar (kasir)
if (!isset($_SESSION['status']) || $_SESSION['status'] != "login" || $_SESSION['level'] != "kasir") {
    header("location: ../login.php?pesan=belum_login");
    exit;
}

// Perhatikan jalur file disesuaikan tanpa titik dua di depan (karena di root utama)
include '../koneksi.php';
include 'header.php'; 

// Logika Sapaan Berdasarkan Waktu
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

$today = date('Y-m-d');

// Ambil Statistik
$q_produk = mysqli_query($koneksi, "SELECT COUNT(*) AS total FROM produk");
$total_produk = ($q_produk) ? mysqli_fetch_assoc($q_produk)['total'] : 0;

$q_pelanggan = mysqli_query($koneksi, "SELECT COUNT(*) AS total FROM pelanggan");
$total_pelanggan = ($q_pelanggan) ? mysqli_fetch_assoc($q_pelanggan)['total'] : 0;

$q_transaksi_today = mysqli_query($koneksi, "SELECT COUNT(*) AS total FROM penjualan WHERE TanggalPenjualan = '$today'");
$transaksi_today = ($q_transaksi_today) ? mysqli_fetch_assoc($q_transaksi_today)['total'] : 0;
?>

<style>
/* Reset Background - Jalur gambar disesuaikan */
body {
    background: url('../assets/img/bgkasir.jfif') no-repeat center center fixed !important;
    background-size: cover !important;
    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif !important;
    overflow-x: hidden;
}

.dashboard-kasir-wrapper {
    padding: 30px 10px;
    max-width: 1100px;
    margin: 0 auto;
}

/* --- ANIMASI BERVARIASI & BERJENJANG (STAGGERED) --- */
@keyframes slideDownHeader {
    0% { opacity: 0; transform: translateY(-30px); }
    100% { opacity: 1; transform: translateY(0); }
}

@keyframes slideUpCards {
    0% { opacity: 0; transform: translateY(40px); }
    100% { opacity: 1; transform: translateY(0); }
}

@keyframes slideUpTable {
    0% { opacity: 0; transform: translateY(50px); }
    100% { opacity: 1; transform: translateY(0); }
}

@keyframes floatAction {
    0%, 100% { transform: translateY(0); }
    50% { transform: translateY(-4px); }
}

/* Terapkan animasi ke elemen secara terpisah */
.header-animated {
    animation: slideDownHeader 1s cubic-bezier(0.2, 0.8, 0.2, 1) forwards;
}

.stat-box-modern {
    opacity: 0;
    animation: slideUpCards 0.9s cubic-bezier(0.2, 0.8, 0.2, 1) forwards;
}

/* Jeda waktu kemunculan tiap kartu biar bervariasi dan estetik */
.stat-box-modern:nth-child(1) { animation-delay: 0.2s; }
.stat-box-modern:nth-child(2) { animation-delay: 0.35s; }
.stat-box-modern:nth-child(3) { animation-delay: 0.5s; }
.stat-box-modern:nth-child(4) { 
    animation-delay: 0.65s; 
    animation: slideUpCards 0.9s cubic-bezier(0.2, 0.8, 0.2, 1) forwards, floatAction 4s ease-in-out infinite 1.5s;
}

.table-wrapper-modern {
    opacity: 0;
    animation: slideUpTable 1s cubic-bezier(0.2, 0.8, 0.2, 1) forwards;
    animation-delay: 0.8s;
}

/* Header Sapaan Besar */
.sapaan-title {
    font-size: 42px !important;
    font-weight: 900 !important;
    color: #1A2E1A !important;
    margin: 0 0 6px 0 !important;
    letter-spacing: -0.5px;
    text-shadow: 0 2px 8px rgba(255, 255, 255, 0.7);
    display: flex;
    align-items: center;
    gap: 12px;
}

.sapaan-subtitle {
    font-size: 15px !important;
    color: #384E38 !important;
    font-weight: 600 !important;
    margin: 0 !important;
    text-shadow: 0 1px 4px rgba(255, 255, 255, 0.6);
}

.badge-tgl {
    background: rgba(255, 255, 255, 0.6) !important;
    backdrop-filter: blur(8px);
    -webkit-backdrop-filter: blur(8px);
    border: 1px solid rgba(255, 255, 255, 0.8) !important;
    padding: 8px 16px;
    border-radius: 20px;
    font-size: 13px;
    font-weight: 700;
    color: #1A2E1A;
    display: inline-flex;
    align-items: center;
    gap: 8px;
    box-shadow: 0 4px 12px rgba(0,0,0,0.03);
    transition: transform 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
}

.badge-tgl:hover {
    transform: scale(1.05) rotate(-1deg);
}

/* Grid Layout Statistik */
.stats-container {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(210px, 1fr));
    gap: 16px;
    margin: 30px 0;
}

.stat-box-modern {
    backdrop-filter: blur(10px);
    -webkit-backdrop-filter: blur(10px);
    border-radius: 18px;
    padding: 22px;
    display: flex;
    align-items: center;
    gap: 16px;
    cursor: pointer;
    transition: all 0.5s cubic-bezier(0.175, 0.885, 0.32, 1.275);
    border: 1px solid rgba(255, 255, 255, 0.4);
    box-shadow: 0 8px 20px rgba(0,0,0,0.06);
    position: relative;
    overflow: hidden;
}

.stat-box-modern:hover {
    transform: translateY(-8px) scale(1.03);
    box-shadow: 0 18px 35px rgba(0,0,0,0.15);
}

.stat-box-modern:active {
    transform: translateY(-2px) scale(0.98);
}

/* Varian Warna Khusus Setiap Kartu */
.card-produk {
    background: linear-gradient(135deg, rgba(85, 107, 47, 0.88), rgba(60, 80, 30, 0.94)) !important;
    color: white !important;
}

.card-pelanggan {
    background: linear-gradient(135deg, rgba(108, 122, 105, 0.88), rgba(75, 90, 72, 0.94)) !important;
    color: white !important;
}

.card-transaksi {
    background: linear-gradient(135deg, rgba(138, 154, 134, 0.88), rgba(100, 118, 96, 0.94)) !important;
    color: white !important;
}

.card-aksi {
    background: linear-gradient(135deg, rgba(46, 71, 46, 0.92), rgba(25, 45, 25, 0.96)) !important;
    color: white !important;
}

.stat-box-icon {
    width: 48px;
    height: 48px;
    border-radius: 14px;
    background: rgba(255, 255, 255, 0.25);
    color: white;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
    transition: transform 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
}

.stat-box-modern:hover .stat-box-icon {
    transform: scale(1.15) rotate(10deg);
}

.stat-box-label {
    font-size: 11px;
    font-weight: 800;
    opacity: 0.9;
    text-transform: uppercase;
    letter-spacing: 0.6px;
    display: block;
}

.stat-box-value {
    font-size: 24px;
    font-weight: 800;
    margin: 2px 0 0 0;
}

/* Tabel Transaksi Semi-Transparan Translucence */
.table-wrapper-modern {
    background: rgba(255, 255, 255, 0.6) !important;
    backdrop-filter: blur(12px);
    -webkit-backdrop-filter: blur(12px);
    border: 1px solid rgba(255, 255, 255, 0.8) !important;
    border-radius: 22px;
    padding: 26px;
    box-shadow: 0 10px 30px rgba(0,0,0,0.05);
    transition: box-shadow 0.4s ease;
}

.table-wrapper-modern:hover {
    box-shadow: 0 16px 40px rgba(0,0,0,0.08);
}

.table-header-flex {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 18px;
}

.table-title {
    font-size: 18px;
    font-weight: 800;
    color: #1A2E1A;
    margin: 0;
}

.btn-lihat-semua {
    color: #1A2E1A;
    font-size: 13px;
    font-weight: 700;
    text-decoration: none;
    background: rgba(255, 255, 255, 0.75);
    padding: 7px 16px;
    border-radius: 12px;
    transition: all 0.3s cubic-bezier(0.175, 0.885, 0.32, 1.275);
    box-shadow: 0 2px 6px rgba(0,0,0,0.03);
}

.btn-lihat-semua:hover {
    background: rgba(255, 255, 255, 0.98);
    transform: translateX(5px) scale(1.02);
    color: #2E472E;
}

.table-kasir-custom {
    width: 100%;
    border-collapse: collapse;
}

.table-kasir-custom th {
    text-align: left;
    padding: 12px 14px;
    font-size: 12px;
    font-weight: 800;
    color: #1A2E1A;
    text-transform: uppercase;
    border-bottom: 2px solid rgba(46, 71, 46, 0.25);
}

.table-kasir-custom td {
    padding: 14px;
    font-size: 14px;
    font-weight: 600;
    color: #111E11;
    border-bottom: 1px solid rgba(0, 0, 0, 0.05);
}

.table-kasir-custom tr {
    transition: all 0.25s ease;
}

.table-kasir-custom tr:hover td {
    background: rgba(255, 255, 255, 0.75);
    padding-left: 18px; /* Efek geser kanan halus */
}
</style>

<div class="dashboard-kasir-wrapper">
    <!-- Header Sapaan dengan Animasi Turun Lembut -->
    <div class="header-animated" style="display: flex; justify-content: space-between; align-items: flex-end; flex-wrap: wrap; gap: 15px;">
        <div>
            <h1 class="sapaan-title">
                <svg width="38" height="38" viewBox="0 0 24 24" fill="none" stroke="#1A2E1A" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
                    <circle cx="12" cy="7" r="4"></circle>
                </svg>
                <?= $sapaan ?>, Kasir!
            </h1>
            <p class="sapaan-subtitle">Siap melayani dan mencatat transaksi pelanggan hari ini?</p>
        </div>
        
        <div class="badge-tgl">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><rect x="3" y="4" width="18" height="18" rx="2"></rect><line x1="16" y1="2" x2="16" y2="6"></line><line x1="8" y1="2" x2="8" y2="6"></line><line x1="3" y1="10" x2="21" y2="10"></line></svg>
            <?= date('d F Y'); ?>
        </div>
    </div>

    <!-- Layout 4 Statistik Bernuansa Nature dengan Animasi Bergantian (Staggered) -->
    <div class="stats-container">
        <!-- Jalur link tetap seperti aslinya karena berada satu folder di dalam kasir -->
        <div class="stat-box-modern card-produk" onclick="window.location='produk.php';">
            <div class="stat-box-icon">
                <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M6 2L3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4z"></path><line x1="3" y1="6" x2="21" y2="6"></line></svg>
            </div>
            <div>
                <span class="stat-box-label">Total Produk</span>
                <h3 class="stat-box-value"><?= number_format($total_produk, 0, ',', '.'); ?></h3>
            </div>
        </div>

        <div class="stat-box-modern card-pelanggan" onclick="window.location='pelanggan.php';">
            <div class="stat-box-icon">
                <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path><circle cx="9" cy="7" r="4"></circle></svg>
            </div>
            <div>
                <span class="stat-box-label">Total Pelanggan</span>
                <h3 class="stat-box-value"><?= number_format($total_pelanggan, 0, ',', '.'); ?></h3>
            </div>
        </div>

        <div class="stat-box-modern card-transaksi" onclick="window.location='penjualan.php';">
            <div class="stat-box-icon">
                <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="2" y="5" width="20" height="14" rx="2"></rect><line x1="2" y1="10" x2="22" y2="10"></line></svg>
            </div>
            <div>
                <span class="stat-box-label">Transaksi Hari Ini</span>
                <h3 class="stat-box-value"><?= number_format($transaksi_today, 0, ',', '.'); ?></h3>
            </div>
        </div>

        <div class="stat-box-modern card-aksi" onclick="window.location='tambah_penjualan.php';">
            <div class="stat-box-icon">
                <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"></circle><line x1="12" y1="8" x2="12" y2="16"></line><line x1="8" y1="12" x2="16" y2="12"></line></svg>
            </div>
            <div>
                <span class="stat-box-label">Aksi Cepat</span>
                <h3 class="stat-box-value" style="font-size: 16px;">+ Transaksi Baru</h3>
            </div>
        </div>
    </div>

    <!-- Tabel Transaksi Terbaru dengan Animasi Masuk Menyusul -->
    <div class="table-wrapper-modern">
        <div class="table-header-flex">
            <h3 class="table-title">Aktivitas Transaksi Terbaru</h3>
            <a href="penjualan.php" class="btn-lihat-semua">Lihat Semua &rarr;</a>
        </div>

        <table class="table-kasir-custom">
            <thead>
                <tr>
                    <th>No</th>
                    <th>ID Transaksi</th>
                    <th>Tanggal</th>
                    <th>Nama Pelanggan</th>
                    <th>Total Harga</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $no = 1;
                $query_limit = mysqli_query($koneksi, "SELECT penjualan.*, pelanggan.NamaPelanggan FROM penjualan LEFT JOIN pelanggan ON penjualan.PelangganID = pelanggan.PelangganID ORDER BY penjualan.PenjualanID DESC LIMIT 5");
                
                if ($query_limit && mysqli_num_rows($query_limit) > 0) {
                    while ($row = mysqli_fetch_assoc($query_limit)) {
                        echo "<tr>";
                        echo "<td>" . $no++ . "</td>";
                        echo "<td><strong>TRX-" . str_pad($row['PenjualanID'], 4, '0', STR_PAD_LEFT) . "</strong></td>";
                        echo "<td>" . $row['TanggalPenjualan'] . "</td>";
                        echo "<td>" . ($row['NamaPelanggan'] ? htmlspecialchars($row['NamaPelanggan']) : 'Umum') . "</td>";
                        echo "<td><strong>Rp " . number_format($row['TotalHarga'], 0, ',', '.') . "</strong></td>";
                        echo "</tr>";
                    }
                } else {
                    echo "<tr><td colspan='5' style='text-align: center; color: #444;'>Belum ada data transaksi.</td></tr>";
                }
                ?>
            </tbody>
        </table>
    </div>
</div>

</body>
</html>