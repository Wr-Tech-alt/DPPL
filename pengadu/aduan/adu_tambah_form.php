<?php
// Pastikan koneksi ke database sudah tersedia
// Sesuaikan path ke koneksi.php jika berbeda
include_once 'inc/koneksi.php'; // Contoh path, sesuaikan dengan struktur folder Anda

// Ambil data jenis aduan dari database untuk dropdown
$query_jenis_dropdown = $koneksi->query("SELECT id_jenis, jenis FROM tb_jenis ORDER BY jenis ASC");
?>

<div class="panel panel-info">
    <div class="panel-heading">
        <i class="glyphicon glyphicon-plus"></i>
        <b>Tambah Aduan Baru</b>
    </div>
    <div class="panel-body">
        <div class="row">
            <div class="col-md-12">
                <form action="?page=aduan_tambah" method="post" enctype="multipart/form-data">
                    <div class="form-group">
                        <label>Nama Anda (Judul Aduan)</label>
                        <input type="text" name="judul" class="form-control" placeholder="Masukkan Nama Anda atau Judul Aduan" required>
                    </div>

                    <div class="form-group">
                        <label>Lokasi Aduan (Alamat)</label>
                        <input type="text" name="lokasi" class="form-control" placeholder="Masukkan Lokasi atau Alamat Aduan" required>
                    </div>

                    <div class="form-group">
                        <label>No Hp/Telegram/label>
                        <input type="text" name="no_telpon" class="form-control" placeholder="Masukkan Nomor Telepon/WhatsApp" required>
                    </div>

                    <div class="form-group">
                        <label>Jenis Aduan</label>
                        <select name="jenis" class="form-control" required>
                            <option value="">-- Pilih Jenis Aduan --</option>
                            <?php
                            while ($data_jenis = $query_jenis_dropdown->fetch_assoc()) {
                                echo "<option value='" . $data_jenis['id_jenis'] . "'>" . $data_jenis['jenis'] . "</option>";
                            }
                            ?>
                        </select>
                    </div>

                    <div class="form-group">
                        <label>Keterangan Aduan</label>
                        <textarea name="keterangan" class="form-control" rows="5" placeholder="Jelaskan keterangan aduan Anda" required></textarea>
                    </div>

                    <div class="form-group">
                        <label>Foto (Opsional)</label>
                        <input type="file" name="foto" class="form-control" accept="images/pengaduan/*">
                        <p class="help-block">Ukuran file maksimal: 2MB. Format: JPG, PNG.</p>
                    </div>

                    <div class="box-footer">
                        <button type="submit" name="Simpan" class="btn btn-primary">
                            <i class="glyphicon glyphicon-send"></i> Simpan
                        </button>
                        <a href="?page=aduan_view" class="btn btn-danger">
                            <i class="glyphicon glyphicon-remove"></i> Batal
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<?php
// Pastikan script untuk SweetAlert (jika digunakan) sudah dimuat di halaman utama (index.php)
// atau tambahkan script di sini jika ini adalah halaman mandiri.
?>