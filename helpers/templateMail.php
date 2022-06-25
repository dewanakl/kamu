<!DOCTYPE html>
<html>

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
</head>

<body style="width: 100% !important; -webkit-text-size-adjust: none; margin: 0; padding: 0;">
    <table style="border-spacing: 0; border-collapse: collapse; font-family: proxima-nova, 'helvetica neue', helvetica, arial, geneva, sans-serif; width: 100% !important; height: 100% !important; color: #4c4c4c; font-size: 15px; line-height: 150%; background: #ffffff; margin: 0; padding: 0; border: 0;">
        <tr style="vertical-align: top; padding: 0;">
            <td align="center" valign="top" style="vertical-align: top; padding: 0;">
                <table style="border-spacing: 0; border-collapse: collapse; font-family: proxima-nova, 'helvetica neue', helvetica, arial, geneva, sans-serif; width: 600px; color: #4c4c4c; font-size: 15px; line-height: 150%; background: #ffffff; margin: 40px 0; padding: 0; border: 0;">
                    <tr style="vertical-align: top; padding: 0;">
                        <td align="center" valign="top" style="vertical-align: top; padding: 0 40px;">
                            <table style="border-spacing: 0; border-collapse: collapse; font-family: proxima-nova, 'helvetica neue', helvetica, arial, geneva, sans-serif; width: 100%; background: #ffffff; margin: 0; padding: 0; border: 0;">
                                <tr style="vertical-align: top; padding: 0;">
                                    <td style="vertical-align: top; text-align: left; padding: 0;" align="left" valign="top">
                                        <h1 style="color: #126dff; display: block; font-size: 35px; font-weight: 200; text-align: left; margin: 0 0 40px;" align="left"><?= env('APP_NAME') ?></h1>

                                        <p style="margin: 20px 0;">Terima kasih telah verifikasi email pada <?= $namaEmail ?>. Berikut link untuk verifikasi dan akan hangus dalam 1 jam kedepan :</p>

                                        <p style="margin: 20px 0;"><a href="<?= asset('/') ?>" style="color: #126dff;"><?= asset('/') ?></a>
                                        <p style="margin: 20px 0;">Jika ada yang ditanyakan, bisa balas email ini</p>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                    <tr style="vertical-align: top; padding: 0;">
                        <td valign="top" style="vertical-align: top; padding: 0 40px;">
                            <table style="border-spacing: 0; border-collapse: collapse; width: 100%; border-top-style: solid; border-top-color: #000; color: #000; background: #ffffff; margin: 0; padding: 0; border-width: 1px 0 0;">
                                <tr style="vertical-align: top; padding: 0;">
                                    <td valign="top" style="vertical-align: top; text-align: left; padding: 0;" align="left">
                                        <p style="margin: 20px 0;">
                                            Salam dari <?= env('APP_NAME') ?>
                                            <br />
                                            <a href="<?= asset('/') ?>" style="color: #126dff;"><?= asset('/') ?></a>
                                        </p>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>

</html>