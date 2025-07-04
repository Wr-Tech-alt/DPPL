<?php
// Aktifkan error reporting penuh untuk debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start(); // Start a PHP session at the very beginning of the page
ob_start();      // Mulai output buffering untuk mencegah 'headers already sent'

require_once 'inc/koneksi.php'; // Path is correct as 'inc' is a direct child of the root

$error_message = '';

if (isset($_POST['login_submit'])) {
    $nama = $conn->real_escape_string($_POST['nama']);
    $password = $conn->real_escape_string($_POST['password']);

    if (empty($nama) || empty($password)) {
        $error_message = "Nama Pengguna dan Password harus diisi.";
    } else {
        $stmt = $conn->prepare("SELECT iduser, Role, password, nama FROM pengguna WHERE nama = ?");
        if ($stmt === FALSE) {
            $error_message = "Terjadi kesalahan saat menyiapkan query.";
        } else {
            $stmt->bind_param("s", $nama);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($result->num_rows === 1) {
                $user = $result->fetch_assoc();
                
                // Jika password di database di-hash, gunakan password_verify()
                // if (password_verify($password, $user['password'])) {
                // Untuk saat ini, asumsikan password masih plain text (HARAP GANTI DENGAN HASHING DI PRODUKSI!)
                if ($password === $user['password']) { 
                    $_SESSION['loggedin'] = true;
                    $_SESSION['iduser'] = $user['iduser'];
                    $_SESSION['nama'] = $user['nama'];
                    $_SESSION['role'] = $user['Role'];

                    $redirect_url = '';
                    switch ($user['Role']) {
                        case 'Admin':
                            $redirect_url = "dashboard/dashboard_admin.php";
                            break;
                        case 'Petugas':
                            $redirect_url = "dashboard/dashboard_petugas.php";
                            break;
                        case 'Pengadu':
                            $redirect_url = "dashboard/dashboard_pengadu.php";
                            break;
                        default:
                            $redirect_url = "index.php";
                            break;
                    }
                    
                    if (!empty($redirect_url)) {
                        header("Location: " . $redirect_url);
                        exit();
                    } else {
                        $error_message = "Role pengguna tidak valid atau URL redirect tidak ditemukan.";
                    }

                } else {
                    $error_message = "Nama Pengguna atau Password salah.";
                }
            } else {
                $error_message = "Nama Pengguna atau Password salah.";
            }

            $stmt->close();
        }
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
                    <button class="tab-button active" data-tab="login">Login</button>
                    <button class="tab-button" data-tab="info">Tentang SiCepu</button>
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
                    <a href="register.php" class="register-link">Belum punya akun? Daftar di sini</a>
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
<?php
ob_end_flush(); // Akhiri output buffering dan kirimkan output ke browser
?>
