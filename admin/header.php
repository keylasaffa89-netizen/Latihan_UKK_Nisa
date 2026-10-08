<!DOCTYPE html>
<html>
<head>
    <title>Aplikasi Kasir Nis & Co - Admin</title>
    <link rel="icon" type="image/png" href="../assets/img/icon.png">
    <!-- Import Google Font: Poppins -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <!-- Import Font Awesome Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <style>
        body { 
            font-family: 'Poppins', sans-serif !important; 
            background-image: url('assets/img/bgadmin.jfif');
            background-size: cover;        
            background-position: center;   
            background-repeat: no-repeat;  
            background-attachment: fixed;  
            color: #2C3531;
            margin: 0;
            padding: 0;
        }

        /* Header & Navigasi Super Estetik ala Dashboard Sora */
        header {
            background: linear-gradient(135deg, rgba(44, 62, 45, 0.95), rgba(31, 46, 32, 0.95));
            backdrop-filter: blur(15px);
            color: white;
            padding: 14px 28px;
            margin: 24px auto 0 auto;
            max-width: 1060px;
            border-radius: 24px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: 0 12px 35px rgba(0, 0, 0, 0.15);
            border: 1px solid rgba(255, 255, 255, 0.15);
        }

        .logo-area {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        /* Ikon Logo Mewah */
        .custom-logo-icon {
            width: 42px;
            height: 42px;
            background: rgba(255, 255, 255, 0.12);
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #FFFFFF;
            font-size: 18px;
            border: 1px solid rgba(255, 255, 255, 0.1);
            box-shadow: inset 0 2px 4px rgba(255, 255, 255, 0.1);
        }

        header h1 {
            margin: 0;
            font-size: 21px;
            font-weight: 700;
            letter-spacing: 0.8px;
            font-family: 'Poppins', sans-serif;
        }

        nav {
            display: flex;
            gap: 6px;
            align-items: center;
        }

        nav a {
            color: #D1DBD0;
            text-decoration: none;
            font-weight: 500;
            font-size: 13px;
            padding: 9px 15px;
            border-radius: 14px;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            display: inline-flex;
            align-items: center;
            gap: 8px;
            font-family: 'Poppins', sans-serif;
        }

        nav a:hover {
            background-color: rgba(255, 255, 255, 0.1);
            color: #FFFFFF;
        }

        /* Menu Aktif yang Lebih Menonjol & Elegan */
        nav a.active {
            background-color: #FFFFFF;
            color: #2C3E2D;
            font-weight: 600;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
            transform: translateY(-1px);
        }

        /* Tombol Logout Seragam (Berubah Merah Saat Hover) */
        nav a.logout-btn {
            color: #D1DBD0;
            background: transparent;
            border: none;
            margin-left: 4px;
        }

        nav a.logout-btn:hover {
            background-color: rgba(217, 83, 79, 0.25);
            color: #ffcccc;
            transform: translateY(-1px);
        }

        /* Container Konten */
        .container {
            padding: 30px 40px;
            max-width: 1100px;
            margin: 0 auto;
        }

        h2 { 
            color: #4A5D4E; 
            margin-bottom: 25px;
            font-size: 24px;
            font-weight: 600;
            font-family: 'Poppins', sans-serif;
        }

        /* Tabel Estetik */
        .container table { 
            width: 100%; 
            border-collapse: separate; 
            border-spacing: 0;
            background-color: rgba(255, 255, 255, 0.92); 
            backdrop-filter: blur(5px);
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.08);
            border-radius: 16px;
            overflow: hidden;
            font-family: 'Poppins', sans-serif;
        }

        .container th, .container td { 
            padding: 16px 20px; 
            text-align: left; 
            font-size: 14px;
            font-family: 'Poppins', sans-serif;
        }

        .container th { 
            background-color: #2C3E2D; 
            color: white; 
            font-weight: 600;
            letter-spacing: 0.5px;
        }

        .container tr:nth-child(even) {
            background-color: rgba(249, 248, 246, 0.6);
        }

        .container tr:hover {
            background-color: rgba(239, 236, 230, 0.85);
            transition: background 0.2s ease;
        }

        .container td {
            border-bottom: 1px solid rgba(239, 236, 230, 0.8);
        }
    </style>
</head>
<body>

    <header>
        <div class="logo-area">
            <div class="custom-logo-icon">
                <i class="fa-solid fa-utensils"></i>
            </div>
            <h1>Nis & Co</h1>
        </div>
        <nav>
            <?php 
            $page = basename($_SERVER['PHP_SELF']); 
            ?>
            <a href="dashboard.php" class="<?= ($page == 'dashboard.php') ? 'active' : ''; ?>">
                <i class="fa-solid fa-house"></i> Dashboard
            </a>
            <a href="produk.php" class="<?= ($page == 'produk.php') ? 'active' : ''; ?>">
                <i class="fa-solid fa-box"></i> Produk
            </a>
            <a href="pelanggan.php" class="<?= ($page == 'pelanggan.php') ? 'active' : ''; ?>">
                <i class="fa-solid fa-users"></i> Pelanggan
            </a>
            <a href="penjualan.php" class="<?= ($page == 'penjualan.php' || $page == 'tambah_penjualan.php' || $page == 'detail_penjualan.php') ? 'active' : ''; ?>">
                <i class="fa-solid fa-cart-shopping"></i> Penjualan
            </a>
            <a href="laporan.php" class="<?= ($page == 'laporan.php') ? 'active' : ''; ?>">
                <i class="fa-solid fa-file-invoice"></i> Laporan
            </a>
            <a href="logout_admin.php" class="logout-btn" onclick="return confirm('Yakin ingin keluar dari aplikasi?')">
                <i class="fa-solid fa-right-from-bracket"></i> Keluar
            </a>
        </nav>
    </header>