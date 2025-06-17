<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SiCepu - Sistem Pelaporan Kerusakan Fasilitas Kampus</title>
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
    <!-- Banner Section -->
    <div class="banner-illustration bg-teal-700 py-16 px-4 sm:px-6 lg:px-8 shadow-md">
        <div class="max-w-4xl mx-auto text-center">
            <h1 class="text-3xl sm:text-4xl md:text-5xl font-bold text-white mb-6 leading-tight">
                Sistem Pelaporan Kerusakan<br>Fasilitas Kampus
            </h1>
            <div class="flex justify-center">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-16 w-16 text-white opacity-80" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                </svg>
            </div>
        </div>
    </div>

    <!-- Main Content -->
    <main class="flex-grow flex items-center justify-center py-12 px-4 sm:px-6 lg:px-8">
        <div class="max-w-3xl mx-auto bg-white rounded-xl shadow-lg overflow-hidden">
            <div class="p-8 sm:p-10 text-center">
                <h2 class="text-2xl sm:text-3xl font-semibold text-gray-800 mb-6">
                    Solusi Cepat untuk Lingkungan Belajar yang Optimal
                </h2>
                <div class="h-1 w-24 bg-teal-500 mx-auto mb-8 rounded-full"></div>
                <p class="text-gray-600 text-lg leading-relaxed mb-8">
                    Platform ini dirancang untuk memfasilitasi pelaporan kerusakan fasilitas di lingkungan kampus secara efisien dan transparan. Mahasiswa dan staf dapat dengan mudah melaporkan insiden melalui sistem terintegrasi kami.
                </p>
                <p class="text-gray-600 text-lg leading-relaxed">
                    Setiap laporan akan segera diterima oleh tim administrator atau penindak, yang kemudian akan memproses dan menindaklanjuti permasalahan guna memastikan perbaikan cepat dan menjaga kenyamanan serta keamanan seluruh pengguna fasilitas kampus.
                </p>
            </div>
            <div class="bg-gray-50 px-8 py-6 flex flex-col sm:flex-row justify-center items-center gap-4">
                <a href="#" class="w-full sm:w-auto px-6 py-3 bg-teal-600 hover:bg-teal-700 text-white font-medium rounded-lg shadow-sm transition duration-200 text-center">
                    Laporkan Kerusakan
                </a>
                <a href="#" class="w-full sm:w-auto px-6 py-3 border border-gray-300 bg-white hover:bg-gray-50 text-gray-700 font-medium rounded-lg shadow-sm transition duration-200 text-center">
                    Status Laporan
                </a>
            </div>
        </div>
    </main>

    <!-- Footer -->
    <footer class="bg-gray-800 text-white py-6 px-4">
        <div class="max-w-4xl mx-auto text-center text-sm">
            <p>© 2023 SiCepu - Sistem Pelaporan Kerusakan Fasilitas Kampus. All rights reserved.</p>
        </div>
    </footer>
</body>
</html>