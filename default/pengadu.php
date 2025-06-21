<?php
// Pastikan koneksi dan session dimulai
// include '../inc/koneksi.php'; // Tidak perlu lagi di-include di sini karena sudah di-include di index.php
// Pastikan $_SESSION['ses_level'] sudah tersedia dari index.php

// Periksa apakah pengguna sudah login dan apakah level-nya 'masyarakat'
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true || $_SESSION['ses_level'] !== 'masyarakat') {
    header("Location: ../login.php"); // Arahkan kembali ke halaman login jika tidak valid
    exit();
}

$id_pengguna_saat_ini = $_SESSION["ses_id"]; // Ambil ID pengguna yang sedang login

// Query untuk mengambil riwayat laporan pengguna yang sedang login
$sql_riwayat_laporan = "
    SELECT
        p.idpengaduan,
        p.judul,
        p.waktu_aduan,
        jp.jenis,
        p.status,
        p.keterangan
    FROM
        pengaduan p
    JOIN
        jenis_pengaduan jp ON p.idjenis = jp.idjenis
    WHERE
        p.iduser = ? -- Filter berdasarkan id user yang login
    ORDER BY
        p.waktu_aduan DESC"; // Urutkan dari laporan terbaru

$stmt_riwayat = mysqli_prepare($koneksi, $sql_riwayat_laporan);
mysqli_stmt_bind_param($stmt_riwayat, "i", $id_pengguna_saat_ini); // 'i' untuk integer
mysqli_stmt_execute($stmt_riwayat);
$result_riwayat = mysqli_stmt_get_result($stmt_riwayat);

?>

<div class="row">
    <div class="col-md-12">
        <h2 style="margin-top:0;">Dashboard Pengadu</h2>
        <h5>Selamat datang, <?php echo htmlspecialchars($_SESSION['ses_nama_lengkap']); ?>! Anda login sebagai <?php echo htmlspecialchars($_SESSION['ses_level']); ?>.</h5>
        <hr>
    </div>
</div>

<div class="row dashboard-cards">
    <?php
    // Query untuk menghitung jumlah laporan per status
    $sql_stats = "
        SELECT status, COUNT(*) as jumlah
        FROM pengaduan
        WHERE iduser = ?
        GROUP BY status";
    $stmt_stats = mysqli_prepare($koneksi, $sql_stats);
    mysqli_stmt_bind_param($stmt_stats, "i", $id_pengguna_saat_ini);
    mysqli_stmt_execute($stmt_stats);
    $result_stats = mysqli_stmt_get_result($stmt_stats);

    $stats = [
        'pending' => 0,
        'diproses' => 0, // Asumsi status 'diproses'
        'selesai' => 0
    ];

    while ($row = mysqli_fetch_assoc($result_stats)) {
        // Sesuaikan dengan nama status di DB Anda (misal: 'pending', 'dikerjakan', 'selesai')
        if (strtolower($row['status']) == 'pending') {
            $stats['pending'] = $row['jumlah'];
        } elseif (strtolower($row['status']) == 'dikerjakan' || strtolower($row['status']) == 'diproses') { // Contoh jika ada status 'dikerjakan' atau 'diproses'
            $stats['diproses'] += $row['jumlah'];
        } elseif (strtolower($row['status']) == 'selesai') {
            $stats['selesai'] = $row['jumlah'];
        }
    }
    mysqli_stmt_close($stmt_stats);
    ?>

    <div class="col-md-3 col-sm-6 col-xs-12">
        <div class="card pending">
            <div class="card-icon"><i class="fa fa-clock-o"></i></div>
            <div class="card-number"><?= $stats['pending']; ?></div>
            <div class="card-title">Laporan Pending</div>
        </div>
    </div>
    <div class="col-md-3 col-sm-6 col-xs-12">
        <div class="card responded">
            <div class="card-icon"><i class="fa fa-spinner"></i></div>
            <div class="card-number"><?= $stats['diproses']; ?></div>
            <div class="card-title">Laporan Diproses</div>
        </div>
    </div>
    <div class="col-md-3 col-sm-6 col-xs-12">
        <div class="card completed">
            <div class="card-icon"><i class="fa fa-check-circle"></i></div>
            <div class="card-number"><?= $stats['selesai']; ?></div>
            <div class="card-title">Laporan Selesai</div>
        </div>
    </div>
    <div class="col-md-3 col-sm-6 col-xs-12">
        <a href="?page=aduan_tambah_form" style="text-decoration: none;">
            <div class="card add-new">
                <div class="card-icon"><i class="fa fa-plus-circle"></i></div>
                <div class="card-number">Baru</div>
                <div class="card-title">Buat Laporan Baru</div>
            </div>
        </a>
    </div>
</div>
<hr>

<div class="row">
    <div class="col-md-12">
        <a href="?page=aduan_tambah_form" class="btn btn-primary" style="margin-bottom: 20px;">
            <i class="fa fa-plus"></i> Tambah Laporan Baru
        </a>
    </div>
</div>

<div class="row">
    <div class="col-md-12">
        <div class="panel panel-default">
            <div class="panel-heading">
                Riwayat Laporan Anda
            </div>
            <div class="panel-body">
                <div class="table-responsive">
                    <table class="table table-striped table-bordered table-hover" id="dataTables-example">
                        <thead>
                            <tr>
                                <th>No.</th>
                                <th>Judul Laporan</th>
                                <th>Waktu Aduan</th>
                                <th>Jenis Aduan</th>
                                <th>Status</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $no = 1;
                            while ($row = mysqli_fetch_assoc($result_riwayat)) {
                                $status_class = '';
                                if (strtolower($row['status']) == 'pending') {
                                    $status_class = 'label label-danger';
                                } elseif (strtolower($row['status']) == 'dikerjakan' || strtolower($row['status']) == 'diproses') {
                                    $status_class = 'label label-warning';
                                } elseif (strtolower($row['status']) == 'selesai') {
                                    $status_class = 'label label-success';
                                }
                            ?>
                                <tr>
                                    <td><?= $no++; ?></td>
                                    <td><?= htmlspecialchars($row['judul']); ?></td>
                                    <td><?= htmlspecialchars(date('d-m-Y H:i', strtotime($row['waktu_aduan']))); ?></td>
                                    <td><?= htmlspecialchars($row['jenis']); ?></td>
                                    <td><span class="<?= $status_class; ?>"><?= htmlspecialchars($row['status']); ?></span></td>
                                    <td>
                                        <a href="?page=aduan_kelola&id=<?= $row['idpengaduan']; ?>" class="btn btn-info btn-xs">
                                            <i class="fa fa-info-circle"></i> Detail
                                        </a>
                                    </td>
                                </tr>
                            <?php
                            }
                            mysqli_stmt_close($stmt_riwayat);
                            ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>