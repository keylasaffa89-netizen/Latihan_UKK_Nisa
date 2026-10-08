<?php
session_start();

// Pengecekan session admin
if (!isset($_SESSION['status']) || $_SESSION['status'] != "login" || $_SESSION['role'] != "admin") {
    header("location:../login.php?pesan=belum_login");
    exit;
}

include '../koneksi.php';
include 'header.php';

// Ambil PenjualanID dari URL
if (!isset($_GET['id']) || empty($_GET['id'])) {
    echo "<script>alert('ID Penjualan tidak valid!'); window.location='penjualan.php';</script>";
    exit;
}

$penjualan_id = mysqli_real_escape_string($koneksi, $_GET['id']);

// Ambil data penjualan & pelanggan (Pelanggan dikunci sesuai transaksi)
$q_transaksi = mysqli_query($koneksi, "SELECT penjualan.*, pelanggan.* FROM penjualan JOIN pelanggan ON penjualan.PelangganID = pelanggan.PelangganID WHERE penjualan.PenjualanID = '$penjualan_id'");
$transaksi = mysqli_fetch_assoc($q_transaksi);

if (!$transaksi) {
    echo "<script>alert('Data transaksi tidak ditemukan!'); window.location='penjualan.php';</script>";
    exit;
}

// Simpan total harga asli/lama yang sudah dibayar sebelumnya oleh pelanggan
$total_harga_lama = $transaksi['TotalHarga'];

// Inisialisasi Session Cart Edit jika belum ada atau jika baru pertama buka halaman ini
if (!isset($_SESSION['edit_cart_' . $penjualan_id])) {
    $_SESSION['edit_cart_' . $penjualan_id] = [];
    
    // Ambil detail penjualan langsung ambil kolom 'gambar' dari database produk
    $q_detail_db = mysqli_query($koneksi, "SELECT detailpenjualan.*, produk.NamaProduk, produk.Harga, produk.Stok, produk.gambar FROM detailpenjualan JOIN produk ON detailpenjualan.ProdukID = produk.ProdukID WHERE detailpenjualan.PenjualanID = '$penjualan_id'");
    while ($row = mysqli_fetch_assoc($q_detail_db)) {
        $img_file = !empty($row['gambar']) ? $row['gambar'] : 'default.png';

        $_SESSION['edit_cart_' . $penjualan_id][$row['ProdukID']] = [
            'nama' => $row['NamaProduk'],
            'harga' => $row['Harga'],
            'jumlah' => $row['JumlahProduk'],
            'stok' => $row['Stok'],
            'gambar' => $img_file
        ];
    }
}

// Handler Tambah Produk ke Keranjang Edit
if (isset($_POST['add_to_edit_cart'])) {
    $produk_id = mysqli_real_escape_string($koneksi, $_POST['ProdukID']);
    $jumlah = (int)$_POST['Jumlah'];

    $q_p = mysqli_query($koneksi, "SELECT * FROM produk WHERE ProdukID = '$produk_id'");
    $p = mysqli_fetch_assoc($q_p);

    if ($p) {
        if ($jumlah > $p['Stok']) {
            echo "<script>alert('Stok tidak mencukupi! Stok tersisa: {$p['Stok']}');</script>";
        } else {
            $nama_file_gambar = !empty($p['gambar']) ? $p['gambar'] : 'default.png';

            if (isset($_SESSION['edit_cart_' . $penjualan_id][$produk_id])) {
                $total_baru_item = $_SESSION['edit_cart_' . $penjualan_id][$produk_id]['jumlah'] + $jumlah;
                if ($total_baru_item > $p['Stok']) {
                    echo "<script>alert('Jumlah melebihi stok yang tersedia!');</script>";
                } else {
                    $_SESSION['edit_cart_' . $penjualan_id][$produk_id]['jumlah'] = $total_baru_item;
                }
            } else {
                $_SESSION['edit_cart_' . $penjualan_id][$produk_id] = [
                    'nama' => $p['NamaProduk'],
                    'harga' => $p['Harga'],
                    'jumlah' => $jumlah,
                    'stok' => $p['Stok'],
                    'gambar' => $nama_file_gambar
                ];
            }
        }
    }
}

// Handler Hapus Item dari Keranjang Edit
if (isset($_GET['hapus_cart'])) {
    $id_hapus = mysqli_real_escape_string($koneksi, $_GET['hapus_cart']);
    unset($_SESSION['edit_cart_' . $penjualan_id][$id_hapus]);
    echo "<script>window.location='edit_penjualan.php?id=$penjualan_id';</script>";
}

