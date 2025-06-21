<?php

$error = $_SERVER["REDIRECT_STATUS"];

$error_tittle = '';
$error_message = '';
$error_sub = '';

if ($error == 404) {
    $error_tittle = '404 Page Not Found';
    $error_message = 'Halaman Tidak Ditemukan';
    $error_sub = 'Pastikan URL yang anda tuju benar.';
} elseif ($error == 403) {
    $error_tittle = '403 Access Denied';
    $error_message = 'Silahkan hubungi <a href="https://www.instagram.com/arifinza.engr/">arifinza.engr</a> untuk info lebih lanjut.';
    $error_sub = 'Akses ditolak untuk mengakses halaman ini.';
}
?>

<!doctype html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.1/dist/css/bootstrap.min.css" integrity="sha384-zCbKRCUGaJDkqS1kPbPd7TveP5iyJE0EjAuZQTgFLD2ylzuqKfdKlfG/eSrtxUkn" crossorigin="anonymous">

    <link rel="stylesheet" href="../assets/css/error.css"> 

    <title><?php echo $error_tittle; ?></title>
</head>

<body>
    <div class="container text-white" style="margin-top:150px">
        <div class="jumbotron" style="background-image: url('../assets/img/403.jpg');" width="100%" height="100%">
            <h1 class="display-4"><?php echo $error_tittle; ?></h1>
            <p class="lead"><?php echo $error_sub; ?></p>
            <hr class="my-4">
            <p><?php echo $error_message; ?>.</p>
            <a class="btn btn-primary btn-md" href="../login1.php" role="button">Kembali ke Login</a> 
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/jquery@3.5.1/dist/jquery.slim.min.js" integrity="sha384-DfXdz2htPH0lsSSs5nCTpuj/zy4C+OGpamoFVy38MVBnE+IbbVYUew+OrCXaRkfj" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.1/dist/js/bootstrap.bundle.min.js" integrity="sha384-fQybjgWLrvvRgtW6bFlB7jaZrFsaBXjsOMm/tB9LTS58ONXgqbR9W8oWht/amnpF" crossorigin="anonymous"></script>

</body>

</html>