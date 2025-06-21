<?php
// Pastikan koneksi dan session dimulai
include '../inc/koneksi.php'; // Path disesuaikan karena berada di sub-folder

// Periksa apakah pengguna sudah login dan apakah level-nya 'masyarakat'
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true || $_SESSION['level'] !== 'masyarakat') {
    header("Location: ../login.php"); // Arahkan kembali ke halaman login
    exit();
}
?>
<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Dashboard Pelapor</title>
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
                <a class="navbar-brand" href="pengadu.php">Pelapor Dashboard</a> 
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
                    <li><a class="active-menu"  href="pengadu.php"><i class="fa fa-dashboard fa-3x"></i> Dashboard</a></li>
                    <li><a  href="../pengadu/aduan/adu_tambah_form.php"><i class="fa fa-edit fa-3x"></i> Buat Pengaduan</a></li>
                    <li><a  href="../pengadu/aduan/adu_tampil.php"><i class="fa fa-eye fa-3x"></i> Lihat Pengaduan Saya</a></li>
                    </ul>
            </div>
        </nav>  
        <div id="page-wrapper" >
            <div id="page-inner">
                <div class="row">
                    <div class="col-md-12">
                        <h2>Pelapor Dashboard</h2>   
                        <h5>Selamat datang, <?php echo htmlspecialchars($_SESSION['nama_lengkap']); ?> ! Anda login sebagai <?php echo htmlspecialchars($_SESSION['level']); ?>. </h5>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-3 col-sm-6 col-xs-6">           
                        <div class="panel panel-back noti-box">
                            <span class="icon-box bg-color-blue set-icon">
                                <i class="fa fa-file-text-o"></i>
                            </span>
                            <div class="text-box" >
                                <p class="main-text">5 Pengaduan</p>
                                <p class="text-muted">Total Anda</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3 col-sm-6 col-xs-6">           
                        <div class="panel panel-back noti-box">
                            <span class="icon-box bg-color-green set-icon">
                                <i class="fa fa-check"></i>
                            </span>
                            <div class="text-box" >
                                <p class="main-text">3 Pengaduan</p>
                                <p class="text-muted">Selesai</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3 col-sm-6 col-xs-6">           
                        <div class="panel panel-back noti-box">
                            <span class="icon-box bg-color-red set-icon">
                                <i class="fa fa-clock-o"></i>
                            </span>
                            <div class="text-box" >
                                <p class="main-text">2 Pengaduan</p>
                                <p class="text-muted">Pending</p>
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