// Handler Simpan Perubahan Total (Update Database Transaksi & Detail)
if (isset($_POST['update_transaksi'])) {
    $cart_items = $_SESSION['edit_cart_' . $penjualan_id];
    $nominal_tambahan_bayar = (float)$_POST['nominal_tambahan_bayar'];

    // Hitung ulang total harga baru
    $total_harga_baru = 0;
    foreach ($cart_items as $item) {
        $total_harga_baru += $item['harga'] * $item['jumlah'];
    }

    // Hitung kekurangan pembayaran dari produk yang baru ditambahkan
    $kekurangan_bayar = $total_harga_baru - $total_harga_lama;
    if ($kekurangan_bayar < 0) {
        $kekurangan_bayar = 0; // Jika total belanja malah berkurang (karena item dihapus)
    }

    if (empty($cart_items)) {
        echo "<script>alert('Keranjang tidak boleh kosong!');</script>";
    } elseif ($nominal_tambahan_bayar < $kekurangan_bayar) {
        echo "<script>alert('Nominal tambahan bayar kurang dari harga produk yang belum dibayar!');</script>";
    } else {
        mysqli_begin_transaction($koneksi);

        try {
            // 1. Kembalikan stok lama terlebih dahulu berdasarkan data asli di DB sebelum di-update
            $q_old_detail = mysqli_query($koneksi, "SELECT * FROM detailpenjualan WHERE PenjualanID = '$penjualan_id'");
            while ($old = mysqli_fetch_assoc($q_old_detail)) {
                mysqli_query($koneksi, "UPDATE produk SET Stok = Stok + {$old['JumlahProduk']} WHERE ProdukID = '{$old['ProdukID']}'");
            }

            // 2. Hapus detail penjualan lama
            mysqli_query($koneksi, "DELETE FROM detailpenjualan WHERE PenjualanID = '$penjualan_id'");

            // 3. Masukkan kembali detail yang baru dari session cart sekaligus kurangi stok produk
            foreach ($cart_items as $pid => $item) {
                $subtotal = $item['harga'] * $item['jumlah'];
                $qty = (int)$item['jumlah'];
                $pid_esc = mysqli_real_escape_string($koneksi, $pid);

                // Insert detail baru
                mysqli_query($koneksi, "INSERT INTO detailpenjualan (PenjualanID, ProdukID, JumlahProduk, Subtotal) VALUES ('$penjualan_id', '$pid_esc', '$qty', '$subtotal')");

                // Kurangi stok produk dengan qty baru
                mysqli_query($koneksi, "UPDATE produk SET Stok = Stok - $qty WHERE ProdukID = '$pid_esc'");
            }

            // 4. Update tabel penjualan (TotalHarga)
            mysqli_query($koneksi, "UPDATE penjualan SET TotalHarga = '$total_harga_baru' WHERE PenjualanID = '$penjualan_id'");

            mysqli_commit($koneksi);
            unset($_SESSION['edit_cart_' . $penjualan_id]);
            echo "<script>alert('Transaksi Berhasil Diperbarui!'); window.location='detail_penjualan.php?id=$penjualan_id';</script>";

        } catch (Exception $e) {
            mysqli_rollback($koneksi);
            echo "<script>alert('Gagal Memperbarui Transaksi: " . $e->getMessage() . "');</script>";
        }
    }
}
?>

<style>
body {
    background: url('../assets/img/bgkasir.jfif') no-repeat center center fixed !important;
    background-size: cover !important;
    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif !important;
    overflow-x: hidden;
}

.kasir-container {
    padding: 30px 15px;
    max-width: 1300px;
    margin: 0 auto;
}

@keyframes slideDownHeader {
    0% { opacity: 0; transform: translateY(-25px); }
    100% { opacity: 1; transform: translateY(0); }
}

@keyframes slideUpGrid {
    0% { opacity: 0; transform: translateY(25px); }
    100% { opacity: 1; transform: translateY(0); }
}

.header-animated {
    animation: slideDownHeader 0.8s cubic-bezier(0.2, 0.8, 0.2, 1) forwards;
}

.grid-animated {
    opacity: 0;
    animation: slideUpGrid 0.8s cubic-bezier(0.2, 0.8, 0.2, 1) forwards;
    animation-delay: 0.2s;
}

