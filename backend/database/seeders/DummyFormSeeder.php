<?php

namespace Database\Seeders;

use App\Enums\FieldType;
use App\Models\Form;
use App\Models\FormField;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class DummyFormSeeder extends Seeder
{
    public function run(): void
    {
        $users = User::where('role', 'admin')->take(5)->get();
        if ($users->isEmpty()) return;

        $formDefs = [
            [
                'title'       => 'Pendataan Warga',
                'description' => 'Formulir pendataan warga untuk keperluan administrasi kependudukan.',
                'theme'       => 'kependudukan',
            ],
            [
                'title'       => 'Survey Kepuasan Masyarakat',
                'description' => 'Survey untuk mengukur tingkat kepuasan masyarakat terhadap pelayanan publik.',
                'theme'       => 'survey',
            ],
            [
                'title'       => 'Pendaftaran Kegiatan',
                'description' => 'Formulir pendaftaran peserta kegiatan dan pelatihan.',
                'theme'       => 'pendaftaran',
            ],
            [
                'title'       => 'Pengaduan Masyarakat',
                'description' => 'Formulir untuk menyampaikan pengaduan, saran, dan masukan.',
                'theme'       => 'pengaduan',
            ],
            [
                'title'       => 'Data Inventaris',
                'description' => 'Formulir pencatatan inventaris barang dan aset daerah.',
                'theme'       => 'inventaris',
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

            $this->command->info("Form created: {$form->title} (owner: {$user->name}, slug: {$form->slug}, {$form->fields()->count()} fields)");
        }
    }
}
