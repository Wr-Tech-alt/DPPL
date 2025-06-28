<?php
// login.php (Combined Login Form and Processing)

session_start(); // Start a PHP session at the very beginning of the page

// Include your database connection file
// Make sure 'inc/koneksi.php' is the correct path to your database connection.
// Based on your code, it's now 'inc/koneksi.php' instead of 'db_connection.php'
require_once 'inc/koneksi.php'; 

$error_message = ''; // Initialize error message

// Check if the form was submitted via POST
if (isset($_POST['login_submit'])) {
    // *** CHANGED: Get 'nama' from POST instead of 'iduser' ***
    $nama = $conn->real_escape_string($_POST['nama']);
    $password = $conn->real_escape_string($_POST['password']);

    // Basic input validation
    if (empty($nama) || empty($password)) { // *** CHANGED: Error message refers to Nama Pengguna ***
        $error_message = "Nama Pengguna dan Password harus diisi.";
    } else {
        // Prepare a SQL query to select user data by 'nama'.
        // *** CHANGED: WHERE clause to 'nama = ?' ***
        $stmt = $conn->prepare("SELECT iduser, Role, password, nama FROM pengguna WHERE nama = ?");
        // *** CHANGED: Bind 'nama' instead of 'iduser' ***
        $stmt->bind_param("s", $nama);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows === 1) {
            $user = $result->fetch_assoc();

            // --- SECURITY WARNING: Password Hashing is CRITICAL for production ---
            // As discussed, this part is for demonstration with plain text passwords.
            // In a real application, replace this with:
            // if (password_verify($password, $user['password'])) { ... }
            // ---------------------------------------------------------------------

            if ($password === $user['password']) { // Plain text password comparison (INSECURE)
                // Password matches, login successful
                $_SESSION['loggedin'] = true;
                $_SESSION['iduser'] = $user['iduser'];
                $_SESSION['nama'] = $user['nama'];
                $_SESSION['role'] = $user['Role'];

                // Redirect based on user role
                switch ($user['Role']) {
                    case 'Admin':
                        // *** CHANGED: Redirect path for Admin ***
                        header("Location:dashboard/dashboard_admin.php");
                        break;
                    case 'Pengadu':
                        header("Location: pengadu_dashboard.php");
                        break;
                    default:
                        // Fallback for any other roles not specifically handled
                        header("Location: default_dashboard.php"); // Ensure this file exists or remove
                        break;
                }
                exit(); // Stop script execution after redirection

            } else {
                // Password does not match
                $error_message = "Password salah.";
            }
        } else {
            // User (nama) not found
            $error_message = "Nama Pengguna tidak ditemukan."; // *** CHANGED: Error message refers to Nama Pengguna ***
        }

        $stmt->close(); // Close the prepared statement
    }
    $conn->close(); // Close the database connection
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