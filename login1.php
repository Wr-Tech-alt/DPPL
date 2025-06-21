<?php
// Pastikan inc/koneksi.php sudah memulai session_start();
// Jika tidak, tambahkan session_start(); di baris paling atas file ini.
include "inc/koneksi.php"; // Ini diasumsikan sudah ada session_start() di dalamnya

// Variabel untuk menyimpan pesan error (jika diperlukan untuk HTML langsung)
$error_message = '';

if (isset($_POST['btnLogin'])) {
    $username_input = $_POST['username']; // Input dari form
    $password_input = $_POST['password']; // Input dari form

    // Gunakan Prepared Statements untuk keamanan SQL Injection
    // Menggunakan tb_pengguna dan nama_pengguna sesuai kode Anda yang terakhir diberikan
    $sql_login = "SELECT id_pengguna, username, password, nama_pengguna, level FROM tb_pengguna WHERE username = ?";
    
    // Periksa apakah koneksi valid sebelum prepare
    if ($koneksi === false) { // Menggunakan $koneksi sesuai inc/koneksi.php Anda
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
        // Ini akan berhasil jika password di DB Anda sudah di-hash menggunakan password_hash()
        if (password_verify($password_input, $data_login['password'])) {
            // Login berhasil
            $_SESSION["ses_id"] = $data_login["id_pengguna"];
            $_SESSION["ses_username"] = $data_login["username"];
            // Menggunakan 'nama_pengguna' sesuai kode dan struktur yang Anda tunjukkan terakhir
            $_SESSION["ses_nama"] = $data_login["nama_pengguna"]; 
            // Menggunakan 'level' dengan kapitalisasi sesuai kode Anda
            $_SESSION["ses_level"] = $data_login["level"];
            $_SESSION["loggedin"] = true; // Tambahkan ini untuk konsistensi cek login

            // Tentukan URL redirect_url tujuan berdasarkan level (sesuaikan dengan nilai Anda: 'Administrator', 'Petugas', 'Pengadu')
            $redirect_url = '';
            if ($_SESSION["ses_level"] == 'Administrator') {
                $redirect_url = 'default/admin.php'; // Langsung ke dashboard admin
            } elseif ($_SESSION["ses_level"] == 'Petugas') {
                $redirect_url = 'default/tugas.php'; // Langsung ke dashboard petugas
            } elseif ($_SESSION["ses_level"] == 'Pengadu') {
                $redirect_url = 'default/pengadu.php'; // Langsung ke dashboard masyarakat/pengadu
            } else {
                // Level tidak dikenal (fallback)
                session_unset(); // Hapus semua variabel session
                session_destroy(); // Hancurkan session
                $redirect_url = 'login1.php'; // Kembali ke login1.php jika level tidak valid
                echo "<script>
                    Swal.fire({title: 'GAGAL', text: 'Level pengguna tidak dikenal.', icon: 'error', confirmButtonText: 'OK'})
                    .then((result) => {
                        if (result.value) {
                            window.location = '" . $redirect_url . "';
                        }
                    })</script>";
                exit(); // Penting untuk menghentikan eksekusi
            }

            // Eksekusi SweetAlert dan kemudian redirect ke URL yang ditentukan
            echo "<script>
                Swal.fire({title: 'Login Berhasil!', text: 'Selamat datang " . htmlspecialchars($_SESSION["ses_nama"]) . "!', icon: 'success', confirmButtonText: 'OK'})
                .then((result) => {
                    if (result.value) {
                        window.location = '" . $redirect_url . "'; // LANGSUNG KE DASHBOARD MASING-MASING!
                    }
                })</script>";
            exit(); // Sangat penting untuk menghentikan eksekusi setelah SweetAlert & redirect
        } else {
            // Password tidak cocok
            echo "<script>
                Swal.fire({title: 'GAGAL', text: 'Username atau password salah.', icon: 'error', confirmButtonText: 'OK'})
                .then((result) => {
                    if (result.value) {
                        window.location = 'login1.php'; // Kembali ke login1.php
                    }
                })</script>";
        }
    } else {
        // Username tidak ditemukan
        echo "<script>
            Swal.fire({title: 'GAGAL', text: 'Username atau password salah.', icon: 'error', confirmButtonText: 'OK'})
            .then((result) => {
                if (result.value) {
                    window.location = 'login1.php'; // Kembali ke login1.php
                }
            })</script>";
    }
    mysqli_stmt_close($stmt);
}
?>
<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Login Sistem Pengaduan</title>
    <link href="assets/css/bootstrap.css" rel="stylesheet" />
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