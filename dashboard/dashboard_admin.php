<?php
session_start();
include "../inc/koneksi.php"; // Sesuaikan path jika diperlukan

// Cek apakah user sudah login sebagai admin
if (!isset($_SESSION['ses_id']) || $_SESSION['ses_level'] !== 'admin') {
    header("Location: ../login.php");
    exit();
}

$id_user = $_SESSION['ses_id'];
$nama_user = $_SESSION['ses_nama'];

// Query untuk mendapatkan jumlah data aduan
$sql_aduan_masuk = "SELECT COUNT(*) AS total_masuk FROM pengaduan WHERE status = 'Masuk'";
$sql_aduan_selesai = "SELECT COUNT(*) AS total_selesai FROM pengaduan WHERE status = 'Selesai'";
$sql_aduan_diproses = "SELECT COUNT(*) AS total_diproses FROM pengaduan WHERE status = 'Diproses'";

$query_masuk = mysqli_query($koneksi, $sql_aduan_masuk);
$data_masuk = mysqli_fetch_assoc($query_masuk);
$total_masuk = $data_masuk['total_masuk'];

$query_selesai = mysqli_query($koneksi, $sql_aduan_selesai);
$data_selesai = mysqli_fetch_assoc($query_selesai);
$total_selesai = $data_selesai['total_selesai'];

$query_diproses = mysqli_query($koneksi, $sql_aduan_diproses);
$data_diproses = mysqli_fetch_assoc($query_diproses);
$total_diproses = $data_diproses['total_diproses'];

// Untuk total aduan, bisa juga sum dari ketiganya atau query terpisah
$sql_total_aduan = "SELECT COUNT(*) AS total_aduan FROM pengaduan";
$query_total_aduan = mysqli_query($koneksi, $sql_total_aduan);
$data_total_aduan = mysqli_fetch_assoc($query_total_aduan);
$total_aduan = $data_total_aduan['total_aduan'];

?>