.page-title {
    font-size: 28px !important;
    font-weight: 900 !important;
    color: #1A2E1A !important;
    margin: 0 0 6px 0 !important;
    text-shadow: 0 2px 8px rgba(255, 255, 255, 0.9);
    display: flex;
    align-items: center;
    gap: 12px;
}

.page-subtitle {
    font-size: 14px !important;
    color: #2E472E !important;
    font-weight: 700 !important;
    margin: 0 0 25px 0 !important;
    text-shadow: 0 1px 4px rgba(255, 255, 255, 0.9);
}

.pos-grid {
    display: grid;
    grid-template-columns: 1fr 1.35fr;
    gap: 24px;
}

@media(max-width: 992px) {
    .pos-grid {
        grid-template-columns: 1fr;
    }
}

.glass-panel {
    background: #FFFFFF !important;
    border: 2px solid rgba(46, 71, 46, 0.15) !important;
    border-radius: 20px;
    padding: 24px;
    box-shadow: 0 10px 30px rgba(0,0,0,0.08);
}

.panel-title {
    font-size: 17px;
    font-weight: 800;
    color: #1A2E1A;
    margin: 0 0 16px 0;
    display: flex;
    align-items: center;
    gap: 8px;
    border-bottom: 2px solid rgba(46, 71, 46, 0.15);
    padding-bottom: 10px;
}

.form-group {
    margin-bottom: 15px;
}

.form-group label {
    display: block;
    font-size: 12.5px;
    font-weight: 700;
    color: #1A2E1A;
    margin-bottom: 6px;
}

.form-control {
    width: 100%;
    padding: 10px 14px;
    border-radius: 12px;
    border: 1.5px solid rgba(46, 71, 46, 0.25);
    background: #FAFAFA;
    outline: none;
    font-size: 13.5px;
    font-weight: 600;
    color: #1A2E1A;
    box-sizing: border-box;
    transition: all 0.2s ease;
}

.form-control:focus {
    border-color: #2E472E;
    background: #FFFFFF;
    box-shadow: 0 3px 12px rgba(46, 71, 46, 0.12);
}

