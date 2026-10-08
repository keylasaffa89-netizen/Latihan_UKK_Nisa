<?php
session_start();

// Perbaikan: Ubah $_SESSION['level'] menjadi $_SESSION['role'] agar konsisten dengan dashboard & produk
if (!isset($_SESSION['status']) || $_SESSION['status'] != "login" || $_SESSION['role'] != "admin") {
    header("location:../login.php?pesan=belum_login");
    exit;
}

// Mengarahkan path koneksi ke folder utama (../koneksi.php)
include '../koneksi.php';
include 'header.php';
?>

<!-- Import Google Fonts: Poppins & Inter -->
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700;800&family=Poppins:wght@600;700;800&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

<style>
/* Background Admin */
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

/* Container List Pelanggan */
.customer-list-container {
    display: flex;
    flex-direction: column;
    gap: 12px;
    animation: fadeInUp 0.7s ease forwards;
}

/* Desain Baris List Tunggal */
.customer-row-item {
    background: rgba(255, 255, 255, 0.85);
    backdrop-filter: blur(12px);
    -webkit-backdrop-filter: blur(12px);
    border: 1px solid rgba(255, 255, 255, 0.7);
    border-radius: 14px;
    padding: 16px 22px;
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 20px;
    box-shadow: 0 4px 15px rgba(0, 0, 0, 0.04);
    transition: all 0.25s ease;
}

.customer-row-item:hover {
    background: rgba(255, 255, 255, 0.95);
    transform: translateX(4px);
    box-shadow: 0 6px 20px rgba(0, 0, 0, 0.08);
}

/* Bagian Kiri: Avatar & Identitas */
.row-left {
    display: flex;
    align-items: center;
    gap: 16px;
    min-width: 250px;
}

.avatar-circle {
    width: 48px;
    height: 48px;
    background: #1c2e1b;
    color: #ffffff;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-family: 'Poppins', sans-serif;
    font-size: 18px;
    font-weight: 700;
    box-shadow: 0 3px 8px rgba(28, 46, 27, 0.2);
    flex-shrink: 0;
}

.customer-name {
    font-family: 'Poppins', sans-serif;
    font-size: 16px;
    font-weight: 700;
    color: #112211;
    margin: 0;
    line-height: 1.2;
}

.badge-id {
    font-size: 11px;
    font-weight: 700;
    background: #E2EEDD;
    color: #1c2e1b;
    padding: 2px 8px;
    border-radius: 10px;
    display: inline-block;
    margin-top: 4px;
}

/* Bagian Tengah: Info Alamat & Telp */
.row-center {
    display: flex;
    gap: 30px;
    flex: 1;
}

.info-col {
    display: flex;
    align-items: center;
    gap: 8px;
    font-size: 13.5px;
    color: #2d422a;
    font-weight: 600;
}

.info-col i {
    color: #556B2F;
}

/* Bagian Kanan: Tombol Aksi */
.row-actions {
    display: flex;
    gap: 8px;
}

.btn-aksi {
    padding: 8px 14px;
    border-radius: 8px;
    font-size: 12.5px;
    font-weight: 700;
    text-decoration: none;
    display: inline-flex;
    align-items: center;
    gap: 6px;
    transition: all 0.2s ease;
}

.btn-edit {
    background-color: #556B2F;
    color: #ffffff;
}

.btn-edit:hover {
    background-color: #3f5122;
    box-shadow: 0 3px 8px rgba(85, 107, 47, 0.3);
}

.btn-hapus {
    background-color: #FFF5F5;
    color: #E74C3C;
    border: 1px solid #FADBD8;
}

.btn-hapus:hover {
    background-color: #E74C3C;
    color: #ffffff;
    box-shadow: 0 3px 8px rgba(231, 76, 60, 0.2);
}

.btn-tambah {
    transition: all 0.3s ease;
}

.btn-tambah:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 20px rgba(28, 46, 27, 0.3) !important;
}

