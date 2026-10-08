<?php
session_start();

// Cek apakah user sudah login dan memiliki role kasir (sesuaikan nama session-nya jika berbeda)
if (!isset($_SESSION['status']) || $_SESSION['status'] != "login") {
    header("location:../index.php?pesan=belum_login");
    exit;
}

include '../koneksi.php';
include 'header.php';

// Ambil kata kunci pencarian jika ada
$keyword = isset($_GET['search']) ? trim($_GET['search']) : '';
?>

<style>
/* Reset Background */
body {
    background: url('../assets/img/bgkasir.jfif') no-repeat center center fixed !important;
    background-size: cover !important;
    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif !important;
    overflow-x: hidden;
}

.kasir-container {
    padding: 30px 15px;
    max-width: 1200px;
    margin: 0 auto;
}

/* --- ANIMASI HALUS --- */
@keyframes slideDownHeader {
    0% { opacity: 0; transform: translateY(-30px); }
    100% { opacity: 1; transform: translateY(0); }
}

@keyframes slideUpSearch {
    0% { opacity: 0; transform: translateY(20px); }
    100% { opacity: 1; transform: translateY(0); }
}

@keyframes fadeInGrid {
    0% { opacity: 0; transform: translateY(30px); }
    100% { opacity: 1; transform: translateY(0); }
}

.header-animated {
    animation: slideDownHeader 1s cubic-bezier(0.2, 0.8, 0.2, 1) forwards;
}

.search-animated {
    opacity: 0;
    animation: slideUpSearch 0.9s cubic-bezier(0.2, 0.8, 0.2, 1) forwards;
    animation-delay: 0.25s;
}

.grid-animated {
    opacity: 0;
    animation: fadeInGrid 1s cubic-bezier(0.2, 0.8, 0.2, 1) forwards;
    animation-delay: 0.4s;
}

/* Header Section */
.page-title {
    font-size: 36px !important;
    font-weight: 900 !important;
    color: #1A2E1A !important;
    margin: 0 0 6px 0 !important;
    text-shadow: 0 2px 8px rgba(255, 255, 255, 0.9);
    display: flex;
    align-items: center;
    gap: 12px;
}

.page-subtitle {
    font-size: 15px !important;
    color: #2E472E !important;
    font-weight: 700 !important;
    margin: 0 !important;
    text-shadow: 0 1px 4px rgba(255, 255, 255, 0.9);
}

/* Search Bar Section */
.top-bar-flex {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin: 25px 0 25px 0;
    flex-wrap: wrap;
    gap: 15px;
}

.search-form-group {
    display: flex;
    align-items: center;
    gap: 8px;
    flex: 1;
    max-width: 480px;
}

.search-box {
    position: relative;
    display: flex;
    align-items: center;
    flex: 1;
}

.search-box input {
    width: 100%;
    padding: 11px 16px 11px 42px;
    border-radius: 14px;
    border: 2px solid rgba(46, 71, 46, 0.4);
    background: rgba(255, 255, 255, 0.95);
    backdrop-filter: blur(10px);
    font-size: 14px;
    font-weight: 600;
    color: #1A2E1A;
    outline: none;
    box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
    transition: all 0.3s ease;
}

.search-box input:focus {
    background: #FFFFFF;
    border-color: #2E472E;
    box-shadow: 0 6px 20px rgba(0, 0, 0, 0.18);
}

.search-box svg {
    position: absolute;
    left: 14px;
    color: #2E472E;
    z-index: 2;
}

.btn-search {
    background: #2E472E;
    color: white;
    border: none;
    padding: 11px 18px;
    border-radius: 14px;
    font-weight: 700;
    font-size: 14px;
    cursor: pointer;
    box-shadow: 0 4px 12px rgba(46, 71, 46, 0.3);
    transition: all 0.3s ease;
}

.btn-search:hover {
    background: #1A2E1A;
    transform: translateY(-2px);
}

.btn-reset {
    background: rgba(220, 38, 38, 0.9);
    color: white;
    border: none;
    padding: 11px 16px;
    border-radius: 14px;
    font-weight: 700;
    font-size: 14px;
    text-decoration: none;
    box-shadow: 0 4px 12px rgba(220, 38, 38, 0.3);
    transition: all 0.3s ease;
}

.btn-reset:hover {
    background: #DC2626;
    color: white;
}

/* --- GRID PRODUCT CARDS --- */
.product-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(240px, 1fr));
    gap: 24px;
}

.product-card {
    background: #FFFFFF !important;
    border: 2px solid rgba(46, 71, 46, 0.25) !important;
    border-radius: 22px;
    overflow: hidden;
    box-shadow: 0 10px 30px rgba(0, 0, 0, 0.15);
    transition: all 0.35s cubic-bezier(0.175, 0.885, 0.32, 1.275);
    display: flex;
    flex-direction: column;
    position: relative;
}

.product-card:hover {
    transform: translateY(-8px);
    box-shadow: 0 20px 45px rgba(0, 0, 0, 0.25);
    border-color: #2E472E !important;
}

/* Bagian Gambar Card */
.product-image-container {
    width: 100%;
    height: 220px;
    background: #F4F6F4;
    display: flex;
    align-items: center;
    justify-content: center;
    overflow: hidden;
    position: relative;
    padding: 12px;
    border-bottom: 2px solid rgba(46, 71, 46, 0.15);
}

