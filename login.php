<?php
session_start();
include 'koneksi.php'; 

$error = "";

if (isset($_POST['btn_login'])) {
    $u = mysqli_real_escape_string($koneksi, $_POST['username']);
    $p = $_POST['password'];

    $q = mysqli_query($koneksi, "SELECT * FROM user WHERE username='$u'");
    
    if ($q && mysqli_num_rows($q) > 0) {
        $row = mysqli_fetch_assoc($q);
        
        $db_password = $row['password'];
        
        if ($db_password == md5($p) || $db_password == $p) {
            
            $_SESSION['id_user']   = $row['id_user'] ?? ($row['UserID'] ?? 1);
            $_SESSION['username']  = $row['username'];
            $_SESSION['nama_user'] = $row['nama_user'] ?? $row['NamaUser'] ?? 'Pengguna';
            
            $role = strtolower(trim($row['role'] ?? $row['level'] ?? 'kasir'));
            
            $_SESSION['role']      = $role;
            $_SESSION['level']     = $role; 
            $_SESSION['status']    = "login";

            session_write_close();

            if ($role == 'admin') {
                header("Location: /Latihan_UKK_Nisa/admin/dashboard.php");
                exit();
            } else {
                header("Location: /Latihan_UKK_Nisa/kasir/dashboard.php");
                exit();
            }
        } else {
            $error = "Password yang Anda masukkan salah!";
        }
    } else {
        $error = "Username tidak ditemukan di database!";
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Nis & Co</title>
    <link rel="icon" type="image/png" href="assets/img/icon.png">
    <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:wght@600;700&family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; font-family: 'Plus Jakarta Sans', sans-serif; }
        body {
            background: linear-gradient(135deg, #E6F0E4 0%, #D2E4CE 100%);
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            position: relative;
            padding: 20px;
            overflow: hidden;
        }
        
        /* --- ANIMASI LEMBUT & HALUS --- */
        @keyframes floatBubble {
            0% { transform: translateY(0px) scale(1); }
            50% { transform: translateY(-15px) scale(1.03); }
            100% { transform: translateY(0px) scale(1); }
        }

        @keyframes cardFadeIn {
            0% { opacity: 0; transform: translateY(20px) scale(0.98); }
            100% { opacity: 1; transform: translateY(0) scale(1); }
        }

        /* Gelembung dengan animasi pelan */
        .bg-bubble {
            position: fixed;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.45);
            filter: blur(18px);
            z-index: 0;
            pointer-events: none;
            animation: floatBubble 8s ease-in-out infinite;
        }
        .bubble-1 { width: 380px; height: 380px; top: -60px; left: -60px; animation-duration: 9s; }
        .bubble-2 { width: 420px; height: 420px; bottom: -60px; right: -60px; background: rgba(175, 205, 170, 0.4); animation-duration: 11s; }

        /* Kartu Login dengan durasi lambat & lembut (1.2 detik) */
        .login-card {
            position: relative;
            z-index: 2;
            width: 100%;
            max-width: 440px;
            background: rgba(255, 255, 255, 0.9);
            backdrop-filter: blur(25px);
            border: 1px solid rgba(255, 255, 255, 1);
            border-radius: 32px;
            padding: 45px 40px;
            box-shadow: 0 25px 50px rgba(53, 79, 51, 0.12);
            animation: cardFadeIn 1.2s cubic-bezier(0.16, 1, 0.3, 1) forwards;
        }

        .brand-header { display: flex; align-items: center; gap: 16px; margin-bottom: 30px; }
        .brand-icon {
            width: 52px; height: 52px;
            background: linear-gradient(135deg, #3B5A3B 0%, #2A402A 100%);
            border-radius: 16px;
            display: flex; justify-content: center; align-items: center;
            transition: transform 0.5s ease;
        }
        .brand-icon:hover { transform: rotate(6deg) scale(1.05); }
        .brand-icon svg { width: 24px; height: 24px; fill: #ffffff; }
        .brand-title h1 { font-family: 'Cormorant Garamond', serif; font-size: 34px; color: #243824; line-height: 1; }
        .brand-title span { font-size: 10px; font-weight: 700; color: #52796F; text-transform: uppercase; letter-spacing: 1.5px; margin-top: 5px; display: block; }
        
        .error-alert { background: #FDF2F2; border: 1px solid #F8B4B4; color: #9B2C2C; padding: 12px; border-radius: 14px; font-size: 12.5px; text-align: center; margin-bottom: 22px; }
        
        .field-group { margin-bottom: 18px; }
        .field-group label { display: block; font-size: 11px; font-weight: 700; color: #3B5A3B; margin-bottom: 7px; text-transform: uppercase; }
        .field-group input { 
            width: 100%; 
            padding: 14px 18px; 
            background: rgba(244, 247, 242, 0.8); 
            border: 1.5px solid #C8D6C3; 
            border-radius: 16px; 
            font-size: 14px; 
            color: #243824; 
            outline: none; 
            transition: all 0.4s ease; 
        }
        .field-group input:focus {
            border-color: #3B5A3B;
            background: #ffffff;
            box-shadow: 0 0 0 4px rgba(59, 90, 59, 0.1);
        }

        .btn-action { 
            width: 100%; 
            padding: 15px; 
            background: #3B5A3B; 
            color: white; 
            border: none; 
            border-radius: 16px; 
            font-weight: 700; 
            font-size: 14px; 
            cursor: pointer; 
            margin-top: 10px; 
            box-shadow: 0 10px 25px rgba(59, 90, 59, 0.25); 
            transition: all 0.4s ease; 
        }
        .btn-action:hover {
            background: #2D462D;
            transform: translateY(-2px);
            box-shadow: 0 14px 30px rgba(59, 90, 59, 0.35);
        }

        .card-footer { text-align: center; margin-top: 25px; font-size: 11.5px; color: #6A8E67; border-top: 1px solid rgba(106, 142, 103, 0.15); padding-top: 18px; }
    </style>
</head>
<body>

    <!-- Gelembung Background dengan Animasi Mengalir -->
    <div class="bg-bubble bubble-1"></div>
    <div class="bg-bubble bubble-2"></div>

    <div class="login-card">
        <div class="brand-header">
            <div class="brand-icon">
                <svg viewBox="0 0 24 24"><path d="M11 9H9V2H7v7H5V2H3v7c0 2.21 1.79 4 4 4v9h2v-9c2.21 0 4-1.79 4-4V2h-2v7zm3-7v12h2v8h2v-20h-4z"/></svg>
            </div>
            <div class="brand-title">
                <h1>Nis & Co</h1>
                <span>Sistem Kasir Digital</span>
            </div>
        </div>

        <?php if (!empty($error)): ?>
            <div class="error-alert"><?= $error; ?></div>
        <?php endif; ?>

        <form action="" method="POST">
            <div class="field-group">
                <label>Username</label>
                <input type="text" name="username" required autocomplete="off" placeholder="Masukkan username">
            </div>

            <div class="field-group">
                <label>Password</label>
                <input type="password" name="password" required placeholder="Masukkan password">
            </div>

            <button type="submit" name="btn_login" class="btn-action">Masuk</button>
        </form>

        <!-- Tombol kembali khusus jika diakses dari kasir yang sudah login -->
        <?php if (isset($_SESSION['status']) && $_SESSION['status'] == "login" && $_SESSION['level'] == "kasir"): ?>
            <div style="margin-top: 15px; text-align: center;">
                <a href="kasir/dashboard.php" style="display: inline-flex; align-items: center; gap: 8px; font-size: 13px; font-weight: 600; color: #3B5A3B; text-decoration: none; background: rgba(59, 90, 59, 0.1); padding: 10px 18px; border-radius: 12px; transition: all 0.3s ease;">
                    <i class="fa-solid fa-arrow-left"></i> Kembali ke Dashboard Kasir
                </a>
            </div>
        <?php endif; ?>

        <div class="card-footer">&copy; 2026 Nis & Co &bull; n</div>
    </div>

</body>
</html>