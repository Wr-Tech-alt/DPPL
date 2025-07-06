<?php
// config_email.php (Simpan di lokasi yang aman, contoh: di folder 'inc' yang sama dengan koneksi.php)

// Kredensial Email Pengirim Notifikasi (INI BUKAN EMAIL ADMIN BIASA, TAPI EMAIL KHUSUS SISTEM)
// PENTING: Gunakan App Password jika pakai Gmail, bukan password akun Gmail kamu.
// Cari tahu cara membuat App Password di pengaturan akun Google-mu.
define('EMAIL_SENDER_USERNAME', 'ryuugavariantes@gmail.com'); // Ganti dengan email pengirim notifikasi
define('EMAIL_SENDER_PASSWORD', 'mbyc vbyx kesy iacf'); // Ganti dengan App Password Gmail
define('EMAIL_SENDER_NAME', 'SiCepu Kampus Pilihan Anda STMI :)'); // Nama pengirim yang akan muncul
define('SMTP_HOST', 'smtp.gmail.com');
define('SMTP_PORT', 587);
//define('SMTP_SECURE', PHPMailer::ENCRYPTION_STARTTLS); // Atau PHPMailer::ENCRYPTION_SMTPS untuk port 46