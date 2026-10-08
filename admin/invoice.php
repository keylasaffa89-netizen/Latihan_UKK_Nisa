<?php
session_start();

// Cek status login dan level Admin (Proteksi keamanan halaman cetak struk)
if (!isset($_SESSION['status']) || $_SESSION['status'] != "login" || $_SESSION['level'] != "admin") {
    header("location:../login.php?pesan=belum_login");
    exit;
}

include '../koneksi.php';
date_default_timezone_set('Asia/Jakarta');

$id = $_GET['id'] ?? '';
if (empty($id)) {
    header("Location: penjualan.php");
    exit;
}

// Ambil data penjualan & pelanggan
$q_penjualan = mysqli_query($koneksi, "
    SELECT penjualan.*, pelanggan.NamaPelanggan, pelanggan.Alamat, pelanggan.NomorTelepon 
    FROM penjualan 
    JOIN pelanggan ON penjualan.PelangganID = pelanggan.PelangganID 
    WHERE penjualan.PenjualanID = '$id'
");
$data_penjualan = mysqli_fetch_assoc($q_penjualan);

if (!$data_penjualan) {
    echo "<script>alert('Data transaksi tidak ditemukan!'); window.location='penjualan.php';</script>";
    exit;
}

// Ambil detail produk
$q_detail = mysqli_query($koneksi, "
    SELECT detailpenjualan.*, produk.NamaProduk 
    FROM detailpenjualan 
    JOIN produk ON detailpenjualan.ProdukID = produk.ProdukID 
    WHERE detailpenjualan.PenjualanID = '$id'
");

include 'header.php';
?>

<style>
    body {
        background-image: url('../assets/img/bgadmin.jfif');
        background-size: cover;        
        background-position: center;   
        background-repeat: no-repeat;  
        background-attachment: fixed;  
    }

    @keyframes fadeInUp {
        from { opacity: 0; transform: translateY(20px); }
        to { opacity: 1; transform: translateY(0); }
    }

    .page-container {
        padding: 30px;
        max-width: 650px;
        margin: 0 auto;
        box-sizing: border-box;
        animation: fadeInUp 0.6s ease-out forwards;
    }

    /* Struk Style ala Mini Market (Alfamart / Indomaret Look) */
    .receipt-card {
        background-color: #FFFFFF;
        padding: 30px 35px;
        border-radius: 12px;
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.15);
        font-family: 'Courier New', Courier, monospace; /* Font struk khas */
        color: #1C2E1B;
        position: relative;
        border-top: 6px solid #2C3E2D;
    }

    .receipt-header {
        text-align: center;
        border-bottom: 2px dashed #bbb;
        padding-bottom: 15px;
        margin-bottom: 15px;
    }

    .store-logo {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        background-color: #2C3E2D;
        color: white;
        width: 48px;
        height: 48px;
        border-radius: 50%;
        margin-bottom: 8px;
    }

    .store-name {
        font-size: 18px;
        font-weight: bold;
        text-transform: uppercase;
        letter-spacing: 1px;
        margin: 0;
        color: #1C2E1B;
        font-family: 'Poppins', sans-serif;
    }

    .store-info, .transaction-info {
        font-size: 12px;
        color: #555;
        margin: 3px 0;
    }

    .transaction-meta {
        font-size: 12px;
        margin-bottom: 15px;
        border-bottom: 1px dashed #bbb;
        padding-bottom: 12px;
    }

    .meta-row {
        display: flex;
        justify-content: space-between;
        margin: 4px 0;
    }

    .item-table {
        width: 100%;
        border-collapse: collapse;
        font-size: 12px;
        margin-bottom: 15px;
    }

    .item-table th {
        border-bottom: 1px solid #333;
        border-top: 1px solid #333;
        padding: 6px 4px;
        font-weight: bold;
    }

    .item-table td {
        padding: 6px 4px;
        vertical-align: top;
    }

    .text-right {
        text-align: right;
    }

    .text-center {
        text-align: center;
    }

    .text-left {
        text-align: left;
    }

    .receipt-total {
        border-top: 1px dashed #bbb;
        padding-top: 12px;
        font-size: 13px;
    }

    .total-row {
        display: flex;
        justify-content: space-between;
        margin: 5px 0;
        font-weight: bold;
    }

    .grand-total {
        font-size: 15px;
        color: #1C2E1B;
        border-top: 1px solid #333;
        border-bottom: 1px solid #333;
        padding: 6px 0;
        margin-top: 6px;
    }

    .receipt-footer {
        text-align: center;
        margin-top: 25px;
        font-size: 11px;
        color: #666;
        border-top: 2px dashed #bbb;
        padding-top: 15px;
    }

    .action-buttons {
        display: flex;
        gap: 12px;
        margin-top: 20px;
    }

    .btn-action {
        flex: 1;
        padding: 12px;
        border-radius: 10px;
        font-family: 'Poppins', sans-serif;
        font-weight: 500;
        font-size: 14px;
        cursor: pointer;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        text-decoration: none;
        transition: all 0.3s ease;
        border: none;
    }

    .btn-back {
        background-color: #E2E8DF;
        color: #2C3E2D;
    }

    .btn-back:hover {
        background-color: #d1dad0;
    }

    .btn-print {
        background-color: #2C3E2D;
        color: white;
        box-shadow: 0 4px 12px rgba(44, 62, 45, 0.3);
    }

    .btn-print:hover {
        background-color: #1F2E20;
    }

    /* Pengaturan Khusus Saat Dicetak (Print Mode) */
    @media print {
        body {
            background: none !important;
        }
        .page-container {
            padding: 0;
            max-width: 100%;
        }
        .action-buttons, header, .navbar, footer {
            display: none !important;
        }
        .receipt-card {
            box-shadow: none;
            border-radius: 0;
            padding: 10px;
            width: 100%;
            border-top: none;
        }
    }
</style>

<div class="page-container">
    
    <!-- Tampilan Struk Kasir -->
    <div class="receipt-card" id="printArea">
        <div class="receipt-header">
            <!-- Icon Garpu dan Pisau (Sama seperti Header) -->
            <div class="store-logo">
                <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M18 2v6a3 3 0 0 1-3 3 3 3 0 0 1-3-3V2"></path>
                    <line x1="15" y1="2" x2="15" y2="22"></line>
                    <path d="M5 2v10a3 3 0 0 0 3 3h1"></path>
                    <line x1="9" y1="15" x2="9" y2="22"></line>
                </svg>
            </div>
            <h3 class="store-name">Nis & Co OFFICIAL</h3>
            <p class="store-info">Jl. Hastinapura, No. 03, Kec. Boja</p>
            <p class="store-info">Telp: 08823649580</p>
        </div>

        <div class="transaction-meta">
            <div class="meta-row">
                <span>No. Transaksi</span>
                <span>#TRX-<?= str_pad($data_penjualan['PenjualanID'], 4, '0', STR_PAD_LEFT); ?></span>
            </div>
            <div class="meta-row">
                <span>Tanggal</span>
                <span><?= date('d/m/Y H:i', strtotime($data_penjualan['TanggalPenjualan'])); ?></span>
            </div>
            <div class="meta-row">
                <span>Kasir</span>
                <span>Admin Nis & Co</span>
            </div>
            <div class="meta-row">
                <span>Pelanggan</span>
                <span><?= htmlspecialchars($data_penjualan['NamaPelanggan']); ?></span>
            </div>
        </div>

        <table class="item-table">
            <thead>
                <tr>
                    <th class="text-left" style="width: 45%;">Item</th>
                    <th class="text-center" style="width: 15%;">Qty</th>
                    <th class="text-right" style="width: 40%;">Subtotal</th>
                </tr>
            </thead>
            <tbody>
                <?php 
                $total_qty = 0;
                while ($row = mysqli_fetch_assoc($q_detail)) { 
                    $total_qty += $row['JumlahProduk'];
                ?>
                <tr>
                    <td class="text-left">
                        <?= htmlspecialchars($row['NamaProduk']); ?><br>
                        <span style="font-size: 10px; color: #777;">@Rp <?= number_format($row['Subtotal'] / $row['JumlahProduk'], 0, ',', '.'); ?></span>
                    </td>
                    <td class="text-center"><?= $row['JumlahProduk']; ?></td>
                    <td class="text-right">Rp <?= number_format($row['Subtotal'], 0, ',', '.'); ?></td>
                </tr>
                <?php } ?>
            </tbody>
        </table>

        <div class="receipt-total">
            <div class="meta-row">
                <span>Total Item</span>
                <span><?= $total_qty; ?> Pcs</span>
            </div>
            <div class="meta-row grand-total">
                <span>GRAND TOTAL</span>
                <span>Rp <?= number_format($data_penjualan['TotalHarga'], 0, ',', '.'); ?></span>
            </div>
        </div>

        <div class="receipt-footer">
            <p style="margin: 0 0 4px 0; font-weight: bold;">TERIMA KASIH TELAH BERBELANJA</p>
            <p style="margin: 0;">Barang yang sudah dibeli tidak dapat ditukar/dikembalikan.</p>
            <p style="margin: 6px 0 0 0; font-size: 10px; color: #888;">--- Kasir System Verified ---</p>
        </div>
    </div>

    <!-- Tombol Aksi (Tidak Ikut Tercetak) -->
    <div class="action-buttons">
        <a href="penjualan.php" class="btn-action btn-back">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="19" y1="12" x2="5" y2="12"></line><polyline points="12 19 5 12 12 5"></polyline></svg>
            Kembali
        </a>
        <button onclick="window.print()" class="btn-action btn-print">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="6 9 6 2 18 2 18 9"></polyline><path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"></path><rect x="6" y="14" width="12" height="8"></rect></svg>
            Cetak Struk
        </button>
    </div>

</div>

</body>
</html>