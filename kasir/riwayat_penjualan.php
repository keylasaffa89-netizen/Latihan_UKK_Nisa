<?php
session_start();

// Cek apakah user sudah login
if (!isset($_SESSION['status']) || $_SESSION['status'] != "login") {
    header("location:../index.php?pesan=belum_login");
    exit;
}

include '../koneksi.php';
include 'header.php';
?>

<style>
/* Reset Background */
body {
    background: url('../assets/img/bgkasir.jfif') no-repeat center center fixed !important;
    background-size: cover !important;
    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif !important;
    overflow-x: hidden;
}

.riwayat-container {
    padding: 30px 10px;
    max-width: 1150px;
    margin: 0 auto;
}

/* --- ANIMASI HALUS & BERJENJANG (STAGGERED) --- */
@keyframes slideDownHeader {
    0% { opacity: 0; transform: translateY(-30px); }
    100% { opacity: 1; transform: translateY(0); }
}

@keyframes slideUpTable {
    0% { opacity: 0; transform: translateY(35px); }
    100% { opacity: 1; transform: translateY(0); }
}

.header-animated {
    animation: slideDownHeader 1s cubic-bezier(0.2, 0.8, 0.2, 1) forwards;
}

.table-animated {
    opacity: 0;
    animation: slideUpTable 1s cubic-bezier(0.2, 0.8, 0.2, 1) forwards;
    animation-delay: 0.25s;
}

/* Header Section */
.page-title {
    font-size: 36px !important;
    font-weight: 900 !important;
    color: #1A2E1A !important;
    margin: 0 0 6px 0 !important;
    text-shadow: 0 2px 8px rgba(255, 255, 255, 0.8);
    display: flex;
    align-items: center;
    gap: 12px;
}

.page-subtitle {
    font-size: 15px !important;
    color: #2E472E !important;
    font-weight: 700 !important;
    margin: 0 0 25px 0 !important;
    text-shadow: 0 1px 4px rgba(255, 255, 255, 0.8);
}

.glass-panel {
    background: rgba(255, 255, 255, 0.65) !important;
    backdrop-filter: blur(12px);
    -webkit-backdrop-filter: blur(12px);
    border: 1px solid rgba(255, 255, 255, 0.8) !important;
    border-radius: 20px;
    padding: 24px;
    box-shadow: 0 10px 30px rgba(0,0,0,0.05);
    transition: box-shadow 0.4s ease;
}

.glass-panel:hover {
    box-shadow: 0 16px 40px rgba(0,0,0,0.08);
}

.table-riwayat {
    width: 100%;
    border-collapse: collapse;
    margin-top: 10px;
}

.table-riwayat th {
    text-align: left;
    padding: 14px 16px;
    font-size: 12px;
    font-weight: 800;
    color: #1A2E1A;
    text-transform: uppercase;
    border-bottom: 2px solid rgba(46, 71, 46, 0.25);
}

.table-riwayat td {
    padding: 14px 16px;
    font-size: 14px;
    font-weight: 600;
    color: #111E11;
    border-bottom: 1px solid rgba(0, 0, 0, 0.05);
    transition: all 0.25s ease;
}

.table-riwayat tr {
    transition: all 0.25s ease;
}

.table-riwayat tr:hover td {
    background: rgba(255, 255, 255, 0.75);
    padding-left: 20px; /* Efek geser kanan halus */
}

.btn-action {
    background: #2E472E;
    color: white;
    padding: 8px 14px;
    border-radius: 10px;
    font-size: 12px;
    font-weight: 700;
    text-decoration: none;
    display: inline-block;
    box-shadow: 0 4px 12px rgba(46, 71, 46, 0.3);
    transition: all 0.3s cubic-bezier(0.175, 0.885, 0.32, 1.275);
}

.btn-action:hover {
    background: #1A2E1A;
    color: white;
    transform: translateY(-2px) scale(1.05);
    box-shadow: 0 6px 16px rgba(46, 71, 46, 0.4);
}
</style>

<div class="riwayat-container">
    <!-- Header dengan Animasi Turun & Icon -->
    <div class="header-animated">
        <h1 class="page-title">
            <svg width="34" height="34" viewBox="0 0 24 24" fill="none" stroke="#1A2E1A" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
                <polyline points="14 2 14 8 20 8"></polyline>
                <line x1="16" y1="13" x2="8" y2="13"></line>
                <line x1="16" y1="17" x2="8" y2="17"></line>
                <polyline points="10 9 9 9 8 9"></polyline>
            </svg>
            Riwayat Transaksi
        </h1>
        <p class="page-subtitle">Daftar seluruh transaksi penjualan yang telah berhasil diproses</p>
    </div>

    <!-- Tabel Riwayat dengan Animasi Naik -->
    <div class="glass-panel table-animated">
        <table class="table-riwayat">
            <thead>
                <tr>
                    <th>ID Transaksi</th>
                    <th>Tanggal</th>
                    <th>Nama Pelanggan</th>
                    <th>Total Harga</th>
                    <th style="text-align: center;">Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php
                // Query mengambil data penjualan digabung dengan data pelanggan
                $query = "SELECT penjualan.*, pelanggan.NamaPelanggan 
                          FROM penjualan 
                          LEFT JOIN pelanggan ON penjualan.PelangganID = pelanggan.PelangganID 
                          ORDER BY penjualan.PenjualanID DESC";
                $result = mysqli_query($koneksi, $query);

                if ($result && mysqli_num_rows($result) > 0) {
                    while ($row = mysqli_fetch_assoc($result)) {
                        ?>
                        <tr>
                            <td><strong>#<?= $row['PenjualanID']; ?></strong></td>
                            <td><?= date('d-m-Y', strtotime($row['TanggalPenjualan'])); ?></td>
                            <td><?= htmlspecialchars($row['NamaPelanggan'] ?? 'Pelanggan Umum'); ?></td>
                            <td><strong>Rp <?= number_format($row['TotalHarga'], 0, ',', '.'); ?></strong></td>
                            <td style="text-align: center;">
                                <!-- Tombol untuk melihat detail atau cetak struk -->
                                <a href="detail_penjualan.php?id=<?= $row['PenjualanID']; ?>" class="btn-action">Detail / Struk</a>
                            </td>
                        </tr>
                        <?php
                    }
                } else {
                    echo "<tr><td colspan='5' style='text-align: center; color: #556B2F; padding: 25px; font-weight: 700;'>Belum ada riwayat transaksi.</td></tr>";
                }
                ?>
            </tbody>
        </table>
    </div>
</div>

</body>
</html>