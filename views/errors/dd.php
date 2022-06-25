<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kamu - Debug</title>
    <style>
        pre {
            margin-bottom: 30px;
            margin-left: 10px;
            font-size: 14px;
        }
    </style>
</head>

<body>
    <?php foreach ($param as $value) : ?>
        <pre><?php var_dump($value) ?></pre>
    <?php endforeach ?>
</body>

</html>