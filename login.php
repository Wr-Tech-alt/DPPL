<?php
// Pastikan koneksi dan session dimulai.
// File koneksi.php sudah diubah untuk memulai session.
include 'inc/koneksi.php';

$error_message = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Menggunakan username untuk login, sesuai struktur baru
    $username_input = mysqli_real_escape_string($conn, $_POST['email']); // Asumsi 'email' di form login sekarang adalah 'username'
    $password_input = mysqli_real_escape_string($conn, $_POST['password']);

    // Query untuk mengambil data pengguna berdasarkan username
    // Kita butuh password (hashed) dan level
    $sql = "SELECT id_pengguna, username, password, nama_lengkap, level FROM pengguna WHERE username = '$username_input'";
    $result = mysqli_query($conn, $sql);

    if (mysqli_num_rows($result) > 0) {
        $user = mysqli_fetch_assoc($result);

        // --- PENTING: Verifikasi Password ---
        // Anda HARUS menggunakan password_verify() jika password di database Anda di-hash.
        // Jika belum, ini adalah contoh untuk transisi. Segera hash password di DB!
        // Jika password di database Anda saat ini masih polos (TIDAK AMAN):
        if ($password_input == $user['password']) { // <<--- GANTI INI DENGAN password_verify() !!!
        // Contoh jika sudah menggunakan password_hash() saat pendaftaran:
        // if (password_verify($password_input, $user['password'])) {

            // Login berhasil
            $_SESSION['loggedin'] = true;
            $_SESSION['id_pengguna'] = $user['id_pengguna'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['nama_lengkap'] = $user['nama_lengkap'];
            $_SESSION['level'] = $user['level']; // Simpan level ke session

            // Redirect berdasarkan level
            if ($user['level'] == 'admin') {
                header("Location: default/admin.php"); // Arahkan ke dashboard admin
            } elseif ($user['level'] == 'petugas') {
                // Asumsi ada dashboard terpisah untuk petugas, atau mereka diarahkan ke admin dashboard
                // Jika petugas juga mengakses admin_dashboard.php, maka tidak perlu elseif ini.
                // Jika ada default/petugas.php, ganti ke situ
                header("Location: default/admin.php"); // Contoh: Petugas juga diarahkan ke dashboard admin
            } elseif ($user['level'] == 'masyarakat') {
                header("Location: default/pengadu.php"); // Arahkan ke dashboard masyarakat/pelapor
            } else {
                // Jika level tidak dikenal, log out dan arahkan ke login dengan error
                session_unset();
                session_destroy();
                $error_message = "Level pengguna tidak dikenal.";
            }
            exit();
        } else {
            // Password salah
            $error_message = "Username atau password salah.";
        }
    } else {
        // Username tidak ditemukan
        $error_message = "Username atau password salah.";
    }
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