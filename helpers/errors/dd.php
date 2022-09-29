<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kamu - Debug</title>
    <style>
        pre {
            margin: 2rem 1rem;
            font-size: 14px;
            overflow: auto;
            white-space: pre-wrap;
            word-wrap: break-word;
        }
    </style>
</head>

<body>
    <?php
    foreach ($param as $val) {
        ob_start();
        var_dump($val);
        $res = ob_get_contents();
        ob_end_clean();

        echo '<pre>' . e($res) . '</pre>';
    }
    ?>
</body>

</html>