<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Daftar Hadir Presensi</title>
    <style>
        @page { margin: 12mm 10mm; size: landscape; }
        * { box-sizing: border-box; }
        body { font-family: sans-serif; font-size: 8pt; color: #1a1a2e; line-height: 1.2; margin: 0; padding: 0; }

        .header { width: 100%; margin-bottom: 4px; }
        .header-top { display: flex; align-items: flex-start; width: 100%; border-bottom: 3px double #003778; padding-bottom: 6px; }
        .header-logo { flex: 0 0 auto; margin-right: 14px; }
        .header-logo img { height: 48px; }
        .header-text { flex: 1; }
        .header-text .gov { font-size: 11pt; font-weight: 800; color: #003778; margin: 0 0 1px 0; letter-spacing: 0.5px; }
        .header-text .dept { font-size: 9pt; font-weight: 700; color: #333; margin: 0 0 2px 0; }
        .header-text .unit { font-size: 8pt; font-weight: 600; color: #003778; margin: 0 0 2px 0; }
        .header-text .addr { font-size: 6.5pt; color: #666; margin: 0; line-height: 1.3; }

        .title-section { text-align: center; margin: 8px 0 4px 0; padding: 6px 0; background: linear-gradient(135deg, #003778 0%, #0050a0 100%); border-radius: 4px; }
        .title-section h1 { font-size: 12pt; color: #fff; margin: 0 0 2px 0; text-transform: uppercase; letter-spacing: 1px; font-weight: 800; }
        .title-section .event { font-size: 8pt; color: #d4e4f7; margin: 0; font-weight: 500; }
        .title-section .detail { font-size: 7pt; color: #b0c8e8; margin: 1px 0 0 0; }

        .info-bar { display: flex; justify-content: space-between; align-items: center; margin-bottom: 4px; padding: 3px 8px; background: #f0f4f8; border: 1px solid #d0dbe8; border-radius: 3px; font-size: 7pt; color: #555; }
        .info-bar strong { color: #003778; }

        table { width: 100%; border-collapse: collapse; }
        th { background: #003778; color: #fff; padding: 4px 3px; text-align: center; font-weight: 600; border: 1px solid #002a5c; font-size: 7pt; white-space: nowrap; }
        td { padding: 3px 3px; border: 1px solid #d0d5dd; color: #333; text-align: center; vertical-align: middle; font-size: 7.5pt; word-wrap: break-word; overflow-wrap: break-word; }
        td.l { text-align: left; }
        tr:nth-child(even) td { background: #f7f9fb; }
        .empty-cell { color: transparent; }

        .footer { margin-top: 8px; page-break-inside: avoid; }
        .footer-line { width: 100%; border-top: 1px solid #ccc; margin-bottom: 4px; }
        .footer-row { display: flex; justify-content: space-between; }
        .footer-block { width: 30%; text-align: center; }
        .footer-block .label { font-size: 7pt; font-weight: 600; color: #003778; margin-bottom: 2px; }
        .footer-block .nip { font-size: 6.5pt; color: #888; margin-top: 1px; }
        .footer-block .name { font-weight: bold; font-size: 7.5pt; }
        .footer-block .space { height: 28px; }
        .footer-date { text-align: center; font-size: 6.5pt; color: #999; margin-top: 4px; }
    </style>
</head>
<body>
    <div class="header">
        <div class="header-top">
            <div class="header-logo">
                <img src="{{ public_path('images/kbb-logo.png') }}" alt="KBB Logo">
            </div>
            <div class="header-text">
                <p class="gov">PEMERINTAH KABUPATEN BANDUNG BARAT</p>
                <p class="dept">DINAS KOMUNIKASI, INFORMATIKA, PERSANDIAN DAN STATISTIK</p>
                <p class="unit">Bidang Sosialisasi dan Penyuluhan</p>
                <p class="addr">Gedung B Lt. 2 Komplek Perkantoran Pemerintah Kabupaten Bandung Barat</p>
                <p class="addr">Jl. Raya Padalarang &ndash; Cisarua Km 2 Ngamprah &bull; Email: kominfo@bandungbaratkab.go.id &bull; Kode Pos 40552</p>
            </div>
        </div>
    </div>

    <div class="title-section">
        <h1>Daftar Hadir Presensi</h1>
        <p class="event">Transfer Knowledge Teknologi Terbaru (Smart Governance &amp; Smart Society) Batch 2 Tahun 2026</p>
        <p class="detail">Pelaksanaan: 14 Juli 2026 &bull; Zoom: https://s.komdigi.go.id/transferknowledgebatch2</p>
    </div>

    <div class="info-bar">
        <span><strong>Total Peserta:</strong> {{ count($submissions) }} orang</span>
        <span><strong>Diekspor:</strong> {{ now()->format('d M Y H:i') }} WIB</span>
    </div>

    <table>
        <thead>
            <tr>
                <th>No</th>
                <th>Nama Lengkap</th>
                <th>Role</th>
                <th>OPD / Institusi</th>
                <th>Jabatan</th>
                <th>NIP/NIK</th>
                <th>Tanggal</th>
                <th>Tanda Tangan</th>
            </tr>
        </thead>
        <tbody>
            @php $no = ($offset ?? 0) + 1; @endphp
            @foreach($submissions as $submission)
                @php
                    $data = [];
                    foreach ($submission->data as $d) {
                        if ($d->formField) {
                            $data[strtolower($d->formField->label)] = $d->value;
                        }
                    }
                @endphp
                <tr>
                    <td>{{ $no++ }}</td>
                    <td class="l">{{ $data['nama lengkap'] ?? '' }}</td>
                    <td>{{ $data['role'] ?? '' }}</td>
                    <td class="l">{{ $data['opd / institusi'] ?? '' }}</td>
                    <td class="l">{{ $data['jabatan'] ?? '' }}</td>
                    <td>{{ $data['no. induk pegawai (nip)'] ?? '' }}</td>
                    <td>{{ $data['tanggal kehadiran'] ? \Carbon\Carbon::parse($data['tanggal kehadiran'])->format('d/m/Y') : '' }}</td>
                    <td class="empty-cell">.</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    @if (!empty($showFooter))
    <div class="footer">
        <div class="footer-line"></div>
        <div class="footer-row">
            <div class="footer-block">
                <p class="label">Pejabat Pelaksana Teknis Kegiatan</p>
                <div class="space"></div>
                <p class="name">TIMMY SAMPURNA IRAWAN, ST., M.Si</p>
                <p class="nip">NIP. 19860429 201101 1 004</p>
            </div>
            <div class="footer-block">
                <p class="label">Bendahara Pengeluaran</p>
                <div class="space"></div>
                <p class="name">KUSTIA MULYANA, SE</p>
                <p class="nip">NIP. 19780720 200902 2 004</p>
            </div>
            <div class="footer-block">
                <p class="label">Pengguna Anggaran / Kadis Kominfotik</p>
                <div class="space"></div>
                <p class="name">DRS. RONY RUDYANA</p>
                <p class="nip">NIP. 19700414 199101 1 002</p>
            </div>
        </div>
        <p class="footer-date">Bandung Barat, {{ now()->format('d F Y') }} &mdash; Dibayar Lunas</p>
    </div>
    @endif
</body>
</html>
