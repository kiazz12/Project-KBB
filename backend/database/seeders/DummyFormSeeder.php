<?php

namespace Database\Seeders;

use App\Enums\FieldType;
use App\Models\Form;
use App\Models\FormField;
use App\Models\FormSubmission;
use App\Models\SubmissionData;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class DummyFormSeeder extends Seeder
{
    protected static array $kecamatan = ['Padalarang', 'Cisarua', 'Ngamprah', 'Cipatat', 'Batujajar', 'Cimareme', 'Cikalongwetan', 'Cipeundeuy', 'Cipongkor', 'Gununghalu'];

    protected static array $pekerjaan = ['Petani', 'Guru', 'PNS', 'Karyawan Swasta', 'Wiraswasta', 'Buruh', 'Nelayan', 'Pedagang', 'Dokter', 'Perawat', 'Polisi', 'TNI', 'Sopir', 'Ibu Rumah Tangga', 'Pensiunan'];

    protected static array $namaDepanLaki = ['Agus', 'Bambang', 'Cecep', 'Dedi', 'Eko', 'Fajar', 'Gunawan', 'Hendra', 'Indra', 'Joko', 'Kusnadi', 'Lukman', 'Maman', 'Nana', 'Oman', 'Purnama', 'Rudi', 'Slamet', 'Tatang', 'Ujang', 'Wawan', 'Yusuf', 'Zainal', 'Asep', 'Dadang'];

    protected static array $namaDepanPerempuan = ['Ai', 'Bella', 'Cici', 'Dewi', 'Euis', 'Fitri', 'Gina', 'Heni', 'Intan', 'Juju', 'Kartika', 'Lilis', 'Maya', 'Neng', 'Ovi', 'Popon', 'Ratna', 'Siti', 'Tina', 'Upit', 'Vivi', 'Winda', 'Yani', 'Zahra', 'Ani'];

    protected static array $namaBelakang = ['Suparman', 'Wijaya', 'Kusuma', 'Pratama', 'Hidayat', 'Nugraha', 'Ramdhan', 'Fauzi', 'Hakim', 'Saputra', 'Maulana', 'Sudrajat', 'Permana', 'Suryana', 'Sofyan', 'Saepuloh', 'Rahayu', 'Handayani', 'Wulandari', 'Lestari', 'Mulyani', 'Susilawati', 'Hasanah', 'Maryati', 'Nurdin'];

    protected static array $alamat = [
        'Kp. Cikembang RT 02/03, Desa', 'Jl. Raya Padalarang No.', 'Kp. Babakan RT 01/04, Desa', 'Perum KBB Indah Blok', 'Kp. Pasirhuni RT 03/02, Desa',
        'Jl. Cipatat KM', 'Kp. Bojong RT 04/01, Desa', 'Gg. Melati No.', 'Kp. Cibodas RT 02/05, Desa', 'Jl. Raya Batujajar No.',
    ];

    protected static array $namaBarang = [
        'Laptop Lenovo ThinkPad', 'AC Daikin 1 PK', 'Meja Kantor Kayu Jati', 'Kursi Kantor Eksklusif', 'Mobil Dinas Toyota Innova',
        'Proyektor Epson', 'Printer Canon', 'Lemari Arsip Besi', 'Server Rack Dell', 'UPS APC 2000VA',
        'Kamera DSLR Canon', 'Televisi LED 50"', 'Sound System Aktif', 'Kipas Angin Dinding', 'Kendaraan Roda 3',
        'Komputer PC Workstation', 'Scanner Fujitsu', 'Mesin Fotocopy', 'Dispenser Air', 'Sofa Ruang Tunggu',
    ];

    protected static array $isiPengaduan = [
        'Jalan di depan kantor kecamatan mengalami kerusakan parah dan berlubang sejak 3 bulan lalu. Mohon segera diperbaiki karena membahayakan pengguna jalan dan menghambat akses warga ke kantor desa.',
        'Lampu penerangan jalan di wilayah RW 03 sudah mati total selama 2 minggu. Warga khawatir dengan tingkat keamanan dan sering terjadi kejadian pencurian di malam hari.',
        'Pelayanan di loket pendaftaran sangat lambat dan petugas kurang ramah. Saya harus menunggu lebih dari 3 jam hanya untuk mengurus surat keterangan. Mohon ada peningkatan kualitas pelayanan.',
        'Saluran air di depan rumah warga tersumbat menyebabkan genangan air setiap kali hujan. Kondisi ini sudah berlangsung sejak musim hujan tahun lalu dan belum ada perbaikan.',
        'Terdapat tiang listrik yang miring dan hampir roboh di pinggir Jl. Raya Cipatat. Sangat membahayakan warga yang melintas, apalagi jika cuaca sedang hujan dan angin kencang.',
        'Pembangunan trotoar di area pasar dilakukan asal-asalan. Material yang digunakan berkualitas rendah dan sudah mulai rusak padahal baru sebulan selesai dibangun.',
        'Sampah di sungai dekat pemukiman warga sudah menumpuk dan menimbulkan bau busuk. Belum ada penanganan dari dinas terkait. Warga resah dengan kondisi lingkungan ini.',
        'Pelayanan pembuatan KTP di kecamatan masih dipungut biaya tidak resmi oleh oknum petugas. Mohon segera ditindaklanjuti karena memberatkan warga yang tidak mampu.',
        'Jembatan penghubung antar desa di Kp. Bojong kondisinya sudah sangat memprihatinkan. Struktur besi mulai keropos dan lantai jembatan berlubang. Warga sangat khawatir saat melintas.',
        'Kegiatan posyandu di desa kami sudah tidak aktif selama 3 bulan terakhir karena ketiadaan tenaga medis. Bayi dan balita tidak mendapatkan imunisasi tepat waktu.',
    ];

    protected static array $saranSurvey = [
        'Pelayanan sudah cukup baik, namun perlu ditingkatkan kecepatan proses administrasinya.',
        'Tingkatkan kebersihan ruang tunggu dan sediakan fasilitas air minum gratis bagi pengunjung.',
        'Petugas pelayanan sudah ramah dan membantu. Mohon dipertahankan dan ditingkatkan.',
        'Sebaiknya ada sistem antrian online agar tidak perlu datang pagi-pagi untuk mengambil nomor.',
        'Ruang pelayanan perlu diperluas karena seringkali penuh dan tidak nyaman.',
        'Kurangnya informasi mengenai prosedur pelayanan. Sebaiknya ada papan informasi yang jelas.',
        'Pelayanan kesehatan sudah baik, apoteknya lengkap, dan obatnya gratis. Sangat membantu masyarakat.',
        'Halaman parkir terlalu sempit dan tidak tertata rapi. Mohon ada penataan ulang.',
        'Pelayanan perizinan sekarang lebih cepat setelah ada sistem online. Terima kasih sudah berbenah.',
        'Toilet umum perlu lebih sering dibersihkan karena kurang terawat dan bau.',
    ];

    public function run(): void
    {
        $users = User::where('role', 'admin')->take(5)->get();
        if ($users->isEmpty()) return;

        $formDefs = [
            [
                'title'       => 'Pendataan Warga',
                'description' => 'Formulir pendataan warga untuk keperluan administrasi kependudukan.',
                'theme'       => 'kependudukan',
                'count'       => 20,
            ],
            [
                'title'       => 'Survey Kepuasan Masyarakat',
                'description' => 'Survey untuk mengukur tingkat kepuasan masyarakat terhadap pelayanan publik.',
                'theme'       => 'survey',
                'count'       => 30,
            ],
            [
                'title'       => 'Pendaftaran Kegiatan',
                'description' => 'Formulir pendaftaran peserta kegiatan dan pelatihan.',
                'theme'       => 'pendaftaran',
                'count'       => 15,
            ],
            [
                'title'       => 'Pengaduan Masyarakat',
                'description' => 'Formulir untuk menyampaikan pengaduan, saran, dan masukan.',
                'theme'       => 'pengaduan',
                'count'       => 12,
            ],
            [
                'title'       => 'Data Inventaris',
                'description' => 'Formulir pencatatan inventaris barang dan aset daerah.',
                'theme'       => 'inventaris',
                'count'       => 10,
            ],
        ];

        $fieldSets = [
            'kependudukan' => [
                ['type' => FieldType::Text,      'label' => 'NIK (Nomor Induk Kependudukan)', 'placeholder' => '16 digit NIK', 'required' => true],
                ['type' => FieldType::Text,      'label' => 'Nama Lengkap',                   'placeholder' => 'Sesuai KTP', 'required' => true],
                ['type' => FieldType::Radio,     'label' => 'Jenis Kelamin',                  'required' => true,  'options' => ['Laki-laki', 'Perempuan']],
                ['type' => FieldType::Select,    'label' => 'Agama',                          'required' => true,  'options' => ['Islam', 'Kristen', 'Katolik', 'Hindu', 'Buddha', 'Konghucu']],
                ['type' => FieldType::Select,    'label' => 'Status Perkawinan',              'required' => true,  'options' => ['Belum Kawin', 'Kawin', 'Cerai Hidup', 'Cerai Mati']],
                ['type' => FieldType::Textarea,  'label' => 'Alamat',                         'placeholder' => 'Alamat lengkap sesuai KTP', 'required' => true],
                ['type' => FieldType::Date,      'label' => 'Tanggal Lahir',                  'required' => true],
                ['type' => FieldType::Number,    'label' => 'Jumlah Anggota Keluarga',        'placeholder' => 'Masukkan angka', 'required' => false],
                ['type' => FieldType::Email,     'label' => 'Email',                          'placeholder' => 'contoh@email.com', 'required' => false],
                ['type' => FieldType::Number,    'label' => 'Nomor WhatsApp',                 'placeholder' => '08xxxxxxxxxx', 'required' => true],
                ['type' => FieldType::Heading,   'label' => 'Data Pekerjaan',                 'help_text' => 'Isi data pekerjaan jika ada', 'required' => false],
                ['type' => FieldType::Text,      'label' => 'Pekerjaan',                      'placeholder' => 'Misal: Petani, Guru, dll', 'required' => false],
                ['type' => FieldType::Signature, 'label' => 'Tanda Tangan',                  'help_text' => 'Tanda tangan elektronik', 'required' => true],
            ],

            'survey' => [
                ['type' => FieldType::Heading,   'label' => 'Profil Responden',              'help_text' => 'Data diri responden', 'required' => false],
                ['type' => FieldType::Text,      'label' => 'Nama',                           'placeholder' => 'Nama lengkap', 'required' => true],
                ['type' => FieldType::Radio,     'label' => 'Jenis Kelamin',                  'required' => true,  'options' => ['Laki-laki', 'Perempuan']],
                ['type' => FieldType::Select,    'label' => 'Rentang Usia',                   'required' => true,  'options' => ['17-25', '26-35', '36-45', '46-55', '56+']],
                ['type' => FieldType::Select,    'label' => 'Pendidikan',                     'required' => true,  'options' => ['SD', 'SMP', 'SMA/SMK', 'D3', 'S1', 'S2/S3']],
                ['type' => FieldType::Radio,     'label' => 'Kepuasan Pelayanan',             'required' => true,  'options' => ['Sangat Puas', 'Puas', 'Cukup', 'Kurang Puas', 'Tidak Puas']],
                ['type' => FieldType::Textarea,  'label' => 'Saran dan Masukan',             'placeholder' => 'Tulis saran Anda', 'required' => false],
                ['type' => FieldType::Number,    'label' => 'Nilai (1-10)',                   'placeholder' => '1-10', 'required' => true, 'min_length' => 1, 'max_length' => 10],
                ['type' => FieldType::Email,     'label' => 'Email (opsional)',              'placeholder' => 'contoh@email.com', 'required' => false],
                ['type' => FieldType::Checkbox,  'label' => 'Layanan yang digunakan',         'required' => true,  'options' => ['Pelayanan Administrasi', 'Pelayanan Kesehatan', 'Pelayanan Pendidikan', 'Pelayanan Perizinan', 'Pelayanan Sosial']],
                ['type' => FieldType::Date,      'label' => 'Tanggal Survey',                 'required' => true],
                ['type' => FieldType::Time,      'label' => 'Jam Survey',                     'placeholder' => 'Pilih jam', 'required' => false],
                ['type' => FieldType::Paragraph, 'label' => 'Terima kasih atas partisipasi Anda. Setiap masukan sangat berarti untuk perbaikan layanan kami.', 'required' => false],
            ],

            'pendaftaran' => [
                ['type' => FieldType::Text,      'label' => 'Nama Lengkap',                   'placeholder' => 'Nama lengkap peserta', 'required' => true],
                ['type' => FieldType::Email,     'label' => 'Email',                          'placeholder' => 'contoh@email.com', 'required' => true],
                ['type' => FieldType::Number,    'label' => 'Nomor Telepon',                  'placeholder' => '08xxxxxxxxxx', 'required' => true],
                ['type' => FieldType::Radio,     'label' => 'Jenis Kelamin',                  'required' => true,  'options' => ['Laki-laki', 'Perempuan']],
                ['type' => FieldType::Select,    'label' => 'Asal Instansi',                  'required' => false, 'options' => ['Pemerintah', 'Swasta', 'BUMN', 'Akademisi', 'Masyarakat Umum', 'Lainnya']],
                ['type' => FieldType::Checkbox,  'label' => 'Bidang Minat',                   'required' => false, 'options' => ['Teknologi Informasi', 'Kesehatan', 'Pendidikan', 'Infrastruktur', 'Sosial Budaya', 'Ekonomi']],
                ['type' => FieldType::Textarea,  'label' => 'Alamat',                         'placeholder' => 'Alamat lengkap', 'required' => false],
                ['type' => FieldType::Date,      'label' => 'Tanggal Lahir',                  'required' => true],
                ['type' => FieldType::Text,      'label' => 'Pekerjaan',                      'placeholder' => 'Pekerjaan saat ini', 'required' => false],
                ['type' => FieldType::Time,      'label' => 'Jam Kehadiran',                  'placeholder' => 'Pilih jam', 'required' => false],
                ['type' => FieldType::Heading,   'label' => 'Dokumen',                        'help_text' => 'Unggah dokumen yang diperlukan', 'required' => false],
                ['type' => FieldType::File,      'label' => 'Upload File',                    'help_text' => 'PDF/JPG maks 2MB', 'required' => false],
                ['type' => FieldType::Signature, 'label' => 'Tanda Tangan',                  'help_text' => 'Tanda tangan digital', 'required' => true],
            ],

            'pengaduan' => [
                ['type' => FieldType::Heading,   'label' => 'Data Pelapor',                   'help_text' => 'Data diri pelapor akan dirahasiakan', 'required' => false],
                ['type' => FieldType::Text,      'label' => 'Nama Pelapor',                   'placeholder' => 'Boleh inisial jika anonim', 'required' => true],
                ['type' => FieldType::Email,     'label' => 'Email',                          'placeholder' => 'contoh@email.com', 'required' => true],
                ['type' => FieldType::Number,    'label' => 'Nomor Telepon',                  'placeholder' => '08xxxxxxxxxx', 'required' => false],
                ['type' => FieldType::Select,    'label' => 'Kategori Pengaduan',             'required' => true,  'options' => ['Infrastruktur', 'Pelayanan', 'Lingkungan', 'Sosial', 'Hukum', 'Lainnya']],
                ['type' => FieldType::Textarea,  'label' => 'Isi Pengaduan',                  'placeholder' => 'Tulis kronologi kejadian secara jelas dan rinci', 'required' => true],
                ['type' => FieldType::Radio,     'label' => 'Sudah pernah lapor sebelumnya?', 'required' => true,  'options' => ['Ya', 'Tidak']],
                ['type' => FieldType::Date,      'label' => 'Tanggal Kejadian',               'required' => true],
                ['type' => FieldType::Time,      'label' => 'Jam Kejadian',                   'placeholder' => 'Pilih jam', 'required' => false],
                ['type' => FieldType::File,      'label' => 'Upload Bukti Pendukung',         'help_text' => 'Foto/dokumen pendukung (PDF/JPG maks 2MB)', 'required' => false],
                ['type' => FieldType::Checkbox,  'label' => 'Saya setuju data diproses',      'required' => true,  'options' => ['Setuju']],
                ['type' => FieldType::Signature, 'label' => 'Tanda Tangan',                  'help_text' => 'Tanda tangan elektronik', 'required' => true],
                ['type' => FieldType::Paragraph, 'label' => 'Pengaduan akan ditindaklanjuti maksimal 5 hari kerja.', 'required' => false],
            ],

            'inventaris' => [
                ['type' => FieldType::Text,      'label' => 'Nama Barang',                    'placeholder' => 'Nama barang/inventaris', 'required' => true],
                ['type' => FieldType::Select,    'label' => 'Kategori Barang',                'required' => true,  'options' => ['Elektronik', 'Furnitur', 'Kendaraan', 'Alat Kantor', 'Bangunan', 'Lainnya']],
                ['type' => FieldType::Number,    'label' => 'Jumlah Barang',                  'placeholder' => 'Masukkan jumlah', 'required' => true, 'min_length' => 1],
                ['type' => FieldType::Select,    'label' => 'Kondisi Barang',                 'required' => true,  'options' => ['Baik', 'Rusak Ringan', 'Rusak Berat', 'Hilang']],
                ['type' => FieldType::Textarea,  'label' => 'Deskripsi',                      'placeholder' => 'Deskripsi barang', 'required' => false],
                ['type' => FieldType::Date,      'label' => 'Tanggal Pengadaan',              'required' => true],
                ['type' => FieldType::Radio,     'label' => 'Sumber Dana',                    'required' => true,  'options' => ['APBD', 'APBN', 'Bantuan', 'Swadaya']],
                ['type' => FieldType::Number,    'label' => 'Nilai Perolehan (Rp)',           'placeholder' => 'Dalam Rupiah', 'required' => true],
                ['type' => FieldType::Text,      'label' => 'Lokasi Barang',                  'placeholder' => 'Ruangan/gedung', 'required' => true],
                ['type' => FieldType::Email,     'label' => 'Email PIC',                      'placeholder' => 'Email penanggung jawab', 'required' => false],
                ['type' => FieldType::Checkbox,  'label' => 'Kelengkapan Dokumen',            'required' => true,  'options' => ['Nota Pembelian', 'Sertifikat', 'Buku Manual', 'Garansi']],
                ['type' => FieldType::Heading,   'label' => 'Dokumen Pendukung',              'help_text' => 'Upload foto/dokumen barang', 'required' => false],
                ['type' => FieldType::File,      'label' => 'Upload Gambar/Dokumen',          'help_text' => 'Foto barang atau dokumen (PDF/JPG maks 2MB)', 'required' => false],
            ],
        ];

        foreach ($formDefs as $i => $def) {
            $user = $users[$i];
            $slug = 'dummy-' . $def['theme'] . '-' . Str::random(4);

            $form = Form::create([
                'uuid'        => Str::uuid(),
                'user_id'     => $user->id,
                'title'       => $def['title'],
                'description' => $def['description'],
                'slug'        => $slug,
                'status'      => 'published',
                'settings'    => [
                    'collect_ip'       => true,
                    'show_kbb_logo'    => true,
                ],
                'confirmation_message' => 'Terima kasih, data Anda berhasil dikirim.',
                'confirmation_type'    => 'message',
                'limit_one_response'   => false,
            ]);

            $fields = $fieldSets[$def['theme']];

            foreach ($fields as $j => $field) {
                FormField::create(array_merge($field, [
                    'form_id' => $form->id,
                    'order'   => $j + 1,
                ]));
            }

            $formFields = $form->fields()->get();

            $submissionCount = $def['count'];
            $this->command->info("Form created: {$form->title} (owner: {$user->name}, slug: {$form->slug}, {$formFields->count()} fields) — generating {$submissionCount} submissions...");

            for ($s = 0; $s < $submissionCount; $s++) {
                $submission = FormSubmission::create([
                    'uuid'         => Str::uuid(),
                    'form_id'      => $form->id,
                    'user_id'      => null,
                    'ip_address'   => $this->randomIP(),
                    'user_agent'   => $this->randomUserAgent(),
                    'submitted_at' => $this->randomDate($s),
                ]);

                foreach ($formFields as $field) {
                    $value = $this->generateFieldValue($field, $s, $submissionCount);
                    if ($value !== null) {
                        SubmissionData::create([
                            'submission_id' => $submission->id,
                            'form_field_id' => $field->id,
                            'value'         => $value,
                        ]);
                    }
                }
            }
        }
    }

    protected function randomIP(): string
    {
        $prefixes = ['192.168', '10.0', '172.16', '100.64', '203.190'];
        $pref = $prefixes[array_rand($prefixes)];
        return "{$pref}." . mt_rand(1, 254) . '.' . mt_rand(1, 254);
    }

    protected function randomUserAgent(): string
    {
        $agents = [
            'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 Chrome/120.0.0.0 Safari/537.36',
            'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 Chrome/119.0.0.0 Safari/537.36',
            'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 Chrome/121.0.0.0 Safari/537.36',
            'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:120.0) Gecko/20100101 Firefox/120.0',
            'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:121.0) Gecko/20100101 Firefox/121.0',
            'Mozilla/5.0 (Macintosh; Intel Mac OS X 14_2) AppleWebKit/605.1.15 Safari/604.1',
            'Mozilla/5.0 (Macintosh; Intel Mac OS X 14_1) AppleWebKit/537.36 Chrome/120.0.0.0 Safari/537.36',
            'Mozilla/5.0 (Linux; Android 14; Pixel 8) AppleWebKit/537.36 Chrome/120.0.6099 Mobile Safari/537.36',
            'Mozilla/5.0 (Linux; Android 13; Samsung Galaxy S23) AppleWebKit/537.36 Chrome/119.0.0.0 Mobile Safari/537.36',
            'Mozilla/5.0 (iPhone; CPU iPhone OS 17_2 like Mac OS X) AppleWebKit/605.1.15 Mobile/15E148',
        ];
        return $agents[array_rand($agents)];
    }

    protected function randomDate(int $index): string
    {
        $daysAgo = mt_rand(1, 90);
        $hour = mt_rand(7, 21);
        $minute = mt_rand(0, 59);
        $second = mt_rand(0, 59);
        return now()->subDays($daysAgo)->setTime($hour, $minute, $second)->format('Y-m-d H:i:s');
    }

    protected function randomOf(array $arr)
    {
        return $arr[array_rand($arr)];
    }

    protected function weightedRandom(array $items, array $weights, int $index = 0)
    {
        $total = array_sum($weights);
        $rand = mt_rand(1, $total);
        $cumulative = 0;
        foreach ($items as $i => $item) {
            $cumulative += $weights[$i % count($weights)];
            if ($rand <= $cumulative) return $item;
        }
        return $items[0];
    }

    protected function generateFieldValue(FormField $field, int $subIndex, int $totalSubs): ?string
    {
        $type = $field->type->value;

        if ($type === 'heading' || $type === 'paragraph') {
            return '';
        }
        if ($type === 'file' || $type === 'signature') {
            return mt_rand(0, 3) === 0 ? '' : null;
        }

        $label = $field->label;

        switch ($type) {
            case 'text':
                return $this->generateTextValue($label, $subIndex);

            case 'textarea':
                return $this->generateTextareaValue($label, $subIndex, $totalSubs);

            case 'number':
                return $this->generateNumberValue($label, $field);

            case 'email':
                return $this->generateEmailValue($label, $subIndex);

            case 'date':
                return $this->generateDateValue($label, $subIndex);

            case 'time':
                return $this->generateTimeValue();

            case 'select':
            case 'radio':
                $options = $field->options ?? [];
                return empty($options) ? '' : $this->randomOf($options);

            case 'checkbox':
                $options = $field->options ?? [];
                if (empty($options)) return '';
                $count = mt_rand(1, min(count($options), 3));
                $keys = array_rand($options, $count);
                $keys = is_array($keys) ? $keys : [$keys];
                $selected = array_map(fn($k) => $options[$k], $keys);
                return implode(', ', $selected);
        }

        return '';
    }

    protected function generateTextValue(string $label, int $subIndex): string
    {
        if (str_contains($label, 'NIK')) {
            $nik = mt_rand(320101, 320499) . mt_rand(100101, 311299) . mt_rand(0001, 9999) . mt_rand(0001, 9999);
            return (string) $nik;
        }
        if (str_contains($label, 'Nama')) {
            $gender = str_contains($label, 'Lengkap') ? null : null;
            $depan = $this->randomOf(array_merge(static::$namaDepanLaki, static::$namaDepanPerempuan));
            $belakang = $this->randomOf(static::$namaBelakang);
            return $depan . ' ' . $belakang;
        }
        if (str_contains($label, 'WhatsApp') || str_contains($label, 'Telepon')) {
            return '08' . mt_rand(100000000, 999999999);
        }
        if (str_contains($label, 'Pekerjaan')) {
            return $this->randomOf(static::$pekerjaan);
        }
        if (str_contains($label, 'Lokasi')) {
            return $this->randomOf(static::$kecamatan) . ' - ' . $this->randomOf(['Kantor Bupati', 'Gedung D', 'Lantai 2 Ruang ' . mt_rand(201, 215), 'Gudang Utama', 'Ruang Server']);
        }
        return 'Data ' . $label . ' #' . ($subIndex + 1);
    }

    protected function generateTextareaValue(string $label, int $subIndex, int $totalSubs): string
    {
        if (str_contains($label, 'Alamat')) {
            $noRumah = mt_rand(1, 200);
            $kec = $this->randomOf(static::$kecamatan);
            $desa = 'Desa ' . $this->randomOf(['Mekarwangi', 'Sukamaju', 'Cijeruk', 'Cimerang', 'Cibodas']);
            $alamat = $this->randomOf(static::$alamat);
            return "{$alamat} {$noRumah}, RT " . mt_rand(1, 9) . '/RW ' . mt_rand(1, 9) . ", {$desa}, Kec. {$kec}, Kab. Bandung Barat";
        }
        if (str_contains($label, 'Pengaduan') || str_contains($label, 'Isi')) {
            return static::$isiPengaduan[$subIndex % count(static::$isiPengaduan)];
        }
        if (str_contains($label, 'Saran') || str_contains($label, 'Masukan')) {
            return static::$saranSurvey[$subIndex % count(static::$saranSurvey)];
        }
        if (str_contains($label, 'Deskripsi')) {
            $kondisi = $this->randomOf(['Baru', 'Bekas', 'Rekondisi', 'Second']);
            return "Barang dalam kondisi {$kondisi}. " . $this->randomOf([
                'Dilengkapi dengan kelengkapan standar pabrik.',
                'Sudah termasuk biaya pengiriman dan instalasi.',
                'Masih dalam masa garansi.',
                'Telah dilakukan pengecekan dan berfungsi dengan baik.',
            ]);
        }
        return 'Isian ' . $label . ' untuk entri ke-' . ($subIndex + 1);
    }

    protected function generateNumberValue(string $label, FormField $field): string
    {
        if (str_contains($label, 'Anggota Keluarga')) {
            return (string) mt_rand(1, 8);
        }
        if (str_contains($label, 'Nilai') && str_contains($label, '1-10')) {
            $weights = [1, 2, 3, 10, 15, 14, 12, 8, 5, 2]; // skew toward middle
            return (string) $this->weightedRandom([1, 2, 3, 4, 5, 6, 7, 8, 9, 10], $weights);
        }
        if (str_contains($label, 'Jumlah') || str_contains($label, 'Qty')) {
            return (string) mt_rand(1, 25);
        }
        if (str_contains($label, 'Nilai Perolehan') || str_contains($label, 'Rupiah')) {
            $amounts = [1500000, 2500000, 3500000, 5000000, 7500000, 10000000, 15000000, 25000000, 50000000, 100000000, 250000000, 500000000];
            return (string) $this->randomOf($amounts);
        }
        if (str_contains($label, 'WhatsApp') || str_contains($label, 'Telepon')) {
            return '08' . mt_rand(100000000, 999999999);
        }
        return (string) mt_rand(1, 999);
    }

    protected function generateEmailValue(string $label, int $subIndex): string
    {
        $domains = ['gmail.com', 'yahoo.com', 'outlook.com', 'proton.me', 'dinas.kbb.go.id', 'smk-kbb.sch.id'];
        $name = strtolower(str_replace(' ', '', $this->randomOf(static::$namaDepanLaki))) . $subIndex;
        return $name . '@' . $this->randomOf($domains);
    }

    protected function generateDateValue(string $label, int $subIndex): string
    {
        if (str_contains($label, 'Lahir')) {
            $year = mt_rand(1960, 2005);
            $month = mt_rand(1, 12);
            $day = mt_rand(1, 28);
            return sprintf('%04d-%02d-%02d', $year, $month, $day);
        }
        if (str_contains($label, 'Kejadian')) {
            $daysAgo = mt_rand(1, 60);
            return now()->subDays($daysAgo)->format('Y-m-d');
        }
        if (str_contains($label, 'Pengadaan')) {
            $yearsAgo = mt_rand(0, 5);
            return now()->subYears($yearsAgo)->subDays(mt_rand(1, 365))->format('Y-m-d');
        }
        if (str_contains($label, 'Survey')) {
            $daysAgo = mt_rand(0, 7);
            return now()->subDays($daysAgo)->format('Y-m-d');
        }
        $daysAgo = mt_rand(0, 30);
        return now()->subDays($daysAgo)->format('Y-m-d');
    }

    protected function generateTimeValue(): string
    {
        return sprintf('%02d:%02d', mt_rand(7, 21), mt_rand(0, 11) * 5);
    }
}
