<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Status Verifikasi Owner KitaSewa</title>
</head>
<body style="font-family: Arial, 'Helvetica Neue', Helvetica, sans-serif; background-color: #f4f5f6; margin: 0; padding: 30px 10px;">
    <table align="center" width="100%" cellpadding="0" cellspacing="0" role="presentation" style="max-width: 600px; background-color: #ffffff; border-top: 5px solid #FFC000; box-shadow: 0 4px 10px rgba(0,0,0,0.05); margin: 0 auto;">
        <tr>
            <td style="padding: 40px 40px 20px 40px;">
                <!-- Title -->
                <h1 style="font-size: 24px; color: #333333; margin-bottom: 25px; font-weight: normal;">
                    Halo {{ $notifiable->name ?? 'Calon Owner' }},
                </h1>

                <!-- Content -->
                @if($status === 'verified')
                    <p style="font-size: 16px; color: #333333; margin-bottom: 25px; line-height: 1.5;">
                        Selamat! Pendaftaran Anda sebagai Owner di <strong>KitaSewa</strong> telah <strong>disetujui</strong>.
                    </p>
                    <p style="font-size: 15px; color: #555555; margin-bottom: 35px; line-height: 1.5;">
                        Kini Anda dapat mengakses Dashboard Owner dan mulai menyewakan aset Anda untuk menjangkau lebih banyak pelanggan.
                    </p>

                    <div style="margin-bottom: 40px;">
                        <a href="{{ route('owner.dashboard') ?? url('/') }}" style="display: inline-block; padding: 12px 30px; background-color: #FFC000; color: #0A2540; text-decoration: none; border-radius: 6px; font-weight: bold; font-size: 15px;">
                            Buka Dashboard Owner
                        </a>
                    </div>
                @else
                    <p style="font-size: 16px; color: #333333; margin-bottom: 25px; line-height: 1.5;">
                        Mohon maaf, pendaftaran Anda sebagai Owner di <strong>KitaSewa</strong> saat ini <strong>belum dapat disetujui</strong>.
                    </p>
                    
                    @if($reason)
                        <div style="background-color: #FEF2F2; border-left: 4px solid #EF4444; padding: 15px; margin-bottom: 25px;">
                            <p style="margin: 0; color: #991B1B; font-size: 14px; font-weight: bold;">Alasan Penolakan:</p>
                            <p style="margin: 5px 0 0 0; color: #7F1D1D; font-size: 14px; line-height: 1.5;">
                                {{ $reason }}
                            </p>
                        </div>
                    @endif

                    <p style="font-size: 15px; color: #555555; margin-bottom: 35px; line-height: 1.5;">
                        Silakan lengkapi atau perbaiki dokumen yang diperlukan, lalu ajukan ulang pendaftaran Anda.
                    </p>

                    <div style="margin-bottom: 40px;">
                        <a href="{{ route('owner.register') ?? url('/') }}" style="display: inline-block; padding: 12px 30px; background-color: #FFC000; color: #0A2540; text-decoration: none; border-radius: 6px; font-weight: bold; font-size: 15px;">
                            Ajukan Ulang Pendaftaran
                        </a>
                    </div>
                @endif

                <!-- Section Title -->
                <div style="font-size: 12px; color: #777777; letter-spacing: 1.5px; text-transform: uppercase; font-weight: bold; margin-bottom: 15px;">
                    RINCIAN STATUS
                </div>
            </td>
        </tr>
        <tr>
            <!-- Gray block -->
            <td style="background-color: #f7f7f7; padding: 25px 40px;">
                <table width="100%" cellpadding="0" cellspacing="0" role="presentation" style="font-size: 15px; color: #444444; line-height: 1.6;">
                    <tr>
                        <td width="35%" style="padding-bottom: 12px; vertical-align: top;">Status Pendaftaran</td>
                        <td width="5%" style="padding-bottom: 12px; vertical-align: top;">:</td>
                        <td width="60%" style="padding-bottom: 12px; vertical-align: top;">
                            <strong style="color: {{ $status === 'verified' ? '#059669' : '#DC2626' }};">
                                {{ $status === 'verified' ? 'DISETUJUI' : 'DITOLAK' }}
                            </strong>
                        </td>
                    </tr>
                    <tr>
                        <td width="35%" style="vertical-align: top;">Tanggal Konfirmasi</td>
                        <td width="5%" style="vertical-align: top;">:</td>
                        <td width="60%" style="vertical-align: top;">{{ $date }}</td>
                    </tr>
                </table>
            </td>
        </tr>
        <tr>
            <td style="padding: 25px 40px 40px 40px;">
                <p style="font-size: 14px; color: #555555; margin: 0; line-height: 1.5;">
                    Jika Anda memiliki pertanyaan lebih lanjut, jangan ragu untuk menghubungi layanan pelanggan kami.
                </p>
            </td>
        </tr>
    </table>
</body>
</html>
