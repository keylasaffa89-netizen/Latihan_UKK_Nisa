<?php
session_start();

// Perbaikan: Tambahkan pengecekan session agar halaman hapus aman dari akses luar
if (!isset($_SESSION['status']) || $_SESSION['status'] != "login" || $_SESSION['level'] != "admin") {
    header("location:../login.php?pesan=belum_login");
    exit;
}

include '../koneksi.php';

if (isset($_GET['id'])) {
    $penjualan_id = $_GET['id'];

    // Hapus terlebih dahulu data terkait di tabel detailpenjualan (untuk menjaga integritas relasi foreign key)
    mysqli_query($koneksi, "DELETE FROM detailpenjualan WHERE PenjualanID = '$penjualan_id'");

    // Hapus data utama di tabel penjualan
    $query = mysqli_query($koneksi, "DELETE FROM penjualan WHERE PenjualanID = '$penjualan_id'");

    if ($query) {
        echo "<script>alert('Data penjualan berhasil dihapus!'); window.location='penjualan.php';</script>";
    } else {
        echo "<script>alert('Gagal menghapus data penjualan!'); window.location='penjualan.php';</script>";
    }
} else {
    header("Location: penjualan.php");
    exit;
}
?>