<?php
session_start(); // Pastikan session_start() ada di paling awal. Kode Anda sudah ada.

// Cek apakah pengguna sudah login
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header("location: login.php"); // Arahkan ke halaman login jika belum login
    exit();
}

// Ambil data session
$data_id = $_SESSION["ses_id"];
$data_nama = $_SESSION["ses_nama_lengkap"]; // Sesuaikan dengan nama lengkap
$data_level = $_SESSION["ses_level"];
// $data_grup = $_SESSION["ses_grup"]; // Hapus ini jika tidak ada di session lagi

include "inc/koneksi.php";

?>

<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml">

<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>SICEPU</title>
    <link rel="icon" href="assets/img/stmi.png" type="image/icon type">
    <link href="assets/css/bootstrap.css" rel="stylesheet" />
    <link href="assets/css/font-awesome.css" rel="stylesheet" />
    <link href="assets/css/style.css" rel="stylesheet" />
    <link href='http://fonts.googleapis.com/css?family=Open+Sans' rel='stylesheet' type='text/css' />
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Roboto&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="dist/css/select2.min.css" />

    <link href="assets/js/dataTables/dataTables.bootstrap.css" rel="stylesheet" />

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@9"></script>
    <style>
        .swal2-popup {
            font-size: 1.6rem !important;
        }

        /* --- CSS Tambahan/Modifikasi untuk tampilan mirip gambar kedua --- */
        body {
            font-family: 'Roboto', sans-serif; /* Menggunakan Roboto sesuai dengan kode Anda */
            background-color: #f0f2f5; /* Warna background lebih terang */
        }

        #wrapper {
            display: flex; /* Menggunakan flexbox untuk layout sidebar dan konten */
            min-height: 100vh;
        }

        /* Navbar Samping (Sidebar) */
        .navbar-default.navbar-side {
            background-color: #34495e; /* Warna gelap untuk sidebar */
            border-right: none;
            width: 250px; /* Lebar sidebar */
            position: fixed;
            height: 100%;
            padding-top: 20px;
            box-shadow: 2px 0 5px rgba(0,0,0,0.1);
            z-index: 1001; /* Pastikan di atas konten */
        }

        .navbar-side .sidebar-collapse {
            padding: 0 15px;
        }

        .navbar-side .user-image {
            display: block;
            margin: 0 auto 30px auto;
            border-radius: 50%; /* Membuat gambar bulat */
            width: 100px; /* Ukuran gambar */
            height: 100px;
            object-fit: cover;
            border: 3px solid #fff; /* Border putih */
            box-shadow: 0 0 10px rgba(0,0,0,0.3);
        }

        .navbar-side .nav > li > a {
            color: #ecf0f1; /* Warna teks menu */
            padding: 12px 15px;
            margin-bottom: 5px;
            border-radius: 8px;
            transition: background-color 0.3s ease, color 0.3s ease;
            display: flex; /* Untuk ikon dan teks sejajar */
            align-items: center;
        }

        .navbar-side .nav > li > a:hover,
        .navbar-side .nav > li.active-menu > a {
            background-color: #2c3e50; /* Warna hover/active */
            color: #18bc9c; /* Warna teks active/hover lebih terang */
        }

        .navbar-side .nav > li > a .fa-2x {
            font-size: 1.5em; /* Ukuran ikon */
            margin-right: 15px;
            width: 25px; /* Lebar tetap untuk ikon */
            text-align: center;
        }

        .navbar-side .nav .nav-second-level li a {
            padding-left: 55px; /* Indent sub-menu */
            font-size: 0.95em;
            color: #bdc3c7; /* Warna teks sub-menu */
        }

        .navbar-side .nav .nav-second-level li a:hover {
            background-color: #253340;
            color: #fff;
        }
        /* Akhir Navbar Samping */


        /* Navbar Atas (Top Navbar) */
        .navbar-cls-top {
            background-color: #34495e; /* Ubah warna ini agar sama dengan sidebar */
            border-bottom: none;
            height: 60px;
            padding: 0 20px;
            display: flex;
            align-items: center;
            justify-content: space-between; /* Untuk merata-ratakan item */
            margin-left: 250px; /* Offset dari sidebar */
            width: calc(100% - 250px); /* Lebar navbar atas */
            position: fixed;
            top: 0;
            left: 0;
            z-index: 1000;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }

        .navbar-header {
            display: none; /* Sembunyikan bagian navbar-header lama jika tidak diperlukan */
        }

        .navbar-cls-top .navbar-brand {
            font-size: 24px;
            font-weight: 700;
            color: #fff;
            display: flex;
            align-items: center;
        }
        .navbar-cls-top .navbar-brand i {
            margin-right: 10px;
        }

        /* Styling untuk nama level pengguna di tengah */
        .namalevel {
            flex-grow: 1; /* Agar mengisi ruang kosong */
            text-align: center;
            font-weight: 500;
            color: #fff; /* Warna teks putih */
            font-size: 1.1em;
            padding-left: 20px; /* Sedikit padding agar tidak terlalu mepet dengan search */
        }

        /* Search Box */
        .search-box {
            position: relative;
            width: 280px; /* Lebar search box */
        }
        .search-box input {
            width: 100%;
            padding: 8px 15px 8px 40px; /* Padding untuk ikon */
            border: none;
            border-radius: 25px; /* Border radius lebih besar */
            background-color: rgba(255, 255, 255, 0.2); /* Background transparan */
            color: #fff;
            font-size: 1em;
            outline: none;
        }
        .search-box input::placeholder {
            color: rgba(255, 255, 255, 0.7);
        }
        .search-box .fa-search {
            position: absolute;
            left: 15px;
            top: 50%;
            transform: translateY(-50%);
            color: rgba(255, 255, 255, 0.8);
        }

        /* Profile dan Logout (disatukan) */
        .profile-logout-section {
            display: flex;
            align-items: center;
            gap: 15px; /* Jarak antar elemen */
        }

        .profile-avatar {
            width: 35px;
            height: 35px;
            border-radius: 50%;
            background-color: rgba(255, 255, 255, 0.3); /* Background ikon profil */
            display: flex;
            align-items: center;
            justify-content: center;
            color: #fff;
            font-size: 1.2em;
        }

        .logout-btn {
            background-color: #ff4d4f; /* Warna merah untuk logout */
            color: white;
            border: none;
            padding: 8px 15px;
            border-radius: 25px; /* Border radius yang sama dengan search box */
            cursor: pointer;
            text-decoration: none;
            transition: background-color 0.3s ease;
            display: flex;
            align-items: center;
            gap: 5px; /* Jarak antara ikon dan teks */
        }

        .logout-btn:hover {
            background-color: #cf1322;
            color: white;
        }

        /* Akhir Navbar Atas */

        /* Konten Halaman */
        #page-wrapper {
            margin-left: 250px; /* Offset dari sidebar */
            padding: 20px;
            width: calc(100% - 250px);
            margin-top: 60px; /* Offset dari top navbar */
        }

        #page-inner {
            background-color: #fff;
            padding: 25px;
            border-radius: 10px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.08);
            min-height: calc(100vh - 80px); /* Sesuaikan tinggi minimum */
        }

        #marquee {
            text-align: center;
            margin-bottom: 25px;
            padding-bottom: 15px;
            border-bottom: 1px solid #eee;
            color: #333;
        }

        #marquee h4 {
            font-size: 22px;
            font-weight: 600;
            margin-bottom: 5px;
        }

        #marquee p {
            font-size: 15px;
            color: #777;
            margin-top: 0;
        }

        /* Dashboard Cards Styling (jika ada di default/pengadu.php, dll.) */
        .dashboard-cards {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
            gap: 20px;
            margin-top: 20px;
        }

        .card {
            background-color: #fff;
            border-radius: 10px;
            padding: 25px;
            box-shadow: 0 4px 10px rgba(0,0,0,0.08);
            display: flex;
            flex-direction: column;
            align-items: center;
            text-align: center;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            border: 1px solid #eee; /* Tambahkan border tipis */
        }

        .card:hover {
            transform: translateY(-5px);
            box-shadow: 0 6px 15px rgba(0,0,0,0.12);
        }

        .card-icon {
            font-size: 3em;
            margin-bottom: 15px;
            width: 80px;
            height: 80px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }

        /* Specific card colors */
        .card.pending .card-icon { background-color: #ff4d4f; } /* Red */
        .card.responded .card-icon { background-color: #52c41a; } /* Green */
        .card.completed .card-icon { background-color: #1890ff; } /* Blue */
        .card.add-new .card-icon { background-color: #9254de; } /* Purple */

        .card-number {
            font-size: 2.5em;
            font-weight: 700;
            color: #333;
            margin-bottom: 5px;
        }

        .card-title {
            font-size: 1.1em;
            color: #666;
        }

        .card.add-new .card-title {
            font-weight: 600;
            color: #9254de;
        }

        /* Responsive adjustments */
        @media (max-width: 768px) {
            .navbar-default.navbar-side {
                width: 100%;
                position: relative;
                height: auto;
            }

            .navbar-cls-top {
                margin-left: 0;
                width: 100%;
                flex-direction: column; /* Stack items vertically */
                height: auto;
                padding: 10px;
            }

            .search-box, .namalevel, .profile-logout-section {
                width: 100%;
                margin-bottom: 10px;
                text-align: center;
            }

            .profile-logout-section {
                justify-content: center;
            }

            #page-wrapper {
                margin-left: 0;
                width: 100%;
                margin-top: 0; /* No offset needed from collapsed top navbar */
                padding: 15px;
            }
        }

    </style>

</head>

<body>
    <div id="wrapper">
        <nav class="navbar-default navbar-side" role="navigation">
            <div class="sidebar-collapse">
                <ul class="nav" id="main-menu">
                    <li class="text-center">
                        <img src="assets/img/stmi.png" class="user-image img-responsive" />
                    </li>

                    <?php
                    // Pastikan nama level sesuai dengan yang ada di database Anda: 'admin', 'petugas', 'masyarakat'
                    if ($data_level == "admin") { // Menggunakan 'admin'
                    ?>
                        <li>
                            <a href="?page=admin-def">
                                <i class="fa fa-dashboard fa-2x"></i> Dashboard</a>
                        </li>
                        <li>
                            <a href="#">
                                <i class="fa fa-file fa-2x"></i> Master Data
                                <span class="fa arrow"></span>
                            </a>
                            <ul class="nav nav-second-level">
                                <li>
                                    <a href="?page=pengadu_view">Data Pengadu</a>
                                </li>
                                <li>
                                    <a href="?page=jenis_view">Jenis Pengaduan</a>
                                </li>
                            </ul>
                        </li>
                        <li>
                            <a href="#">
                                <i class="fa fa-bell fa-2x"></i> Pengaduan
                                <span class="fa arrow"></span>
                            </a>
                            <ul class="nav nav-second-level">
                                <li>
                                    <a href="?page=aduan_admin">Menunggu</a>
                                </li>
                                <li>
                                    <a href="?page=aduan_admin_tanggap">Ditanggapi</a>
                                </li>
                                <li>
                                    <a href="?page=aduan_admin_selesai">Selesai</a>
                                </li>
                            </ul>
                        </li>
                        <li>
                            <a href="?page=telegram">
                                <i class="fa fa-comments-o fa-2x"></i> Telegram</a>
                        </li>
                        <li>
                            <a href="?page=user_data">
                                <i class="fa fa-user fa-2x"></i> Pengguna</a>
                        </li>
                        <li>
                            <a href="?page=laporan">
                                <i class="fa fa-file-text-o fa-2x"></i> Laporan</a>
                        </li>

                    <?php
                    } elseif ($data_level == "petugas") { // Menggunakan 'petugas'
                    ?>
                        <li>
                            <a href="?page=petugas-def">
                                <i class="fa fa-dashboard fa-2x"></i> Dashboard</a>
                        </li>
                        <li>
                            <a href="#">
                                <i class="fa fa-bell fa-2x"></i> Pengaduan
                                <span class="fa arrow"></span>
                            </a>
                            <ul class="nav nav-second-level">
                                <li>
                                    <a href="?page=aduan_admin">Menunggu</a>
                                </li>
                                <li>
                                    <a href="?page=aduan_admin_tanggap">Ditanggapi</a>
                                </li>
                                <li>
                                    <a href="?page=aduan_admin_selesai">Selesai</a>
                                </li>
                            </ul>
                        </li>
                    <?php
                    } elseif ($data_level == "masyarakat") { // Menggunakan 'masyarakat'
                    ?>

                        <li>
                            <a href="?page=pengadu">
                                <i class="fa fa-dashboard fa-2x"></i> Dashboard</a>
                        </li>
                        <li>
                            <a href="?page=aduan_view">
                                <i class="fa fa-bell fa-2x"></i> Pengaduan
                            </a>
                        </li>

                    <?php
                    }
                    ?>
                </ul>
            </div>
        </nav>
        <nav class="navbar navbar-default navbar-cls-top" role="navigation">
            <div class="search-box">
                <i class="fa fa-search"></i>
                <input type="text" placeholder="Search..." />
            </div>
            <div class="namalevel">
                <?= $data_nama; ?> (<?= $data_level; ?>)
            </div>

            <div class="profile-logout-section">
                <div class="profile-avatar">
                    <i class="fa fa-user"></i> </div>
                <a href="logout.php" onclick="return confirm('Apakah anda yakin ingin keluar dari aplikasi ini ?')" class="logout-btn">
                    <i class="fa fa-sign-out"></i> Logout
                </a>
            </div>
        </nav>
        <div id="page-wrapper">
            <div id="page-inner">
                <div class="row">
                    <div class="col-md-12">
                        <h4 id="marquee">
                            <b> SISTEM INFORMASI PENGADUAN FASILITAS UMUM DI LINGKUNGAN POLITEKNIK STMI JAKARTA - BERBASIS WEBSITE </b>
                            <p style="margin-top: -10px;">MENGGUNAKAN REALTIME NOTIFIKASI TELEGRAM</p>
                        </h4>
                        <?php
                        // Ini adalah router utama
                        if (isset($_GET['page'])) {
                            $hal = $_GET['page'];

                            switch ($hal) {
                                case 'admin-def':
                                    include "default/admin.php";
                                    break;
                                case 'petugas-def':
                                    include "default/tugas.php"; // Ini adalah dashboard untuk petugas
                                    break;
                                case 'pengadu':
                                    include "default/pengadu.php"; // Ini adalah dashboard untuk pengadu
                                    break;

                                //User Admin (pengguna)
                                case 'user_data':
                                    include "admin/pengguna/pengguna_tampil.php";
                                    break;
                                case 'pengguna_tambah':
                                    include "admin/pengguna/pengguna_tambah.php";
                                    break;
                                case 'pengguna_ubah':
                                    include "admin/pengguna/pengguna_ubah.php";
                                    break;
                                case 'pedu_ubah':
                                    include "admin/pengguna/pedu_ubah.php";
                                    break;
                                case 'pengguna_hapus':
                                    include "admin/pengguna/pengguna_hapus.php";
                                    break;

                                //Jenis Aduan Admin
                                case 'jenis_view':
                                    include "admin/jenis/jenis_tampil.php";
                                    break;
                                case 'jenis_tambah':
                                    include "admin/jenis/jenis_tambah.php";
                                    break;
                                case 'jenis_ubah':
                                    include "admin/jenis/jenis_ubah.php";
                                    break;
                                case 'jenis_hapus':
                                    include "admin/jenis/jenis_hapus.php";
                                    break;

                                //Pengadu Admin (data pengadu)
                                case 'pengadu_view':
                                    include "admin/pengadu/pengadu_tampil.php";
                                    break;
                                case 'pengadu_tambah':
                                    include "admin/pengadu/pengadu_tambah.php";
                                    break;
                                case 'pengadu_ubah':
                                    include "admin/pengadu/pengadu_ubah.php";
                                    break;
                                case 'pengadu_hapus':
                                    include "admin/pengadu/pengadu_hapus.php";
                                    break;

                                //Aduan Admin / Petugas
                                case 'aduan_admin':
                                    include "admin/aduan/adu_tampil.php";
                                    break;
                                case 'aduan_admin_tanggap':
                                    include "admin/aduan/adu_tanggap.php";
                                    break;
                                case 'aduan_admin_selesai':
                                    include "admin/aduan/adu_selesai.php";
                                    break;
                                case 'aduan_kelola':
                                    include "admin/aduan/adu_ubah.php";
                                    break;

                                //Telegram Admin
                                case 'telegram':
                                    include "admin/telegram/telegram.php";
                                    break;

                                //Laporan Admin
                                case 'laporan':
                                    include "admin/laporan/laporan.php";
                                    break;
                                
                                //Logout
                                case 'logout':
                                    include "logout.php";
                                    break;

                                //Aduan Masyarakat (Pengadu)
                                case 'aduan_view': // Melihat riwayat aduan sendiri
                                    include "pengadu/aduan/adu_tampil.php";
                                    break;
                                case 'aduan_tambah': // Proses tambah aduan (setelah form)
                                    include "pengadu/aduan/adu_tambah.php";
                                    break;
                                case 'aduan_tambah_form': // Form untuk menambah aduan
                                    include "pengadu/aduan/adu_tambah_form.php";
                                    break;
                                case 'aduan_ubah':
                                    include "pengadu/aduan/adu_ubah.php";
                                    break;
                                case 'aduan_hapus':
                                    include "pengadu/aduan/adu_hapus.php";
                                    break;

                                //Default (jika tidak ada parameter 'page' di URL)
                                default:
                                    // Arahkan ke dashboard berdasarkan level pengguna saat ini
                                    if ($data_level == "admin") {
                                        include "default/admin.php";
                                    } elseif ($data_level == "petugas") {
                                        include "default/tugas.php";
                                    } elseif ($data_level == "masyarakat") { // Sesuaikan dengan 'masyarakat'
                                        include "default/pengadu.php";
                                    } else {
                                        // Fallback jika level tidak dikenal, bisa ke halaman error atau logout
                                        echo "<center><h1>Level Pengguna Tidak Dikenal!</h1></center>";
                                        // header("location: logout.php"); // Atau langsung logout
                                        // exit();
                                    }
                                    break;
                            }
                        } else {
                            // Jika tidak ada parameter 'page', arahkan ke dashboard berdasarkan level
                            if ($data_level == "admin") {
                                include "default/admin.php";
                            } elseif ($data_level == "petugas") {
                                include "default/tugas.php";
                            } elseif ($data_level == "masyarakat") { // Sesuaikan dengan 'masyarakat'
                                include "default/pengadu.php";
                            } else {
                                // Fallback jika level tidak dikenal saat pertama kali akses index.php
                                echo "<center><h1>Level Pengguna Tidak Dikenal!</h1></center>";
                                // header("location: logout.php"); // Atau langsung logout
                                // exit();
                            }
                        }
                        ?>
                    </div>
                </div>



                <script src="assets/js/jquery-1.10.2.js"></script>
                <script src="assets/js/bootstrap.min.js"></script>
                <script src="assets/js/jquery.metisMenu.js"></script>
                <script src="assets/js/dataTables/jquery.dataTables.js"></script>
                <script src="assets/js/dataTables/dataTables.bootstrap.js"></script>
                <script>
                    $(document).ready(function() {
                        $('#dataTables-example').dataTable();
                    });
                </script>

                <script src="dist/js/select2.min.js"></script>
                <script>
                    $(document).ready(function() {
                        $("#no_pdd").select2({
                            placeholder: "-- Pilih Penduduk --"
                        });
                        $("#no_kk").select2({
                            placeholder: "-- Pilih No.KK --"
                        });
                    });
                </script>
                <script src="assets/js/custom.js"></script>


</body>

</html>