<?php
// Aktifkan error reporting penuh untuk debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start(); // Mulai sesi

require_once 'inc/koneksi.php'; // Sertakan file koneksi database

$success_message = '';
$error_message = '';

// ====================================================================
// BAGIAN LOGIKA PHP UNTUK PROSES REGISTRASI
// ====================================================================
if (isset($_POST['register_submit'])) {
    // Escape string untuk mencegah SQL Injection
    $nama_pengguna = $conn->real_escape_string($_POST['nama_pengguna']);
    $password = $conn->real_escape_string($_POST['password']);
    $confirm_password = $conn->real_escape_string($_POST['confirm_password']);
    $nim = $conn->real_escape_string($_POST['nim']); // Ambil nilai NIM dari form

    // Validasi input
    if (empty($nama_pengguna) || empty($password) || empty($confirm_password) || empty($nim)) {
        $error_message = "Nama Pengguna, Password, Konfirmasi Password, dan NIM harus diisi.";
    } elseif ($password !== $confirm_password) {
        $error_message = "Konfirmasi password tidak cocok.";
    } elseif (strlen($password) < 6) {
        $error_message = "Password minimal 6 karakter.";
    } elseif (!filter_var($nim, FILTER_VALIDATE_EMAIL)) { // Validasi NIM sebagai email
        $error_message = "Format Email tidak valid. (Contoh: email@domain.com)";
    } else {
        // Peran default untuk semua pendaftar dari halaman ini
        $assigned_role = 'Pengadu'; 

        // Periksa apakah nama pengguna sudah ada
        $stmt_check_user = $conn->prepare("SELECT iduser FROM pengguna WHERE nama = ?");
        $stmt_check_user->bind_param("s", $nama_pengguna);
        $stmt_check_user->execute();
        $result_check_user = $stmt_check_user->get_result();

        if ($result_check_user->num_rows > 0) {
            $error_message = "Nama pengguna sudah ada. Silakan pilih nama pengguna lain.";
        } else {
            // Periksa apakah NIM sudah terdaftar (menggunakan kolom 'email' untuk NIM)
            $stmt_check_nim = $conn->prepare("SELECT iduser FROM pengguna WHERE email = ?");
            $stmt_check_nim->bind_param("s", $nim);
            $stmt_check_nim->execute();
            $result_check_nim = $stmt_check_nim->get_result();

            if ($result_check_nim->num_rows > 0) {
                $error_message = "Email ini sudah terdaftar. Silakan login atau hubungi admin.";
                $redirect_to_login = true;
            } else {
                // Password tetap plain text sesuai permintaan
                $hashed_password = $password; 

                // Masukkan data pengguna baru ke database
                // Kolom yang dimasukkan: nama, password, Role, dan email (untuk NIM)
                $stmt_insert = $conn->prepare("INSERT INTO pengguna (nama, password, Role, email) VALUES (?, ?, ?, ?)");
                $stmt_insert->bind_param("ssss", $nama_pengguna, $hashed_password, $assigned_role, $nim);

                if ($stmt_insert->execute()) {
                    $success_message = "Pendaftaran berhasil! Silakan login.";
                    $redirect_to_login = true;
                    // Opsional: Redirect ke halaman login setelah sukses
                    // header("Location: index.php?registration=success");
                    // exit();
                } else {
                    $error_message = "Terjadi kesalahan saat mendaftar: " . $stmt_insert->error;
                }
                $stmt_insert->close();
            }
            $stmt_check_nim->close();
        }
        $stmt_check_user->close();
    }
    $conn->close();
}
// ====================================================================
// AKHIR BAGIAN LOGIKA PHP
// ====================================================================
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SICepu Regis</title>
    <link rel="stylesheet" href="assets/css/login.css"> 
    <link href="https://fonts.googleapis.com/css2?family=Raleway:wght@400;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        /* Tambahan style untuk tombol register */
        .register-link {
            display: block;
            text-align: center;
            margin-top: 20px;
            color: #007bff;
            text-decoration: none;
            font-weight: 600;
            transition: color 0.3s ease;
        }
        .register-link:hover {
            color: #0056b3;
        }
        /* Menyesuaikan lebar tab agar pas */
        .tabs {
            display: flex;
            justify-content: center;
            margin-bottom: 25px;
            border-bottom: 2px solid rgba(255,255,255,0.2);
        }
        .tab-button {
            background: none;
            border: none;
            padding: 12px 25px;
            color: rgba(255,255,255,0.7);
            font-size: 1.1em;
            cursor: pointer;
            transition: all 0.3s ease;
            position: relative;
            outline: none;
        }
        .tab-button.active {
            color: #fff;
            font-weight: 600;
        }
        .tab-button.active::after {
            content: '';
            position: absolute;
            bottom: -2px;
            left: 0;
            width: 100%;
            height: 2px;
            background-color: #fff;
        }
        .form-content {
            display: none;
            padding: 20px 0;
        }
        .form-content.active {
            display: block;
        }
    </style>
</head>
<body>
    <div class="login-wrapper">
        <div class="login-container">
            <div class="login-form-panel">
                <div class="tabs">
                    <button class="tab-button active" data-tab="login">Register</button>
                    <button class="tab-button" data-tab="info">Tentang SiCepu</button>
                </div>

                <div id="login-form-content" class="form-content active">
                    <h1>Salam Kenal!</h1>
                    <p>Silahkan Buat Akun barumu</p>

                    <?php if (!empty($success_message)): ?>
                        <div style="color: green; text-align: center; margin-bottom: 15px; background-color: rgba(0,255,0,0.2); padding: 10px; border-radius: 5px;">
                            <?php echo htmlspecialchars($success_message); ?>
                        </div>
                    <?php endif; ?>

                    <?php if (!empty($error_message)): ?>
                        <div style="color: red; text-align: center; margin-bottom: 15px; background-color: rgba(255,0,0,0.2); padding: 10px; border-radius: 5px;">
                            <?php echo htmlspecialchars($error_message); ?>
                        </div>
                    <?php endif; ?>

                    <form action="" method="POST">
                        <div class="input-group">
                            <i class="fa-solid fa-user"></i>
                            <input type="text" placeholder="Nama Pengguna (Username)" name="nama_pengguna" required value="<?php echo isset($_POST['nama_pengguna']) ? htmlspecialchars($_POST['nama_pengguna']) : ''; ?>">
                        </div>
                        <div class="input-group">
                            <i class="fa-solid fa-lock"></i>
                            <input type="password" placeholder="Password" name="password" required>
                        </div>
                        <div class="input-group">
                            <i class="fa-solid fa-lock"></i>
                            <input type="password" placeholder="Konfirmasi Password" name="confirm_password" required>
                        </div>
                        <div class="input-group">
                            <i class="fa-solid fa-envelope"></i> <!-- Menggunakan ikon ID card untuk NIM -->
                            <input type="email" placeholder="Email Kampus" name="nim" required value="<?php echo isset($_POST['nim']) ? htmlspecialchars($_POST['nim']) : ''; ?>">
                        </div>
                        
                        <button type="submit" class="login-button" name="register_submit">Daftar</button>
                    </form>
                    <a href="login.php" class="register-link">Udah punya akun? Login di sini</a>
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
            
            <?php if ($redirect_to_login): ?>
                setTimeout(function() {
                    window.location.href = 'index.php'; // Redirect ke halaman login
                }, 1000); // Redirect setelah 1 detik
            <?php endif; ?>
        });
    </script>
</body>
</html>
<?php
ob_end_flush(); // Akhiri output buffering dan kirimkan output ke browser
?>
