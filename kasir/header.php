<?php
// Pastikan session sudah aktif
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Cek status login dan level Kasir (Proteksi keamanan global halaman kasir)
if (!isset($_SESSION['status']) || $_SESSION['status'] != "login" || $_SESSION['level'] != "kasir") {
    header("location:../login.php?pesan=belum_login");
    exit;
}

// Cek halaman yang sedang aktif untuk penanda menu (Active State)
$current_page = basename($_SERVER['PHP_SELF']);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Aplikasi Kasir Nis & Co - Kasir</title>
    <link rel="icon" type="image/png" href="../assets/img/icon.png">

    <!-- Google Fonts & Font Awesome Icon -->
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <style>
        * {
            box-sizing: border-box;
            font-family: 'Plus Jakarta Sans', sans-serif;
            margin: 0;
            padding: 0;
        }

        body {
            background-color: #d8dfd5;
            background-image: 
                radial-gradient(circle at 10% 20%, rgba(255, 255, 255, 0.5) 0%, transparent 40%),
                radial-gradient(circle at 90% 80%, rgba(255, 255, 255, 0.5) 0%, transparent 40%);
            min-height: 100vh;
            color: #2c3e2e;
            padding-bottom: 40px;
        }

        /* Top Floating Navbar - Dibuat Bulat (Pill Shape) */
        .navbar-wrapper {
            position: sticky;
            top: 15px;
            z-index: 1000;
            padding: 0 20px;
        }

        .navbar {
            background: rgba(255, 255, 255, 0.85);
            backdrop-filter: blur(16px);
            -webkit-backdrop-filter: blur(16px);
            border: 1px solid rgba(255, 255, 255, 0.9);
            padding: 10px 24px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-radius: 50px;
            max-width: 1250px;
            margin: 0 auto;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.06);
        }

        .brand {
            display: flex;
            align-items: center;
            gap: 12px;
            text-decoration: none;
            color: #2c3e2e;
        }

        .brand-icon {
            background: #2E472E;
            color: white;
            width: 40px;
            height: 40px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 18px;
            box-shadow: 0 4px 12px rgba(46, 71, 46, 0.25);
        }

        .brand-text {
            font-size: 22px;
            font-weight: 700;
            letter-spacing: -0.5px;
        }

        .nav-menu {
            display: flex;
            align-items: center;
            gap: 6px;
            list-style: none;
        }

        .nav-link {
            display: flex;
            align-items: center;
            gap: 8px;
            padding: 10px 16px;
            color: #2c3e2e;
            text-decoration: none;
            font-weight: 600;
            font-size: 14px;
            border-radius: 30px;
            transition: all 0.25s ease;
            opacity: 0.85;
        }

        .nav-link:hover {
            opacity: 1;
            background: rgba(46, 71, 46, 0.12);
            color: #1A2E1A;
            transform: translateY(-1px);
        }

        /* Menu Aktif */
        .nav-link.active {
            opacity: 1;
            background: #2E472E;
            color: #ffffff;
            box-shadow: 0 4px 14px rgba(46, 71, 46, 0.3);
        }

        /* Container Tombol di Ujung Kanan */
        .nav-right-actions {
            display: flex;
            align-items: center;
            gap: 8px;
        }

        /* Tombol Login Admin di Header Kasir */
        .nav-link-admin {
            display: flex;
            align-items: center;
            gap: 6px;
            padding: 10px 16px;
            color: #1c2e1b;
            text-decoration: none;
            font-weight: 700;
            font-size: 13.5px;
            border-radius: 30px;
            border: 1px solid rgba(28, 46, 27, 0.25);
            background: rgba(235, 242, 231, 0.8);
            transition: all 0.25s ease;
        }

        .nav-link-admin:hover {
            background: #1c2e1b;
            color: #ffffff;
            border-color: #1c2e1b;
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(28, 46, 27, 0.25);
        }

        /* Tombol Keluar Khusus di Ujung Kanan */
        .nav-link-logout {
            display: flex;
            align-items: center;
            gap: 6px;
            padding: 10px 16px;
            color: #991b1b;
            text-decoration: none;
            font-weight: 700;
            font-size: 13.5px;
            border-radius: 30px;
            border: 1px solid rgba(239, 68, 68, 0.3);
            background: rgba(254, 226, 226, 0.6);
            transition: all 0.25s ease;
        }

        .nav-link-logout:hover {
            background: #dc2626;
            color: #ffffff;
            border-color: #dc2626;
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(220, 38, 38, 0.25);
        }

        /* Container Isi Halaman */
        .container {
            max-width: 1200px;
            margin: 25px auto;
            padding: 0 20px;
        }
    </style>
</head>
<body>

    <div class="navbar-wrapper">
        <nav class="navbar">
            <a href="dashboard.php" class="brand">
                <div class="brand-icon"><i class="fa-solid fa-utensils"></i></div>
                <span class="brand-text">Nis & Co</span>
            </a>

            <ul class="nav-menu">
                <li>
                    <a href="dashboard.php" class="nav-link <?= ($current_page == 'dashboard.php') ? 'active' : ''; ?>">
                        <i class="fa-solid fa-house"></i> Dashboard
                    </a>
                </li>
                <li>
                    <a href="produk.php" class="nav-link <?= ($current_page == 'produk.php') ? 'active' : ''; ?>">
                        <i class="fa-solid fa-box-open"></i> Produk
                    </a>
                </li>
                <li>
                    <a href="pelanggan.php" class="nav-link <?= ($current_page == 'pelanggan.php') ? 'active' : ''; ?>">
                        <i class="fa-solid fa-users"></i> Pelanggan
                    </a>
                </li>
                <li>
                    <a href="penjualan.php" class="nav-link <?= ($current_page == 'penjualan.php') ? 'active' : ''; ?>">
                        <i class="fa-solid fa-cart-shopping"></i> Penjualan
                    </a>
                </li>
                <li>
                    <a href="riwayat_penjualan.php" class="nav-link <?= ($current_page == 'riwayat_penjualan.php') ? 'active' : ''; ?>">
                        <i class="fa-solid fa-clock-rotate-left"></i> Riwayat
                    </a>
                </li>
            </ul>

            <!-- Bagian Kanan: Tombol Login Admin & Tombol Keluar -->
            <div class="nav-right-actions">
                <a href="../login.php" class="nav-link-admin" title="Pindah ke halaman login admin">
                    <i class="fa-solid fa-user-shield"></i> Login Admin
                </a>
                <a href="../logout.php" class="nav-link-logout" onclick="return confirm('Apakah Anda yakin ingin keluar?');" title="Keluar Sistem">
                    <i class="fa-solid fa-right-from-bracket"></i> Keluar
                </a>
            </div>
        </nav>
    </div>

    <div class="container">