<?php
session_start();

// Proteksi keamanan halaman tanpa simbol garis vertikal
if (!isset($_SESSION['status'])) {
    header("location:../login.php?pesan=belum_login");
    exit;
}

if ($_SESSION['status'] != "login") {
    header("location:../login.php?pesan=belum_login");
    exit;
}

if ($_SESSION['level'] != "admin") {
    header("location:../login.php?pesan=akses_ditolak");
    exit;
}

// Naik satu direktori karena koneksi.php berada di folder utama project
include '../koneksi.php';

// Proses Update Data jika tombol 'update' ditekan di halaman ini sendiri
if (isset($_POST['update'])) {
    $pelanggan_id   = mysqli_real_escape_string($koneksi, $_POST['PelangganID']);
    $nama_pelanggan = mysqli_real_escape_string($koneksi, $_POST['NamaPelanggan']);
    $alamat         = mysqli_real_escape_string($koneksi, $_POST['Alamat']);
    $nomor_telepon  = mysqli_real_escape_string($koneksi, $_POST['NomorTelepon']);

    if (empty($pelanggan_id) || empty($nama_pelanggan) || empty($alamat) || empty($nomor_telepon)) {
        echo "<script>alert('Semua kolom wajib diisi!'); window.history.back();</script>";
        exit;
    }

    $query_update = mysqli_query($koneksi, "UPDATE pelanggan SET NamaPelanggan = '$nama_pelanggan', Alamat = '$alamat', NomorTelepon = '$nomor_telepon' WHERE PelangganID = '$pelanggan_id'");

    if ($query_update) {
        echo "<script>alert('Data pelanggan berhasil diperbarui!'); window.location='pelanggan.php';</script>";
        exit;
    } else {
        echo "<script>alert('Gagal memperbarui data: " . mysqli_error($koneksi) . "'); window.history.back();</script>";
        exit;
    }
}

// Ambil ID dari URL untuk ditampilkan di form
if (isset($_GET['id'])) {
    $id = $_GET['id'];
    $query = mysqli_query($koneksi, "SELECT * FROM pelanggan WHERE PelangganID = '$id'");
    $row = mysqli_fetch_assoc($query);

    if (!$row) {
        echo "<script>alert('Data pelanggan tidak ditemukan!'); window.location='pelanggan.php';</script>";
        exit;
    }
} else {
    header("Location: pelanggan.php");
    exit;
}

include 'header.php';
?>

