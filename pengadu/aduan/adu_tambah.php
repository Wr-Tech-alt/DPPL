<?php
// ... (bagian atas kode HTML dan PHP lainnya tidak berubah)

if (isset($_POST['Simpan'])) {
    $aduan = $_POST['judul']; // Nama Anda
    $lokasi = $_POST['lokasi']; // Lokasi Aduan (Alamat)
    $no_telp = $_POST['no_telpon']; // No Hp/Whatsapp
    $jenis_aduan_id = $_POST['jenis']; // ID Jenis Aduan
    $keterangan = $_POST['keterangan']; // Keterangan Aduan

    // --- Ambil nama jenis aduan dari ID ---
    // Pastikan koneksi ke database ($koneksi) sudah tersedia
    $query_jenis = $koneksi->query("SELECT jenis FROM tb_jenis WHERE id_jenis = '$jenis_aduan_id'");
    $data_jenis = $query_jenis->fetch_assoc();
    $nama_jenis_aduan = $data_jenis['jenis']; // Ini adalah "Jenis" yang akan tampil di Telegram

    // --- Proses Upload Foto ---
    $sumber = $_FILES['foto']['tmp_name'];
    $nama_file = $_FILES['foto']['name'];
    $ext = pathinfo($nama_file, PATHINFO_EXTENSION); // Dapatkan ekstensi file (misal: jpg, png)
    $nama_file_baru = uniqid() . '.' . $ext; // Buat nama file unik untuk mencegah tabrakan nama
    $target_dir = 'foto/'; // Pastikan folder 'foto' ada di root project Anda
    $pindah = move_uploaded_file($sumber, $target_dir . $nama_file_baru); // Pindahkan file ke folder 'foto'

    // --- Pastikan ID Chat Telegram sudah didapatkan ---
    // Ini mengantisipasi jika $id_chat belum terinisialisasi dari query di awal file
    if (empty($id_chat)) {
        $sql_tele_check = $koneksi->query("SELECT id_chat FROM tb_telegram LIMIT 1");
        if ($data_tele_check = $sql_tele_check->fetch_assoc()) {
            $id_chat = $data_tele_check['id_chat'];
        } else {
            // Jika ID Chat Telegram masih kosong, tampilkan pesan error dan hentikan proses
            echo "<script>
                Swal.fire({title: 'Error',text: 'ID Chat Telegram tidak ditemukan. Pastikan sudah diatur di menu Telegram.',icon: 'error',confirmButtonText: 'OK'})
                .then((result) => {if (result.value) {window.location = 'index.php?page=aduan_view';}})
                </script>";
            exit(); // Hentikan eksekusi
        }
    }

    // --- Simpan data ke Database ---
    $sql_simpan = "INSERT INTO tb_pengaduan (`judul`, `no_telpon`, `jenis`, `lokasi`, `keterangan`, `foto`, `author`, `tgl_aduan`) VALUES (
        '" . $aduan . "',
        '" . $no_telp . "',
        '" . $jenis_aduan_id . "', // Simpan ID jenis ke database
        '" . $lokasi . "',
        '" . $keterangan . "',
        '" . $nama_file_baru . "', // Simpan nama file unik ke database
        '$author',
        NOW() // Tambahkan tanggal aduan otomatis
    )";
    $query_simpan = mysqli_query($koneksi, $sql_simpan);

    // --- Kirim Notifikasi Telegram jika penyimpanan dan upload berhasil ---
    if ($query_simpan && $pindah) { // Pastikan data ke DB dan foto berhasil diunggah

        // Tampilkan SweetAlert sukses
        echo "<script>
                Swal.fire({title: 'Tambah Sukses',text: '',icon: 'success',confirmButtonText: 'OK'})
                .then((result) => {if (result.value) {window.location = 'index.php?page=aduan_view';}})
                </script>";

        $token = "7659078113:AAFxx6gvXPGAJVJQ1dSAcXzOe0MO9nw_T_Y"; // Ganti dengan Token bot Telegram Anda
        
        // **SANGAT PENTING: GANTI DENGAN URL ASLI SERVER ANDA**
        // Contoh: 'https://nama-domain-anda.com/sicepu/foto/'
        $base_url_foto = "http://localhost/sicepu/foto/"; // Sesuaikan dengan path server Anda
        $photo_url = $base_url_foto . $nama_file_baru;

        // --- Format Pesan Telegram (Caption Foto) ---
        // Menggunakan Markdown untuk bold (*) dan baris baru (\n)
        $caption_text = "*📢 INFO PENGADUAN BARU*\n\n" . // Icon speaker
                        "👤 *Nama Pengirim:* " . $aduan . "\n" . // Icon orang
                        "📍 *Lokasi Aduan:* " . $lokasi . "\n" . // Icon pin lokasi (Alamat di gambar pertama Anda)
                        "📞 *No Hp/Whatsapp:* " . $no_telp . "\n" . // Icon telepon
                        "🛠️ *Jenis Aduan:* " . $nama_jenis_aduan . "\n" . // Icon tools/gear
                        "📝 *Keterangan:* " . $keterangan . "\n\n" . // Icon pensil/kertas
                        "Memerlukan penanganan segera. Terima kasih.";

        // --- Kirim Foto dengan Caption ke Telegram ---
        $url_telegram_api = "https://api.telegram.org/bot" . $token . "/sendPhoto";

        $data_telegram = array(
            'chat_id' => $id_chat,
            'photo' => $photo_url,
            'caption' => $caption_text,
            'parse_mode' => 'Markdown' // Menggunakan Markdown untuk format teks
        );

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url_telegram_api);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data_telegram);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); // HANYA UNTUK TESTING DI LOCALHOST! HAPUS DI PRODUKSI!
        $result_telegram = curl_exec($ch);
        
        if (curl_errno($ch)) {
            error_log('cURL Error Telegram: ' . curl_error($ch)); // Catat error jika ada
        }
        curl_close($ch);

    } else {
        // Jika simpan ke DB atau upload foto gagal
        echo "<script>
                Swal.fire({title: 'Tambah Gagal',text: 'Terjadi kesalahan saat menyimpan data atau mengunggah foto.',icon: 'error',confirmButtonText: 'OK'})
                .then((result) => {if (result.value) {window.location = 'index.php?page=aduan_view';}})
                </script>";
        // Hapus file yang mungkin sudah terupload jika ada kesalahan database
        if ($pindah && file_exists($target_dir . $nama_file_baru)) {
            unlink($target_dir . $nama_file_baru);
        }
    }
}
?>