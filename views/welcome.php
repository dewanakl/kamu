<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kamu - PHP Framework</title>
    <style>
        html {
            height: 100%;
        }

        body {
            font-family: 'Lato', sans-serif;
            color: #555;
            margin: 0;
        }

        #main {
            display: table;
            width: 100%;
            height: 100vh;
            text-align: center;
        }

        .fof {
            display: table-cell;
            vertical-align: middle;
        }

        .fof h1 {
            font-size: 40px;
            display: inline;
        }

        .fof p {
            font-size: 20px;
            margin-left: 5px;
            display: inline;
        }

        .fof small {
            display: block;
            margin-top: 15px;
        }
    </style>
</head>

<body>
    <div id="main">
        <div class="fof">
            <h1>Kamu |</h1>
            <p><?= $data ?></p>
            <small>for educational purposes</small>
        </div>
    </div>
</body>

</html>