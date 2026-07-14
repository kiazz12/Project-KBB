<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Tanda Terima Uang Saku Peserta</title>
    <style>
        @page { margin: 15mm 12mm; size: landscape; }
        body { font-family: 'Segoe UI', sans-serif; font-size: 9pt; color: #1a1a2e; line-height: 1.4; }
        .header { text-align: center; margin-bottom: 10px; }
        .header img { height: 45px; margin-bottom: 4px; }
        .header h1 { font-size: 12pt; color: #003778; margin: 2px 0; text-transform: uppercase; }
        .header h2 { font-size: 10pt; color: #333; margin: 2px 0; font-weight: normal; }
        .header p { font-size: 8pt; color: #666; margin: 1px 0; }
        .info { font-size: 8pt; color: #444; margin-bottom: 8px; text-align: center; }
        table { width: 100%; border-collapse: collapse; font-size: 8pt; }
        th { background: #003778; color: #fff; padding: 5px 4px; text-align: center; font-weight: 600; border: 1px solid #002a5c; }
        td { padding: 4px; border: 1px solid #ccc; color: #333; text-align: center; vertical-align: middle; }
        td.left { text-align: left; }
        td.right { text-align: right; }
        tr:nth-child(even) td { background: #f8f9fb; }
        .footer { margin-top: 15px; font-size: 8pt; }
        .footer-row { display: flex; justify-content: space-between; margin-top: 10px; }
        .footer-block { width: 30%; }
        .footer-block p { margin: 1px 0; }
        .footer-block .label { font-weight: 600; color: #003778; }
        .footer-block .name { font-weight: bold; text-decoration: underline; }
        .footer-block .nip { font-size: 7pt; color: #666; }
        .page-break { page-break-before: always; }
    </style>
</head>
<body>
    <div class="header">
        <img src="{{ public_path('images/kbb-logo.png') }}" alt="KBB Logo">
        <h2 style="font-size:9pt; font-weight:700; color:#333; margin:2px 0;">PEMERINTAH KABUPATEN BANDUNG BARAT</h2>
        <h1 style="font-size:10pt; font-weight:700; color:#003778; margin:2px 0;">DINAS KOMUNIKASI, INFORMATIKA, PERSANDIAN DAN STATISTIK</h1>
        <p style="font-size:7.5pt; color:#666; margin:1px 0;">Gedung B Lt. 2 Komplek Perkantoran Pemerintah Kabupaten Bandung Barat</p>
        <p style="font-size:7.5pt; color:#666; margin:1px 0;">Jl. Raya Padalarang – Cisarua Km 2 Ngamprah Email: kominfo@bandungbaratkab.go.id Kode Pos 40552</p>
        <br>
        <h1 style="font-size:11pt; color:#003778; margin:2px 0; text-transform:uppercase;">Tanda Terima Uang Saku Peserta</h1>
        <p style="font-size:8pt; color:#555; margin:1px 0;">Kegiatan: Pengelolaan E-Government di Lingkup Pemerintah Daerah Kabupaten/Kota</p>
        <p style="font-size:8pt; color:#555; margin:1px 0;">Sub Kegiatan: Koordinasi Pemanfaatan Portal Pelayanan Pemerintah Daerah yang Terintegrasi</p>
        <p style="font-size:7.5pt; color:#666; margin:1px 0;">Kode Rekening: 2.16.03.2.02.0025.5.1.02.04.001.00004</p>
        <p style="font-size:8pt; color:#555; margin:1px 0;">Sosialisasi Pemanfaatan Portal Layanan Pemerintah Daerah yang Terintegrasi di Kabupaten Bandung Barat</p>
    </div>

    <div class="info">
        <strong>Total Peserta:</strong> {{ count($submissions) }} |
        <strong>Tanggal Export:</strong> {{ now()->format('d M Y H:i') }}
    </div>

    <table>
        <thead>
            <tr>
                <th style="width:30px">No</th>
                <th style="width:150px">Nama</th>
                <th style="width:150px">Instansi/Utusan</th>
                <th style="width:120px">Jabatan</th>
                <th style="width:100px">NIK</th>
                <th style="width:70px">Jumlah Uang Saku</th>
                <th style="width:60px">PPh 21 (5%)</th>
                <th style="width:80px">Jumlah Diterima</th>
                <th style="width:80px">Tanda Tangan</th>
            </tr>
        </thead>
        <tbody>
            @php $no = 1; @endphp
            @foreach($submissions as $submission)
                @php
                    $data = $submission->data->keyBy('formField.label');
                    $nama = $data->get('Nama Peserta')?->value ?? '—';
                    $instansi = $data->get('Instansi/Utusan')?->value ?? '—';
                    $jabatan = $data->get('Jabatan')?->value ?? '—';
                    $nik = $data->get('NIK')?->value ?? '';
                    $uangSaku = $data->get('Jumlah Uang Saku')?->value ?? '0';
                    $pph21 = $data->get('PPh 21 (5%)')?->value ?? '0';
                    $jumlahDiterima = $data->get('Jumlah Diterima')?->value ?? '0';
                @endphp
                <tr>
                    <td>{{ $no++ }}</td>
                    <td class="left">{{ $nama }}</td>
                    <td class="left">{{ $instansi }}</td>
                    <td class="left">{{ $jabatan }}</td>
                    <td>{{ $nik }}</td>
                    <td class="right">Rp {{ number_format((float)$uangSaku, 0, ',', '.') }}</td>
                    <td class="right">Rp {{ number_format((float)$pph21, 0, ',', '.') }}</td>
                    <td class="right">Rp {{ number_format((float)$jumlahDiterima, 0, ',', '.') }}</td>
                    <td></td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <div class="footer">
        <div class="footer-row">
            <div class="footer-block">
                <p class="label">Pejabat Pelaksana Teknis Kegiatan</p>
                <br><br>
                <p class="name">TIMMY SAMPURNA IRAWAN, ST., M.Si</p>
                <p class="nip">NIP. 19860429 201101 1 004</p>
            </div>
            <div class="footer-block">
                <p class="label">Bendahara Pengeluaran</p>
                <br><br>
                <p class="name">KUSTIA MULYANA, SE</p>
                <p class="nip">NIP. 19780720 200902 2 004</p>
            </div>
            <div class="footer-block">
                <p class="label">Pengguna Anggaran / Kepala Dinas Kominfotik</p>
                <br><br>
                <p class="name">DRS. RONY RUDYANA</p>
                <p class="nip">NIP. 19700414 199101 1 002</p>
            </div>
        </div>
        <p style="text-align:center; margin-top:15px; font-size:7pt; color:#999;">
            Bandung Barat, {{ \Carbon\Carbon::now()->format('d F Y') }} — Dibayar Lunas
        </p>
    </div>
</body>
</html>
