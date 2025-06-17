<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SiCepu - Login Admin/Petugas</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Inter', sans-serif;
        }
        .banner-illustration {
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='100' height='100' viewBox='0 0 100 100'%3E%3Cpath fill='%23ffffff' fill-opacity='0.2' d='M50,10 L90,50 L50,90 L10,50 Z'%3E%3C/path%3E%3Cpath fill='none' stroke='%23ffffff' stroke-width='2' stroke-dasharray='5,5' d='M10,10 L90,90 M10,90 L90,10'%3E%3C/path%3E%3C/svg%3E");
            background-size: 120px;
            background-position: center;
        }
    </style>
</head>
<body class="bg-gray-50 min-h-screen flex flex-col">
    <div class="banner-illustration bg-teal-700 py-10 px-4 sm:px-6 lg:px-8 shadow-md">
        <div class="max-w-4xl mx-auto text-center">
            <h1 class="text-3xl sm:text-4xl md:text-5xl font-bold text-white mb-4 leading-tight">
                SiCepu
            </h1>
            <p class="text-white text-lg opacity-90">Login Administrator & Petugas</p>
        </div>
    </div>

    <main class="flex-grow flex items-center justify-center py-12 px-4 sm:px-6 lg:px-8">
        <div class="max-w-md w-full bg-white rounded-xl shadow-lg overflow-hidden">
            <div class="p-8 sm:p-10">
                <h2 class="text-2xl sm:text-3xl font-semibold text-gray-800 text-center mb-6">
                    Masuk ke Akun Anda
                </h2>
                <div class="h-1 w-24 bg-teal-500 mx-auto mb-8 rounded-full"></div>
                
                <form action="#" method="POST" class="space-y-6">
                    <div>
                        <label for="username" class="block text-sm font-medium text-gray-700 mb-2">Nama Pengguna</label>
                        <input type="text" id="username" name="username" required class="block w-full px-4 py-2 border border-gray-300 rounded-lg shadow-sm placeholder-gray-400 focus:ring-teal-500 focus:border-teal-500 sm:text-base" placeholder="Masukkan nama pengguna">
                    </div>
                    <div>
                        <label for="password" class="block text-sm font-medium text-gray-700 mb-2">Kata Sandi</label>
                        <input type="password" id="password" name="password" required class="block w-full px-4 py-2 border border-gray-300 rounded-lg shadow-sm placeholder-gray-400 focus:ring-teal-500 focus:border-teal-500 sm:text-base" placeholder="Masukkan kata sandi">
                    </div>
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
                    <div>
                        <button type="submit" class="w-full flex justify-center py-3 px-4 border border-transparent rounded-lg shadow-sm text-lg font-medium text-white bg-teal-600 hover:bg-teal-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-teal-500 transition duration-200">
                            Masuk
                        </button>
                    </div>
                </form>
            </div>
            <div class="bg-gray-50 px-8 py-6 text-center text-sm text-gray-600">
                <p>Belum punya akun? Hubungi Administrator.</p>
            </div>
        </div>
    </main>

    <footer class="bg-gray-800 text-white py-6 px-4">
        <div class="max-w-4xl mx-auto text-center text-sm">
            <p>© 2023 SiCepu - Sistem Pelaporan Kerusakan Fasilitas Kampus. All rights reserved.</p>
        </div>
    </footer>
</body>
</html>