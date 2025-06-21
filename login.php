<?php
// Pastikan inc/koneksi.php sudah memulai session_start();
// Jika tidak, tambahkan session_start(); di baris paling atas file ini.
include "inc/koneksi.php";

// Variabel untuk menyimpan pesan error (akan ditampilkan di HTML langsung)
$error_message = '';

if (isset($_POST['btnLogin'])) {
    $username_input = $_POST['username'];
    $password_input = $_POST['password'];

    $sql_login = "SELECT id_pengguna, username, password, nama_pengguna, level FROM tb_pengguna WHERE username = ?";
    
    if ($koneksi === false) {
        die("Koneksi database belum dibuat atau gagal.");
    }

    $stmt = mysqli_prepare($koneksi, $sql_login);
    
    if ($stmt === false) {
        die("Prepare failed: " . mysqli_error($koneksi));
    }

    mysqli_stmt_bind_param($stmt, "s", $username_input);
    mysqli_stmt_execute($stmt);
    $query_login = mysqli_stmt_get_result($stmt);
    $data_login = mysqli_fetch_array($query_login, MYSQLI_ASSOC);
    $jumlah_login = mysqli_num_rows($query_login);

    if ($jumlah_login == 1) {
        // --- PENTING: Verifikasi Password dengan password_verify() ---
        if (password_verify($password_input, $data_login['password'])) {
            // Login berhasil
            $_SESSION["ses_id"] = $data_login["id_pengguna"];
            $_SESSION["ses_username"] = $data_login["username"];
            $_SESSION["ses_nama"] = $data_login["nama_pengguna"]; 
            $_SESSION["ses_level"] = $data_login["level"];
            $_SESSION["loggedin"] = true; 

            $redirect_url = '';
            if ($_SESSION["ses_level"] == 'Administrator') {
                $redirect_url = 'default/admin.php';
            } elseif ($_SESSION["ses_level"] == 'Petugas') {
                $redirect_url = 'default/tugas.php'; // Atau 'default/admin.php' jika petugas pakai dashboard admin
            } elseif ($_SESSION["ses_level"] == 'Pengadu') {
                $redirect_url = 'default/pengadu.php';
            } else {
                // Level tidak dikenal
                session_unset();
                session_destroy();
                header("Location: login1.php?error=level_tidak_dikenal"); // Redirect ke login1.php
                exit();
            }

            // --- REDIRECT LANGSUNG DARI PHP ---
            header("Location: " . $redirect_url);
            exit(); // SANGAT PENTING untuk menghentikan eksekusi script
        } else {
            // Password tidak cocok
            header("Location: login1.php?error=password_salah"); // Redirect ke login1.php
            exit();
        }
    } else {
        // Username tidak ditemukan
        header("Location: login1.php?error=user_tidak_ditemukan"); // Redirect ke login1.php
        exit();
    }
    mysqli_stmt_close($stmt);
}
?>
<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Login Sistem Pengaduan</title> <link href="assets/css/bootstrap.css" rel="stylesheet" />
    <link href="assets/css/font-awesome.css" rel="stylesheet" />
    <link href="assets/css/custom.css" rel="stylesheet" />
    <link href='http://fonts.googleapis.com/css?family=Open+Sans' rel='stylesheet' type='text/css' />
    <link href="assets/css/signup.css" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@9/dist/sweetalert2.min.css">
</head>
<body>
    <div class="container">
        <div class="row text-center ">
            <div class="col-md-12">
                <br /><br />
                <h2> Sistem Pengaduan : Login</h2>
                <h5>( Login Sendiri )</h5>
                <br />
            </div>
        </div>
         <div class="row ">
               
                <div class="col-md-4 col-md-offset-4 col-sm-6 col-sm-offset-3 col-xs-10 col-xs-offset-1">
                        <div class="panel panel-default">
                            <div class="panel-heading">
                        <strong>  Masukkan Detail Login </strong>  
                            </div>
                            <div class="panel-body">
                                <form role="form" action="" method="post">
                                       <br />
                                     <div class="form-group input-group">
                                            <span class="input-group-addon"><i class="fa fa-tag"  ></i></span>
                                            <input type="text" class="form-control" name="username" placeholder="Username Anda " required />
                                        </div>
                                        <div class="form-group input-group">
                                            <span class="input-group-addon"><i class="fa fa-lock"  ></i></span>
                                            <input type="password" class="form-control"  name="password" placeholder="Password Anda" required />
                                        </div>
                                    <div class="form-group">
                                            <label class="checkbox-inline">
                                                <input type="checkbox" /> Ingat Saya
                                            </label>
                                            <span class="pull-right">
                                                   <a href="#" >Lupa Password ? </a> 
                                            </span>
                                        </div>
                                     
                                    <input type="submit" name="btnLogin" value="Login Sekarang" class="btn btn-primary ">
                                    <hr />
                                    Belum Punya Akun ? <a href="signup.php" >Klik disini untuk daftar </a>
                                    </form>
                                    <?php
                                    // Tampilkan pesan error di sini jika ada parameter error di URL (dari redirect gagal)
                                    if (isset($_GET['error'])) {
                                        $error_msg_display = '';
                                        if ($_GET['error'] == 'level_tidak_dikenal') {
                                            $error_msg_display = 'Level pengguna tidak dikenal.';
                                        } elseif ($_GET['error'] == 'password_salah') {
                                            $error_msg_display = 'Username atau password salah.';
                                        } elseif ($_GET['error'] == 'user_tidak_ditemukan') {
                                            $error_msg_display = 'Username atau password salah.';
                                        }
                                        echo '<div class="alert alert-danger" style="margin-top: 20px;">' . htmlspecialchars($error_msg_display) . '</div>';
                                    }
                                    ?>
                            </div>
                           
                        </div>
                    </div>
                
                
        </div>
    </div>


     <script src="assets/js/jquery-1.10.2.js"></script>
      <script src="assets/js/bootstrap.min.js"></script>
    <script src="assets/js/jquery.metisMenu.js"></script>
      <script src="assets/js/custom.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@9"></script>
</body>
</html>