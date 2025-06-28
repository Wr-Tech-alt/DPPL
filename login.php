<?php
session_start(); // Start a PHP session at the very beginning of the page

require_once 'inc/koneksi.php';

$error_message = '';

if (isset($_POST['login_submit'])) {
    $nama = $conn->real_escape_string($_POST['nama']);
    $password = $conn->real_escape_string($_POST['password']);

    if (empty($nama) || empty($password)) {
        $error_message = "Nama Pengguna dan Password harus diisi.";
    } else {
        $stmt = $conn->prepare("SELECT iduser, Role, password, nama FROM pengguna WHERE nama = ?");
        $stmt->bind_param("s", $nama);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows === 1) {
            $user = $result->fetch_assoc();

            // Menggunakan perbandingan plain text password (INSECURE - HANYA UNTUK DEMO/TESTING)
            // SANGAT DISARANKAN untuk menggunakan password_verify() setelah melakukan password_hash() saat registrasi.
            if ($password === $user['password']) { // Plain text password comparison
                $_SESSION['loggedin'] = true;
                $_SESSION['iduser'] = $user['iduser'];
                $_SESSION['nama'] = $user['nama'];
                $_SESSION['role'] = $user['Role'];

                switch ($user['Role']) {
                    case 'Admin':
                        // Path yang benar untuk Dashboard Admin
                        header("Location: dashboard/dashboard_admin.php");
                        break;
                    case 'Petugas': // Tambahkan case untuk role Petugas
                        // Path yang benar untuk Dashboard Petugas
                        header("Location: dashboard/dashboard_petugas.php");
                        break;
                    case 'Pengadu':
                        // Path yang benar untuk Dashboard Pengadu
                        header("Location: dashboard/dashboard_pengadu.php");
                        break;
                    default:
                        // Fallback jika ada role lain yang tidak spesifik
                        // Pastikan file default_dashboard.php ini ada di direktori utama,
                        // atau sesuaikan path-nya jika berada di folder dashboard
                        header("Location: default_dashboard.php"); // Atau dashboard/default_dashboard.php
                        break;
                }
                exit();

            } else {
                $error_message = "Password salah.";
            }
        } else {
            $error_message = "Nama Pengguna tidak ditemukan.";
        }

        $stmt->close();
    }
    $conn->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SICepu Login</title>
    <link rel="stylesheet" href="assets/css/login.css">
    <link href="https://fonts.googleapis.com/css2?family=Raleway:wght@400;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>
<body>
    <div class="login-wrapper">
        <div class="login-container">
            <div class="login-form-panel">
                <div class="tabs">
                    <button class="tab-button active" data-tab="login">Login</button>
                    <button class="tab-button" data-tab="info">Sistem SiCepu</button>
                </div>

                <div id="login-form-content" class="form-content active">
                    <h1>Selamat Datang!</h1>
                    <p>Log in untuk melanjutkan</p>

                    <?php if (!empty($error_message)): ?>
                        <div style="color: red; text-align: center; margin-bottom: 15px; background-color: rgba(255,0,0,0.2); padding: 10px; border-radius: 5px;">
                            <?php echo htmlspecialchars($error_message); ?>
                        </div>
                    <?php endif; ?>

                    <form action="" method="POST">
                        <div class="input-group">
                            <i class="fa-solid fa-user"></i>
                            <input type="text" placeholder="Masukkan Nama Pengguna Anda" name="nama" required>
                        </div>
                        <div class="input-group">
                            <i class="fa-solid fa-lock"></i>
                            <input type="password" placeholder="********" name="password" required>
                        </div>

                        <div class="options">
                            <label class="remember-me">
                                <input type="checkbox">
                                Ingat saya
                            </label>
                            <a href="#" class="forgot-password">Lupa Password?</a>
                        </div>

                        <button type="submit" class="login-button" name="login_submit">Log In</button>
                    </form>

                </div>

                <div id="info-form-content" class="form-content">
                    <h1>Tentang SiCepu</h1>
                    <p>Sistem Informasi Cepat Pengaduan Fasilitas Umum (SiCepu) adalah platform yang dirancang untuk memudahkan mahasiswa dalam melaporkan masalah terkait fasilitas kampus, seperti ac yang kurang sejuk, projektor bermasalah dan tidak menyala, kursi serta meja gabungan dalam keadaan tidak layak pakai dan segala hal lain yang mahasiswa temukan dalam kegiatan perkuliahan.</p>
                    <p>Tujuan utama SiCepu adalah meningkatkan kualitas pelayanan mahasiswa dan responsibilitas kampus dalam menangani keluhan mahasiswanya. Kami berkomitmen untuk menciptakan lingkungan yang lebih baik dan nyaman bagi seluruh mahasiswa dalam menjalani kegiatan mereka dikampus.</p>
                    <p style="text-align: center; margin-top: 30px; font-size: 0.9em; color: rgba(255,255,255,0.6);">
                        &copy; 2025 SiCepu. All rights reserved.
                    </p>
                </div>

            </div>
            <div class="login-image-panel">
                <img src="assets/img/scenery.jpg" alt="Mountain Landscape">
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const tabButtons = document.querySelectorAll('.tab-button');
            const formContents = document.querySelectorAll('.form-content');

            tabButtons.forEach(button => {
                button.addEventListener('click', () => {
                    tabButtons.forEach(btn => btn.classList.remove('active'));
                    formContents.forEach(content => content.classList.remove('active'));

                    button.classList.add('active');

                    const targetTab = button.dataset.tab;
                    document.getElementById(targetTab + '-form-content').classList.add('active');
                });
            });
        });
    </script>
</body>
</html>