/* Responsif untuk layar kecil/mobile */
@media(max-width: 850px) {
    .customer-row-item {
        flex-direction: column;
        align-items: flex-start;
        gap: 14px;
    }
    .row-center {
        flex-direction: column;
        gap: 6px;
        width: 100%;
    }
    .row-actions {
        width: 100%;
        justify-content: flex-end;
    }
}
</style>

<!-- max-width disamakan 1200px seperti halaman produk agar posisi lebarnya persis sama -->
<div style="max-width: 1200px; margin: 30px auto; padding: 0 20px;">
    <!-- Header Halaman: Tanpa margin samping tambahan agar lurus rata kiri -->
    <div class="animate-header" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 25px;">
        <div>
            <h2 style="margin: 0; font-family: 'Poppins', sans-serif; font-size: 28px; font-weight: 800; color: #112211; text-shadow: 0 2px 10px rgba(255, 255, 255, 0.6); display: flex; align-items: center; gap: 12px;">
                <!-- Ikon Pelanggan -->
                <i class="fa-solid fa-users" style="font-size: 24px; color: #1c2e1b;"></i>
                Data Pelanggan Kasir
            </h2>
            <p style="margin: 6px 0 0 0; font-size: 14.5px; color: #2d422a; font-weight: 600;">
                Daftar pelanggan terdaftar pada sistem kasir.
            </p>
        </div>
        
        <div>
            <a href="tambah_pelanggan.php" class="btn-tambah" style="background-color: #1c2e1b; color: white; padding: 10px 22px; border-radius: 30px; text-decoration: none; font-weight: 700; font-size: 14px; box-shadow: 0 4px 15px rgba(28, 46, 27, 0.25); display: inline-flex; align-items: center; gap: 8px;">
                <i class="fa-solid fa-plus"></i> Tambah Pelanggan
            </a>
        </div>
    </div>

    <!-- Tampilan List Baris Memanjang -->
    <div class="customer-list-container">
        <?php
        $query = mysqli_query($koneksi, "SELECT * FROM pelanggan ORDER BY PelangganID DESC");
        if ($query && mysqli_num_rows($query) > 0) {
            while ($row = mysqli_fetch_assoc($query)) {
                $initial = strtoupper(substr($row['NamaPelanggan'], 0, 1));
        ?>
            <div class="customer-row-item">
                <!-- Kiri: Avatar & Nama -->
                <div class="row-left">
                    <div class="avatar-circle">
                        <?php echo $initial; ?>
                    </div>
                    <div>
                        <h3 class="customer-name"><?php echo htmlspecialchars($row['NamaPelanggan']); ?></h3>
                        <span class="badge-id">ID #<?php echo $row['PelangganID']; ?></span>
                    </div>
                </div>

                <!-- Tengah: Alamat & Telepon -->
                <div class="row-center">
                    <div class="info-col">
                        <i class="fa-solid fa-location-dot"></i>
                        <span><?php echo htmlspecialchars($row['Alamat']); ?></span>
                    </div>
                    <div class="info-col">
                        <i class="fa-solid fa-phone"></i>
                        <span><?php echo htmlspecialchars($row['NomorTelepon']); ?></span>
                    </div>
                </div>

                <!-- Kanan: Tombol Aksi -->
                <div class="row-actions">
                    <a href="edit_pelanggan.php?id=<?php echo $row['PelangganID']; ?>" class="btn-aksi btn-edit">
                        <i class="fa-solid fa-pen-to-square"></i> Edit
                    </a>
                    <a href="hapus_pelanggan.php?id=<?php echo $row['PelangganID']; ?>" onclick="return confirm('Yakin ingin menghapus pelanggan ini?')" class="btn-aksi btn-hapus">
                        <i class="fa-solid fa-trash-can"></i> Hapus
                    </a>
                </div>
            </div>
        <?php 
            } 
        } else {
            echo "<div style='text-align: center; padding: 40px; color: #2d422a; background: rgba(255,255,255,0.85); backdrop-filter: blur(10px); border-radius: 14px; font-weight: 700;'>Belum ada data pelanggan tersimpan.</div>";
        }
        ?>
    </div>
</div>

</body>
</html>