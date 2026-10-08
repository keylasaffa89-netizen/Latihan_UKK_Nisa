<?php
session_start();

// Hapus sesi admin lama
session_unset();
session_destroy();

// Buat sesi baru secara instan sebagai kasir agar lolos pengecekan satpam dashboard kasir
session_start();
$_SESSION['id_user']   = 1; 
$_SESSION['username']  = 'kasir';
$_SESSION['nama_user'] = 'Kasir';
$_SESSION['role']      = 'kasir';
$_SESSION['level']     = 'kasir';
$_SESSION['status']    = 'login';

// Karena file ini ada di dalam folder admin/, gunakan "../" untuk keluar ke folder utama lalu masuk ke folder kasir
header("Location: ../kasir/dashboard.php");
exit();
?>