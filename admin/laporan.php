<?php
session_start();

// Pengecekan session admin
if (!isset($_SESSION['status']) || $_SESSION['status'] != "login" || $_SESSION['role'] != "admin") {
    header("location:../login.php?pesan=belum_login");
    exit;
}

include '../koneksi.php';
include 'header.php';

// Ambil parameter filter dari form
$filter_type = isset($_GET['filter_type']) ? $_GET['filter_type'] : 'all';
$search = isset($_GET['search']) ? mysqli_real_escape_string($koneksi, $_GET['search']) : '';
$tanggal_mulai = isset($_GET['tanggal_mulai']) ? $_GET['tanggal_mulai'] : '';
$tgl_mulai_rentang = isset($_GET['tgl_mulai_rentang']) ? $_GET['tgl_mulai_rentang'] : '';
$tgl_selesai_rentang = isset($_GET['tgl_selesai_rentang']) ? $_GET['tgl_selesai_rentang'] : '';
$bulan = isset($_GET['bulan']) ? $_GET['bulan'] : date('m');
$tahun = isset($_GET['tahun']) ? $_GET['tahun'] : date('Y');
?>

<!-- Import Google Fonts: Poppins & Inter -->
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Poppins:wght@600;700;800&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

<style>
body {
    background: url('../assets/img/bgadmin.jfif') no-repeat center center fixed !important;
    background-size: cover !important;
    font-family: 'Inter', sans-serif;
}

@keyframes slideDown {
    from { opacity: 0; transform: translateY(-20px); }
    to { opacity: 1; transform: translateY(0); }
}

@keyframes fadeInUp {
    from { opacity: 0; transform: translateY(20px); }
    to { opacity: 1; transform: translateY(0); }
}

.animate-header {
    animation: slideDown 0.7s ease forwards;
}

.animate-content {
    animation: fadeInUp 0.9s ease forwards;
}

/* Desain Kartu Filter Glassmorphism */
.filter-card-glass {
    background: rgba(255, 255, 255, 0.92) !important;
    backdrop-filter: blur(14px);
    -webkit-backdrop-filter: blur(14px);
    border: 1px solid rgba(255, 255, 255, 0.8);
    padding: 24px;
    border-radius: 20px;
    box-shadow: 0 10px 30px rgba(28, 46, 27, 0.08);
    margin-bottom: 25px;
}

