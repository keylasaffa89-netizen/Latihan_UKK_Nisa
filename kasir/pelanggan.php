<?php
session_start();

// Cek status login dan level Kasir (Proteksi keamanan halaman)
if (!isset($_SESSION['status']) || $_SESSION['status'] != "login" || $_SESSION['level'] != "kasir") {
    header("location:../login.php?pesan=belum_login");
    exit;
}

include '../koneksi.php';
include 'header.php';

// Proses Tambah Pelanggan
if (isset($_POST['tambah_pelanggan'])) {
    $nama = mysqli_real_escape_string($koneksi, $_POST['NamaPelanggan']);
    $alamat = mysqli_real_escape_string($koneksi, $_POST['Alamat']);
    $telepon = mysqli_real_escape_string($koneksi, $_POST['NomorTelepon']);
    $panjang_telp = strlen($telepon);

    // Validasi Backend: Hanya angka DAN panjang harus antara 11 sampai 13 digit
    if (!preg_match('/^[0-9]+$/', $telepon)) {
        echo "<script>alert('Gagal! Nomor telepon hanya boleh diisi dengan angka.');</script>";
    } elseif ($panjang_telp < 11 || $panjang_telp > 13) {
        echo "<script>alert('Gagal! Nomor telepon harus memiliki panjang antara 11 hingga 13 digit.');</script>";
    } else {
        $query_insert = mysqli_query($koneksi, "INSERT INTO pelanggan (NamaPelanggan, Alamat, NomorTelepon) VALUES ('$nama', '$alamat', '$telepon')");
        
        if ($query_insert) {
            echo "<script>alert('Pelanggan berhasil ditambahkan!'); window.location='pelanggan.php';</script>";
        } else {
            echo "<script>alert('Gagal menambahkan pelanggan ke database!');</script>";
        }
    }
}

// Ambil kata kunci pencarian jika ada
$keyword = isset($_GET['search']) ? mysqli_real_escape_string($koneksi, $_GET['search']) : '';
?>

<style>
/* Reset Background dengan Ekstensi .jfif */
body {
    background: url('../assets/img/bgkasir.jfif') no-repeat center center fixed !important;
    background-size: cover !important;
    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif !important;
    overflow-x: hidden;
}

.kasir-container {
    padding: 30px 10px;
    max-width: 1100px;
    margin: 0 auto;
}

/* --- ANIMASI HALUS & BERJENJANG (STAGGERED) --- */
@keyframes slideDownHeader {
    0% { opacity: 0; transform: translateY(-30px); }
    100% { opacity: 1; transform: translateY(0); }
}

@keyframes slideUpSearch {
    0% { opacity: 0; transform: translateY(20px); }
    100% { opacity: 1; transform: translateY(0); }
}

@keyframes slideUpTable {
    0% { opacity: 0; transform: translateY(40px); }
    100% { opacity: 1; transform: translateY(0); }
}

.header-animated {
    animation: slideDownHeader 1s cubic-bezier(0.2, 0.8, 0.2, 1) forwards;
}

.search-animated {
    opacity: 0;
    animation: slideUpSearch 0.9s cubic-bezier(0.2, 0.8, 0.2, 1) forwards;
    animation-delay: 0.25s;
}

.table-animated {
    opacity: 0;
    animation: slideUpTable 1s cubic-bezier(0.2, 0.8, 0.2, 1) forwards;
    animation-delay: 0.45s;
}

/* Header Section */
.page-title {
    font-size: 36px !important;
    font-weight: 900 !important;
    color: #1A2E1A !important;
    margin: 0 0 6px 0 !important;
    text-shadow: 0 2px 8px rgba(255, 255, 255, 0.8);
    display: flex;
    align-items: center;
    gap: 12px;
}

.page-subtitle {
    font-size: 15px !important;
    color: #2E472E !important;
    font-weight: 700 !important;
    margin: 0 !important;
    text-shadow: 0 1px 4px rgba(255, 255, 255, 0.8);
}

/* Filter & Search Bar Section */
.top-bar-flex {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin: 25px 0 20px 0;
    flex-wrap: wrap;
    gap: 15px;
}

