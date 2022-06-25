<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kamu - Error</title>
    <style>
        th {
            background-color: #aaaaaa;
        }

        td {
            text-align: left;
            height: 25px;
            border-bottom: 1px solid #bbb;
            cursor: context-menu;
        }

        tr:hover {
            background-color: #cccccc;
        }
    </style>
</head>

<body>
    <div style="display: grid;">
        <h2><?= $error->getMessage() ?></h2>
        <p><?= $error->getFile() . '::' . $error->getLine() ?></p>
        <div style="overflow-x: auto;">
            <table style="width: 100%; border-collapse: collapse;">
                <tr>
                    <th>No</th>
                    <th>File</th>
                    <th>Line</th>
                    <th>Action</th>
                </tr>
                <?php foreach ($error->getTrace() as $key => $value) : ?>
                    <tr>
                        <td><?= $key + 1 ?></td>
                        <td><?= $value['file'] ?? '-' ?></td>
                        <td><?= $value['line'] ?? '-' ?></td>
                        <td><?= @$value['class'] ? $value['class'] . $value['type'] . $value['function'] : $value['function'] ?></td>
                    </tr>
                <?php endforeach ?>
            </table>
        </div>
    </div>
</body>

</html>