.customer-card-box {
    background: linear-gradient(135deg, #F4F6F4 0%, #E8F0E8 100%);
    border: 1.5px solid rgba(46, 71, 46, 0.25);
    border-radius: 14px;
    padding: 16px;
    margin-bottom: 22px;
    box-shadow: inset 0 1px 3px rgba(255,255,255,0.6);
}

.customer-card-header {
    display: flex;
    align-items: center;
    gap: 8px;
    font-size: 12px;
    font-weight: 800;
    color: #556B2F;
    text-transform: uppercase;
    margin-bottom: 8px;
    letter-spacing: 0.5px;
}

.customer-name {
    font-size: 16px;
    font-weight: 900;
    color: #1A2E1A;
    margin: 0 0 4px 0;
}

.customer-phone {
    font-size: 13px;
    font-weight: 700;
    color: #384E38;
    margin: 0;
    display: flex;
    align-items: center;
    gap: 6px;
}

.product-list-box {
    max-height: 210px;
    overflow-y: auto;
    border: 1.5px solid rgba(46, 71, 46, 0.25);
    border-radius: 12px;
    background: #FAFAFA;
    margin-top: 6px;
    padding: 6px;
}

.product-item-card {
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 8px 10px;
    border-radius: 8px;
    cursor: pointer;
    transition: all 0.15s ease;
    border-bottom: 1px solid rgba(0,0,0,0.03);
}

.product-item-card:hover {
    background: #E8F0E8;
}

.product-item-card.selected {
    background: #2E472E;
    color: white;
}

.product-item-card.selected .prod-price,
.product-item-card.selected .prod-stock {
    color: #D1E7D1;
}

.prod-thumb {
    width: 40px;
    height: 40px;
    object-fit: contain;
    background: #FFFFFF;
    border-radius: 6px;
    border: 1px solid rgba(46, 71, 46, 0.2);
    padding: 2px;
}

.prod-info {
    flex-grow: 1;
}

.prod-name {
    font-size: 13px;
    font-weight: 700;
    color: inherit;
    margin: 0;
}

.prod-meta {
    font-size: 11px;
    font-weight: 600;
    color: #556B2F;
    display: flex;
    gap: 10px;
}

.btn-primary-custom {
    background: #2E472E;
    color: white;
    border: none;
    padding: 11px 18px;
    border-radius: 12px;
    font-weight: 700;
    font-size: 13.5px;
    cursor: pointer;
    width: 100%;
    transition: all 0.25s ease;
    display: flex;
    justify-content: center;
    align-items: center;
    gap: 8px;
    box-shadow: 0 4px 12px rgba(46, 71, 46, 0.25);
    text-decoration: none;
}

.btn-primary-custom:hover {
    background: #1A2E1A;
    transform: translateY(-1px);
    box-shadow: 0 5px 15px rgba(46, 71, 46, 0.35);
}

.btn-danger-sm {
    background: #FFEBEE;
    color: #C62828;
    border: 1px solid #EF9A9A;
    padding: 4px 8px;
    border-radius: 6px;
    font-size: 11.5px;
    font-weight: 700;
    text-decoration: none;
    transition: all 0.2s ease;
}

.btn-danger-sm:hover {
    background: #C62828;
    color: white;
}

.table-pos {
    width: 100%;
    border-collapse: collapse;
    margin-bottom: 16px;
}

.table-pos th {
    text-align: left;
    padding: 8px 6px;
    font-size: 11.5px;
    font-weight: 800;
    color: #1A2E1A;
    text-transform: uppercase;
    border-bottom: 2px solid rgba(46, 71, 46, 0.2);
}

.table-pos td {
    padding: 8px 6px;
    font-size: 12.5px;
    font-weight: 600;
    color: #111E11;
    border-bottom: 1px solid rgba(0, 0, 0, 0.05);
    vertical-align: middle;
}

.cart-img-thumb {
    width: 42px;
    height: 42px;
    object-fit: contain;
    background: #F4F6F4;
    border-radius: 8px;
    border: 1px solid rgba(46, 71, 46, 0.2);
    padding: 2px;
}

.total-box {
    background: #F4F6F4;
    border: 1.5px dashed rgba(46, 71, 46, 0.4);
    border-radius: 12px;
    padding: 12px 14px;
    margin-bottom: 12px;
}

.total-display {
    font-size: 20px;
    font-weight: 900;
    color: #1A2E1A;
    margin-top: 2px;
}

.change-display {
    font-size: 18px;
    font-weight: 800;
    color: #2E472E;
    margin-top: 2px;
}
</style>

<div class="kasir-container">
    <div class="header-animated">
        <h1 class="page-title">
            <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="#1A2E1A" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path>
                <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path>
            </svg>
            Edit Transaksi & Tambah Produk (#<?= $penjualan_id; ?>)
        </h1>
        <p class="page-subtitle">Pelanggan menambah produk baru, kasir cukup memasukkan kekurangan pembayaran produk baru.</p>
    </div>

    <form method="POST" action="">
        <div class="pos-grid grid-animated">
            <!-- Panel Kiri: Card Kotak Pelanggan & Form Tambah Produk -->
            <div class="glass-panel">
                <h3 class="panel-title">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path><circle cx="12" cy="7" r="4"></circle></svg>
                    Informasi Pelanggan
                </h3>

                <div class="customer-card-box">
                    <div class="customer-card-header">
                        <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect><path d="M7 11V7a5 5 0 0 1 10 0v4"></path></svg>
                        Pelanggan Terkunci (Transaksi Asli)
                    </div>
                    <h4 class="customer-name"><?= htmlspecialchars($transaksi['NamaPelanggan']); ?></h4>
                    <p class="customer-phone">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"></path></svg>
                        <?= htmlspecialchars($transaksi['NomorTelepon']); ?>
                    </p>
                </div>

                <h3 class="panel-title" style="margin-top: 25px;">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="12" y1="5" x2="12" y2="19"></line><line x1="5" y1="12" x2="19" y2="12"></line></svg>
                    Tambah Produk ke Transaksi
                </h3>

                <input type="hidden" name="ProdukID" id="selectedProdukID" required>

                <div class="form-group">
                    <label>Cari & Pilih Produk</label>
                    <input type="text" id="searchProduk" class="form-control" placeholder="Ketik nama produk..." onkeyup="filterProductCards()">
                    
                    <div class="product-list-box" id="productListContainer">
                        <?php
                        $q_produk = mysqli_query($koneksi, "SELECT * FROM produk WHERE Stok > 0 ORDER BY NamaProduk ASC");
                        while ($p = mysqli_fetch_assoc($q_produk)) {
                            $img_file = !empty($p['gambar']) ? $p['gambar'] : 'default.png';
                            $path_i = '../assets/img/' . $img_file;
                            ?>
                            <div class="product-item-card" data-id="<?= $p['ProdukID']; ?>" data-name="<?= strtolower($p['NamaProduk']); ?>" onclick="selectProduct('<?= $p['ProdukID']; ?>', '<?= htmlspecialchars($p['NamaProduk'], ENT_QUOTES); ?>')">
                                <img src="<?= $path_i; ?>" class="prod-thumb" alt="" onerror="this.onerror=null; this.src='../assets/img/default.png';">
                                <div class="prod-info">
                                    <p class="prod-name"><?= htmlspecialchars($p['NamaProduk']); ?></p>
                                    <div class="prod-meta">
                                        <span class="prod-price">Rp <?= number_format($p['Harga'], 0, ',', '.'); ?></span>
                                        <span class="prod-stock">Stok: <?= $p['Stok']; ?></span>
                                    </div>
                                </div>
                            </div>
                            <?php
                        }
                        ?>
                    </div>
                    <small id="selectedInfoText" style="color: #2E472E; font-size: 11.5px; font-weight: 700; margin-top: 5px; display: block;">*Belum ada produk dipilih untuk ditambah</small>
                </div>

                <div class="form-group">
                    <label>Jumlah (Qty Tambahan)</label>
                    <input type="number" name="Jumlah" min="1" value="1" class="form-control">
                </div>

                <button type="submit" name="add_to_edit_cart" class="btn-primary-custom">
                    + Tambahkan ke Daftar
                </button>
            </div>

            <!-- Panel Kanan: Daftar Produk dalam Transaksi -->
            <div class="glass-panel">
                <h3 class="panel-title">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                        <circle cx="9" cy="21" r="1"></circle>
                        <circle cx="20" cy="21" r="1"></circle>
                        <path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"></path>
                    </svg>
                    Daftar Produk yang Dibeli
                </h3>

                <table class="table-pos">
                    <thead>
                        <tr>
                            <th style="width: 45px; text-align: center;">Gambar</th>
                            <th>Item</th>
                            <th>Harga</th>
                            <th style="text-align: center;">Qty</th>
                            <th>Subtotal</th>
                            <th style="text-align: center;">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $grand_total = 0;
                        if (isset($_SESSION['edit_cart_' . $penjualan_id])) {
                            $cart_edit = $_SESSION['edit_cart_' . $penjualan_id];
                            if (!empty($cart_edit)) {
                                foreach ($cart_edit as $pid => $item) {
                                    $subtotal = $item['harga'] * $item['jumlah'];
                                    $grand_total += $subtotal;
                                    $path_img = '../assets/img/' . (!empty($item['gambar']) ? $item['gambar'] : 'default.png');
                                    ?>
                                    <tr>
                                        <td style="text-align: center;">
                                            <img src="<?= $path_img; ?>" alt="" class="cart-img-thumb" onerror="this.onerror=null; this.src='../assets/img/default.png';">
                                        </td>
                                        <td><strong><?= htmlspecialchars($item['nama']); ?></strong></td>
                                        <td>Rp <?= number_format($item['harga'], 0, ',', '.'); ?></td>
                                        <td style="text-align: center;"><?= $item['jumlah']; ?></td>
                                        <td><strong>Rp <?= number_format($subtotal, 0, ',', '.'); ?></strong></td>
                                        <td style="text-align: center;">
                                            <a href="edit_penjualan.php?id=<?= $penjualan_id; ?>&hapus_cart=<?= $pid; ?>" class="btn-danger-sm">Hapus</a>
                                        </td>
                                    </tr>
                                    <?php
                                }
                            } else {
                                echo "<tr><td colspan='6' style='text-align: center; color: #556B2F; padding: 20px; font-weight: 700;'>Daftar produk kosong.</td></tr>";
                            }
                        }

                        // Hitung kekurangan bayar (Selisih Total Baru - Total Lama yang sudah dibayar)
                        $kekurangan_bayar = $grand_total - $total_harga_lama;
                        if ($kekurangan_bayar < 0) {
                            $kekurangan_bayar = 0;
                        }
                        ?>
                    </tbody>
                </table>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 10px; margin-bottom: 12px;">
                    <div class="total-box" style="margin-bottom: 0;">
                        <span style="font-size: 10.5px; font-weight: 800; color: #384E38; text-transform: uppercase;">Sudah Dibayar (Lama)</span>
                        <div style="font-size: 15px; font-weight: 900; color: #1A2E1A; margin-top: 2px;">Rp <?= number_format($total_harga_lama, 0, ',', '.'); ?></div>
                    </div>
                    <div class="total-box" style="margin-bottom: 0;">
                        <span style="font-size: 10.5px; font-weight: 800; color: #384E38; text-transform: uppercase;">Total Harga Baru</span>
                        <div class="total-display" style="font-size: 15px;" id="grandTotalDisplay">Rp <?= number_format($grand_total, 0, ',', '.'); ?></div>
                    </div>
                </div>

                <div class="total-box" style="background: #EAF2EA; border: 1.5px solid #2E472E;">
                    <span style="font-size: 11.5px; font-weight: 800; color: #2E472E; text-transform: uppercase;">Harga Produk Belum Dibayar (Kekurangan)</span>
                    <div class="total-display" style="color: #1A2E1A; font-size: 24px;" id="kekuranganBayarDisplay">Rp <?= number_format($kekurangan_bayar, 0, ',', '.'); ?></div>
                </div>

                <!-- Input Nominal Tambahan Bayar -->
                <div class="form-group">
                    <label>Nominal Tambahan Uang Bayar (Rp)</label>
                    <input type="number" name="nominal_tambahan_bayar" id="nominalTambahan" min="<?= $kekurangan_bayar; ?>" value="<?= $kekurangan_bayar; ?>" class="form-control" onkeyup="hitungKembalianTambahan()" onchange="hitungKembalianTambahan()" required>
                </div>

                <div class="total-box" style="background: #F4F6F4; border-style: solid;">
                    <span style="font-size: 11.5px; font-weight: 800; color: #384E38; text-transform: uppercase;">Kembalian dari Tambahan</span>
                    <div class="change-display" id="kembalianDisplay">Rp 0</div>
                </div>

                <div style="display: flex; gap: 10px;">
                    <button type="submit" name="update_transaksi" class="btn-primary-custom" style="flex: 2;" <?= empty($cart_edit) ? 'disabled' : ''; ?>>
                        Simpan Perubahan Transaksi
                    </button>
                    <a href="detail_penjualan.php?id=<?= $penjualan_id; ?>" class="btn-primary-custom" style="flex: 1; background: #6B7280; text-align: center;">
                        Batal
                    </a>
                </div>
            </div>
        </div>
    </form>
</div>

<script>
var totalHargaLama = <?= $total_harga_lama; ?>;

function hitungKembalianTambahan() {
    // Ambil total harga baru dari list cart atau hitung ulang dari elemen HTML jika diinginkan
    // Kita bisa ambil text dari grandTotalDisplay atau hitung otomatis
    var textGrandTotal = document.getElementById('grandTotalDisplay').innerText;
    var grandTotalNilai = parseFloat(textGrandTotal.replace(/[^0-9]/g, '')) || 0;
    
    var kekurangan = grandTotalNilai - totalHargaLama;
    if (kekurangan < 0) kekurangan = 0;

    var nominalBayarTambahan = parseFloat(document.getElementById('nominalTambahan').value) || 0;
    var kembalian = nominalBayarTambahan - kekurangan;

    var display = document.getElementById('kembalianDisplay');
    if (kembalian >= 0) {
        display.innerText = "Rp " + kembalian.toLocaleString('id-ID');
        display.style.color = "#2E472E";
    } else {
        display.innerText = "Uang Pembayaran Kurang!";
        display.style.color = "#C62828";
    }
}

// Jalankan saat pertama kali halaman dimuat
hitungKembalianTambahan();

function filterProductCards() {
    var input = document.getElementById('searchProduk').value.toLowerCase();
    var cards = document.getElementsByClassName('product-item-card');

    for (var i = 0; i < cards.length; i++) {
        var name = cards[i].getAttribute('data-name');
        if (name.indexOf(input) > -1) {
            cards[i].style.display = "flex";
        } else {
            cards[i].style.display = "none";
        }
    }
}

function selectProduct(id, name) {
    document.getElementById('selectedProdukID').value = id;
    
    var cards = document.getElementsByClassName('product-item-card');
    for (var i = 0; i < cards.length; i++) {
        cards[i].classList.remove('selected');
        if (cards[i].getAttribute('data-id') === id) {
            cards[i].classList.add('selected');
        }
    }

    document.getElementById('selectedInfoText').innerText = "Terpilih: " + name;
    document.getElementById('selectedInfoText').style.color = "#1A2E1A";
}
</script>

</body>
</html>