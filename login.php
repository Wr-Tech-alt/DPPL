<?php
include "inc/koneksi.php";
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SiCepu - Login Admin/Petugas</title>
    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <!-- Google Fonts: Inter and Lato -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Lato:wght@300;400;700&display=swap" rel="stylesheet">
    <!-- SweetAlert2 CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@9/dist/sweetalert2.min.css">
    <style>
        body {
            font-family: 'Lato', sans-serif; /* Use Lato as the primary font */
            /* Updated background image */
            background-image: url('https://placehold.co/1920x1080/AAD3DF/333333?text=New+Background'); /* New placeholder image for background */
            background-size: cover; /* Ensure the image covers the entire area */
            background-position: center; /* Center the image */
            background-attachment: fixed; /* Fixed background during scroll */
        }
        .login-container {
            background-color: rgba(255, 255, 255, 0.95); /* Slightly transparent background for the login form */
        }
        .button-masuk-custom {
            background: #14B8A6; /* teal-600 */
            color: white;
            padding: 12px 28px;
            font-size: 18px;
            font-weight: bold;
            border-radius: 8px;
            text-decoration: none;
            transition: 0.3s ease-in-out;
            box-shadow: 0 4px 6px rgba(0,0,0,0.2);
            display: flex; /* For centering content */
            justify-content: center; /* For centering content */
            width: 100%; /* Make it full width */
            border: none; /* Remove default border */
        }
        .button-masuk-custom:hover {
            background: #0D9488; /* teal-700 */
            transform: scale(1.02); /* Slight scaling on hover */
            box-shadow: 0 6px 12px rgba(0,0,0,0.3);
        }
        .swal2-popup {
            font-size: 1.6rem !important;
        }
    </style>
</head>
<body class="bg-gray-50 min-h-screen flex flex-col">
    <main class="flex-grow flex items-center justify-center py-12 px-4 sm:px-6 lg:px-8">
        <div class="max-w-md w-full bg-white rounded-xl shadow-lg overflow-hidden login-container">
            <div class="p-8 sm:p-10">
                <h2 class="text-2xl sm:text-3xl font-bold text-gray-800 text-center mb-6">
                    Masuk ke SiCepu
                </h2>
                <div class="h-1 w-24 bg-teal-500 mx-auto mb-8 rounded-full"></div>

                <form action="" method="POST" enctype="multipart/form-data" class="space-y-6">
                    <!-- Username field -->
                    <div>
                        <label for="username" class="block text-sm font-medium text-gray-700 mb-2">Nama Pengguna</label>
                        <input type="text" id="username" name="username" required class="block w-full px-4 py-2 border border-gray-300 rounded-lg shadow-sm placeholder-gray-400 focus:ring-teal-500 focus:border-teal-500 sm:text-base" placeholder="Masukkan nama pengguna">
                    </div>
                    <!-- Password field -->
                    <div>
                        <label for="password" class="block text-sm font-medium text-gray-700 mb-2">Kata Sandi</label>
                        <input type="password" id="password" name="password" required class="block w-full px-4 py-2 border border-gray-300 rounded-lg shadow-sm placeholder-gray-400 focus:ring-teal-500 focus:border-teal-500 sm:text-base" placeholder="Masukkan kata sandi">
                    </div>
                    <!-- Remember Me and Forgot Password (Placeholder for now) -->
                    <div class="flex items-center justify-between">
                        <div class="flex items-center">
                            <input id="remember-me" name="remember-me" type="checkbox" class="h-4 w-4 text-teal-600 focus:ring-teal-500 border-gray-300 rounded">
                            <label for="remember-me" class="ml-2 block text-sm text-gray-900">
                                Ingat Saya
                            </label>
                        </div>
                        <div class="text-sm">
                            <a href="#" class="font-medium text-teal-600 hover:text-teal-500">
                                Lupa Kata Sandi?
                            </a>
                        </div>
                    </div>
                    <!-- Login button -->
                    <div>
                        <button type="submit" class="button-masuk-custom" name="btnLogin" id="clicker">
                            Masuk
                        </button>
                    </div>
                </form>
            </div>
            <!-- Footer text for registration -->
            <div class="bg-gray-50 px-8 py-6 text-center text-sm text-gray-600">
                <p>Belum punya akun? Hubungi Administrator.</p>
            </div>
        </div>
    </main>

    <!-- Page footer -->
    <footer class="bg-gray-800 text-white py-6 px-4 mt-auto">
        <div class="max-w-4xl mx-auto text-center text-sm">
            <p>© 2023 SiCepu - Sistem Pelaporan Kerusakan Fasilitas Kampus. All rights reserved.</p>
        </div>
    </footer>

    <!-- SweetAlert2 JS -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@9"></script>
</body>
</html>

<?php
// PHP login logic
if (isset($_POST['btnLogin'])) {
    $sql_login = "SELECT * FROM tb_pengguna WHERE username='" . $_POST['username'] . "' AND password='" . $_POST['password'] . "'";
    $query_login = mysqli_query($koneksi, $sql_login);
    $data_login = mysqli_fetch_array($query_login, MYSQLI_BOTH);
    $jumlah_login = mysqli_num_rows($query_login);

    if ($jumlah_login == 1) {
        session_start();
        $_SESSION["ses_id"] = $data_login["id_pengguna"];
        $_SESSION["ses_nama"] = $data_login["nama_pengguna"];
        $_SESSION["ses_level"] = $data_login["level"];
        $_SESSION["ses_grup"] = $data_login["grup"];

        echo "<script>
            Swal.fire({
                title: 'SUKSES',
                text: 'Login berhasil!',
                icon: 'success',
                confirmButtonText: 'OK'
            }).then((result) => {
                if (result.value) {
                    window.location = 'index';
                }
            });
        </script>";
    } else {
        echo "<script>
            Swal.fire({
                title: 'GAGAL',
                text: 'Username atau password salah.',
                icon: 'error',
                confirmButtonText: 'OK'
            }).then((result) => {
                if (result.value) {
                    // Stay on the login page or redirect as needed
                    window.location = 'login1.php'; // Or simply remove this line to stay
                }
            });
        </script>";
    }
}
?>
