<?php
session_start();

// Perbaikan: Tambahkan pengecekan session agar halaman hapus pelanggan aman
if (!isset($_SESSION['status']) || $_SESSION['status'] != "login" || $_SESSION['level'] != "admin") {
    header("location:../login.php?pesan=belum_login");
    exit;
}

include '../koneksi.php'; // Diubah dari 'koneksi.php' menjadi '../koneksi.php'

if (isset($_GET['id'])) {
    $id = $_GET['id'];

    // Hapus data pelanggan berdasarkan PelangganID
    $query = mysqli_query($koneksi, "DELETE FROM pelanggan WHERE PelangganID = '$id'");

    if ($query) {
        echo "<script>alert('Data pelanggan berhasil dihapus!'); window.location='pelanggan.php';</script>";
    } else {
        echo "<script>alert('Gagal menghapus data pelanggan! (Kemungkinan masih terikat dengan data penjualan)'); window.location='pelanggan.php';</script>";
    }
} else {
    header("Location: pelanggan.php");
    exit;
}
?>