.search-form-group {
    display: flex;
    align-items: center;
    gap: 8px;
    flex: 1;
    max-width: 480px;
}

.search-box {
    position: relative;
    display: flex;
    align-items: center;
    flex: 1;
}

.search-box input {
    width: 100%;
    padding: 11px 16px 11px 42px;
    border-radius: 14px;
    border: 2px solid rgba(46, 71, 46, 0.3);
    background: rgba(255, 255, 255, 0.85);
    backdrop-filter: blur(10px);
    -webkit-backdrop-filter: blur(10px);
    font-size: 14px;
    font-weight: 600;
    color: #1A2E1A;
    outline: none;
    box-shadow: 0 4px 15px rgba(0, 0, 0, 0.08);
    transition: all 0.3s cubic-bezier(0.175, 0.885, 0.32, 1.275);
}

.search-box input::placeholder {
    color: #556B2F;
    opacity: 0.8;
}

.search-box input:focus {
    background: #FFFFFF;
    border-color: #2E472E;
    box-shadow: 0 6px 20px rgba(0, 0, 0, 0.15);
    transform: scale(1.01);
}

.search-box svg {
    position: absolute;
    left: 14px;
    color: #2E472E;
    z-index: 2;
}

/* Buttons */
.btn-search {
    background: #2E472E;
    color: white;
    border: none;
    padding: 11px 18px;
    border-radius: 14px;
    font-weight: 700;
    font-size: 14px;
    cursor: pointer;
    box-shadow: 0 4px 12px rgba(46, 71, 46, 0.3);
    transition: all 0.3s cubic-bezier(0.175, 0.885, 0.32, 1.275);
    white-space: nowrap;
}

.btn-search:hover {
    background: #1A2E1A;
    transform: translateY(-3px) scale(1.02);
    box-shadow: 0 6px 18px rgba(46, 71, 46, 0.4);
}

.btn-reset {
    background: rgba(220, 38, 38, 0.85);
    color: white;
    border: none;
    padding: 11px 16px;
    border-radius: 14px;
    font-weight: 700;
    font-size: 14px;
    text-decoration: none;
    box-shadow: 0 4px 12px rgba(220, 38, 38, 0.3);
    transition: all 0.3s cubic-bezier(0.175, 0.885, 0.32, 1.275);
    display: flex;
    align-items: center;
    gap: 6px;
    white-space: nowrap;
}

.btn-reset:hover {
    background: #DC2626;
    color: white;
    transform: translateY(-3px) scale(1.02);
    box-shadow: 0 6px 18px rgba(220, 38, 38, 0.4);
}

.btn-tambah {
    background: linear-gradient(135deg, #2E472E, #1A2E1A);
    color: white;
    border: none;
    padding: 11px 20px;
    border-radius: 14px;
    font-weight: 700;
    font-size: 14px;
    cursor: pointer;
    box-shadow: 0 4px 12px rgba(46, 71, 46, 0.3);
    transition: all 0.3s cubic-bezier(0.175, 0.885, 0.32, 1.275);
    display: flex;
    align-items: center;
    gap: 8px;
}

.btn-tambah:hover {
    transform: translateY(-3px) scale(1.02);
    box-shadow: 0 6px 18px rgba(46, 71, 46, 0.4);
}

/* Table Card Wrapper */
.table-card-wrapper {
    background: rgba(255, 255, 255, 0.6) !important;
    backdrop-filter: blur(12px);
    -webkit-backdrop-filter: blur(12px);
    border: 1px solid rgba(255, 255, 255, 0.8) !important;
    border-radius: 20px;
    padding: 24px;
    box-shadow: 0 10px 30px rgba(0,0,0,0.05);
    transition: box-shadow 0.4s ease;
}

.table-card-wrapper:hover {
    box-shadow: 0 16px 40px rgba(0,0,0,0.08);
}

.table-custom {
    width: 100%;
    border-collapse: collapse;
}

.table-custom th {
    text-align: left;
    padding: 14px 16px;
    font-size: 12px;
    font-weight: 800;
    color: #1A2E1A;
    text-transform: uppercase;
    border-bottom: 2px solid rgba(46, 71, 46, 0.25);
}

.table-custom td {
    padding: 14px 16px;
    font-size: 14px;
    font-weight: 600;
    color: #111E11;
    border-bottom: 1px solid rgba(0, 0, 0, 0.05);
    transition: all 0.25s ease;
}

.table-custom tr {
    transition: all 0.25s ease;
}

.table-custom tr:hover td {
    background: rgba(255, 255, 255, 0.75);
    padding-left: 20px;
}

/* Style Modal Pop-up Tambah Pelanggan */
.modal-overlay {
    display: none;
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0, 0, 0, 0.4);
    backdrop-filter: blur(6px);
    -webkit-backdrop-filter: blur(6px);
    z-index: 999;
    justify-content: center;
    align-items: center;
    opacity: 0;
    transition: opacity 0.3s ease;
}