<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Dashboard Admin</title>

    <link href="../assets/css/bootstrap.css" rel="stylesheet" />
    <link href="../assets/css/font-awesome.css" rel="stylesheet" />
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
                <a class="navbar-brand" href="dashboard_admin.php">SiCepu</a>
            </div>
            <div class="namalevel">Selamat Datang, <?php echo $nama_user; ?> (Admin)</div>
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
                        <a class="active-menu" href="dashboard_admin.php"><i class="fa fa-dashboard fa-2x"></i> Dashboard</a>
                    </li>
                    <li>
                        <a href="#"><i class="fa fa-envelope fa-2x"></i> Aduan Fasilitas<span class="fa arrow"></span></a>
                        <ul class="nav nav-second-level">
                            <li>
                                <a href="aduan/adu_tampil.php"><i class="fa fa-eye"></i> Lihat Aduan</a>
                            </li>
                            <li>
                                <a href="aduan/adu_tanggap.php"><i class="fa fa-comment"></i> Tanggapi Aduan</a>
                            </li>
                            <li>
                                <a href="aduan/adu_selesai.php"><i class="fa fa-check-square"></i> Aduan Selesai</a>
                            </li>
                            <li>
                                <a href="aduan/adu_ubah.php"><i class="fa fa-edit"></i> Ubah Aduan</a>
                            </li>
                        </ul>
                    </li>
                    <li>
                        <a href="#"><i class="fa fa-users fa-2x"></i> Pengguna<span class="fa arrow"></span></a>
                        <ul class="nav nav-second-level">
                            <li>
                                <a href="pengguna/pengguna_lihat.php"><i class="fa fa-eye"></i> Lihat Pengguna</a>
                            </li>
                            <li>
                                <a href="pengguna/pengguna_tambah.php"><i class="fa fa-plus"></i> Tambah Pengguna</a>
                            </li>
                            <li>
                                <a href="pengguna/pengguna_ubah.php"><i class="fa fa-edit"></i> Ubah Pengguna</a>
                            </li>
                            <li>
                                <a href="pengguna/pengguna_hapus.php"><i class="fa fa-trash"></i> Hapus Pengguna</a>
                            </li>
                        </ul>
                    </li>
                    <li>
                        <a href="#"><i class="fa fa-user-circle fa-2x"></i> Pengadu<span class="fa arrow"></span></a>
                        <ul class="nav nav-second-level">
                            <li>
                                <a href="pengadu/pengadu_lihat.php"><i class="fa fa-eye"></i> Lihat Pengadu</a>
                            </li>
                            <li>
                                <a href="pengadu/pengadu_tambah.php"><i class="fa fa-plus"></i> Tambah Pengadu</a>
                            </li>
                            <li>
                                <a href="pengadu/pengadu_ubah.php"><i class="fa fa-edit"></i> Ubah Pengadu</a>
                            </li>
                            <li>
                                <a href="pengadu/pengadu_hapus.php"><i class="fa fa-trash"></i> Hapus Pengadu</a>
                            </li>
                        </ul>
                    </li>
                    <li>
                        <a href="#"><i class="fa fa-list-alt fa-2x"></i> Jenis Pengaduan<span class="fa arrow"></span></a>
                        <ul class="nav nav-second-level">
                            <li>
                                <a href="jenis/jenis_lihat.php"><i class="fa fa-eye"></i> Lihat Jenis</a>
                            </li>
                            <li>
                                <a href="jenis/jenis_tambah.php"><i class="fa fa-plus"></i> Tambah Jenis</a>
                            </li>
                            <li>
                                <a href="jenis/jenis_ubah.php"><i class="fa fa-edit"></i> Ubah Jenis</a>
                            </li>
                            <li>
                                <a href="jenis/jenis_hapus.php"><i class="fa fa-trash"></i> Hapus Jenis</a>
                            </li>
                        </ul>
                    </li>
                    <li>
                        <a href="laporan/laporan.php"><i class="fa fa-file-text fa-2x"></i> Laporan</a>
                    </li>
                    <li>
                        <a href="telegram/telegram.php"><i class="fa fa-paper-plane fa-2x"></i> Notifikasi Telegram</a>
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
                            <p>Di Sistem Informasi Cepat Pengaduan Fasilitas Kampus (SiCepu)</p>
                        </div>
                    </div>
                </div>
                 <hr />
                <div class="row">
                    <div class="col-md-3 col-sm-6 col-xs-6">
                        <div class="panel panel-primary text-center no-boder bg-color-green">
                            <div class="panel-left pull-left">
                                <i class="fa fa-bell fa-5x"></i>
                            </div>
                            <div class="panel-right">
                                <div class="main-text"><?php echo $total_masuk; ?></div>
                                <div class="text-box">Aduan Masuk</div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3 col-sm-6 col-xs-6">
                        <div class="panel panel-primary text-center no-boder bg-color-blue">
                            <div class="panel-left pull-left">
                                <i class="fa fa-spinner fa-5x"></i>
                            </div>
                            <div class="panel-right">
                                <div class="main-text"><?php echo $total_diproses; ?></div>
                                <div class="text-box">Aduan Diproses</div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3 col-sm-6 col-xs-6">
                        <div class="panel panel-primary text-center no-boder bg-color-red">
                            <div class="panel-left pull-left">
                                <i class="fa fa-check-square fa-5x"></i>
                            </div>
                            <div class="panel-right">
                                <div class="main-text"><?php echo $total_selesai; ?></div>
                                <div class="text-box">Aduan Selesai</div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3 col-sm-6 col-xs-6">
                        <div class="panel panel-primary text-center no-boder bg-color-custom">
                            <div class="panel-left pull-left">
                                <i class="fa fa-list fa-5x"></i>
                            </div>
                            <div class="panel-right">
                                <div class="main-text"><?php echo $total_aduan; ?></div>
                                <div class="text-box">Total Aduan</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            </div>
        </div>
    <script src="../assets/js/bootstrap.min.js"></script>
    <script src="../assets/js/custom.js"></script>
    
</body>
</html>