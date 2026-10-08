<?php
session_start();

// Perbaikan: Ubah $_SESSION['level'] menjadi $_SESSION['role'] agar konsisten
if (!isset($_SESSION['status']) || $_SESSION['status'] != "login" || $_SESSION['role'] != "admin") {
    header("location:../login.php?pesan=belum_login");
    exit;
}

include '../koneksi.php';
include 'header.php';
?>

<!-- Import Google Fonts: Poppins & Inter -->
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700;800&family=Poppins:wght@600;700;800&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

<style>
/* 1. Menampilkan Gambar Background Admin */
body {
    background: url('../assets/img/bgadmin.jfif') no-repeat center center fixed !important;
    background-size: cover !important;
    font-family: 'Inter', sans-serif;
}

@keyframes slideDown {
    from { opacity: 0; transform: translateY(-20px); }
    to { opacity: 1; transform: translateY(0); }
}

@keyframes fadeInUp {
    from { opacity: 0; transform: translateY(20px); }
    to { opacity: 1; transform: translateY(0); }
}

.animate-header {
    animation: slideDown 0.7s ease forwards;
}

.animate-table-box {
    animation: fadeInUp 0.9s ease forwards;
}

.btn-tambah {
    transition: all 0.3s cubic-bezier(0.165, 0.84, 0.44, 1);
}

.btn-tambah:hover {
    transform: translateY(-3px) scale(1.03);
    box-shadow: 0 8px 20px rgba(28, 46, 27, 0.4) !important;
}

/* Container Tabel Transparan Glassmorphism */
.table-container-glass {
    background: rgba(255, 255, 255, 0.85) !important;
    backdrop-filter: blur(12px);
    -webkit-backdrop-filter: blur(12px);
    border: 1px solid rgba(255, 255, 255, 0.6);
    padding: 18px;
    border-radius: 18px;
    box-shadow: 0 8px 25px rgba(0, 0, 0, 0.06);
}

/* Styling Tabel Penjualan */
table {
    width: 100%;
    border-collapse: collapse;
    table-layout: fixed; /* Mengunci lebar kolom agar lurus dan konsisten */
}

table th {
    background-color: #1c2e1b;
    color: #ffffff;
    padding: 14px 16px;
    font-family: 'Poppins', sans-serif;
    font-size: 13.5px;
    font-weight: 700;
    text-align: left;
}

table th:first-child { border-top-left-radius: 10px; }
table th:last-child { border-top-right-radius: 10px; text-align: center; }

table td {
    padding: 14px 16px;
    border-bottom: 1px solid rgba(0, 0, 0, 0.05);
    font-size: 13.5px;
    color: #1c2e1b;
    word-wrap: break-word;
}

table tr {
    transition: all 0.25s ease;
}

table tr:hover {
    background-color: rgba(244, 247, 242, 0.8);
}

/* Animasi Tombol Aksi */
.btn-aksi {
    transition: all 0.2s ease;
}

.btn-aksi:hover {
    transform: translateY(-2px);
    filter: brightness(1.1);
}
</style>

