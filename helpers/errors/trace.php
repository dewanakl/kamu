<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kamu - Error</title>
    <style>
        pre {
            font-size: 22px;
            font-weight: bold;
            overflow: auto;
            white-space: pre-wrap;
            word-wrap: break-word;
        }

        .font {
            font-family: monospace;
            font-size: 15px;
        }

        th {
            background-color: #aaaaaa;
        }

        td {
            text-align: left;
            height: 25px;
            border-bottom: 1px solid #bbb;
        }

        tr:hover {
            background-color: #cccccc;
        }
    </style>
</head>

<body style="display: grid;">
    <pre><?= e($error->getMessage()) ?></pre>
    <div class="font">
        <p><?= e($error->getFile()) . '::' . e($error->getLine()) ?></p>
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
                        <td><?= e($value['file'] ?? '-') ?></td>
                        <td><?= e($value['line'] ?? '-') ?></td>
                        <td><?= @$value['class'] ? e($value['class'] . $value['type'] . $value['function']) : e($value['function']) ?></td>
                    </tr>
                <?php endforeach ?>
            </table>
        </div>
    </div>
</body>

</html>