<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kode Verifikasi KuSewa</title>
</head>
<body style="font-family: Arial, 'Helvetica Neue', Helvetica, sans-serif; background-color: #f4f5f6; margin: 0; padding: 30px 10px;">
    <table align="center" width="100%" cellpadding="0" cellspacing="0" role="presentation" style="max-width: 600px; background-color: #ffffff; border-top: 5px solid #FFC000; box-shadow: 0 4px 10px rgba(0,0,0,0.05); margin: 0 auto;">
        <tr>
            <td style="padding: 40px 40px 20px 40px;">
                <!-- Title -->
                <h1 style="font-size: 24px; color: #333333; margin-bottom: 25px; font-weight: normal;">
                    Kode Verifikasi Rahasia KuSewa
                </h1>

                <!-- Content -->
                <p style="font-size: 16px; color: #333333; margin-bottom: 25px; line-height: 1.5;">
                    Ini adalah kode verifikasi KuSewa Anda: <strong style="font-size: 18px;">{{ $otp }}</strong>
                </p>

                <p style="font-size: 15px; color: #555555; margin-bottom: 35px; line-height: 1.5;">
                    Kode ini berlaku hanya <strong>1 menit</strong>. Atau, Anda dapat memverifikasi secara otomatis dengan menekan tombol berikut:
                </p>

                <div style="margin-bottom: 40px;">
                    <a href="{{ $magicLink }}" style="display: inline-block; padding: 12px 30px; background-color: #FFC000; color: #0A2540; text-decoration: none; border-radius: 6px; font-weight: bold; font-size: 15px;">
                        Verifikasi
                    </a>
                </div>

                <!-- Section Title -->
                <div style="font-size: 12px; color: #777777; letter-spacing: 1.5px; text-transform: uppercase; font-weight: bold; margin-bottom: 15px;">
                    RINCIAN
                </div>
            </td>
        </tr>
        <tr>
            <!-- Gray block -->
            <td style="background-color: #f7f7f7; padding: 25px 40px;">
                <table width="100%" cellpadding="0" cellspacing="0" role="presentation" style="font-size: 15px; color: #444444; line-height: 1.6;">
                    <tr>
                        <td width="35%" style="padding-bottom: 12px; vertical-align: top;">Tanggal dan Waktu</td>
                        <td width="5%" style="padding-bottom: 12px; vertical-align: top;">:</td>
                        <td width="60%" style="padding-bottom: 12px; vertical-align: top;">{{ $date }}</td>
                    </tr>
                    <tr>
                        <td style="padding-bottom: 12px; vertical-align: top;">Alamat IP</td>
                        <td style="padding-bottom: 12px; vertical-align: top;">:</td>
                        <td style="padding-bottom: 12px; vertical-align: top;">{{ $ipAddress }}</td>
                    </tr>
                    <tr>
                        <td style="vertical-align: top;">Jenis Perangkat</td>
                        <td style="vertical-align: top;">:</td>
                        <td style="vertical-align: top;">{{ $device }}</td>
                    </tr>
                </table>
            </td>
        </tr>
        <tr>
            <td style="padding: 25px 40px 40px 40px;">
                <p style="font-size: 14px; color: #555555; margin: 0; line-height: 1.5;">
                    Mohon pastikan Anda tidak pernah memberitahukan kode ini pada siapa pun.
                </p>
            </td>
        </tr>
    </table>
</body>
</html>
