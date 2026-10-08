<?php
session_start();

// Cek status login dan level Admin (Penting untuk keamanan)
if (!isset($_SESSION['status']) || $_SESSION['status'] != "login" || $_SESSION['level'] != "admin") {
    header("location:../login.php?pesan=belum_login");
    exit;
}

include '../koneksi.php';

if (isset($_GET['id'])) {
    $id = $_GET['id'];

    // 1. AMBIL DATA GAMBAR TERLEBIH DAHULU
    // Kita perlu tahu nama file gambarnya sebelum data produknya hilang
    $query_pilih = mysqli_query($koneksi, "SELECT * FROM produk WHERE ProdukID = '$id'");
    $data = mysqli_fetch_assoc($query_pilih);
    
    // Pastikan data produk ditemukan sebelum melakukan proses
    if (!$data) {
        echo "<script>alert('Data produk tidak ditemukan!'); window.location='produk.php';</script>";
        exit;
    }

    $nama_gambar = $data['gambar']; // Asumsi nama kolom di DB adalah 'gambar'

    // 2. HAPUS FILE FISIK DARI FOLDER assets/img/
    // Cek apakah nama gambar tidak kosong dan file benar-benar ada di folder
    if (!empty($nama_gambar) && file_exists("../assets/img/" . $nama_gambar)) {
        
        // KEAMANAN TAMBAHAN: Jangan hapus file default atau file sistem
        if ($nama_gambar != 'default.png' && $nama_gambar != 'bgadmin.jfif') {
            // Perintah untuk menghapus file fisik
            if (unlink("../assets/img/" . $nama_gambar)) {
                // File berhasil dihapus (opsional: bisa tambahkan log di sini)
            } else {
                // Gagal menghapus file (mungkin karena permission)
                // Anda bisa memilih untuk tetap melanjutkan hapus DB atau stop di sini
                // echo "<script>alert('Gagal menghapus file gambar fisik.');</script>";
            }
        }
    }

    // 3. HAPUS DATA DARI DATABASE
    // Setelah file gambar dibersihkan, baru hapus data produknya
    $query_hapus = mysqli_query($koneksi, "DELETE FROM produk WHERE ProdukID = '$id'");

    if ($query_hapus) {
        // HAPUS BERHASIL
        echo "<script>alert('Produk dan gambar terkait berhasil dihapus!'); window.location='produk.php';</script>";
    } else {
        // HAPUS GAGAL
        echo "<script>alert('Gagal menghapus data produk dari database!'); window.location='produk.php';</script>";
    }

} else {
    // Jika tidak ada ID di URL, kembalikan ke halaman produk
    header("Location: produk.php");
    exit;
}
?>