<?php
session_start();

// Perbaikan: Tambahkan pengecekan session agar halaman kasir aman
if (!isset($_SESSION['status']) || $_SESSION['status'] != "login" || $_SESSION['role'] != "admin") {
    header("location:../login.php?pesan=belum_login");
    exit;
}

include '../koneksi.php';
include 'header.php';

// Inisialisasi Keranjang Belanja di Session jika belum ada
if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

// Handler Tambah Produk ke Keranjang
if (isset($_POST['add_to_cart'])) {
    $produk_id = mysqli_real_escape_string($koneksi, $_POST['ProdukID']);
    $jumlah = (int)$_POST['Jumlah'];

    // Ambil detail produk dari database
    $q_p = mysqli_query($koneksi, "SELECT * FROM produk WHERE ProdukID = '$produk_id'");
    $p = mysqli_fetch_assoc($q_p);

    if ($p) {
        if ($jumlah > $p['Stok']) {
            echo "<script>alert('Stok tidak mencukupi! Stok tersisa: {$p['Stok']}');</script>";
        } else {
            // Pemetaan gambar otomatis berdasarkan nama produk
            $nama_file_gambar = 'default.png';
            $nama_prod = trim($p['NamaProduk']);
            
            if ($nama_prod == 'Botanica Canvas Tote') {
                $nama_file_gambar = 'produk1.png';
            } elseif ($nama_prod == 'Botanica Tumbler') {
                $nama_file_gambar = 'produk11.jfif';
            } elseif ($nama_prod == 'Botanica Wheat Bento Set') {
                $nama_file_gambar = 'produk2.png';
            } elseif ($nama_prod == 'Earth & Co. Bamboo Lunch Box') {
                $nama_file_gambar = 'produk3.png';
            } elseif ($nama_prod == 'EcoGlow Cork Glass Bottle') {
                $nama_file_gambar = 'produk4.png';
            } elseif ($nama_prod == 'Forest Mist Portable Cutlery') {
                $nama_file_gambar = 'produk5.png';
            } elseif ($nama_prod == 'Forest Mist Straw Cup') {
                $nama_file_gambar = 'produk6.jfif';
            } elseif ($nama_prod == 'Natura Bamboo Bento Lunch Box') {
                $nama_file_gambar = 'produk7_bento.jfif';
            } elseif ($nama_prod == 'Natura Bamboo Cutlery Set') {
                $nama_file_gambar = 'produk7_cutlery.jfif';
            } elseif ($nama_prod == 'Natura Bamboo Flask') {
                $nama_file_gambar = 'produk7.jfif';
            } elseif ($nama_prod == 'Natura Wooden Coaster') {
                $nama_file_gambar = 'produk8.jfif';
            } elseif ($nama_prod == 'Sage Layers Stack Box') {
                $nama_file_gambar = 'produk9.jfif';
            } elseif ($nama_prod == 'Sage Leaf Vacuum Tumbler') {
                $nama_file_gambar = 'produk10.jfif';
            }

            // Jika produk sudah ada di keranjang, update jumlahnya
            if (isset($_SESSION['cart'][$produk_id])) {
                $total_baru = $_SESSION['cart'][$produk_id]['jumlah'] + $jumlah;
                if ($total_baru > $p['Stok']) {
                    echo "<script>alert('Jumlah melebihi stok yang tersedia!');</script>";
                } else {
                    $_SESSION['cart'][$produk_id]['jumlah'] = $total_baru;
                }
            } else {
                $_SESSION['cart'][$produk_id] = [
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

// Handler Hapus Item Keranjang
if (isset($_GET['hapus_cart'])) {
    $id_hapus = mysqli_real_escape_string($koneksi, $_GET['hapus_cart']);
    unset($_SESSION['cart'][$id_hapus]);
    echo "<script>window.location='tambah_penjualan.php';</script>";
}

// Handler Reset / Batal Keranjang
if (isset($_GET['reset_cart'])) {
    unset($_SESSION['cart']);
    echo "<script>window.location='tambah_penjualan.php';</script>";
}

// Handler Simpan Transaksi Selesai
if (isset($_POST['checkout'])) {
    if (empty($_SESSION['cart'])) {
        echo "<script>alert('Keranjang belanja masih kosong!');</script>";
    } else {
        $pelanggan_id = mysqli_real_escape_string($koneksi, $_POST['PelangganID']);
        $bayar = (float)$_POST['bayar'];
        $total_harga = (float)$_POST['total_harga'];

        if ($bayar < $total_harga) {
            echo "<script>alert('Uang pembayaran kurang!');</script>";
        } else {
            mysqli_begin_transaction($koneksi);

            try {
                // 1. Simpan ke tabel penjualan
                $q_penjualan = mysqli_query($koneksi, "INSERT INTO penjualan (TanggalPenjualan, TotalHarga, PelangganID) VALUES (NOW(), '$total_harga', '$pelanggan_id')");
                if (!$q_penjualan) throw new Exception(mysqli_error($koneksi));
                
                $penjualan_id = mysqli_insert_id($koneksi);

                // 2. Simpan detail & kurangi stok
                foreach ($_SESSION['cart'] as $pid => $item) {
                    $subtotal = $item['harga'] * $item['jumlah'];
                    $qty = (int)$item['jumlah'];
                    $pid_esc = mysqli_real_escape_string($koneksi, $pid);

                    $q_detail = mysqli_query($koneksi, "INSERT INTO detailpenjualan (PenjualanID, ProdukID, JumlahProduk, Subtotal) VALUES ('$penjualan_id', '$pid_esc', '$qty', '$subtotal')");
                    if (!$q_detail) throw new Exception(mysqli_error($koneksi));

                    $q_update_stok = mysqli_query($koneksi, "UPDATE produk SET Stok = Stok - $qty WHERE ProdukID = '$pid_esc'");
                    if (!$q_update_stok) throw new Exception(mysqli_error($koneksi));
                }

                mysqli_commit($koneksi);
                unset($_SESSION['cart']);
                echo "<script>alert('Transaksi Berhasil Disimpan!'); window.location='detail_penjualan.php?id=$penjualan_id';</script>";

            } catch (Exception $e) {
                mysqli_rollback($koneksi);
                echo "<script>alert('Transaksi Gagal: " . $e->getMessage() . "');</script>";
            }
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
    font-size: 30px !important;
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

.product-list-box {
    max-height: 230px;
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
}

.btn-primary-custom:hover:not(:disabled) {
    background: #1A2E1A;
    transform: translateY(-1px);
    box-shadow: 0 5px 15px rgba(46, 71, 46, 0.35);
}

.btn-primary-custom:disabled {
    background: #9CA3AF;
    cursor: not-allowed;
    box-shadow: none;
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
    border: 1px dashed rgba(46, 71, 46, 0.4);
    border-radius: 12px;
    padding: 14px;
    margin-bottom: 16px;
}

.total-display {
    font-size: 24px;
    font-weight: 900;
    color: #1A2E1A;
    margin-top: 2px;
}

.kembalian-display {
    font-size: 14px;
    font-weight: 800;
    color: #2E472E;
    margin-top: 4px;
}
</style>

<div class="kasir-container">
    <div class="header-animated">
        <h1 class="page-title">
            <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="#1A2E1A" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                <circle cx="9" cy="21" r="1"></circle>
                <circle cx="20" cy="21" r="1"></circle>
                <path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"></path>
            </svg>
            Tambah Transaksi Penjualan (Admin)
        </h1>
        <p class="page-subtitle">Cari produk, pilih, masukkan jumlah, dan hitung kembalian uang pelanggan secara otomatis.</p>
    </div>

    <div class="pos-grid grid-animated">
        <!-- Panel Kiri: Form Pilih Barang -->
        <div class="glass-panel">
            <h3 class="panel-title">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="12" y1="5" x2="12" y2="19"></line><line x1="5" y1="12" x2="19" y2="12"></line></svg>
                Pilih Produk
            </h3>

            <form method="POST" action="">
                <input type="hidden" name="ProdukID" id="selectedProdukID" required>

                <div class="form-group">
                    <label>Cari & Pilih Produk</label>
                    <input type="text" id="searchProduk" class="form-control" placeholder="Ketik nama produk..." onkeyup="filterProductCards()">
                    
                    <div class="product-list-box" id="productListContainer">
                        <?php
                        $q_produk = mysqli_query($koneksi, "SELECT * FROM produk WHERE Stok > 0 ORDER BY NamaProduk ASC");
                        while ($p = mysqli_fetch_assoc($q_produk)) {
                            $img_file = 'default.png';
                            $n_prod = trim($p['NamaProduk']);
                            if ($n_prod == 'Botanica Canvas Tote') { $img_file = 'produk1.png'; }
                            elseif ($n_prod == 'Botanica Tumbler') { $img_file = 'produk11.jfif'; }
                            elseif ($n_prod == 'Botanica Wheat Bento Set') { $img_file = 'produk2.png'; }
                            elseif ($n_prod == 'Earth & Co. Bamboo Lunch Box') { $img_file = 'produk3.png'; }
                            elseif ($n_prod == 'EcoGlow Cork Glass Bottle') { $img_file = 'produk4.png'; }
                            elseif ($n_prod == 'Forest Mist Portable Cutlery') { $img_file = 'produk5.png'; }
                            elseif ($n_prod == 'Forest Mist Straw Cup') { $img_file = 'produk6.jfif'; }
                            elseif ($n_prod == 'Natura Bamboo Bento Lunch Box') { $img_file = 'produk7_bento.jfif'; }
                            elseif ($n_prod == 'Natura Bamboo Cutlery Set') { $img_file = 'produk7_cutlery.jfif'; }
                            elseif ($n_prod == 'Natura Bamboo Flask') { $img_file = 'produk7.jfif'; }
                            elseif ($n_prod == 'Natura Wooden Coaster') { $img_file = 'produk8.jfif'; }
                            elseif ($n_prod == 'Sage Layers Stack Box') { $img_file = 'produk9.jfif'; }
                            elseif ($n_prod == 'Sage Leaf Vacuum Tumbler') { $img_file = 'produk10.jfif'; }

                            $path_i = '../assets/img/' . $img_file;
                            ?>
                            <div class="product-item-card" data-id="<?= $p['ProdukID']; ?>" data-name="<?= strtolower($p['NamaProduk']); ?>" onclick="selectProduct('<?= $p['ProdukID']; ?>', '<?= htmlspecialchars($p['NamaProduk'], ENT_QUOTES); ?>')">
                                <img src="<?= $path_i; ?>" class="prod-thumb" alt="">
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
                    <small id="selectedInfoText" style="color: #2E472E; font-size: 11.5px; font-weight: 700; margin-top: 5px; display: block;">*Belum ada produk dipilih</small>
                </div>

                <div class="form-group">
                    <label>Jumlah (Qty)</label>
                    <input type="number" name="Jumlah" min="1" value="1" class="form-control" required>
                </div>

                <button type="submit" name="add_to_cart" class="btn-primary-custom">
                    + Tambahkan ke Keranjang
                </button>
            </form>
        </div>

        <!-- Panel Kanan: Keranjang Belanja & Pembayaran -->
        <div class="glass-panel">
            <div style="display: flex; justify-content: space-between; align-items: center;" class="panel-title">
                <span style="display: flex; align-items: center; gap: 8px;">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                        <circle cx="9" cy="21" r="1"></circle>
                        <circle cx="20" cy="21" r="1"></circle>
                        <path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"></path>
                    </svg>
                    Keranjang Belanja
                </span>
                <?php if (!empty($_SESSION['cart'])): ?>
                    <a href="tambah_penjualan.php?reset_cart=1" style="font-size: 11.5px; color: #DC2626; font-weight: 700; text-decoration: none;">Kosongkan</a>
                <?php endif; ?>
            </div>

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
                    if (!empty($_SESSION['cart'])) {
                        foreach ($_SESSION['cart'] as $pid => $item) {
                            $subtotal = $item['harga'] * $item['jumlah'];
                            $grand_total += $subtotal;
                            $path_img = '../assets/img/' . $item['gambar'];
                            ?>
                            <tr>
                                <td style="text-align: center;">
                                    <img src="<?= $path_img; ?>" alt="" class="cart-img-thumb">
                                </td>
                                <td><strong><?= htmlspecialchars($item['nama']); ?></strong></td>
                                <td>Rp <?= number_format($item['harga'], 0, ',', '.'); ?></td>
                                <td style="text-align: center;"><?= $item['jumlah']; ?></td>
                                <td><strong>Rp <?= number_format($subtotal, 0, ',', '.'); ?></strong></td>
                                <td style="text-align: center;">
                                    <a href="tambah_penjualan.php?hapus_cart=<?= $pid; ?>" class="btn-danger-sm">Hapus</a>
                                </td>
                            </tr>
                            <?php
                        }
                    } else {
                        echo "<tr><td colspan='6' style='text-align: center; color: #556B2F; padding: 20px; font-weight: 700;'>Keranjang masih kosong.</td></tr>";
                    }
                    ?>
                </tbody>
            </table>

            <form method="POST" action="">
                <input type="hidden" name="total_harga" id="inputTotalHarga" value="<?= $grand_total; ?>">

                <div class="form-group">
                    <label>Pilih Pelanggan</label>
                    <select name="PelangganID" class="form-control" required>
                        <option value="">-- Pilih Pelanggan --</option>
                        <?php
                        $q_pelanggan = mysqli_query($koneksi, "SELECT * FROM pelanggan ORDER BY NamaPelanggan ASC");
                        while ($pel = mysqli_fetch_assoc($q_pelanggan)) {
                            echo "<option value='{$pel['PelangganID']}'>{$pel['NamaPelanggan']} ({$pel['NomorTelepon']})</option>";
                        }
                        ?>
                    </select>
                </div>

                <div class="total-box">
                    <span style="font-size: 11.5px; font-weight: 800; color: #384E38; text-transform: uppercase;">Total Keseluruhan</span>
                    <div class="total-display">Rp <?= number_format($grand_total, 0, ',', '.'); ?></div>
                    <div class="kembalian-display" id="kembalianText">Kembalian: Rp 0</div>
                </div>

                <div class="form-group">
                    <label>Uang Bayar (Tunai)</label>
                    <input type="number" name="bayar" id="inputBayar" class="form-control" placeholder="Masukkan nominal bayar" required oninput="hitungKembalian()">
                </div>

                <button type="submit" name="checkout" class="btn-primary-custom" <?= empty($_SESSION['cart']) ? 'disabled' : ''; ?>>
                    Simpan Transaksi
                </button>
            </form>
        </div>
    </div>
</div>

<script>
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

function hitungKembalian() {
    var total = parseFloat(document.getElementById('inputTotalHarga').value) || 0;
    var bayar = parseFloat(document.getElementById('inputBayar').value) || 0;
    var kembalian = bayar - total;

    var kembalianText = document.getElementById('kembalianText');

    if (bayar > 0 && kembalian >= 0) {
        kembalianText.innerText = "Kembalian: Rp " + kembalian.toLocaleString('id-ID');
        kembalianText.style.color = "#2E472E";
    } else if (bayar > 0 && kembalian < 0) {
        kembalianText.innerText = "Kurang: Rp " + Math.abs(kembalian).toLocaleString('id-ID');
        kembalianText.style.color = "#DC2626";
    } else {
        kembalianText.innerText = "Kembalian: Rp 0";
        kembalianText.style.color = "#2E472E";
    }
}
</script>

</body>
</html>