<?php
session_start();

// Perbaikan: Ubah $_SESSION['level'] menjadi $_SESSION['role'] agar cocok dengan sistem login
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
    from { opacity: 0; transform: translateY(-15px); }
    to { opacity: 1; transform: translateY(0); }
}

@keyframes fadeInUp {
    from { opacity: 0; transform: translateY(20px); }
    to { opacity: 1; transform: translateY(0); }
}

.animate-header {
    animation: slideDown 0.5s ease forwards;
}

/* Grid Layout */
.product-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(260px, 1fr));
    gap: 18px;
    animation: fadeInUp 0.7s ease forwards;
}

/* Base Kartu Glassmorphism Transparan */
.product-card {
    border-radius: 16px;
    padding: 20px;
    border: 1px solid rgba(255, 255, 255, 0.6);
    backdrop-filter: blur(12px);
    -webkit-backdrop-filter: blur(12px);
    box-shadow: 0 8px 20px rgba(0, 0, 0, 0.05);
    transition: all 0.25s ease;
    display: flex;
    flex-direction: column;
    justify-content: space-between;
}

.product-card:hover {
    transform: translateY(-4px);
    box-shadow: 0 12px 25px rgba(0, 0, 0, 0.1);
}

/* Variasi Warna Transparan */
.card-theme-0 { background: rgba(242, 247, 240, 0.88); }
.card-theme-0 .badge-id { color: #556B2F; background: #E2EEDD; }
.card-theme-0 .badge-stok { color: #3A4A36; background: #D5E4D0; }
.card-theme-0 .price-container { background: rgba(255, 255, 255, 0.9); border-color: #E2EEDD; }

.card-theme-1 { background: rgba(238, 243, 232, 0.88); }
.card-theme-1 .badge-id { color: #435930; background: #DBE8CE; }
.card-theme-1 .badge-stok { color: #2C3B1F; background: #CFDDBF; }
.card-theme-1 .price-container { background: rgba(255, 255, 255, 0.9); border-color: #DBE8CE; }

.card-theme-2 { background: rgba(234, 244, 238, 0.88); }
.card-theme-2 .badge-id { color: #2E5A4E; background: #D2E7DC; }
.card-theme-2 .badge-stok { color: #1E3F36; background: #C2DEC1; }
.card-theme-2 .price-container { background: rgba(255, 255, 255, 0.9); border-color: #D2E7DC; }

.card-theme-3 { background: rgba(245, 246, 235, 0.88); }
.card-theme-3 .badge-id { color: #5B5A2D; background: #E9EAC8; }
.card-theme-3 .badge-stok { color: #43421F; background: #DDDFA2; }
.card-theme-3 .price-container { background: rgba(255, 255, 255, 0.9); border-color: #E9EAC8; }

/* Styling Gambar Produk */
.product-img-container {
    width: 100%;
    height: 140px;
    background: rgba(255, 255, 255, 0.6);
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    margin-bottom: 14px;
    border: 1px solid rgba(255, 255, 255, 0.8);
    overflow: hidden;
}

.product-img {
    width: 100%;
    height: 100%;
    object-fit: contain;
    padding: 8px;
    transition: transform 0.3s ease;
}

.product-card:hover .product-img {
    transform: scale(1.05);
}

/* General Elements */
.card-header-info {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 10px;
}

.badge-id {
    font-size: 11px;
    font-weight: 700;
    padding: 3px 10px;
    border-radius: 20px;
}

.badge-stok {
    font-size: 12px;
    font-weight: 700;
    padding: 3px 10px;
    border-radius: 20px;
}

.product-title {
    font-family: 'Poppins', sans-serif;
    font-size: 15px;
    font-weight: 700;
    color: #1c2e1b;
    margin: 0 0 12px 0;
    line-height: 1.35;
    height: 40px;
    overflow: hidden;
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
}

.price-container {
    padding: 8px 12px;
    border-radius: 10px;
    border-style: solid;
    border-width: 1px;
    margin-bottom: 16px;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.price-label {
    font-size: 11px;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    color: #5c705a;
    font-weight: 700;
}

.price-value {
    font-family: 'Poppins', sans-serif;
    font-size: 15px;
    font-weight: 800;
    color: #1c2e1b;
}

/* Tombol Aksi */
.card-actions {
    display: flex;
    gap: 8px;
}

.btn-tambah {
    transition: all 0.3s ease;
}

.btn-tambah:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 20px rgba(28, 46, 27, 0.3) !important;
}

.btn-aksi {
    flex: 1;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 6px;
    padding: 8px 0;
    border-radius: 8px;
    font-size: 12px;
    font-weight: 700;
    text-decoration: none;
    transition: all 0.2s ease;
}

.btn-edit {
    background-color: #7E6347; 
    color: #ffffff;
}

.btn-edit:hover {
    background-color: #654E37;
    box-shadow: 0 3px 8px rgba(126, 99, 71, 0.3);
}

.btn-hapus {
    background-color: #FFF5F5;
    color: #E74C3C;
    border: 1px solid #FADBD8;
}

.btn-hapus:hover {
    background-color: #E74C3C;
    color: #ffffff;
    border-color: #E74C3C;
    box-shadow: 0 3px 8px rgba(231, 76, 60, 0.2);
}
</style>

<div style="max-width: 1200px; margin: 30px auto; padding: 0 20px;">
    <!-- Header: Ditambahkan sedikit margin-left agar tidak terlalu mentok ke kiri -->
    <div class="animate-header" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 25px; margin-left: 5px; margin-right: 5px;">
        <div>
            <h2 style="margin: 0; font-family: 'Poppins', sans-serif; font-size: 28px; font-weight: 800; color: #112211; text-shadow: 0 2px 10px rgba(255, 255, 255, 0.6); display: flex; align-items: center; gap: 12px;">
                <!-- Ikon Kotak Produk -->
                <i class="fa-solid fa-boxes-stacked" style="font-size: 24px; color: #1c2e1b;"></i>
                Data Produk Kasir
            </h2>
            <p style="margin: 6px 0 0 0; font-size: 14.5px; color: #2d422a; font-weight: 600;">
                Kelola ketersediaan barang dan daftar harga produk dengan mudah.
            </p>
        </div>
        
        <div style="display: flex; gap: 10px; align-items: center;">
            <a href="tambah_produk.php" class="btn-tambah" style="background-color: #1c2e1b; color: white; padding: 10px 22px; border-radius: 30px; text-decoration: none; font-weight: 700; font-size: 14px; box-shadow: 0 4px 15px rgba(28, 46, 27, 0.25); display: inline-flex; align-items: center; gap: 8px;">
                + Tambah Produk
            </a>
        </div>
    </div>

    <!-- Grid Produk -->
    <div class="product-grid">
        <?php
        $query = mysqli_query($koneksi, "SELECT * FROM produk ORDER BY ProdukID DESC");
        if ($query && mysqli_num_rows($query) > 0) {
            $index = 0;
            while ($row = mysqli_fetch_assoc($query)) {
                $theme_class = "card-theme-" . ($index % 4);
                $index++;

                $nama_file_gambar = !empty($row['gambar']) ? $row['gambar'] : 'default.png';
        ?>
            <div class="product-card <?php echo $theme_class; ?>">
                <div>
                    <!-- Header Kartu: ID & Stok -->
                    <div class="card-header-info">
                        <span class="badge-id">#<?php echo $row['ProdukID']; ?></span>
                        <span class="badge-stok"><?php echo number_format($row['Stok'], 0, ',', '.'); ?> pcs</span>
                    </div>

                    <!-- Gambar Produk -->
                    <div class="product-img-container">
                        <img src="../assets/img/<?php echo $nama_file_gambar; ?>" alt="<?php echo htmlspecialchars($row['NamaProduk']); ?>" class="product-img">
                    </div>

                    <!-- Nama Produk -->
                    <div class="product-title">
                        <?php echo htmlspecialchars($row['NamaProduk']); ?>
                    </div>

                    <!-- Box Harga -->
                    <div class="price-container">
                        <span class="price-label">Harga Satuan</span>
                        <span class="price-value">Rp <?php echo number_format($row['Harga'], 0, ',', '.'); ?></span>
                    </div>
                </div>

                <!-- Tombol Aksi -->
                <div class="card-actions">
                    <a href="edit_produk.php?id=<?php echo $row['ProdukID']; ?>" class="btn-aksi btn-edit">
                        <i class="fa-solid fa-pen-to-square"></i> Edit
                    </a>
                    <a href="hapus_produk.php?id=<?php echo $row['ProdukID']; ?>" onclick="return confirm('Yakin ingin menghapus produk ini?')" class="btn-aksi btn-hapus">
                        <i class="fa-solid fa-trash-can"></i> Hapus
                    </a>
                </div>
            </div>
        <?php 
            } 
        } else {
            echo "<div style='grid-column: 1/-1; text-align: center; padding: 40px; color: #2d422a; background: rgba(255,255,255,0.85); backdrop-filter: blur(10px); border-radius: 18px; font-weight: 700;'>Belum ada data produk tersimpan.</div>";
        }
        ?>
    </div>
</div>

</body>
</html>