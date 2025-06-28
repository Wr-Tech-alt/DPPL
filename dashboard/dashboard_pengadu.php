<?php
session_start(); // Tetap mulai session karena elemen lain mungkin memerlukannya

// --- DEBUG MODE: HAPUS ATAU KOMENTARI BLOK INI UNTUK PENGUJIAN TANPA LOGIN ---
/*
if (!isset($_SESSION['loggedin']) || $_SESSION['role'] !== 'Pengadu') {
    header("Location: ../login.php");
    exit();
}
$id_user = $_SESSION['iduser'];
$nama_user = $_SESSION['nama'];
*/
// --- AKHIR BLOK DEBUG MODE ---

// --- DEBUG MODE: GANTI DENGAN NILAI DUMMY UNTUK PENGUJIAN ---
// Jika Anda mengomentari blok di atas, Anda perlu dummy data ini:
$id_user = 21; // <-- Ganti dengan ID Pengadu yang ada di database Anda (misal: Hadi punya iduser 21 di sicepu.sql)
$nama_user = "Hadi (Pengadu)"; // <-- Ganti dengan nama Pengadu dummy untuk ditampilkan
// --- AKHIR DEBUG DUMMY DATA ---

include "../inc/koneksi.php"; // Ini tetap diperlukan untuk koneksi database dalam halaman

// Pastikan koneksi berhasil
if (!isset($koneksi) || !$koneksi) {
    die("Error: Koneksi database tidak tersedia. Pastikan inc/koneksi.php sudah benar.");
}

// Query untuk mendapatkan riwayat aduan pengguna
$sql_riwayat_aduan = "
    SELECT
        p.idpengaduan,
        jp.jenis AS jenis_aduan,
        p.waktu_aduan,
        p.judul,
        p.status
    FROM pengaduan p
    JOIN jenis_pengaduan jp ON p.idjenis = jp.idstatus
    WHERE p.iduser = ?
    ORDER BY p.waktu_aduan DESC"; // Diurutkan dari yang terbaru

$stmt_riwayat = $koneksi->prepare($sql_riwayat_aduan);
if ($stmt_riwayat === FALSE) {
    die("Error preparing statement: " . $koneksi->error);
}
$stmt_riwayat->bind_param("i", $id_user); // Menggunakan ID dummy/test
$stmt_riwayat->execute();
$result_riwayat = $stmt_riwayat->get_result();

$aduan_exist = $result_riwayat->num_rows > 0;

?>

<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Dashboard Pengadu</title>
    <link href="../assets/css/bootstrap.css" rel="stylesheet" />
    <link href="../assets/css/font-awesome.css" rel="stylesheet" />
    <link href="../assets/js/dataTables/dataTables.bootstrap.css" rel="stylesheet" />
    <link href="../assets/css/custom.css" rel="stylesheet" />
    <link href='http://fonts.googleapis.com/css?family=Open+Sans' rel='stylesheet' type='text/css' />
    <script src="../assets/js/jquery-1.10.2.js"></script>
    <script src="../assets/js/jquery.metisMenu.js"></script>