@keyframes scaleUpModal {
    0% { transform: scale(0.9); opacity: 0; }
    100% { transform: scale(1); opacity: 1; }
}

.modal-content {
    background: rgba(255, 255, 255, 0.95);
    border-radius: 20px;
    padding: 28px;
    width: 100%;
    max-width: 450px;
    box-shadow: 0 15px 35px rgba(0, 0, 0, 0.25);
    border: 1px solid rgba(255, 255, 255, 0.8);
    animation: scaleUpModal 0.35s cubic-bezier(0.175, 0.885, 0.32, 1.275) forwards;
}

.modal-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 20px;
}

.modal-title {
    font-size: 20px;
    font-weight: 800;
    color: #1A2E1A;
    margin: 0;
}

.btn-close {
    background: none;
    border: none;
    font-size: 22px;
    font-weight: bold;
    color: #556B2F;
    cursor: pointer;
    transition: transform 0.2s;
}

.btn-close:hover {
    transform: scale(1.15);
    color: #1A2E1A;
}

.form-group {
    margin-bottom: 16px;
}

.form-group label {
    display: block;
    font-size: 13px;
    font-weight: 700;
    color: #1A2E1A;
    margin-bottom: 6px;
}

.form-group input, .form-group textarea {
    width: 100%;
    padding: 11px 14px;
    border-radius: 12px;
    border: 1.5px solid rgba(46, 71, 46, 0.3);
    outline: none;
    font-size: 14px;
    font-weight: 600;
    color: #1A2E1A;
    background: #FFFFFF;
    box-sizing: border-box;
    transition: all 0.25s ease;
}

.form-group input:focus, .form-group textarea:focus {
    border-color: #2E472E;
    box-shadow: 0 4px 12px rgba(46, 71, 46, 0.15);
}

.btn-submit {
    width: 100%;
    background: #2E472E;
    color: white;
    border: none;
    padding: 12px;
    border-radius: 12px;
    font-weight: 700;
    font-size: 14px;
    cursor: pointer;
    box-shadow: 0 4px 12px rgba(46, 71, 46, 0.3);
    transition: all 0.3s cubic-bezier(0.175, 0.885, 0.32, 1.275);
}

.btn-submit:hover {
    background: #1A2E1A;
    transform: translateY(-2px);
    box-shadow: 0 6px 16px rgba(46, 71, 46, 0.4);
}
</style>