.form-label-custom {
    display: block;
    font-size: 11.5px;
    font-weight: 700;
    color: #2d422a;
    margin-bottom: 8px;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

/* Custom Pill / Tab Switcher untuk Pilihan Waktu */
.filter-pill-container {
    display: flex;
    background: #E8EDE6;
    padding: 4px;
    border-radius: 14px;
    gap: 4px;
    flex-wrap: wrap;
}

.filter-pill-btn {
    flex: 1;
    min-width: 90px;
    padding: 9px 12px;
    text-align: center;
    font-size: 12.5px;
    font-weight: 600;
    color: #4A5D48;
    background: transparent;
    border: none;
    border-radius: 10px;
    cursor: pointer;
    transition: all 0.25s ease;
    font-family: 'Inter', sans-serif;
}

.filter-pill-btn:hover {
    color: #1c2e1b;
    background: rgba(255, 255, 255, 0.5);
}

.filter-pill-btn.active {
    background: #1c2e1b;
    color: #ffffff;
    box-shadow: 0 4px 12px rgba(28, 46, 27, 0.2);
}

/* Styling Input & Select */
.form-control-custom {
    width: 100%;
    padding: 11px 15px;
    border: 1.5px solid #D5DDD1;
    border-radius: 12px;
    font-family: 'Inter', sans-serif;
    font-size: 13.5px;
    color: #1c2e1b;
    outline: none;
    background: rgba(255, 255, 255, 0.95);
    transition: all 0.3s ease;
    box-sizing: border-box;
}

.form-control-custom:focus {
    border-color: #1c2e1b !important;
    box-shadow: 0 0 0 4px rgba(28, 46, 27, 0.1) !important;
    background-color: #ffffff;
}

/* Tombol Aksi */
.btn-action {
    transition: all 0.25s ease;
    cursor: pointer;
    border: none;
    border-radius: 12px;
    font-weight: 700;
    font-size: 13.5px;
    padding: 11px 20px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
    text-decoration: none;
    height: 44px;
    box-sizing: border-box;
}

.btn-action:hover {
    transform: translateY(-2px);
    box-shadow: 0 6px 15px rgba(28, 46, 27, 0.25);
}

/* Container Transparan Glassmorphism untuk Tabel */
.table-container-glass {
    background: rgba(255, 255, 255, 0.9) !important;
    backdrop-filter: blur(12px);
    -webkit-backdrop-filter: blur(12px);
    border: 1px solid rgba(255, 255, 255, 0.6);
    padding: 22px;
    border-radius: 18px;
    box-shadow: 0 8px 25px rgba(0, 0, 0, 0.06);
}

/* Styling Tabel Laporan */
table {
    width: 100%;
    border-collapse: collapse;
    table-layout: fixed;
}

table th {
    background-color: #1c2e1b;
    color: #ffffff;
    padding: 14px 12px;
    font-family: 'Poppins', sans-serif;
    font-size: 13px;
    font-weight: 700;
    text-align: left;
}

table th:first-child { border-top-left-radius: 10px; }
table th:last-child { border-top-right-radius: 10px; }

table td {
    padding: 14px 12px;
    border-bottom: 1px solid rgba(0, 0, 0, 0.05);
    font-size: 13px;
    color: #1c2e1b;
    word-wrap: break-word;
    vertical-align: top;
}

table tr:hover {
    background-color: rgba(244, 247, 242, 0.8);
}

.product-badge-list {
    margin: 0;
    padding-left: 16px;
    font-size: 12.5px;
    color: #2d422a;
}

.product-badge-list li {
    margin-bottom: 3px;
}

/* Kartu Total Pendapatan Glassmorphism */
.total-card {
    transition: all 0.3s ease;
    background: rgba(235, 242, 231, 0.92) !important;
    backdrop-filter: blur(12px);
    -webkit-backdrop-filter: blur(12px);
    border: 1px solid rgba(255, 255, 255, 0.7);
    box-shadow: 0 4px 15px rgba(28, 46, 27, 0.08);
}
</style>

<!-- Lebar kontainer disamakan persis dengan halaman Penjualan (max-width: 1200px dengan margin auto yang pas) -->
<div style="max-width: 1200px; margin: 30px auto; padding: 0 24px;">

    <!-- Bagian Header & Judul (Tombol cetak sejajar di kanan atas seperti halaman Penjualan) -->
    <div class="no-print animate-header" style="margin-bottom: 25px; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 15px;">
        <div>
            <h2 style="margin: 0; font-family: 'Poppins', sans-serif; font-size: 26px; font-weight: 800; color: #112211; display: flex; align-items: center; gap: 12px;">
                <i class="fa-solid fa-file-invoice-dollar" style="font-size: 24px; color: #1c2e1b;"></i>
                Laporan Penjualan Lengkap
            </h2>
            <p style="margin: 6px 0 0 0; font-size: 13.5px; color: #2d422a; font-weight: 600;">
                Riwayat transaksi dan data laporan penjualan kasir yang tercatat.
            </p>
        </div>
        
        <button onclick="window.print()" class="btn-action" style="background-color: #1c2e1b; color: white;">
            <i class="fa-solid fa-print"></i> Cetak Laporan
        </button>
    </div>

    <!-- Panel Filter & Pencarian -->
    <div class="no-print animate-content filter-card-glass">
        <form method="GET" action="" id="filterForm">
            
            <input type="hidden" name="filter_type" id="filterTypeInput" value="<?= htmlspecialchars($filter_type); ?>">

            <div style="display: flex; flex-direction: column; gap: 20px;">
                
                <div>
                    <label class="form-label-custom">Filter Periode Waktu</label>
                    <div class="filter-pill-container">
                        <button type="button" class="filter-pill-btn <?= $filter_type == 'all' ? 'active' : ''; ?>" onclick="setFilterType('all')">Semua Waktu</button>
                        <button type="button" class="filter-pill-btn <?= $filter_type == 'harian' ? 'active' : ''; ?>" onclick="setFilterType('harian')">Harian</button>
                        <button type="button" class="filter-pill-btn <?= $filter_type == 'rentang' ? 'active' : ''; ?>" onclick="setFilterType('rentang')">Rentang Tanggal</button>
                        <button type="button" class="filter-pill-btn <?= $filter_type == 'bulanan' ? 'active' : ''; ?>" onclick="setFilterType('bulanan')">Bulanan</button>
                        <button type="button" class="filter-pill-btn <?= $filter_type == 'tahunan' ? 'active' : ''; ?>" onclick="setFilterType('tahunan')">Tahunan</button>
                    </div>
                </div>

                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 16px; align-items: flex-end;">
                    
                    <div id="fieldHarian" class="filter-group" style="display: none;">
                        <label class="form-label-custom">Pilih Tanggal</label>
                        <input type="date" name="tanggal_mulai" value="<?= htmlspecialchars($tanggal_mulai); ?>" class="form-control-custom" style="cursor: pointer;">
                    </div>

                    <div id="fieldRentangMulai" class="filter-group" style="display: none;">
                        <label class="form-label-custom">Dari Tanggal</label>
                        <input type="date" name="tgl_mulai_rentang" value="<?= htmlspecialchars($tgl_mulai_rentang); ?>" class="form-control-custom" style="cursor: pointer;">
                    </div>

                    <div id="fieldRentangSelesai" class="filter-group" style="display: none;">
                        <label class="form-label-custom">Sampai Tanggal</label>
                        <input type="date" name="tgl_selesai_rentang" value="<?= htmlspecialchars($tgl_selesai_rentang); ?>" class="form-control-custom" style="cursor: pointer;">
                    </div>

                    <div id="fieldBulanan" class="filter-group" style="display: none;">
                        <label class="form-label-custom">Pilih Bulan</label>
                        <select name="bulan" class="form-control-custom">
                            <?php
                            $nama_bulan = [
                                '01' => 'Januari', '02' => 'Februari', '03' => 'Maret', '04' => 'April',
                                '05' => 'Mei', '06' => 'Juni', '07' => 'Juli', '08' => 'Agustus',
                                '09' => 'September', '10' => 'Oktober', '11' => 'November', '12' => 'Desember'
                            ];
                            foreach ($nama_bulan as $num => $bname) {
                                $sel = ($bulan == $num) ? 'selected' : '';
                                echo "<option value='$num' $sel>$bname</option>";
                            }
                            ?>
                        </select>
                    </div>

                    <div id="fieldTahunan" class="filter-group" style="display: none;">
                        <label class="form-label-custom">Pilih Tahun</label>
                        <select name="tahun" class="form-control-custom">
                            <?php
                            $current_year = date('Y');
                            for ($y = $current_year; $y >= $current_year - 5; $y--) {
                                $sel = ($tahun == $y) ? 'selected' : '';
                                echo "<option value='$y' $sel>$y</option>";
                            }
                            ?>
                        </select>
                    </div>

                    <div>
                        <label class="form-label-custom">Cari Pelanggan / ID</label>
                        <input type="text" name="search" value="<?php echo htmlspecialchars($search); ?>" placeholder="Ketik nama / ID..." class="form-control-custom">
                    </div>

                    <div style="display: flex; gap: 8px; align-items: flex-end;">
                        <button type="submit" class="btn-action" style="background-color: #1c2e1b; color: white; flex: 1;">
                            <i class="fa-solid fa-filter"></i> Terapkan
                        </button>
                        <?php if ($filter_type != 'all' || !empty($search)): ?>
                            <a href="laporan.php" class="btn-action" style="background-color: #E2E8DF; color: #1c2e1b; padding: 11px 14px;" title="Reset Filter">
                                <i class="fa-solid fa-rotate-left"></i>
                            </a>
                        <?php endif; ?>
                    </div>

                </div>

            </div>

        </form>
    </div>

    <!-- Judul khusus saat diprint -->
    <div class="print-title" style="display: none; margin-bottom: 20px; text-align: center;">
        <h2 style="color: #1c2e1b; margin: 0; font-size: 24px;">Laporan Penjualan Kasir Nis & Co</h2>
        <p style="color: #2d422a; font-size: 13px; margin: 5px 0 0 0;">Periode Filter: <?= ucfirst($filter_type); ?></p>
    </div>

    <!-- Konten Tabel Laporan -->
    <div class="animate-content">
        <div class="table-container-glass">
            <table>
                <colgroup>
                    <col style="width: 6%;">
                    <col style="width: 14%;">
                    <col style="width: 18%;">
                    <col style="width: 18%;">
                    <col style="width: 29%;">
                    <col style="width: 15%;">
                </colgroup>
                <thead>
                    <tr>
                        <th>No</th>
                        <th>ID Transaksi</th>
                        <th>Tanggal</th>
                        <th>Nama Pelanggan</th>
                        <th>Produk yang Dibeli (Qty)</th>
                        <th>Total Harga</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $no = 1;
                    $total_pendapatan = 0;

                    $where_clauses = [];
                    
                    if (!empty($search)) {
                        $where_clauses[] = "(penjualan.PenjualanID LIKE '%$search%' OR pelanggan.NamaPelanggan LIKE '%$search%')";
                    }

                    if ($filter_type == 'harian' && !empty($_GET['tanggal_mulai'])) {
                        $tgl = mysqli_real_escape_string($koneksi, $_GET['tanggal_mulai']);
                        $where_clauses[] = "DATE(penjualan.TanggalPenjualan) = '$tgl'";
                    } elseif ($filter_type == 'rentang' && !empty($_GET['tgl_mulai_rentang']) && !empty($_GET['tgl_selesai_rentang'])) {
                        $t_mulai = mysqli_real_escape_string($koneksi, $_GET['tgl_mulai_rentang']);
                        $t_selesai = mysqli_real_escape_string($koneksi, $_GET['tgl_selesai_rentang']);
                        $where_clauses[] = "DATE(penjualan.TanggalPenjualan) BETWEEN '$t_mulai' AND '$t_selesai'";
                    } elseif ($filter_type == 'bulanan') {
                        $where_clauses[] = "MONTH(penjualan.TanggalPenjualan) = '$bulan' AND YEAR(penjualan.TanggalPenjualan) = '$tahun'";
                    } elseif ($filter_type == 'tahunan') {
                        $where_clauses[] = "YEAR(penjualan.TanggalPenjualan) = '$tahun'";
                    }

                    $query_sql = "SELECT penjualan.*, pelanggan.NamaPelanggan FROM penjualan LEFT JOIN pelanggan ON penjualan.PelangganID = pelanggan.PelangganID";
                    if (count($where_clauses) > 0) {
                        $query_sql .= " WHERE " . implode(" AND ", $where_clauses);
                    }
                    $query_sql .= " ORDER BY penjualan.PenjualanID DESC";

                    $query = mysqli_query($koneksi, $query_sql);

                    if ($query && mysqli_num_rows($query) > 0) {
                        while ($row = mysqli_fetch_assoc($query)) {
                            $total_pendapatan += $row['TotalHarga'];
                            $p_id = $row['PenjualanID'];

                            $q_detail = mysqli_query($koneksi, "SELECT detailpenjualan.*, produk.NamaProduk FROM detailpenjualan JOIN produk ON detailpenjualan.ProdukID = produk.ProdukID WHERE detailpenjualan.PenjualanID = '$p_id'");
                            
                            echo "<tr>";
                            echo "<td>" . $no++ . "</td>";
                            echo "<td><strong>#" . $p_id . "</strong></td>";
                            echo "<td>" . $row['TanggalPenjualan'] . "</td>";
                            echo "<td>" . ($row['NamaPelanggan'] ? htmlspecialchars($row['NamaPelanggan']) : 'Umum / Tidak Dikenal') . "</td>";
                            
                            echo "<td>";
                            if (mysqli_num_rows($q_detail) > 0) {
                                echo "<ul class='product-badge-list'>";
                                while ($det = mysqli_fetch_assoc($q_detail)) {
                                    echo "<li>" . htmlspecialchars($det['NamaProduk']) . " <b>(x" . $det['JumlahProduk'] . ")</b></li>";
                                }
                                echo "</ul>";
                            } else {
                                echo "<span style='color: #888;'>Tidak ada produk</span>";
                            }
                            echo "</td>";

                            echo "<td><strong style='color: #1c2e1b;'>Rp " . number_format($row['TotalHarga'], 0, ',', '.') . "</strong></td>";
                            echo "</tr>";
                        }
                    } else {
                        echo "<tr><td colspan='6' style='text-align: center; color: #2d422a; font-weight: 700; padding: 25px;'>Tidak ada data laporan yang cocok dengan filter.</td></tr>";
                    }
                    ?>
                </tbody>
            </table>
        </div>

        <!-- Total Keseluruhan Pendapatan -->
        <div class="total-card" style="margin-top: 20px; padding: 20px; border-radius: 14px; display: flex; justify-content: space-between; align-items: center; border-left: 6px solid #1c2e1b;">
            <span style="font-weight: 700; color: #1c2e1b; font-size: 16px;">Total Pendapatan Sesuai Filter</span>
            <strong style="color: #1c2e1b; font-size: 22px; font-family: 'Poppins', sans-serif;">Rp <?php echo number_format($total_pendapatan, 0, ',', '.'); ?></strong>
        </div>
    </div>

</div>

<!-- Script untuk Interaksi Pill Button & Form Dinamis -->
<script>
function setFilterType(type) {
    document.getElementById('filterTypeInput').value = type;
    
    var buttons = document.querySelectorAll('.filter-pill-btn');
    buttons.forEach(btn => btn.classList.remove('active'));
    event.currentTarget.classList.add('active');
    
    updateFieldVisibility(type);
}

function updateFieldVisibility(type) {
    document.getElementById('fieldHarian').style.display = 'none';
    document.getElementById('fieldRentangMulai').style.display = 'none';
    document.getElementById('fieldRentangSelesai').style.display = 'none';
    document.getElementById('fieldBulanan').style.display = 'none';
    document.getElementById('fieldTahunan').style.display = 'none';

    if (type === 'harian') {
        document.getElementById('fieldHarian').style.display = 'block';
    } else if (type === 'rentang') {
        document.getElementById('fieldRentangMulai').style.display = 'block';
        document.getElementById('fieldRentangSelesai').style.display = 'block';
    } else if (type === 'bulanan') {
        document.getElementById('fieldBulanan').style.display = 'block';
        document.getElementById('fieldTahunan').style.display = 'block';
    } else if (type === 'tahunan') {
        document.getElementById('fieldTahunan').style.display = 'block';
    }
}

window.onload = function() {
    var activeType = document.getElementById('filterTypeInput').value;
    updateFieldVisibility(activeType);
};
</script>

<!-- CSS Khusus untuk Pengaturan Cetak (Print) -->
<style>
    @media print {
        header, .no-print, body {
            background: white !important;
            background-image: none !important;
        }
        header, .no-print {
            display: none !important;
        }
        .print-title {
            display: block !important;
        }
        body, table, th, td, div {
            -webkit-print-color-adjust: exact !important;
            print-color-adjust: exact !important;
        }
        .total-card, .table-container-glass, .filter-card-glass {
            box-shadow: none !important;
            border: 1px solid #D5DDD1 !important;
            background: white !important;
        }
    }
</style>

</body>
</html>