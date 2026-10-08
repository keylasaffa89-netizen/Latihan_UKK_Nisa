<?php
session_start();

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
    SELECT detailpenjualan.*, produk.NamaProduk, produk.gambar 
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

    .page-container {
        padding: 40px 20px;
        max-width: 900px;
        margin: 0 auto;
        font-family: 'Poppins', sans-serif;
    }

    /* Kartu Utama dengan Efek Kaca Semi-Transparan & Border Sage Mewah */
    .card-detail {
        background: rgba(255, 255, 255, 0.92);
        backdrop-filter: blur(12px);
        -webkit-backdrop-filter: blur(12px);
        padding: 40px;
        border-radius: 24px;
        box-shadow: 0 20px 40px rgba(30, 45, 31, 0.18);
        color: #1C2E1B;
        border: 2px solid rgba(180, 200, 175, 0.6);
        border-top: 8px solid #2C3E2D;
    }

    .header-section {
        display: flex;
        justify-content: space-between;
        align-items: center;
        border-bottom: 2px solid #D2E0CD;
        padding-bottom: 24px;
        margin-bottom: 28px;
    }

    .store-brand h2 {
        margin: 0;
        color: #2C3E2D;
        font-size: 26px;
        font-weight: 800;
        letter-spacing: -0.5px;
    }

    .store-brand p {
        margin: 6px 0 0 0;
        font-size: 13px;
        color: #556B53;
        font-weight: 500;
    }

    .trx-badge {
        background: linear-gradient(135deg, #2C3E2D 0%, #405A42 100%);
        color: #FFFFFF;
        padding: 10px 22px;
        border-radius: 50px;
        font-weight: 700;
        font-size: 14px;
        letter-spacing: 0.8px;
        box-shadow: 0 6px 15px rgba(44, 62, 45, 0.3);
    }

    .info-grid {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 20px;
        margin-bottom: 30px;
        background: #EBF2E8;
        padding: 24px;
        border-radius: 16px;
        border: 1px solid #C9DCBF;
    }

    .info-item label {
        font-size: 11px;
        color: #4A6347;
        display: block;
        margin-bottom: 6px;
        text-transform: uppercase;
        letter-spacing: 1px;
        font-weight: 700;
    }

    .info-item span {
        font-size: 16px;
        font-weight: 600;
        color: #1C2E1B;
    }

    .table-responsive {
        overflow-x: auto;
        margin-bottom: 25px;
        border-radius: 16px;
        border: 1px solid #C9DCBF;
        box-shadow: 0 4px 12px rgba(0,0,0,0.03);
    }

    .product-table {
        width: 100%;
        border-collapse: collapse;
        background: #FFFFFF;
    }

    .product-table th {
        background-color: #2C3E2D;
        color: white;
        padding: 16px 20px;
        text-align: left;
        font-size: 13px;
        font-weight: 600;
        letter-spacing: 0.5px;
    }

    .product-table td {
        padding: 16px 20px;
        border-bottom: 1px solid #EAF2E6;
        vertical-align: middle;
        font-size: 14px;
        color: #2D372C;
    }

    .product-table tbody tr:last-child td {
        border-bottom: none;
    }

    .product-table tbody tr:hover {
        background-color: #F4F9F2;
    }

    .product-img {
        width: 55px;
        height: 55px;
        object-fit: cover;
        border-radius: 12px;
        border: 2px solid #C9DCBF;
        background-color: #F4F7F3;
        box-shadow: 0 3px 8px rgba(0,0,0,0.08);
    }

    .total-section {
        background: linear-gradient(135deg, #243325 0%, #354D37 100%);
        color: white;
        padding: 20px 28px;
        border-radius: 16px;
        display: flex;
        justify-content: space-between;
        align-items: center;
        font-weight: 600;
        font-size: 17px;
        margin-bottom: 30px;
        box-shadow: 0 8px 20px rgba(44, 62, 45, 0.25);
    }

    .total-section span:last-child {
        font-size: 22px;
        font-weight: 800;
        letter-spacing: 0.5px;
        color: #D4ECD0;
    }

    .action-buttons {
        display: flex;
        gap: 16px;
    }

    .btn-custom {
        flex: 1;
        padding: 15px 24px;
        border-radius: 14px;
        font-weight: 700;
        font-size: 15px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 10px;
        text-decoration: none;
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        border: none;
        cursor: pointer;
    }

    .btn-back {
        background-color: #D8E6D2;
        color: #2C3E2D;
        border: 1px solid #B7CEAF;
    }
    .btn-back:hover { 
        background-color: #C3D9BC; 
        color: #172416;
        transform: translateY(-3px);
        box-shadow: 0 6px 15px rgba(0,0,0,0.08);
    }

    .btn-print-invoice {
        background-color: #2C3E2D;
        color: white;
        box-shadow: 0 6px 18px rgba(44, 62, 45, 0.3);
    }
    .btn-print-invoice:hover { 
        background-color: #1C2E1B; 
        color: white;
        transform: translateY(-3px);
        box-shadow: 0 10px 25px rgba(44, 62, 45, 0.4);
    }
</style>

<div class="page-container">
    <div class="card-detail">
        
        <div class="header-section">
            <div class="store-brand">
                <h2>Detail Transaksi</h2>
                <p>Nis & Co Official Management System</p>
            </div>
            <div class="trx-badge">
                #TRX-<?= str_pad($data_penjualan['PenjualanID'], 4, '0', STR_PAD_LEFT); ?>
            </div>
        </div>

        <div class="info-grid">
            <div class="info-item">
                <label>Nama Pelanggan</label>
                <span><?= htmlspecialchars($data_penjualan['NamaPelanggan']); ?></span>
            </div>
            <div class="info-item">
                <label>Tanggal Transaksi</label>
                <span><?= date('d/m/Y H:i', strtotime($data_penjualan['TanggalPenjualan'])); ?></span>
            </div>
            <div class="info-item">
                <label>Nomor Telepon</label>
                <span><?= htmlspecialchars($data_penjualan['NomorTelepon'] ?? '-'); ?></span>
            </div>
            <div class="info-item">
                <label>Alamat</label>
                <span><?= htmlspecialchars($data_penjualan['Alamat'] ?? '-'); ?></span>
            </div>
        </div>

        <div class="table-responsive">
            <table class="product-table">
                <thead>
                    <tr>
                        <th style="width: 12%; text-align: center;">Foto</th>
                        <th style="width: 38%;">Nama Produk</th>
                        <th style="width: 15%; text-align: center;">Qty</th>
                        <th style="width: 17%; text-align: right;">Harga Satuan</th>
                        <th style="width: 18%; text-align: right;">Subtotal</th>
                    </tr>
                </thead>
                <tbody>
                    <?>
                    <?php 
                    while ($row = mysqli_fetch_assoc($q_detail)) { 
                        $harga_satuan = $row['JumlahProduk'] > 0 ? $row['Subtotal'] / $row['JumlahProduk'] : 0;
                        $foto_path = (!empty($row['gambar'])) ? '../assets/img/' . $row['gambar'] : '';
                    ?>
                    <tr>
                        <td align="center">
                            <?php if (!empty($row['gambar'])) { ?>
                                <img src="<?= $foto_path; ?>" alt="Produk" class="product-img" onerror="this.onerror=null; this.src='../assets/img/default.png';">
                            <?php } else { ?>
                                <div class="product-img" style="display: flex; align-items: center; justify-content: center; color: #888;">
                                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="18" height="18" rx="2" ry="2"></rect><circle cx="8.5" cy="8.5" r="1.5"></circle><polyline points="21 15 16 10 5 21"></polyline></svg>
                                </div>
                            <?php } ?>
                        </td>
                        <td><strong style="color: #1C2E1B; font-size: 15px;"><?= htmlspecialchars($row['NamaProduk']); ?></strong></td>
                        <td align="center"><span style="background: #D8E6D2; color: #2C3E2D; padding: 6px 12px; border-radius: 8px; font-weight: 700; font-size: 13px;"><?= $row['JumlahProduk']; ?></span></td>
                        <td align="right">Rp <?= number_format($harga_satuan, 0, ',', '.'); ?></td>
                        <td align="right"><strong style="color: #2C3E2D; font-size: 15px;">Rp <?= number_format($row['Subtotal'], 0, ',', '.'); ?></strong></td>
                    </tr>
                    <?>
                    <?php } ?>
                </tbody>
            </table>
        </div>

        <div class="total-section">
            <span>Grand Total Pembayaran</span>
            <span>Rp <?= number_format($data_penjualan['TotalHarga'], 0, ',', '.'); ?></span>
        </div>

        <div class="action-buttons">
            <a href="penjualan.php" class="btn-custom btn-back">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="19" y1="12" x2="5" y2="12"></line><polyline points="12 19 5 12 12 5"></polyline></svg>
                Kembali
            </a>
            <a href="invoice.php?id=<?= $data_penjualan['PenjualanID']; ?>" target="_blank" class="btn-custom btn-print-invoice">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="6 9 6 2 18 2 18 9"></polyline><path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"></path><rect x="6" y="14" width="12" height="8"></rect></svg>
                Cetak Struk / Invoice
            </a>
        </div>

    </div>
</div>

</body>
</html>