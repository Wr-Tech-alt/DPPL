<?php
require_once 'config.php'; //
require_once 'user_agent_parser.php'; // pastikan ada getBrowser() & getPlatform()

$error_message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username_form = $_POST['username'];
    $password_form = $_POST['password'];

    $stmt = $conn->prepare("SELECT * FROM user WHERE UserName = ?");
    $stmt->bind_param("s", $username_form);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();
    $stmt->close();

    if ($user && password_verify($password_form, $user['PassWord'])) {
        session_regenerate_id(true); // Penting untuk keamanan
        $currentSessionId = session_id(); // ID sesi baru setelah regenerate

        $userAgent = $_SERVER['HTTP_USER_AGENT'];
        $currentBrowser = getBrowser($userAgent); //
        $currentPlatform = getPlatform($userAgent); //
        $currentUserIP = $_SERVER['REMOTE_ADDR'];

        // Cek browser & OS terakhir dari DB
        $lastBrowser = $user['LastBrowser'] ?? '';
        $lastOS = $user['LastOS'] ?? '';
        // Anda mungkin ingin menambahkan pengecekan IP juga di sini jika logikanya begitu
        // $lastIP = $user['IPAddress'] ?? '';
        $isFirstLogin = empty($lastBrowser) && empty($lastOS); // Anggap login pertama jika LastBrowser dan LastOS kosong

        // Kondisi untuk meminta PIN: bukan login pertama DAN (browser beda ATAU OS beda)
        // Anda bisa tambahkan pengecekan IP di sini: || $currentUserIP !== $lastIP
        if (!$isFirstLogin && ($currentBrowser !== $lastBrowser || $currentPlatform !== $lastOS)) {
            // Simpan semua informasi yang dibutuhkan untuk pin_auth.php
            $_SESSION['pending_user_id'] = $user['IdUserPrimary'];
            $_SESSION['pending_session_id'] = $currentSessionId; // Simpan ID sesi baru ini
            $_SESSION['pending_ip'] = $currentUserIP;
            $_SESSION['pending_browser'] = $currentBrowser;
            $_SESSION['pending_platform'] = $currentPlatform;
            $_SESSION['pending_username'] = $user['UserName']; // Simpan username jika perlu di pin_auth.php
            $_SESSION['requested_page'] = 'dashboard.php'; //
            $_SESSION['pin_attempt'] = 0; // reset percobaan PIN

            // PENTING: JANGAN UPDATE DATABASE DI SINI. Update dilakukan setelah PIN berhasil.
            // HAPUS blok ini:
            /*
            $stmt = $conn->prepare("UPDATE user SET Session = ?, IPAddress = ? WHERE IdUserPrimary = ?");
            $stmt->bind_param("ssi", $currentSessionId, $currentUserIP, $user['IdUserPrimary']);
            $stmt->execute();
            $stmt->close();
            */

            header("Location: pin_auth.php");
            exit();
        }

        // Lolos verifikasi (perangkat sama atau login pertama), update semua info dan masuk dashboard
        // Untuk login pertama, LastBrowser dan LastOS akan diisi dengan info saat ini
        $stmt = $conn->prepare("UPDATE user SET Session = ?, IPAddress = ?, LastBrowser = ?, LastOS = ? WHERE IdUserPrimary = ?"); //
        $stmt->bind_param("ssssi", $currentSessionId, $currentUserIP, $currentBrowser, $currentPlatform, $user['IdUserPrimary']); //
        $stmt->execute(); //
        $stmt->close(); //

        // Set session utama setelah semua aman
        $_SESSION['user_id'] = $user['IdUserPrimary']; //
        $_SESSION['username'] = $user['UserName']; //
        // Sebaiknya simpan juga IP, Browser, OS yang terverifikasi ke session untuk auth_check.php
        $_SESSION['verified_ip'] = $currentUserIP;
        $_SESSION['verified_browser'] = $currentBrowser;
        $_SESSION['verified_os'] = $currentPlatform;
        // $_SESSION['current_session_id_for_user'] = $currentSessionId; // Ini bisa jadi redundant jika auth_check.php membandingkan session_id() dengan DB

        header("Location: dashboard.php"); //
        exit();
    } else {
        $error_message = "Username atau password yang Anda masukkan salah!"; //
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <link rel="stylesheet" href="style.css">
    <title>Login Pengguna</title>
    <style>
        .error {
            color: red;
            border: 1px solid red;
            padding: 10px;
            margin-bottom: 15px;
        }
    </style>
</head>
<body>
    <h2 class="form-title">🔐 Login Aplikasi</h2>

<?php
if (!empty($error_message)) {
    echo "<div class='error'>" . htmlspecialchars($error_message) . "</div>";
}
?>

<form method="post" action="login.php" class="form-container">
    <label for="username" class="form-label">Username:</label>
    <input type="text" name="username" id="username" class="form-input" required>

    <label for="password" class="form-label">Password:</label>
    <input type="password" name="password" id="password" class="form-input" required>

    <input type="submit" value="Login" class="form-button">

    <p class="form-footer">Belum punya akun? <a href="register.php">Daftar di sini</a></p>
</form>

</body>
</html>