<style>
    /* Styling Background Halaman */
    body {
        background-image: url('../assets/img/bgadmin.jfif');
        background-size: cover;        
        background-position: center;   
        background-repeat: no-repeat;  
        background-attachment: fixed;  
    }

    /* Keyframe Animations */
    @keyframes fadeInUp {
        from {
            opacity: 0;
            transform: translateY(20px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    /* Container Pembungkus Halaman */
    .page-container {
        padding: 40px;
        max-width: 1100px;
        margin: 0 auto;
        box-sizing: border-box;
        animation: fadeInUp 0.6s ease-out forwards;
    }

    /* Outer Wrapper Form agar Rapi */
    .form-wrapper {
        max-width: 650px;
    }

    /* Form Card dengan Glassmorphism */
    .form-card {
        background-color: rgba(255, 255, 255, 0.95);
        backdrop-filter: blur(5px);
        padding: 35px;
        border-radius: 16px;
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
        transition: transform 0.3s ease, box-shadow 0.3s ease;
    }

    .form-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 15px 35px rgba(0, 0, 0, 0.15);
    }

    /* Input Styling Tema Sora (Editable) */
    .form-control-editable {
        width: 100%;
        padding: 12px 16px;
        border: 1px solid #C8D3C4;
        border-radius: 10px;
        font-family: 'Poppins', sans-serif;
        font-size: 14px;
        background-color: #FFFFFF;
        color: #4A5D48;
        box-sizing: border-box;
        outline: none;
        transition: border-color 0.3s ease, box-shadow 0.3s ease;
    }

    .form-control-editable:focus {
        border-color: #2C3E2D;
        box-shadow: 0 0 0 3px rgba(44, 62, 45, 0.15);
    }

    /* Wrapper Tombol Aksi */
    .button-group {
        display: flex;
        gap: 12px;
        align-items: center;
        margin-top: 25px;
    }

    /* Tombol Simpan Perubahan Tema Sora */
    .btn-submit {
        background-color: #2C3E2D;
        color: white;
        padding: 12px 24px;
        border-radius: 10px;
        border: none;
        cursor: pointer;
        font-family: 'Poppins', sans-serif;
        font-weight: 500;
        font-size: 14px;
        display: inline-flex;
        align-items: center;
        box-shadow: 0 4px 12px rgba(44, 62, 45, 0.3);
        transition: all 0.3s ease;
    }

    .btn-submit:hover {
        background-color: #1F2E20;
        transform: translateY(-2px);
        box-shadow: 0 6px 16px rgba(44, 62, 45, 0.4);
    }

    /* Tombol Kembali Tema Sora */
    .btn-back {
        background-color: #6c757d;
        color: white;
        padding: 12px 24px;
        border-radius: 10px;
        text-decoration: none;
        font-family: 'Poppins', sans-serif;
        font-weight: 500;
        font-size: 14px;
        display: inline-flex;
        align-items: center;
        box-shadow: 0 4px 12px rgba(108, 117, 125, 0.3);
        transition: all 0.3s ease;
    }

    .btn-back:hover {
        background-color: #5a6268;
        transform: translateY(-2px);
        box-shadow: 0 6px 16px rgba(108, 117, 125, 0.4);
    }
</style>

<div class="page-container">
    <div class="form-wrapper">
        <!-- Bagian Judul Form -->
        <div style="margin-bottom: 25px;">
            <h2 style="margin: 0; font-size: 26px; font-weight: 700; color: #2C3E2D; display: flex; align-items: center; gap: 12px;">
                <span style="background-color: #2C3E2D; padding: 10px; border-radius: 12px; display: flex; align-items: center; justify-content: center; box-shadow: 0 4px 10px rgba(44, 62, 45, 0.25);">
                    <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path>
                        <circle cx="9" cy="7" r="4"></circle>
                        <path d="M23 21v-2a4 4 0 0 0-3-3.87"></path>
                        <path d="M16 3.13a4 4 0 0 1 0 7.75"></path>
                    </svg>
                </span>
                Edit Pelanggan
            </h2>
            <p style="margin: 5px 0 0 54px; font-size: 14px; color: #4A5D48;">
                Silakan ubah informasi data pelanggan Aplikasi Nis & Co di bawah ini.
            </p>
        </div>

        <!-- Form Card -->
        <div class="form-card">
            <div style="background-color: rgba(235, 240, 233, 0.8); border-left: 4px solid #2C3E2D; padding: 12px 16px; margin-bottom: 20px; border-radius: 8px; font-size: 13px; color: #2C3E2D;">
                <strong>Catatan:</strong> Pastikan data yang diubah sudah benar sebelum menekan tombol simpan.
            </div>

            <!-- Action dikosongkan agar proses eksekusi langsung di file ini -->
            <form action="" method="POST">
                <!-- Input hidden untuk mengirim PelangganID -->
                <input type="hidden" name="PelangganID" value="<?php echo htmlspecialchars($row['PelangganID']); ?>">

                <div style="margin-bottom: 20px;">
                    <label style="display: block; font-weight: 600; color: #2C3E2D; margin-bottom: 8px; font-size: 14px;">Nama Pelanggan</label>
                    <input type="text" name="NamaPelanggan" value="<?php echo htmlspecialchars($row['NamaPelanggan']); ?>" required class="form-control-editable">
                </div>

                <div style="margin-bottom: 20px;">
                    <label style="display: block; font-weight: 600; color: #2C3E2D; margin-bottom: 8px; font-size: 14px;">Alamat</label>
                    <input type="text" name="Alamat" value="<?php echo htmlspecialchars($row['Alamat']); ?>" required class="form-control-editable">
                </div>

                <div style="margin-bottom: 25px;">
                    <label style="display: block; font-weight: 600; color: #2C3E2D; margin-bottom: 8px; font-size: 14px;">Nomor Telepon</label>
                    <input type="text" name="NomorTelepon" value="<?php echo htmlspecialchars($row['NomorTelepon']); ?>" required class="form-control-editable">
                </div>

                <div class="button-group">
                    <button type="submit" name="update" class="btn-submit">
                        Simpan Perubahan
                    </button>
                    <a href="pelanggan.php" class="btn-back">
                        Kembali
                    </a>
                </div>
            </form>
        </div>
    </div>
</div>

</body>
</html>