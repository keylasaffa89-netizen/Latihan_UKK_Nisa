<?php
session_start();

// Perbaikan: Tambahkan pengecekan session agar file aman dan konsisten
if (!isset($_SESSION['status']) || $_SESSION['status'] != "login" || $_SESSION['role'] != "admin") {
    header("location:../login.php?pesan=belum_login");
    exit;
}

// Naik satu direktori karena koneksi.php berada di folder utama project
include '../koneksi.php';

// Proses simpan data pelanggan jika tombol submit ditekan
if (isset($_POST['submit'])) {
    $nama = mysqli_real_escape_string($koneksi, $_POST['NamaPelanggan']);
    $alamat = mysqli_real_escape_string($koneksi, $_POST['Alamat']);
    $telepon = mysqli_real_escape_string($koneksi, $_POST['NomorTelepon']);

    $query = mysqli_query($koneksi, "INSERT INTO pelanggan (NamaPelanggan, Alamat, NomorTelepon) VALUES ('$nama', '$alamat', '$telepon')");

    if ($query) {
        echo "<script>alert('Data pelanggan berhasil ditambahkan!'); window.location='pelanggan.php';</script>";
    } else {
        echo "<script>alert('Gagal menambahkan pelanggan!');</script>";
    }
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

    /* Input Custom Styling & Focus Effect Tema Sora */
    .form-control {
        width: 100%;
        padding: 12px 16px;
        border: 1px solid #C8D3C4;
        border-radius: 10px;
        font-family: 'Poppins', sans-serif;
        font-size: 14px;
        background-color: rgba(249, 248, 246, 0.7);
        box-sizing: border-box;
        outline: none;
        color: #2C3E2D;
        transition: all 0.3s ease;
    }

    .form-control:focus {
        border-color: #2C3E2D;
        background-color: #ffffff;
        box-shadow: 0 0 0 4px rgba(44, 62, 45, 0.15);
    }

    /* Transisi Tombol Tema Sora */
    .btn-submit {
        background-color: #2C3E2D;
        color: white;
        border: none;
        padding: 12px 24px;
        border-radius: 10px;
        font-family: 'Poppins', sans-serif;
        font-weight: 500;
        font-size: 14px;
        cursor: pointer;
        box-shadow: 0 4px 12px rgba(44, 62, 45, 0.3);
        transition: all 0.3s ease;
    }

    .btn-submit:hover {
        background-color: #1F2E20;
        transform: translateY(-2px);
        box-shadow: 0 6px 16px rgba(44, 62, 45, 0.4);
    }

    .btn-submit:active {
        transform: translateY(0);
    }

    .btn-cancel {
        background-color: #E2E8DF;
        color: #2C3E2D;
        text-decoration: none;
        padding: 12px 24px;
        border-radius: 10px;
        font-family: 'Poppins', sans-serif;
        font-weight: 500;
        font-size: 14px;
        display: inline-flex;
        align-items: center;
        transition: all 0.3s ease;
    }

    .btn-cancel:hover {
        background-color: #d1dad0;
        transform: translateY(-2px);
    }

    .btn-cancel:active {
        transform: translateY(0);
    }
</style>

<div class="page-container">
    <div class="form-wrapper">
        <!-- Bagian Judul Form -->
        <div style="margin-bottom: 25px;">
            <h2 style="margin: 0; font-size: 26px; font-weight: 700; color: #2C3E2D; display: flex; align-items: center; gap: 12px;">
                <span style="background-color: #2C3E2D; padding: 10px; border-radius: 12px; display: flex; align-items: center; justify-content: center; box-shadow: 0 4px 10px rgba(44, 62, 45, 0.25);">
                    <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M16 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path>
                        <circle cx="8.5" cy="7" r="4"></circle>
                        <line x1="20" y1="8" x2="20" y2="14"></line>
                        <line x1="23" y1="11" x2="17" y2="11"></line>
                    </svg>
                </span>
                Tambah Data Pelanggan
            </h2>
            <p style="margin: 5px 0 0 54px; font-size: 14px; color: #4A5D48;">
                Masukkan informasi pelanggan baru ke dalam sistem kasir Aplikasi Nis & Co.
            </p>
        </div>

        <!-- Form Card -->
        <div class="form-card">
            <form action="" method="POST">
                
                <div style="margin-bottom: 20px;">
                    <label style="display: block; font-weight: 600; font-size: 14px; color: #2C3E2D; margin-bottom: 8px;">Nama Pelanggan</label>
                    <input type="text" name="NamaPelanggan" required placeholder="Masukkan nama pelanggan" class="form-control">
                </div>

                <div style="margin-bottom: 20px;">
                    <label style="display: block; font-weight: 600; font-size: 14px; color: #2C3E2D; margin-bottom: 8px;">Alamat</label>
                    <textarea name="Alamat" required rows="3" placeholder="Masukkan alamat" class="form-control" style="resize: vertical;"></textarea>
                </div>

                <div style="margin-bottom: 30px;">
                    <label style="display: block; font-weight: 600; font-size: 14px; color: #2C3E2D; margin-bottom: 8px;">Nomor Telepon</label>
                    <input type="text" name="NomorTelepon" required placeholder="Masukkan nomor telepon" class="form-control">
                </div>

                <!-- Tombol Aksi -->
                <div style="display: flex; gap: 12px;">
                    <button type="submit" name="submit" class="btn-submit">
                        Simpan Pelanggan
                    </button>
                    <a href="pelanggan.php" class="btn-cancel">
                        Batal
                    </a>
                </div>

            </form>
        </div>
    </div>
</div>

</body>
</html>