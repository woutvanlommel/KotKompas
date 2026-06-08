<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Nieuw contactbericht</title>
</head>
<body style="margin:0;padding:24px;background:#ebebeb;font-family:Arial,Helvetica,sans-serif;color:#00101e;">
    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="max-width:560px;margin:0 auto;background:#ffffff;border-radius:8px;overflow:hidden;">
        <tr>
            <td style="background:#004e98;padding:20px 28px;">
                <span style="color:#ffffff;font-size:18px;font-weight:bold;">KotKompas</span>
            </td>
        </tr>
        <tr>
            <td style="padding:28px;">
                <h1 style="margin:0 0 16px;font-size:20px;">Nieuw contactbericht</h1>
                <p style="margin:0 0 20px;font-size:14px;line-height:1.5;color:#5e5e5e;">
                    Een verhuurder heeft een bericht gestuurd via het dashboard.
                </p>

                <p style="margin:0 0 6px;font-size:14px;"><strong>Van:</strong> {{ $senderName }} ({{ $senderEmail }})</p>
                <p style="margin:0 0 20px;font-size:14px;"><strong>Onderwerp:</strong> {{ $subjectLine }}</p>

                <div style="border-top:1px solid #ebebeb;padding-top:20px;font-size:15px;line-height:1.6;white-space:pre-line;">{{ $body }}</div>

                <p style="margin:28px 0 0;">
                    <a href="mailto:{{ $senderEmail }}" style="display:inline-block;background:#ff6700;color:#ffffff;text-decoration:none;font-size:14px;font-weight:bold;padding:10px 20px;border-radius:4px;">
                        Beantwoorden
                    </a>
                </p>
            </td>
        </tr>
        <tr>
            <td style="padding:18px 28px;background:#f7f7f7;font-size:12px;color:#8d8d8d;">
                Verstuurd via het KotKompas-dashboard.
            </td>
        </tr>
    </table>
</body>
</html>
