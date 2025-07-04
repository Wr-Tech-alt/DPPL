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
    $nim = $conn->real_escape_string($_POST['nim']); // Ambil nilai NIM dari form

    // Validasi input
    if (empty($nama_pengguna) || empty($password) || empty($confirm_password) || empty($nim)) {
        $error_message = "Nama Pengguna, Password, Konfirmasi Password, dan NIM harus diisi.";
    } elseif ($password !== $confirm_password) {
        $error_message = "Konfirmasi password tidak cocok.";
    } elseif (strlen($password) < 6) {
        $error_message = "Password minimal 6 karakter.";
    } elseif (!preg_match("/^[0-9]+$/", $nim)) { // Validasi NIM hanya angka
        $error_message = "NIM hanya boleh mengandung angka.";
    } elseif (strlen($nim) < 5 || strlen($nim) > 20) { // Contoh batasan panjang NIM
        $error_message = "Panjang NIM tidak valid. (Contoh: 5-20 digit)";
    }
    else {
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
                $error_message = "NIM ini sudah terdaftar. Silakan login atau hubungi admin.";
            } else {
                // Password tetap plain text sesuai permintaan
                $hashed_password = $password; 

                // Masukkan data pengguna baru ke database
                // Kolom yang dimasukkan: nama, password, Role, dan email (untuk NIM)
                $stmt_insert = $conn->prepare("INSERT INTO pengguna (nama, password, Role, email) VALUES (?, ?, ?, ?)");
                $stmt_insert->bind_param("ssss", $nama_pengguna, $hashed_password, $assigned_role, $nim);

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
            $stmt_check_nim->close();
        }
        $stmt_check_user->close();
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
        /* Penyesuaian gaya untuk input agar terlihat rapi seperti di halaman login */
        .input-group {
            display: flex; /* Gunakan flexbox untuk perataan */
            align-items: center; /* Pusatkan item secara vertikal */
            margin-bottom: 20px; /* Spasi antar grup input */
            background-color: rgba(255, 255, 255, 0.1); /* Latar belakang transparan putih */
            border-radius: 5px; /* Sudut membulat */
            padding: 5px 15px; /* Padding di dalam grup input */
            box-shadow: inset 0 1px 3px rgba(0,0,0,0.1); /* Bayangan dalam halus */
        }
        .input-group i {
            margin-right: 15px; /* Spasi antara ikon dan input */
            color: #fff; /* Warna ikon putih */
            font-size: 1.2em;
        }
        .input-group input {
            flex-grow: 1; /* Input mengambil sisa ruang */
            background: none; /* Latar belakang transparan */
            border: none; /* Tanpa border */
            outline: none; /* Tanpa outline saat fokus */
            color: #fff; /* Warna teks putih */
            padding: 10px 0; /* Padding vertikal untuk teks input */
            font-size: 1em;
            height: auto; /* Biarkan padding menentukan tinggi */
        }
        .input-group input::placeholder {
            color: rgba(255, 255, 255, 0.7); /* Warna placeholder lebih terang */
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
                        <i class="fa-solid fa-id-card"></i> <!-- Menggunakan ikon ID card untuk NIM -->
                        <input type="text" placeholder="NIM" name="nim" required value="<?php echo isset($_POST['nim']) ? htmlspecialchars($_POST['nim']) : ''; ?>">
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
