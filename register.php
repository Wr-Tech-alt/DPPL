<?php
// Aktifkan error reporting penuh untuk debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start(); // Mulai sesi

require_once 'inc/koneksi.php'; // Sertakan file koneksi database

$success_message = '';
$error_message = '';

if (isset($_POST['register_submit'])) {
    // Escape string untuk mencegah SQL Injection
    $nama_pengguna = $conn->real_escape_string($_POST['nama_pengguna']);
    $password = $conn->real_escape_string($_POST['password']);
    $confirm_password = $conn->real_escape_string($_POST['confirm_password']);
    $nama_lengkap = $conn->real_escape_string($_POST['nama_lengkap']);
    $nim_nip = $conn->real_escape_string($_POST['nim_nip']); // Ini akan menjadi NIM/NIP
    $notelp = $conn->real_escape_string($_POST['notelp']);

    // Validasi input
    if (empty($nama_pengguna) || empty($password) || empty($confirm_password) || empty($nama_lengkap) || empty($nim_nip) || empty($notelp)) {
        $error_message = "Semua kolom harus diisi.";
    } elseif ($password !== $confirm_password) {
        $error_message = "Konfirmasi password tidak cocok.";
    } elseif (strlen($password) < 6) {
        $error_message = "Password minimal 6 karakter.";
    } elseif (!preg_match("/^[0-9]+$/", $nim_nip)) { // Validasi NIM/NIP hanya angka
        $error_message = "NIM/NIP hanya boleh mengandung angka.";
    } elseif (strlen($nim_nip) < 5 || strlen($nim_nip) > 20) { // Contoh batasan panjang NIM/NIP
        $error_message = "Panjang NIM/NIP tidak valid.";
    } else {
        // --- Validasi NIM/NIP di Database (Konseptual) ---
        // Anda perlu memiliki tabel terpisah (misalnya 'valid_identitas_kampus')
        // yang berisi daftar NIM/NIP yang valid dan peran mereka.
        // Contoh:
        // CREATE TABLE valid_identitas_kampus (
        //     identitas_id VARCHAR(20) PRIMARY KEY,
        //     nama VARCHAR(100),
        //     role_kampus VARCHAR(50) -- 'Mahasiswa', 'Dosen', 'Staf'
        // );

        // Query untuk memeriksa apakah NIM/NIP ada di daftar valid
        // Untuk tujuan demo, kita asumsikan NIM/NIP valid jika tidak kosong dan hanya angka.
        // DI LINGKUNGAN PRODUKSI, ANDA HARUS MENGHUBUNGKAN INI KE DATABASE NIM/NIP ASLI KAMPUS.
        $is_nim_nip_valid = false;
        $assigned_role = 'Pengadu'; // Default role jika validasi NIM/NIP berhasil

        // Contoh sederhana: Jika NIM/NIP dimulai dengan '123' itu mahasiswa, jika '456' itu dosen
        if (substr($nim_nip, 0, 3) === '123') {
            $is_nim_nip_valid = true;
            $assigned_role = 'Pengadu'; // Peran untuk mahasiswa
        } elseif (substr($nim_nip, 0, 3) === '456') {
            $is_nim_nip_valid = true;
            $assigned_role = 'Pengadu'; // Peran untuk dosen/staf
        } else {
            $error_message = "NIM/NIP tidak terdaftar atau tidak valid. Silakan hubungi admin kampus.";
        }
        
        // Jika Anda memiliki tabel valid_identitas_kampus, kodenya akan seperti ini:
        /*
        $stmt_check_nim_nip = $conn->prepare("SELECT role_kampus FROM valid_identitas_kampus WHERE identitas_id = ?");
        $stmt_check_nim_nip->bind_param("s", $nim_nip);
        $stmt_check_nim_nip->execute();
        $result_nim_nip = $stmt_check_nim_nip->get_result();

        if ($result_nim_nip->num_rows > 0) {
            $nim_nip_data = $result_nim_nip->fetch_assoc();
            $is_nim_nip_valid = true;
            // Anda bisa menyesuaikan peran di sini jika perlu, misal:
            // $assigned_role = ($nim_nip_data['role_kampus'] === 'Mahasiswa') ? 'Pengadu' : 'Petugas';
            $assigned_role = 'Pengadu'; // Untuk semua yang mendaftar dari halaman ini
        } else {
            $error_message = "NIM/NIP tidak terdaftar atau tidak valid. Silakan hubungi admin kampus.";
        }
        $stmt_check_nim_nip->close();
        */

        if ($is_nim_nip_valid) {
            // Periksa apakah nama pengguna sudah ada
            $stmt_check_user = $conn->prepare("SELECT iduser FROM pengguna WHERE nama = ?");
            $stmt_check_user->bind_param("s", $nama_pengguna);
            $stmt_check_user->execute();
            $result_check_user = $stmt_check_user->get_result();

            if ($result_check_user->num_rows > 0) {
                $error_message = "Nama pengguna sudah ada. Silakan pilih nama pengguna lain.";
            } else {
                // Hash password sebelum menyimpan (SANGAT DIREKOMENDASIKAN UNTUK PRODUKSI)
                // $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                $hashed_password = $password; // Untuk demo, masih plain text

                // Masukkan data pengguna baru ke database
                $stmt_insert = $conn->prepare("INSERT INTO pengguna (nama, password, Role, nama_lengkap, nim_nip, notelp) VALUES (?, ?, ?, ?, ?, ?)");
                // Perhatikan: 'Pengadu' adalah role default untuk pendaftar dari halaman ini
                $stmt_insert->bind_param("ssssss", $nama_pengguna, $hashed_password, $assigned_role, $nama_lengkap, $nim_nip, $notelp);

                if ($stmt_insert->execute()) {
                    $success_message = "Pendaftaran berhasil! Silakan login.";
                    // Opsional: Redirect ke halaman login setelah sukses
                    // header("Location: index.php?registration=success");
                    // exit();
                } else {
                    $error_message = "Terjadi kesalahan saat mendaftar: " . $stmt_insert->error;
                }
                $stmt_insert->close();
            }
            $stmt_check_user->close();
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
    <title>Register - SiCepu</title>
    <link rel="stylesheet" href="assets/css/login.css"> <!-- Re-use login.css for consistent styling -->
    <link href="https://fonts.googleapis.com/css2?family=Raleway:wght@400;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        /* Custom styles for register page if needed, or override login.css */
        .login-container {
            max-width: 500px; /* Adjust width for register form */
        }
        .login-form-panel {
            padding: 40px;
        }
        .login-button {
            width: 100%;
            padding: 12px;
            font-size: 1.1em;
        }
        .back-to-login {
            display: block;
            text-align: center;
            margin-top: 20px;
            color: #007bff;
            text-decoration: none;
            font-weight: 600;
            transition: color 0.3s ease;
        }
        .back-to-login:hover {
            color: #0056b3;
        }
        .input-group i {
            color: #ccc; /* Warna ikon */
        }
    </style>
</head>
<body>
    <div class="login-wrapper">
        <div class="login-container">
            <div class="login-form-panel">
                <h1>Daftar Akun Baru</h1>
                <p>Silakan isi detail Anda untuk mendaftar</p>

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
                        <i class="fa-solid fa-address-card"></i>
                        <input type="text" placeholder="Nama Lengkap" name="nama_lengkap" required value="<?php echo isset($_POST['nama_lengkap']) ? htmlspecialchars($_POST['nama_lengkap']) : ''; ?>">
                    </div>
                    <div class="input-group">
                        <i class="fa-solid fa-id-card"></i>
                        <input type="text" placeholder="NIM / NIP" name="nim_nip" required value="<?php echo isset($_POST['nim_nip']) ? htmlspecialchars($_POST['nim_nip']) : ''; ?>">
                    </div>
                    <div class="input-group">
                        <i class="fa-solid fa-phone"></i>
                        <input type="text" placeholder="Nomor Telepon" name="notelp" required value="<?php echo isset($_POST['notelp']) ? htmlspecialchars($_POST['notelp']) : ''; ?>">
                    </div>

                    <button type="submit" class="login-button" name="register_submit">Daftar</button>
                </form>

                <a href="index.php" class="back-to-login">Sudah punya akun? Login di sini</a>
            </div>
            <div class="login-image-panel">
                <img src="assets/img/scenery.jpg" alt="Mountain Landscape"> 
            </div>
        </div>
    </div>
</body>
</html>
<?php
ob_end_flush(); // Akhiri output buffering dan kirimkan output ke browser
?>
