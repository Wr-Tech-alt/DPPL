<?php
// Mengarahkan (redirect) pengguna ke halaman login.php

// Pastikan tidak ada output (HTML, spasi, baris kosong) sebelum header()
// Jika ada output, header() akan gagal dan bisa menyebabkan "Headers already sent" error.

header("Location: login.php");
exit(); // Penting: Menghentikan eksekusi script setelah redirect
?>