<div style="max-width: 1200px; margin: 30px auto; padding: 0 20px;">
    <!-- Header Halaman -->
    <div class="animate-header" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 25px;">
        <div>
            <h2 style="margin: 0; font-family: 'Poppins', sans-serif; font-size: 28px; font-weight: 800; color: #112211; text-shadow: 0 2px 10px rgba(255, 255, 255, 0.6); display: flex; align-items: center; gap: 12px;">
                <i class="fa-solid fa-cart-shopping" style="font-size: 24px; color: #1c2e1b;"></i>
                Data Penjualan
            </h2>
            <p style="margin: 6px 0 0 0; font-size: 14.5px; color: #2d422a; font-weight: 600;">
                Riwayat transaksi dan data penjualan kasir yang tercatat.
            </p>
        </div>
        
        <a href="tambah_penjualan.php" class="btn-tambah" style="background-color: #1c2e1b; color: white; padding: 10px 22px; border-radius: 30px; text-decoration: none; font-weight: 700; font-size: 14px; box-shadow: 0 4px 15px rgba(28, 46, 27, 0.25); display: inline-flex; align-items: center; gap: 8px;">
            + Transaksi Baru
        </a>
    </div>

    <!-- Tabel Data Penjualan Transparan Glassmorphism -->
    <div class="animate-table-box table-container-glass">
        <table>
            <colgroup>
                <col style="width: 14%;">
                <col style="width: 19%;">
                <col style="width: 19%;">
                <col style="width: 20%;">
                <col style="width: 28%;">
            </colgroup>
            <thead>
                <tr>
                    <th>ID Penjualan</th>
                    <th>Tanggal Penjualan</th>
                    <th>Total Harga</th>
                    <th>Nama Pelanggan</th>
                    <th style="text-align: center;">Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $query = mysqli_query($koneksi, "SELECT penjualan.*, pelanggan.NamaPelanggan FROM penjualan LEFT JOIN pelanggan ON penjualan.PelangganID = pelanggan.PelangganID ORDER BY PenjualanID DESC");
                
                if ($query && mysqli_num_rows($query) > 0) {
                    while ($row = mysqli_fetch_assoc($query)) {
                        echo "<tr>";
                        echo "<td><strong>#" . $row['PenjualanID'] . "</strong></td>";
                        
                        // Tanggal Penjualan
                        echo "<td><div style='display: flex; align-items: center; gap: 8px;'>
                                <svg width='15' height='15' viewBox='0 0 24 24' fill='none' stroke='#1c2e1b' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'><rect x='3' y='4' width='18' height='18' rx='2' ry='2'></rect><line x1='16' y1='2' x2='16' y2='6'></line><line x1='8' y1='2' x2='8' y2='6'></line><line x1='3' y1='10' x2='21' y2='10'></line></svg>
                                " . $row['TanggalPenjualan'] . "
                              </div></td>";
                        
                        // Total Harga
                        echo "<td><div style='display: flex; align-items: center; gap: 8px;'>
                                <svg width='15' height='15' viewBox='0 0 24 24' fill='none' stroke='#1c2e1b' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'><rect x='2' y='5' width='20' height='14' rx='2' ry='2'></rect><line x1='2' y1='10' x2='22' y2='10'></line></svg>
                                <strong>Rp " . number_format($row['TotalHarga'], 0, ',', '.') . "</strong>
                              </div></td>";
                        
                        // Nama Pelanggan
                        echo "<td><div style='display: flex; align-items: center; gap: 8px;'>
                                <svg width='15' height='15' viewBox='0 0 24 24' fill='none' stroke='#1c2e1b' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'><path d='M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2'></path><circle cx='12' cy='7' r='4'></circle></svg>
                                " . ($row['NamaPelanggan'] ? htmlspecialchars($row['NamaPelanggan']) : 'Umum / Tidak Dikenal') . "
                              </div></td>";
                        
                        // Tombol Aksi: Detail, Edit, & Hapus dengan nuansa hijau dan senada
                        echo "<td style='text-align: center;'>
                                <div style='display: inline-flex; gap: 4px; justify-content: center;'>
                                    <!-- Tombol Detail -->
                                    <a href='detail_penjualan.php?id=" . $row['PenjualanID'] . "' class='btn-aksi' style='background-color: #1c2e1b; color: white; padding: 6px 8px; border-radius: 8px; text-decoration: none; font-size: 11px; font-weight: 700; display: inline-flex; align-items: center; gap: 3px; box-shadow: 0 2px 5px rgba(0,0,0,0.1);'>
                                        <i class='fa-solid fa-magnifying-glass'></i> Detail
                                    </a>
                                    <!-- Tombol Edit (Nuansa Hijau Lembut) -->
                                    <a href='edit_penjualan.php?id=" . $row['PenjualanID'] . "' class='btn-aksi' style='background-color: #E8F0E6; color: #2D462D; border: 1px solid #C8D6C3; padding: 6px 8px; border-radius: 8px; text-decoration: none; font-size: 11px; font-weight: 700; display: inline-flex; align-items: center; gap: 3px;'>
                                        <i class='fa-solid fa-pen-to-square'></i> Edit
                                    </a>
                                    <!-- Tombol Hapus -->
                                    <a href='hapus_penjualan.php?id=" . $row['PenjualanID'] . "' onclick='return confirm(\"Yakin ingin menghapus data penjualan ini?\")' class='btn-aksi' style='background-color: #FFF5F5; color: #E74C3C; border: 1px solid #FADBD8; padding: 6px 8px; border-radius: 8px; text-decoration: none; font-size: 11px; font-weight: 700; display: inline-flex; align-items: center; gap: 3px;'>
                                        <i class='fa-solid fa-trash-can'></i> Hapus
                                    </a>
                                </div>
                              </td>";
                        echo "</tr>";
                    }
                } else {
                    echo "<tr><td colspan='5' style='text-align: center; color: #2d422a; padding: 25px; font-weight: 700;'>Belum ada data transaksi penjualan.</td></tr>";
                }
                ?>
            </tbody>
        </table>
    </div>
</div>

</body>
</html>