<?php
// Diperbaiki: Naik satu direktori karena koneksi.php berada di folder utama project
include '../koneksi.php';

// Proses simpan produk baru
if (isset($_POST['submit'])) {
    $namaproduk = mysqli_real_escape_string($koneksi, $_POST['NamaProduk']);
    // Casting harga dan stok ke tipe data integer/angka untuk keamanan database
    $harga = (int) $_POST['Harga'];
    $stok = (int) $_POST['Stok'];

    $nama_gambar_baru = ""; // Default kosong

    // Cek apakah user mengupload gambar
    if (isset($_FILES['gambar']) && $_FILES['gambar']['error'] === UPLOAD_ERR_OK) {
        $nama_file = $_FILES['gambar']['name'];
        $lokasi_tmp = $_FILES['gambar']['tmp_name'];
        
        // Validasi Ukuran File (Maksimal 2MB)
        $ukuran_file = $_FILES['gambar']['size'];
        $maks_ukuran = 2 * 1024 * 1024; // 2MB dalam bytes

        $ekstensi_diperbolehkan = array('png', 'jpg', 'jpeg', 'jfif', 'webp');
        $x = explode('.', $nama_file);
        $ekstensi = strtolower(end($x));
        
        if ($ukuran_file > $maks_ukuran) {
            echo "<script>alert('Ukuran file terlalu besar! Maksimal 2MB.');</script>";
        } elseif (in_array($ekstensi, $ekstensi_diperbolehkan) === true) {
            // Beri nama unik agar tidak bentrok
            $nama_gambar_baru = uniqid() . '.' . $ekstensi;
            $tujuan = '../assets/img/' . $nama_gambar_baru;

            if (!move_uploaded_file($lokasi_tmp, $tujuan)) {
                echo "<script>alert('Gagal mengupload gambar!');</script>";
                $nama_gambar_baru = ""; // Batalkan jika gagal upload
            }
        } else {
            echo "<script>alert('Ekstensi file gambar tidak diizinkan (hanya jpg, jpeg, png, jfif, webp)!');</script>";
        }
    }

    // Query simpan ke database dengan prepared values yang aman
    $query = mysqli_query($koneksi, "INSERT INTO produk (NamaProduk, Harga, Stok, gambar) VALUES ('$namaproduk', '$harga', '$stok', '$nama_gambar_baru')");

    if ($query) {
        echo "<script>alert('Data produk berhasil ditambahkan!'); window.location='produk.php';</script>";
    } else {
        echo "<script>alert('Gagal menambahkan produk!');</script>";
    }
}

// Memanggil header agar navigation bar Sora muncul di atas
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
                        <path d="M6 2L3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4z"></path>
                        <line x1="3" y1="6" x2="21" y2="6"></line>
                        <path d="M16 10a4 4 0 0 1-8 0"></path>
                    </svg>
                </span>
                Tambah Data Produk
            </h2>
            <p style="margin: 5px 0 0 54px; font-size: 14px; color: #4A5D48;">
                Formulir penambahan inventaris produk baru Aplikasi Nis & Co.
            </p>
        </div>

        <!-- Form Card -->
        <div class="form-card">
            <!-- Tambahkan enctype agar form bisa memproses file upload -->
            <form action="" method="POST" enctype="multipart/form-data">
                
                <div style="margin-bottom: 20px;">
                    <label style="display: block; font-weight: 600; font-size: 14px; color: #2C3E2D; margin-bottom: 8px;">Nama Produk</label>
                    <input type="text" name="NamaProduk" required placeholder="Masukkan nama produk" class="form-control">
                </div>

                <div style="margin-bottom: 20px;">
                    <label style="display: block; font-weight: 600; font-size: 14px; color: #2C3E2D; margin-bottom: 8px;">Harga (Rp)</label>
                    <input type="number" name="Harga" required placeholder="Masukkan harga produk" class="form-control">
                </div>

                <div style="margin-bottom: 20px;">
                    <label style="display: block; font-weight: 600; font-size: 14px; color: #2C3E2D; margin-bottom: 8px;">Stok</label>
                    <input type="number" name="Stok" required placeholder="Masukkan stok produk" class="form-control">
                </div>

                <div style="margin-bottom: 30px;">
                    <label style="display: block; font-weight: 600; font-size: 14px; color: #2C3E2D; margin-bottom: 8px;">Gambar Produk</label>
                    
                    <!-- Desain Custom Tombol Upload -->
                    <div class="file-upload-wrapper">
                        <label for="file-upload" class="file-upload-btn">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path>
                                <polyline points="17 8 12 3 7 8"></polyline>
                                <line x1="12" y1="3" x2="12" y2="15"></line>
                            </svg>
                            Pilih Gambar
                        </label>
                        <input id="file-upload" type="file" name="gambar" class="file-upload-input" onchange="updateFileName(this)">
                        <span id="file-name" class="file-name-display">Belum ada file dipilih</span>
                    </div>

                    <small style="color: #666; font-size: 12px; margin-top: 8px; display: block;">*Format yang didukung: PNG, JPG, JPEG, JFIF, WEBP (Maks. 2MB).</small>
                </div>

                <!-- Tombol Aksi -->
                <div style="display: flex; gap: 12px;">
                    <button type="submit" name="submit" class="btn-submit">
                        Simpan Produk
                    </button>
                    <a href="produk.php" class="btn-cancel">
                        Batal
                    </a>
                </div>

            </form>
        </div>
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