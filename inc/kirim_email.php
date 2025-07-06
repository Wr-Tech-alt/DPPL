<?php
// Pastikan PHPMailer sudah di-load di bagian awal file utama atau di file ini
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Pastikan PATH ini benar relatif terhadap lokasi file functions.php
require_once __DIR__ . '/../PHPMailer-master/src/PHPMailer.php';
require_once __DIR__ . '/../PHPMailer-master/src/SMTP.php';
require_once __DIR__ . '/../PHPMailer-master/src/Exception.php';

// Pastikan file konfigurasi email di-load di sini atau di file utama
require_once __DIR__ . '/../inc/config_email.php'; // Path ke file config_email.php

// Fungsi pengiriman email
// Parameter $conn (koneksi database) kini harus dilewatkan ke fungsi
function kirimEmailPengaduan($conn, $emailTujuan, $namaPengadu, $judulAduan, $status, $idAduan, $deskripsiAduan, $lokasiAduan) {
    $mail = new PHPMailer(true);

    try {
        // Konfigurasi SMTP dari file config_email.php
        $mail->isSMTP();
        $mail->Host       = SMTP_HOST;
        $mail->SMTPAuth   = true;
        $mail->Username   = EMAIL_SENDER_USERNAME; // Ambil dari konfigurasi
        $mail->Password   = EMAIL_SENDER_PASSWORD; // Ambil dari konfigurasi
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS; // <--- UBAH DI SINI
        $mail->SMTPSecure = SMTP_SECURE;
        $mail->Port       = SMTP_PORT;

        $mail->setFrom(EMAIL_SENDER_USERNAME, EMAIL_SENDER_NAME); // Nama pengirim dari konfigurasi
        $mail->addAddress($emailTujuan, $namaPengadu);

        $mail->isHTML(true); // Set email format to HTML
        $mail->Subject = "Status Aduan Anda Telah Diperbarui - $status";

        // Isi email yang lebih informatif untuk status "Selesai"
        $email_body = "";
        if ($status === 'Selesai') {
            $email_body = "
                <p>Halo <strong>$namaPengadu</strong>,</p>
                <p>Kami ingin memberitahukan bahwa aduan Anda dengan judul <strong>\"$judulAduan\"</strong> (ID: #$idAduan) telah **SELESAI** ditangani.</p>
                <p><strong>Detail Aduan:</strong></p>
                <ul>
                    <li><strong>Judul:</strong> $judulAduan</li>
                    <li><strong>Deskripsi:</strong> $deskripsiAduan</li>
                    <li><strong>Lokasi:</strong> $lokasiAduan</li>
                    <li><strong>Status Terbaru:</strong> <span style=\"color: green; font-weight: bold;\">Selesai</span></li>
                </ul>
                <p>Terima kasih atas partisipasi Anda dalam menjaga fasilitas kampus. Jika ada pertanyaan lebih lanjut, Anda bisa menghubungi admin.</p>
                <p>Salam Hormat,<br>Tim Pengelola Fasilitas Kampus</p>
                <hr>
                <small>Email ini dikirim otomatis oleh sistem SiCepu. Mohon jangan membalas email ini.</small>
            ";
        } else {
            // Konten email untuk status lain jika diperlukan (Pending, Diproses)
            $email_body = "
                <p>Halo <strong>$namaPengadu</strong>,</p>
                <p>Aduan Anda dengan judul <strong>\"$judulAduan\"</strong> (ID: #$idAduan) telah diperbarui menjadi <strong>$status</strong>.</p>
                <p>Silakan cek status terbaru aduan Anda di aplikasi.</p>
                <p>Terima kasih telah menggunakan layanan pengaduan kami.</p>
                <hr>
                <small>Email ini dikirim otomatis oleh sistem SiCepu.</small>
            ";
        }

        $mail->Body = $email_body;
        $mail->send();
        return true; // Berhasil kirim
    } catch (Exception $e) {
        // Logging error lebih baik daripada hanya echo
        error_log("Gagal kirim email ke $emailTujuan (Aduan ID: $idAduan). Error: " . $mail->ErrorInfo . " | Exception: " . $e->getMessage());
        return false; // Gagal kirim
    }
}