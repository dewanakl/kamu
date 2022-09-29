<!DOCTYPE html>
<html>

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
</head>

<body>
    <h1 style="color: #126dff;"><?= ucfirst(env('APP_NAME')) ?></h1>

    <h3>Haii <strong><?= e($nama) ?></strong></h3>
    <p>Berikut link untuk pemulihan akun kamu :</p>

    <p><a href="<?= $link ?>" style="color: #126dff;" target="_blank" rel="noopener noreferrer"><?= e($link) ?></a></p>
    <p>Link reset password ini satu kali pakai, jika ini bukan kamu maka abaikan saja.</p>

    <hr />

    <p>Salam hangat dari <?= env('APP_NAME') ?></p>
    <p><a href="<?= asset('/') ?>" style="color: #126dff;"><?= e(parse_url(BASEURL, PHP_URL_HOST)) ?></a></p>
</body>

</html>