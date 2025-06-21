<?php
// Pastikan koneksi dan session dimulai
include '../inc/koneksi.php'; // Path disesuaikan karena berada di sub-folder

// Periksa apakah pengguna sudah login dan apakah level-nya 'admin' atau 'petugas'
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true || ($_SESSION['level'] !== 'admin' && $_SESSION['level'] !== 'petugas')) {
    header("Location: ../login.php"); // Arahkan kembali ke halaman login
    exit();
}
?>
<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Dashboard Admin</title>
    <link href="../assets/css/bootstrap.css" rel="stylesheet" />
    <link href="../assets/css/font-awesome.css" rel="stylesheet" />
    <link href="../assets/js/morris/morris-0.4.3.min.css" rel="stylesheet" />
    <link href="../assets/css/custom.css" rel="stylesheet" />
    <link href='http://fonts.googleapis.com/css?family=Open+Sans' rel='stylesheet' type='text/css' />
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
                <a class="navbar-brand" href="admin.php">Admin Dashboard</a> 
            </div>
            <div style="color: white; padding: 15px 50px 5px 50px; float: right; font-size: 16px;"> 
                Last access : <?php echo date("d-M-Y H:i"); ?> &nbsp; 
                <a href="../logout.php" class="btn btn-danger square-btn-adjust">Logout</a> 
            </div>
        </nav>   
        <nav class="navbar-default navbar-side" role="navigation">
            <div class="sidebar-collapse">
                <ul class="nav" id="main-menu">
                    <li class="text-center">
                        <img src="../assets/img/find_user.png" class="user-image img-responsive"/>
                    </li>
                    <li><a class="active-menu"  href="admin.php"><i class="fa fa-dashboard fa-3x"></i> Dashboard</a></li>
                    <li><a  href="../admin/aduan/adu_tampil.php"><i class="fa fa-desktop fa-3x"></i> Kelola Aduan</a></li>
                    <?php if ($_SESSION['level'] == 'admin'): // Hanya admin yang bisa kelola pengguna ?>
                    <li><a  href="../admin/pengguna/pengguna_tampil.php"><i class="fa fa-users fa-3x"></i> Kelola Pengguna</a></li>
                    <?php endif; ?>
                    <li><a  href="../admin/jenis/jenis_tampil.php"><i class="fa fa-list fa-3x"></i> Kelola Jenis Pengaduan</a></li>
                    <li><a  href="../admin/laporan/laporan.php"><i class="fa fa-file-text fa-3x"></i> Laporan</a></li>
                    <li><a  href="../admin/telegram/telegram.php"><i class="fa fa-paper-plane fa-3x"></i> Notifikasi Telegram</a></li>
                </ul>
            </div>
        </nav>  
        <div id="page-wrapper" >
            <div id="page-inner">
                <div class="row">
                    <div class="col-md-12">
                        <h2>Admin Dashboard</h2>   
                        <h5>Selamat datang, <?php echo htmlspecialchars($_SESSION['nama_lengkap']); ?> ! Anda login sebagai <?php echo htmlspecialchars($_SESSION['level']); ?>. </h5>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-3 col-sm-6 col-xs-6">           
                        <div class="panel panel-back noti-box">
                            <span class="icon-box bg-color-red set-icon">
                                <i class="fa fa-envelope-o"></i>
                            </span>
                            <div class="text-box" >
                                <p class="main-text">120 Laporan</p>
                                <p class="text-muted">Baru</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3 col-sm-6 col-xs-6">           
                        <div class="panel panel-back noti-box">
                            <span class="icon-box bg-color-green set-icon">
                                <i class="fa fa-bars"></i>
                            </span>
                            <div class="text-box" >
                                <p class="main-text">300 Laporan</p>
                                <p class="text-muted">Total</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3 col-sm-6 col-xs-6">           
                        <div class="panel panel-back noti-box">
                            <span class="icon-box bg-color-blue set-icon">
                                <i class="fa fa-bell-o"></i>
                            </span>
                            <div class="text-box" >
                                <p class="main-text">240 Pemberitahuan</p>
                                <p class="text-muted">Baru</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3 col-sm-6 col-xs-6">           
                        <div class="panel panel-back noti-box">
                            <span class="icon-box bg-color-brown set-icon">
                                <i class="fa fa-users"></i>
                            </span>
                            <div class="text-box" >
                                <p class="main-text">150 Pengguna</p>
                                <p class="text-muted">Terdaftar</p>
                            </div>
                        </div>
                    </div>
                </div>
                <hr />
                </div>
             </div>
         </div>
     <script src="../assets/js/jquery-1.10.2.js"></script>
      <script src="../assets/js/bootstrap.min.js"></script>
    <script src="../assets/js/jquery.metisMenu.js"></script>
     <script src="../assets/js/morris/raphael-2.1.0.min.js"></script>
     <script src="../assets/js/morris/morris.js"></script>
      <script src="../assets/js/custom.js"></script>
    
   
</body>
</html>