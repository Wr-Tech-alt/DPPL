<?php
include "inc/koneksi.php"; // Pastikan inc/koneksi.php sudah memulai session

// ... (HTML bagian atas tetap sama) ...

if (isset($_POST['btnLogin'])) {
    $username_input = $_POST['username'];
    $password_input = $_POST['password'];

    // Gunakan Prepared Statements untuk keamanan SQL Injection
    $sql_login = "SELECT id_pengguna, username, password, nama_lengkap, level FROM pengguna WHERE username = ?";
    
    // Periksa apakah koneksi valid sebelum prepare
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
            $_SESSION["ses_nama_lengkap"] = $data_login["nama_lengkap"];
            $_SESSION["ses_level"] = $data_login["level"];

            // Redirect berdasarkan level
            $redirect_url = '';
            if ($_SESSION["ses_level"] == 'admin') {
                $redirect_url = 'default/admin.php'; // Arahkan ke dashboard admin
            } elseif ($_SESSION["ses_level"] == 'petugas') {
                $redirect_url = 'default/admin.php'; // Arahkan ke dashboard admin (atau default/petugas.php jika ada)
            } elseif ($_SESSION["ses_level"] == 'masyarakat') {
                $redirect_url = 'default/pengadu.php'; // Arahkan ke dashboard masyarakat/pelapor
            } else {
                // Level tidak dikenal, seharusnya tidak terjadi jika enum sudah benar di DB
                session_unset(); // Hapus semua variabel session
                session_destroy(); // Hancurkan session
                $redirect_url = 'login.php'; // Kembali ke login
                echo "<script>
                    Swal.fire({title: 'GAGAL', text: 'Level pengguna tidak dikenal.', icon: 'error', confirmButtonText: 'OK'})
                    .then((result) => {
                        if (result.value) {
                            window.location = '" . $redirect_url . "';
                        }
                    })</script>";
                exit(); // Penting untuk menghentikan eksekusi
            }

            echo "<script>
                Swal.fire({title: 'Login Berhasil!', text: 'Selamat datang " . htmlspecialchars($_SESSION["ses_nama_lengkap"]) . "!', icon: 'success', confirmButtonText: 'OK'})
                .then((result) => {
                    if (result.value) {
                        window.location = '" . $redirect_url . "'; // Inilah yang diubah!
                    }
                })</script>";
        } else {
            // Password tidak cocok
            echo "<script>
                Swal.fire({title: 'GAGAL', text: 'Username atau password salah.', icon: 'error', confirmButtonText: 'OK'})
                .then((result) => {
                    if (result.value) {
                        window.location = 'login.php';
                    }
                })</script>";
        }
    } else {
        // Username tidak ditemukan
        echo "<script>
            Swal.fire({title: 'GAGAL', text: 'Username atau password salah.', icon: 'error', confirmButtonText: 'OK'})
            .then((result) => {
                if (result.value) {
                    window.location = 'login.php';
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
                                            <input type="text" class="form-control" name="email" placeholder="Username Anda " required />
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
                                     
                                    <input type="submit" name="login" value="Login Sekarang" class="btn btn-primary ">
                                    <hr />
                                    Belum Punya Akun ? <a href="signup.php" >Klik disini untuk daftar </a>
                                    </form>
                                    <?php if (!empty($error_message)): ?>
                                        <div class="alert alert-danger" style="margin-top: 20px;">
                                            <?php echo $error_message; ?>
                                        </div>
                                    <?php endif; ?>
                            </div>
                           
                        </div>
                    </div>
                
                
        </div>
    </div>


     <script src="assets/js/jquery-1.10.2.js"></script>
      <script src="assets/js/bootstrap.min.js"></script>
    <script src="assets/js/jquery.metisMenu.js"></script>
      <script src="assets/js/custom.js"></script>
   
</body>
</html>