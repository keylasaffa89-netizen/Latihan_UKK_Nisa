<?php
session_start();

// Cek status login dan level Admin (Proteksi keamanan halaman edit)
if (!isset($_SESSION['status']) || $_SESSION['status'] != "login" || $_SESSION['level'] != "admin") {
    header("location:../login.php?pesan=belum_login");
    exit;
}

// Naik satu direktori karena koneksi.php berada di folder utama project
include '../koneksi.php';

// Ambil ID dari URL
if (isset($_GET['id'])) {
    $id = $_GET['id'];
    $query = mysqli_query($koneksi, "SELECT * FROM produk WHERE ProdukID = '$id'");
    $row = mysqli_fetch_assoc($query);

    if (!$row) {
        echo "<script>alert('Data produk tidak ditemukan!'); window.location='produk.php';</script>";
        exit;
    }
} else {
    header("Location: produk.php");
    exit;
}

// Proses update data ketika tombol Simpan ditekan
if (isset($_POST['update'])) {
    $namaproduk = mysqli_real_escape_string($koneksi, $_POST['namaproduk']);
    $harga = mysqli_real_escape_string($koneksi, $_POST['harga']);
    $stok = mysqli_real_escape_string($koneksi, $_POST['stok']);

    // Cek apakah user mengupload gambar baru
    if (isset($_FILES['gambar']) && $_FILES['gambar']['error'] === UPLOAD_ERR_OK) {
        $nama_file = $_FILES['gambar']['name'];
        $lokasi_tmp = $_FILES['gambar']['tmp_name'];
        $ekstensi_diperbolehkan = array('png', 'jpg', 'jpeg', 'jfif', 'webp');
        $x = explode('.', $nama_file);
        $ekstensi = strtolower(end($x));
        
        if (in_array($ekstensi, $ekstensi_diperbolehkan) === true) {
            $nama_gambar_baru = uniqid() . '.' . $ekstensi;
            $tujuan = '../assets/img/' . $nama_gambar_baru;

            if (move_uploaded_file($lokasi_tmp, $tujuan)) {
                // UPDATE MENGGUNAKAN KOLOM 'gambar' (BUKAN 'Foto')
                $update = mysqli_query($koneksi, "UPDATE produk SET NamaProduk='$namaproduk', Harga='$harga', Stok='$stok', gambar='$nama_gambar_baru' WHERE ProdukID='$id'");
            } else {
                echo "<script>alert('Gagal mengupload gambar!');</script>";
                $update = false;
            }
        } else {
            echo "<script>alert('Ekstensi file gambar tidak diizinkan (hanya jpg, jpeg, png, jfif, webp)!');</script>";
            $update = false;
        }
    } else {
        // UPDATE TANPA MENGUBAH GAMBAR
        $update = mysqli_query($koneksi, "UPDATE produk SET NamaProduk='$namaproduk', Harga='$harga', Stok='$stok' WHERE ProdukID='$id'");
    }

    if (isset($update) && $update) {
        echo "<script>alert('Data produk berhasil diperbarui!'); window.location='produk.php';</script>";
    } else {
        echo "<script>alert('Gagal memperbarui data produk!');</script>";
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Edit Produk - Aplikasi Kasir Nis & Co</title>
    <link rel="icon" type="image/png" href="../assets/img/icon.png">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body { 
            font-family: 'Poppins', sans-serif; 
            background-image: url('../assets/img/bgadmin.jfif');
            background-size: cover;        
            background-position: center;   
            background-repeat: no-repeat;  
            background-attachment: fixed;  
            color: #2C3531;
            margin: 0;
            padding: 40px;
        }

        .container {
            max-width: 600px;
            margin: 0 auto;
            animation: fadeInUp 0.8s ease-out forwards;
        }

        .header-title {
            margin-bottom: 25px;
            animation: fadeIn 1s ease-out forwards;
        }

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

        .preview-img {
            width: 70px;
            height: 70px;
            object-fit: contain;
            background: #ffffff;
            border-radius: 10px;
            border: 1px solid #C8D3C4;
            padding: 4px;
            margin-bottom: 10px;
            display: block;
        }

        /* Styling Custom File Upload */
        .file-upload-wrapper {
            display: flex;
            align-items: center;
            gap: 12px;
            flex-wrap: wrap;
        }

        .file-upload-input {
            display: none; /* Sembunyikan input file asli */
        }

        .file-upload-btn {
            background-color: #E2E8DF;
            color: #2C3E2D;
            padding: 10px 18px;
            border-radius: 10px;
            font-size: 14px;
            font-weight: 500;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            border: 1px solid #C8D3C4;
            transition: all 0.3s ease;
        }

        .file-upload-btn:hover {
            background-color: #d1dad0;
            border-color: #2C3E2D;
            transform: translateY(-1px);
        }

        .file-name-display {
            font-size: 13px;
            color: #555;
            font-style: italic;
        }

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

        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }
    </style>
</head>
<body>

    <div class="container">
        <!-- Bagian Judul Form -->
        <div class="header-title">
            <h2 style="margin: 0; font-size: 26px; font-weight: 700; color: #2C3E2D; display: flex; align-items: center; gap: 12px;">
                <span style="background-color: #2C3E2D; padding: 10px; border-radius: 12px; display: flex; align-items: center; justify-content: center; box-shadow: 0 4px 10px rgba(44, 62, 45, 0.25);">
                    <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path>
                        <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path>
                    </svg>
                </span>
                Edit Produk
            </h2>
            <p style="margin: 5px 0 0 54px; font-size: 14px; color: #4A5D48;">
                Perbarui informasi nama, harga, stok, atau gambar barang Aplikasi Nis & Co.
            </p>
        </div>

        <!-- Form Card / Kontainer Estetik -->
        <div class="form-card">
            <form action="" method="POST" enctype="multipart/form-data">
                <div style="margin-bottom: 20px;">
                    <label style="display: block; font-weight: 600; color: #2C3E2D; margin-bottom: 8px; font-size: 14px;">Nama Produk</label>
                    <input type="text" name="namaproduk" value="<?php echo htmlspecialchars($row['NamaProduk']); ?>" required class="form-control">
                </div>

                <div style="margin-bottom: 20px;">
                    <label style="display: block; font-weight: 600; color: #2C3E2D; margin-bottom: 8px; font-size: 14px;">Harga (Rp)</label>
                    <input type="number" name="harga" value="<?php echo $row['Harga']; ?>" required class="form-control">
                </div>

                <div style="margin-bottom: 20px;">
                    <label style="display: block; font-weight: 600; color: #2C3E2D; margin-bottom: 8px; font-size: 14px;">Stok</label>
                    <input type="number" name="stok" value="<?php echo $row['Stok']; ?>" required class="form-control">
                </div>

                <div style="margin-bottom: 30px;">
                    <label style="display: block; font-weight: 600; color: #2C3E2D; margin-bottom: 8px; font-size: 14px;">Ganti Gambar Produk</label>
                    
                    <?php 
                    $nama_prod = $row['NamaProduk'];
                    $nama_file_gambar = 'default.png';
                    // AMBIL DARI KOLOM 'gambar' (BUKAN 'Foto')
                    if (isset($row['gambar']) && !empty($row['gambar'])) {
                        $nama_file_gambar = $row['gambar'];
                    } else {
                        if ($nama_prod == 'Botanica Canvas Tote') {$nama_file_gambar = 'produk1.png'; }
                        elseif ($nama_prod == 'Botanica Wheat Bento Set') {$nama_file_gambar = 'produk2.png'; }
                        elseif ($nama_prod == 'Earth & Co. Bamboo Lunch Box') { $nama_file_gambar = 'produk3.png'; }
                        elseif ($nama_prod == 'EcoGlow Cork Glass Bottle') {$nama_file_gambar = 'produk4.png'; }
                        elseif ($nama_prod == 'Forest Mist Portable Cutlery') {$nama_file_gambar = 'produk5.png'; }
                        elseif ($nama_prod == 'Forest Mist Straw Cup') {$nama_file_gambar = 'produk6.jfif'; }
                        elseif ($nama_prod == 'Natura Bamboo Flask') {$nama_file_gambar = 'produk7.jfif'; }
                        elseif ($nama_prod == 'Natura Wooden Coaster') {$nama_file_gambar = 'produk8.jfif'; }
                        elseif ($nama_prod == 'Sage Layers Stack Box') {$nama_file_gambar = 'produk9.jfif'; }
                        elseif ($nama_prod == 'Sage Leaf Vacuum Tumbler') {$nama_file_gambar = 'produk10.jfif'; }
                    }
                    ?>
                    
                    <div style="margin-bottom: 12px;">
                        <span style="font-size: 12px; color: #555; display: block; margin-bottom: 4px;">Gambar Saat Ini:</span>
                        <img src="../assets/img/<?php echo $nama_file_gambar; ?>" alt="Preview" class="preview-img">
                    </div>
                    
                    <!-- Desain Custom Tombol Upload -->
                    <div class="file-upload-wrapper">
                        <label for="file-upload" class="file-upload-btn">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path>
                                <polyline points="17 8 12 3 7 8"></polyline>
                                <line x1="12" y1="3" x2="12" y2="15"></line>
                            </svg>
                            Pilih File Baru
                        </label>
                        <input id="file-upload" type="file" name="gambar" class="file-upload-input" onchange="updateFileName(this)">
                        <span id="file-name" class="file-name-display">Belum ada file dipilih</span>
                    </div>

                    <small style="color: #666; font-size: 12px; margin-top: 8px; display: block;">*Format yang didukung: PNG, JPG, JPEG, JFIF, WEBP (Biarkan kosong jika tidak ingin mengganti gambar).</small>
                </div>

                <!-- Tombol Aksi -->
                <div style="display: flex; gap: 12px;">
                    <button type="submit" name="update" class="btn-submit">
                        Simpan Perubahan
                    </button>
                    <a href="produk.php" class="btn-cancel">
                        Kembali
                    </a>
                </div>
            </form>
        </div>
    </div>

    <!-- Script JavaScript untuk memunculkan nama file yang dipilih -->
    <script>
        function updateFileName(input) {
            const fileNameDisplay = document.getElementById('file-name');
            if (input.files && input.files.length > 0) {
                fileNameDisplay.textContent = input.files[0].name;
                fileNameDisplay.style.color = '#2C3E2D';
                fileNameDisplay.style.fontWeight = '500';
            } else {
                fileNameDisplay.textContent = 'Belum ada file dipilih';
                fileNameDisplay.style.color = '#555';
                fileNameDisplay.style.fontWeight = 'normal';
            }
        }
    </script>
</body>
</html>