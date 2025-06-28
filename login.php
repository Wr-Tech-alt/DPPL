<?php
// === PENTING: AKTIFKAN SEMUA ERROR REPORTING UNTUK DEBUGGING ===
error_reporting(E_ALL);
ini_set('display_errors', 1);

// === PASTIKAN INI ADALAH BARIS PERTAMA YANG TEREKSEKUSI.
// === TIDAK ADA SPASI, NEWLINE, ATAU KARAKTER LAIN SEBELUM <?php
ob_start(); // Mulai output buffering

include "inc/koneksi.php"; // Pastikan inc/koneksi.php juga bersih dari output sebelum <?php dan tanpa ?> penutup

// Cek apakah koneksi berhasil di-include
if (!isset($koneksi) || !$koneksi) {
    error_log("DEBUG: \$koneksi variable is not set or is false after including koneksi.php");
    ob_end_clean();
    die("Error: Koneksi database tidak tersedia setelah include koneksi.php.");
}

// === BAGIAN LOGIC PROSES LOGIN (Hanya akan dijalankan jika form di-submit via POST) ===
if (isset($_POST['btnLogin'])) {
    $username_input = $_POST['username'];
    $password_input = $_POST['password'];

    // DEBUG: Log input dan waktu
    error_log("DEBUG: Login attempt for username: " . $username_input . " at " . date('Y-m-d H:i:s'));

    $sql_login = "SELECT id_pengguna, username, password, nama_pengguna, level FROM tb_pengguna WHERE username = ?";
    
    $stmt = mysqli_prepare($koneksi, $sql_login);
    
    if ($stmt === false) {
        error_log("DEBUG: mysqli_prepare failed: " . mysqli_error($koneksi));
        ob_end_clean();
        die("Error: Gagal menyiapkan query login.");
    }

    mysqli_stmt_bind_param($stmt, "s", $username_input);
    mysqli_stmt_execute($stmt);
    $query_login = mysqli_stmt_get_result($stmt);
    $data_login = mysqli_fetch_array($query_login, MYSQLI_ASSOC);
    $jumlah_login = mysqli_num_rows($query_login);

    if ($jumlah_login == 1) {
        // DEBUG: Cek password hash
        error_log("DEBUG: User found. Hashed password from DB: " . (isset($data_login['password']) ? $data_login['password'] : 'N/A'));
        error_log("DEBUG: Provided password (plain): " . $password_input);


        if (password_verify($password_input, $data_login['password'])) {
            // Login berhasil
            $_SESSION["ses_id"] = $data_login["id_pengguna"];
            $_SESSION["ses_username"] = $data_login["username"];
            $_SESSION["ses_nama"] = $data_login["nama_pengguna"]; 
            $_SESSION["ses_level"] = $data_login["level"]; 
            $_SESSION["loggedin"] = true; 

            $redirect_url = '';
            if ($_SESSION["ses_level"] == 'admin') { 
                $redirect_url = 'dashboard/admin.php'; 
            } elseif ($_SESSION["ses_level"] == 'petugas') { 
                $redirect_url = 'dashboard/tugas.php'; 
            } elseif ($_SESSION["ses_level"] == 'masyarakat') { 
                $redirect_url = 'dashboard/pengadu.php'; 
            } else {
                session_unset();
                session_destroy();
                error_log("DEBUG: Login failed: Unknown level for user " . $username_input . " - Level: " . $_SESSION["ses_level"]);
                header("Location: login1.php?error=level_tidak_dikenal_debug");
                ob_end_clean();
                die("DEBUG: Redirect ke login1.php (level tidak dikenal).");
            }
            
            error_log("DEBUG: Login successful. Redirecting to: " . $redirect_url);

            // DEBUG: Cek apakah headers sudah terkirim SEBELUM header()
            if (headers_sent($file, $line)) {
                error_log("FATAL DEBUG: Headers ALREADY SENT before final redirect in file: " . $file . " on line: " . $line);
                ob_end_clean();
                die("FATAL ERROR: Output sudah terkirim sebelum redirect. Cek file: " . htmlspecialchars($file) . " baris: " . htmlspecialchars($line));
            }

            header("Location: " . $redirect_url);
            ob_end_flush(); 
            exit(); 
            die("DEBUG: Script seharusnya sudah berhenti setelah exit()."); // Baris ini tidak akan pernah tercapai jika exit() bekerja

        } else {
            error_log("DEBUG: Login failed: Password mismatch for user " . $username_input);
            header("Location: login1.php?error=password_salah_debug");
            ob_end_clean();
            die("DEBUG: Redirect ke login1.php (password salah).");
        }
    } else {
        error_log("DEBUG: Login failed: User not found: " . $username_input);
        header("Location: login1.php?error=user_tidak_ditemukan_debug");
        ob_end_clean();
        die("DEBUG: Redirect ke login1.php (user tidak ditemukan).");
    }
    mysqli_stmt_close($stmt);
} 
// === AKHIR DARI BAGIAN LOGIC PROSES LOGIN ===

// === BAGIAN TAMPILAN FORM LOGIN (Hanya akan ditampilkan jika TIDAK ada POST atau redirect sudah gagal) ===
else { // <-- Ini adalah blok 'else' dari 'if (isset($_POST['btnLogin']))' di atas
?>
<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Login Sistem Pengaduan</title>
    
    <link href="assets/css/bootstrap.css" rel="stylesheet" />
    <link href="assets/css/font-awesome.css" rel="stylesheet" />
    <link href="assets/css/style.css" rel="stylesheet" />
    <link href="assets/css/signup.css" rel="stylesheet" /> <link href="assets/css/custom.css" rel="stylesheet" /> <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@9/dist/sweetalert2.min.css">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Lato:wght@300;400;700&display=swap" rel="stylesheet">

</head>
<body class="login">
    <div class="container">
        <div class="row"> 
            <div class="col-md-4 col-md-offset-4 col-sm-6 col-sm-offset-3 col-xs-10 col-xs-offset-1">
                <div class="panel panel-primary login-shadow">
                    <div class="panel-body">
                        <img src="assets/img/stmi.png" class="user-image img-responsive" style="max-width: 120px; margin: 0 auto 1.5rem auto; display: block;" /> 
                        <center>
                            <h2 class="text-2xl sm:text-3xl font-bold text-gray-800 text-center mb-2">
                                <b>Masuk ke SiCepu</b>
                            </h2>
                        </center>
                        <center class="text-sm text-gray-600 mb-8">Sistem Informasi Cepat Pengaduan Fasilitas Umum</center>
                        <form action="" method="POST" enctype="multipart/form-data" class="space-y-6">
                            <div class="form-group input-group">
                                <span class="input-group-addon">
                                    <i class="fa fa-tag"></i>
                                </span>
                                <input type="text" class="form-control" value="" placeholder="username" name="username" id="username" required />
                            </div>
                            <div class="form-group input-group">
                                <span class="input-group-addon">
                                    <i class="fa fa-lock"></i>
                                </span>
                                <input type="password" class="form-control" value="" placeholder="password" name="password" id="password" required />
                            </div>

                            <button type="submit" class="btn btn-primary form-control" name="btnLogin" title="Masuk Sistem" id="clicker">MASUK</button>
                            <br>
                            <CENTER>
                                <p class="text-sm text-gray-600">Belum punya akun? Hubungi Administrator.</p>
                            </CENTER>
                            <CENTER class="mt-4">
                                <p class="text-sm text-gray-600">SICEPU 2025</p>
                            </CENTER>
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
<?php
} // <-- Ini adalah kurung kurawal penutup untuk blok 'else' yang membuka HTML form
?>