.product-image-container img {
    max-width: 100%;
    max-height: 100%;
    object-fit: contain;
    transition: transform 0.5s ease;
}

.product-card:hover .product-image-container img {
    transform: scale(1.06);
}

/* Bagian Isi Detail Card */
.product-body {
    padding: 18px;
    display: flex;
    flex-direction: column;
    flex: 1;
    justify-content: space-between;
}

.product-title {
    font-size: 16px;
    font-weight: 800;
    color: #1A2E1A;
    margin: 0 0 8px 0;
    line-height: 1.35;
}

.product-price {
    font-size: 17px;
    font-weight: 900;
    color: #2E472E;
    margin-bottom: 14px;
}

.product-footer {
    display: flex;
    justify-content: space-between;
    align-items: center;
    border-top: 1px solid rgba(46, 71, 46, 0.2);
    padding-top: 12px;
    margin-top: auto;
}

.product-stock {
    font-size: 13px;
    font-weight: 700;
    color: #4A5D4E;
}

/* Badge Status Stok */
.badge-stok {
    padding: 5px 12px;
    border-radius: 10px;
    font-size: 11px;
    font-weight: 700;
}

.stok-aman {
    background: #E8F5E9;
    color: #2E7D32;
    border: 1px solid #A5D6A7;
}

.stok-menipis {
    background: #FFF8E1;
    color: #F57F17;
    border: 1px solid #FFE082;
}

.stok-habis {
    background: #FFEBEE;
    color: #C62828;
    border: 1px solid #EF9A9A;
}

.no-data {
    grid-column: 1 / -1;
    text-align: center;
    padding: 40px;
    background: #FFFFFF;
    border-radius: 16px;
    color: #333;
    font-weight: 700;
    box-shadow: 0 4px 15px rgba(0,0,0,0.1);
}
</style>

<div class="kasir-container">
    <!-- Header Title -->
    <div class="header-animated">
        <h1 class="page-title">
            <svg width="34" height="34" viewBox="0 0 24 24" fill="none" stroke="#1A2E1A" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                <path d="M6 2L3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4z"></path>
                <line x1="3" y1="6" x2="21" y2="6"></line>
                <path d="M16 10a4 4 0 0 1-8 0"></path>
            </svg>
            Daftar Produk
        </h1>
        <p class="page-subtitle">Cek ketersediaan stok barang dan informasi harga penjualan</p>
    </div>

    <!-- Search Bar -->
    <div class="top-bar-flex search-animated">
        <form method="GET" action="" class="search-form-group">
            <div class="search-box">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                    <circle cx="11" cy="11" r="8"></circle>
                    <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
                </svg>
                <input type="text" name="search" placeholder="Cari nama produk..." value="<?= htmlspecialchars($keyword); ?>">
            </div>
            
            <button type="submit" class="btn-search">Cari</button>

            <?php if ($keyword != ''): ?>
                <a href="produk.php" class="btn-reset">Reset</a>
            <?php endif; ?>
        </form>
    </div>

    <!-- Product Cards Grid -->
    <div class="product-grid grid-animated">
        <?php
        if ($keyword != '') {
            $stmt = mysqli_prepare($koneksi, "SELECT * FROM produk WHERE NamaProduk LIKE ? ORDER BY NamaProduk ASC");
            $searchTerm = "%" . $keyword . "%";
            mysqli_stmt_bind_param($stmt, "s", $searchTerm);
            mysqli_stmt_execute($stmt);
            $query = mysqli_stmt_get_result($stmt);
        } else {
            $query = mysqli_query($koneksi, "SELECT * FROM produk ORDER BY NamaProduk ASC");
        }

        if ($query && mysqli_num_rows($query) > 0) {
            while ($d = mysqli_fetch_assoc($query)) {
                $stok = $d['Stok'];
                $nama_produk = $d['NamaProduk'];
                
                $nama_file_gambar = !empty($d['gambar']) ? $d['gambar'] : 'default.png';
                $path_gambar = '../assets/img/' . $nama_file_gambar;
                if (!file_exists($path_gambar)) {
                    $path_gambar = '../assets/img/default.png';
                }

                if ($stok <= 0) {
                    $badge_class = "stok-habis";
                    $status_text = "Habis";
                } else if ($stok <= 5) {
                    $badge_class = "stok-menipis";
                    $status_text = "Menipis";
                } else {
                    $badge_class = "stok-aman";
                    $status_text = "Tersedia";
                }
                ?>
                
                <div class="product-card">
                    <div class="product-image-container">
                        <img src="<?= $path_gambar; ?>" alt="<?= htmlspecialchars($nama_produk); ?>">
                    </div>
                    <div class="product-body">
                        <div>
                            <h3 class="product-title"><?= htmlspecialchars($nama_produk); ?></h3>
                            <div class="product-price">Rp <?= number_format($d['Harga'], 0, ',', '.'); ?></div>
                        </div>
                        <div class="product-footer">
                            <span class="product-stock">Stok: <strong><?= number_format($d['Stok'], 0, ',', '.'); ?></strong></span>
                            <span class="badge-stok <?= $badge_class; ?>"><?= $status_text; ?></span>
                        </div>
                    </div>
                </div>

                <?php
            }
        } else {
            echo '<div class="no-data">Data produk tidak ditemukan.</div>';
        }
        ?>
    </div>
</div>

</body>
</html>