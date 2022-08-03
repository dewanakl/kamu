<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kamu - <?= e($pesan) ?></title>
    <style>
        body {
            font-family: 'Lato', sans-serif;
            color: #555;
            margin: 0;
        }

        #main {
            height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
        }

        .fof {
            text-align: center;
        }

        .fof h1 {
            font-size: 40px;
            display: inline-block;
            padding-right: 15px;
            animation: type .5s alternate infinite;
        }

        @keyframes type {
            from {
                box-shadow: inset -3px 0px 0px #555;
            }

            to {
                box-shadow: inset -3px 0px 0px transparent;
            }
        }
    </style>
</head>

<body>
    <div id="main">
        <div class="fof">
            <h1><?= e($pesan) ?></h1>
        </div>
    </div>
</body>

</html>