</head>
<body>
    <div id="wrapper">
        <nav class="navbar navbar-default navbar-cls-top " role="navigation" style="margin-bottom: 0">
            <div class="navbar-header">
                <button type="button" class="navbar-toggle" data-toggle="collapse" data-target=".sidebar-collapse">
                    <span class="sr-only">Toggle navigation</span>
                    <span class="icon-bar"></span>
                    <span class="icon-bar"></span>
                    <span class="icon-bar"></span>
                </button>
                <a class="navbar-brand" href="dashboard_pengadu.php">SiCepu</a>
            </div>
            <div class="namalevel">Selamat Datang, <?php echo $nama_user; ?> (Pengadu)</div>
            <div class="profile-logout-section">
                <div class="search-box">
                    <input type="text" placeholder="Cari...">
                    <i class="fa fa-search"></i>
                </div>
                <div class="profile-avatar"><i class="fa fa-user"></i></div>
                <a href="../logout.php" class="logout-btn"><i class="fa fa-sign-out"></i> Logout</a>
            </div>
        </nav>
        <nav class="navbar-default navbar-side" role="navigation">
            <div class="sidebar-collapse">
                <ul class="nav" id="main-menu">
                    <li class="text-center">
                        <img src="../assets/img/stmi.png" class="user-image img-responsive"/>
                    </li>
                    <li>
                        <a class="active-menu" href="dashboard_pengadu.php"><i class="fa fa-dashboard fa-2x"></i> Dashboard</a>
                    </li>
                    <li>
                        <a href="../pengadu/adu_form_tambah.php"><i class="fa fa-plus-square fa-2x"></i> Tambah Aduan Baru</a>
                    </li>
                    <li>
                        <a href="../foto_aduan/foto_aduan.php"><i class="fa fa-image fa-2x"></i> Foto Aduan</a>
                    </li>
                    </ul>
            </div>
        </nav>
        <div id="page-wrapper" >
            <div id="page-inner">
                <div class="row">
                    <div class="col-md-12">
                        <div id="marquee">
                            <h4>Selamat Datang, <?php echo $nama_user; ?>!</h4>
                            <p>Riwayat Pengaduan Fasilitas Kampus Anda</p>
                        </div>
                    </div>
                </div>
                 <hr />

                <div class="row">
                    <div class="col-md-12">
                        <div class="panel panel-default">
                            <div class="panel-heading">
                                Riwayat Pengaduan Anda
                                <?php if ($aduan_exist): // Tombol akan geser ke kanan atas jika ada aduan ?>
                                    <div class="pull-right">
                                        <a href="../pengadu/adu_form_tambah.php" class="btn btn-primary btn-sm">
                                            <i class="fa fa-plus"></i> Tambahkan Aduan
                                        </a>
                                    </div>
                                <?php endif; ?>
                            </div>
                            <div class="panel-body">
                                <div class="table-responsive">
                                    <?php if ($aduan_exist): ?>
                                    <table class="table table-striped table-bordered table-hover" id="dataTables-example">
                                        <thead>
                                            <tr>
                                                <th>No.</th>
                                                <th>Judul Aduan</th>
                                                <th>Jenis Aduan</th>
                                                <th>Waktu Aduan</th>
                                                <th>Status</th>
                                                <th>Aksi</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php 
                                            $no = 1;
                                            while ($row = $result_riwayat->fetch_assoc()): 
                                            ?>
                                            <tr class="<?php echo ($no % 2 == 1) ? 'odd' : 'even'; ?>">
                                                <td><?php echo $no++; ?></td>
                                                <td><?php echo htmlspecialchars($row['judul']); ?></td>
                                                <td><?php echo htmlspecialchars($row['jenis_aduan']); ?></td>
                                                <td><?php echo date('d-m-Y H:i:s', strtotime($row['waktu_aduan'])); ?></td>
                                                <td>
                                                    <?php 
                                                        $status_class = '';
                                                        switch ($row['status']) {
                                                            case 'Masuk':
                                                                $status_class = 'label-danger'; // Merah untuk status Masuk/Pending
                                                                break;
                                                            case 'Diproses':
                                                                $status_class = 'label-warning'; // Kuning untuk status Diproses
                                                                break;
                                                            case 'Selesai':
                                                                $status_class = 'label-success'; // Hijau untuk status Selesai
                                                                break;
                                                            default:
                                                                $status_class = 'label-default'; // Default jika ada status lain
                                                                break;
                                                        }
                                                    ?>
                                                    <span class="label <?php echo $status_class; ?>">
                                                        <?php echo htmlspecialchars($row['status']); ?>
                                                    </span>
                                                </td>
                                                <td>
                                                    <a href="../pengadu/adu_ubah.php?id=<?php echo $row['idpengaduan']; ?>" class="btn btn-info btn-xs" title="Ubah Aduan">
                                                        <i class="fa fa-edit"></i> Ubah
                                                    </a>
                                                    <a href="detail_aduan.php?id=<?php echo $row['idpengaduan']; ?>" class="btn btn-primary btn-xs" title="Lihat Detail">
                                                        <i class="fa fa-info-circle"></i> Detail
                                                    </a>
                                                </td>
                                            </tr>
                                            <?php endwhile; ?>
                                        </tbody>
                                    </table>
                                    <?php else: // Tombol di tengah jika belum ada aduan ?>
                                        <div class="text-center">
                                            <p>Anda belum memiliki riwayat pengaduan.</p>
                                            <a href="../pengadu/adu_form_tambah.php" class="btn btn-primary btn-lg">
                                                <i class="fa fa-plus"></i> Tambahkan Aduan Pertama Anda
                                            </a>
                                        </div>
                                    <?php endif; ?>
                                </div>
                                
                            </div>
                        </div>
                        </div>
                </div>
            </div>
            </div>
        </div>
    <script src="../assets/js/bootstrap.min.js"></script>
    <script src="../assets/js/jquery.metisMenu.js"></script>
    <script src="../assets/js/dataTables/jquery.dataTables.js"></script>
    <script src="../assets/js/dataTables/dataTables.bootstrap.js"></script>
    <script>
        $(document).ready(function () {
            // Hanya inisialisasi DataTable jika tabelnya ada dan punya data
            <?php if ($aduan_exist): ?>
                $('#dataTables-example').dataTable();
            <?php endif; ?>
        });
    </script>
    <script src="../assets/js/custom.js"></script>
    
</body>
</html>