<div class="kasir-container">
    <!-- Header Section -->
    <div class="header-animated" style="display: flex; justify-content: space-between; align-items: flex-end; flex-wrap: wrap; gap: 15px;">
        <div>
            <h1 class="page-title">
                <svg width="34" height="34" viewBox="0 0 24 24" fill="none" stroke="#1A2E1A" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path>
                    <circle cx="9" cy="7" r="4"></circle>
                    <path d="M23 21v-2a4 4 0 0 0-3-3.87"></path>
                    <path d="M16 3.13a4 4 0 0 1 0 7.75"></path>
                </svg>
                Data Pelanggan
            </h1>
            <p class="page-subtitle">Kelola dan cari informasi pelanggan member toko</p>
        </div>
        <button class="btn-tambah" onclick="openModal()">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="12" y1="5" x2="12" y2="19"></line><line x1="5" y1="12" x2="19" y2="12"></line></svg>
            Tambah Pelanggan
        </button>
    </div>

    <!-- Section Form Pencarian -->
    <div class="top-bar-flex search-animated">
        <form method="GET" action="" class="search-form-group">
            <div class="search-box">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                    <circle cx="11" cy="11" r="8"></circle>
                    <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
                </svg>
                <input type="text" name="search" placeholder="Cari nama / nomor telepon..." value="<?= htmlspecialchars($keyword); ?>">
            </div>
            
            <button type="submit" class="btn-search">Cari</button>

            <?php if ($keyword != ''): ?>
                <a href="pelanggan.php" class="btn-reset">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line></svg>
                    Reset
                </a>
            <?php endif; ?>
        </form>
    </div>

    <!-- Tabel Data Pelanggan -->
    <div class="table-card-wrapper table-animated">
        <table class="table-custom">
            <thead>
                <tr>
                    <th style="width: 60px;">No</th>
                    <th>Nama Pelanggan</th>
                    <th>Alamat</th>
                    <th>Nomor Telepon</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $no = 1;
                if ($keyword != '') {
                    $query = mysqli_query($koneksi, "SELECT * FROM pelanggan WHERE NamaPelanggan LIKE '%$keyword%' OR NomorTelepon LIKE '%$keyword%' ORDER BY NamaPelanggan ASC");
                } else {
                    $query = mysqli_query($koneksi, "SELECT * FROM pelanggan ORDER BY NamaPelanggan ASC");
                }

                if (mysqli_num_rows($query) > 0) {
                    while ($d = mysqli_fetch_assoc($query)) {
                        ?>
                        <tr>
                            <td><?= $no++; ?></td>
                            <td><strong><?= htmlspecialchars($d['NamaPelanggan']); ?></strong></td>
                            <td><?= htmlspecialchars($d['Alamat']); ?></td>
                            <td><?= htmlspecialchars($d['NomorTelepon']); ?></td>
                        </tr>
                        <?php
                    }
                } else {
                    echo "<tr><td colspan='4' style='text-align: center; color: #444; padding: 20px;'>Data pelanggan tidak ditemukan.</td></tr>";
                }
                ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Modal Pop-up Tambah Pelanggan -->
<div class="modal-overlay" id="modalPelanggan">
    <div class="modal-content">
        <div class="modal-header">
            <h3 class="modal-title">Tambah Pelanggan Baru</h3>
            <button class="btn-close" onclick="closeModal()">&times;</button>
        </div>
        <form method="POST" action="" onsubmit="return validateForm()">
            <div class="form-group">
                <label>Nama Pelanggan</label>
                <input type="text" name="NamaPelanggan" placeholder="Masukkan nama pelanggan" required>
            </div>
            <div class="form-group">
                <label>Alamat</label>
                <textarea name="Alamat" rows="3" placeholder="Masukkan alamat lengkap" required></textarea>
            </div>
            <div class="form-group">
                <label>Nomor Telepon</label>
                <input type="text" id="NomorTelepon" name="NomorTelepon" placeholder="Masukkan nomor telepon" maxlength="13" oninput="this.value = this.value.replace(/[^0-9]/g, '')" required>
            </div>
            <button type="submit" name="tambah_pelanggan" class="btn-submit">Simpan Pelanggan</button>
        </form>
    </div>
</div>

<script>
function openModal() {
    var modal = document.getElementById('modalPelanggan');
    modal.style.display = 'flex';
    setTimeout(function() {
        modal.style.opacity = '1';
    }, 10);
}

function closeModal() {
    var modal = document.getElementById('modalPelanggan');
    modal.style.opacity = '0';
    setTimeout(function() {
        modal.style.display = 'none';
    }, 300);
}

function validateForm() {
    var telepon = document.getElementById('NomorTelepon').value;
    var polaAngka = /^[0-9]+$/;
    
    if (!polaAngka.test(telepon)) {
        alert('Nomor telepon hanya boleh berisi angka!');
        return false;
    }
    
    if (telepon.length < 11 || telepon.length > 13) {
        alert('Nomor telepon harus terdiri dari 11 hingga 13 digit!');
        return false;
    }
    
    return true;
}

window.onclick = function(event) {
    var modal = document.getElementById('modalPelanggan');
    if (event.target == modal) {
        closeModal();
    }
}
</script>

</body>
</html>