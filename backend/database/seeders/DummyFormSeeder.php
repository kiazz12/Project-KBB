<?php

namespace Database\Seeders;

use App\Enums\FieldType;
use App\Models\Form;
use App\Models\FormField;
use App\Models\FormSubmission;
use App\Models\SubmissionData;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class DummyFormSeeder extends Seeder
{
    protected static array $kecamatan = ['Padalarang', 'Cisarua', 'Ngamprah', 'Cipatat', 'Batujajar', 'Cimareme', 'Cikalongwetan', 'Cipeundeuy', 'Cipongkor', 'Gununghalu'];

    protected static array $pekerjaan = ['Petani', 'Guru', 'PNS', 'Karyawan Swasta', 'Wiraswasta', 'Buruh', 'Nelayan', 'Pedagang', 'Dokter', 'Perawat', 'Polisi', 'TNI', 'Sopir', 'Ibu Rumah Tangga', 'Pensiunan'];

    protected static array $namaDepanLaki = ['Agus', 'Bambang', 'Cecep', 'Dedi', 'Eko', 'Fajar', 'Gunawan', 'Hendra', 'Indra', 'Joko', 'Kusnadi', 'Lukman', 'Maman', 'Nana', 'Oman', 'Purnama', 'Rudi', 'Slamet', 'Tatang', 'Ujang', 'Wawan', 'Yusuf', 'Zainal', 'Asep', 'Dadang'];

    protected static array $namaDepanPerempuan = ['Ai', 'Bella', 'Cici', 'Dewi', 'Euis', 'Fitri', 'Gina', 'Heni', 'Intan', 'Juju', 'Kartika', 'Lilis', 'Maya', 'Neng', 'Ovi', 'Popon', 'Ratna', 'Siti', 'Tina', 'Upit', 'Vivi', 'Winda', 'Yani', 'Zahra', 'Ani'];

    protected static array $namaBelakang = ['Suparman', 'Wijaya', 'Kusuma', 'Pratama', 'Hidayat', 'Nugraha', 'Ramdhan', 'Fauzi', 'Hakim', 'Saputra', 'Maulana', 'Sudrajat', 'Permana', 'Suryana', 'Sofyan', 'Saepuloh', 'Rahayu', 'Handayani', 'Wulandari', 'Lestari', 'Mulyani', 'Susilawati', 'Hasanah', 'Maryati', 'Nurdin'];

    protected static array $alamat = [
        'Kp. Cikembang RT 02/03, Desa', 'Jl. Raya Padalarang No.', 'Kp. Babakan RT 01/04, Desa', 'Perum KBB Indah Blok',
        'Kp. Pasirhuni RT 03/02, Desa', 'Jl. Cipatat KM', 'Kp. Bojong RT 04/01, Desa', 'Gg. Melati No.',
        'Kp. Cibodas RT 02/05, Desa', 'Jl. Raya Batujajar No.',
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

    protected array $formDefs = [];
    protected array $fieldSets = [];

    public function __construct()
    {
        $this->formDefs = [
            'kependudukan' => ['title' => 'Pendataan Warga', 'description' => 'Formulir pendataan warga untuk keperluan administrasi kependudukan.', 'count' => 10],
            'survey'       => ['title' => 'Survey Kepuasan Masyarakat', 'description' => 'Survey untuk mengukur tingkat kepuasan masyarakat terhadap pelayanan publik.', 'count' => 12],
            'pendaftaran'  => ['title' => 'Pendaftaran Kegiatan', 'description' => 'Formulir pendaftaran peserta kegiatan dan pelatihan.', 'count' => 8],
            'pengaduan'    => ['title' => 'Pengaduan Masyarakat', 'description' => 'Formulir untuk menyampaikan pengaduan, saran, dan masukan.', 'count' => 6],
            'inventaris'   => ['title' => 'Data Inventaris', 'description' => 'Formulir pencatatan inventaris barang dan aset daerah.', 'count' => 6],
            'kegiatan'     => ['title' => 'Laporan Kegiatan Harian', 'description' => 'Laporan pelaksanaan kegiatan harian di lingkungan kecamatan.', 'count' => 8],
            'tamu'         => ['title' => 'Buku Tamu', 'description' => 'Formulir pencatatan tamu yang berkunjung ke kantor.', 'count' => 15],
            'izin'         => ['title' => 'Permohonan Izin', 'description' => 'Formulir permohonan izin kegiatan dan penggunaan fasilitas.', 'count' => 7],
            'bantuan'      => ['title' => 'Data Penerima Bantuan', 'description' => 'Pendataan calon penerima bantuan sosial.', 'count' => 10],
            'aset'         => ['title' => 'Peminjaman Aset', 'description' => 'Formulir peminjaman aset dan perlengkapan kantor.', 'count' => 5],
        ];

        $this->fieldSets = [
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

            'kegiatan' => [
                ['type' => FieldType::Text,      'label' => 'Nama Kegiatan',                  'placeholder' => 'Nama kegiatan yang dilaksanakan', 'required' => true],
                ['type' => FieldType::Select,    'label' => 'Jenis Kegiatan',                 'required' => true,  'options' => ['Rapat', 'Sosialisasi', 'Pelatihan', 'Monitoring', 'Evaluasi', 'Lainnya']],
                ['type' => FieldType::Date,      'label' => 'Tanggal Pelaksanaan',            'required' => true],
                ['type' => FieldType::Time,      'label' => 'Jam Mulai',                      'placeholder' => 'Pilih jam', 'required' => true],
                ['type' => FieldType::Time,      'label' => 'Jam Selesai',                    'placeholder' => 'Pilih jam', 'required' => true],
                ['type' => FieldType::Textarea,  'label' => 'Deskripsi Kegiatan',             'placeholder' => 'Uraian kegiatan yang dilaksanakan', 'required' => true],
                ['type' => FieldType::Number,    'label' => 'Jumlah Peserta',                 'placeholder' => 'Masukkan jumlah', 'required' => false],
                ['type' => FieldType::Text,      'label' => 'Lokasi',                         'placeholder' => 'Tempat pelaksanaan', 'required' => true],
                ['type' => FieldType::Radio,     'label' => 'Kesimpulan',                     'required' => true,  'options' => ['Tercapai', 'Tidak Tercapai']],
                ['type' => FieldType::Textarea,  'label' => 'Tindak Lanjut',                  'placeholder' => 'Rencana tindak lanjut', 'required' => false],
            ],

            'tamu' => [
                ['type' => FieldType::Text,      'label' => 'Nama Tamu',                      'placeholder' => 'Nama lengkap', 'required' => true],
                ['type' => FieldType::Text,      'label' => 'Instansi/Perusahaan',            'placeholder' => 'Asal instansi', 'required' => true],
                ['type' => FieldType::Select,    'label' => 'Tujuan Bertemu',                 'required' => true,  'options' => ['Kepala Dinas', 'Sekretaris', 'Kabid', 'Kasubag', 'Staff', 'Lainnya']],
                ['type' => FieldType::Textarea,  'label' => 'Keperluan',                      'placeholder' => 'Tujuan kunjungan', 'required' => true],
                ['type' => FieldType::Number,    'label' => 'Nomor Telepon',                  'placeholder' => '08xxxxxxxxxx', 'required' => true],
                ['type' => FieldType::Email,     'label' => 'Email',                          'placeholder' => 'contoh@email.com', 'required' => false],
                ['type' => FieldType::Date,      'label' => 'Tanggal Kunjungan',              'required' => true],
                ['type' => FieldType::Time,      'label' => 'Jam Datang',                     'placeholder' => 'Pilih jam', 'required' => true],
                ['type' => FieldType::Time,      'label' => 'Jam Pulang',                     'placeholder' => 'Pilih jam', 'required' => false],
                ['type' => FieldType::File,      'label' => 'Upload KTP/Foto',                'help_text' => 'Upload foto/scan KTP', 'required' => false],
            ],

            'izin' => [
                ['type' => FieldType::Text,      'label' => 'Nama Pemohon',                   'placeholder' => 'Nama lengkap', 'required' => true],
                ['type' => FieldType::Text,      'label' => 'NIK',                            'placeholder' => '16 digit NIK', 'required' => true],
                ['type' => FieldType::Select,    'label' => 'Jenis Izin',                     'required' => true,  'options' => ['Izin Keramaian', 'Izin Mendirikan Bangunan', 'Izin Usaha', 'Izin Parkir', 'Izin Kegiatan', 'Lainnya']],
                ['type' => FieldType::Textarea,  'label' => 'Alamat',                         'placeholder' => 'Alamat lengkap', 'required' => true],
                ['type' => FieldType::Textarea,  'label' => 'Maksud dan Tujuan',             'placeholder' => 'Tujuan pengajuan izin', 'required' => true],
                ['type' => FieldType::Date,      'label' => 'Tanggal Mulai',                  'required' => true],
                ['type' => FieldType::Date,      'label' => 'Tanggal Selesai',                'required' => true],
                ['type' => FieldType::Number,    'label' => 'Estimasi Peserta',               'placeholder' => 'Perkiraan jumlah peserta', 'required' => false],
                ['type' => FieldType::Checkbox,  'label' => 'Persyaratan',                    'required' => true,  'options' => ['KTP', 'Surat Pengantar', 'Surat Keterangan Domisili', 'Pas Foto']],
                ['type' => FieldType::Signature, 'label' => 'Tanda Tangan',                  'help_text' => 'Tanda tangan elektronik', 'required' => true],
            ],

            'bantuan' => [
                ['type' => FieldType::Text,      'label' => 'Nama Lengkap',                   'placeholder' => 'Nama calon penerima', 'required' => true],
                ['type' => FieldType::Text,      'label' => 'NIK',                            'placeholder' => '16 digit NIK', 'required' => true],
                ['type' => FieldType::Textarea,  'label' => 'Alamat',                         'placeholder' => 'Alamat lengkap', 'required' => true],
                ['type' => FieldType::Number,    'label' => 'Jumlah Tanggungan',              'placeholder' => 'Jumlah anggota keluarga', 'required' => true],
                ['type' => FieldType::Select,    'label' => 'Pekerjaan',                      'required' => true,  'options' => ['Tidak Bekerja', 'Buruh Harian', 'Petani', 'Pedagang Kecil', 'Pensiunan', 'Lainnya']],
                ['type' => FieldType::Radio,     'label' => 'Status Rumah',                   'required' => true,  'options' => ['Milik Sendiri', 'Kontrak', 'Numpang', 'Tidak Layak Huni']],
                ['type' => FieldType::Number,    'label' => 'Penghasilan per Bulan (Rp)',     'placeholder' => 'Dalam Rupiah', 'required' => true],
                ['type' => FieldType::Select,    'label' => 'Jenis Bantuan',                  'required' => true,  'options' => ['Bantuan Sembako', 'Bantuan Tunai', 'Bantuan Kesehatan', 'Bantuan Pendidikan', 'Bantuan Rumah']],
                ['type' => FieldType::Checkbox,  'label' => 'Kriteria Keluarga',              'required' => true,  'options' => ['Lansia', 'Disabilitas', 'Ibu Hamil', 'Balita', 'Sakit Kronis']],
                ['type' => FieldType::Date,      'label' => 'Tanggal Pendataan',              'required' => true],
            ],

            'aset' => [
                ['type' => FieldType::Select,    'label' => 'Jenis Aset',                     'required' => true,  'options' => ['Kendaraan', 'Elektronik', 'Furnitur', 'Alat Berat', 'Perlengkapan']],
                ['type' => FieldType::Text,      'label' => 'Nama Aset',                      'placeholder' => 'Nama barang/aset', 'required' => true],
                ['type' => FieldType::Text,      'label' => 'Kode Aset',                      'placeholder' => 'Kode inventaris', 'required' => false],
                ['type' => FieldType::Text,      'label' => 'Nama Peminjam',                  'placeholder' => 'Nama lengkap', 'required' => true],
                ['type' => FieldType::Text,      'label' => 'Bidang/Unit',                    'placeholder' => 'Unit kerja', 'required' => true],
                ['type' => FieldType::Date,      'label' => 'Tanggal Pinjam',                 'required' => true],
                ['type' => FieldType::Date,      'label' => 'Rencana Kembali',                'required' => true],
                ['type' => FieldType::Textarea,  'label' => 'Keperluan',                      'placeholder' => 'Tujuan peminjaman', 'required' => true],
                ['type' => FieldType::Radio,     'label' => 'Status',                         'required' => true,  'options' => ['Dipinjam', 'Dikembalikan']],
                ['type' => FieldType::Signature, 'label' => 'Tanda Tangan',                  'help_text' => 'Tanda tangan peminjam', 'required' => true],
            ],
        ];
    }

    public function run(): void
    {
        $users = User::all();
        if ($users->isEmpty()) return;

        $this->command->info('— — — — — — — — — — — — — — — — — — — —');
        $this->command->info('  DummyFormSeeder: Seeding ' . $users->count() . ' users');
        $this->command->info('— — — — — — — — — — — — — — — — — — — —');

        $themeKeys = array_keys($this->formDefs);
        $totalForms = 0;
        $totalSubmissions = 0;
        $allSubmissionData = [];

        DB::transaction(function () use ($users, $themeKeys, &$totalForms, &$totalSubmissions, &$allSubmissionData) {
            $now = now();

            foreach ($users as $user) {
                $numForms = mt_rand(2, 3);
                $assignedThemes = [];
                $available = $themeKeys;
                shuffle($available);

                for ($f = 0; $f < $numForms; $f++) {
                    $theme = $available[$f % count($available)];
                    $assignedThemes[] = $theme;
                }

                $this->command->info("  [{$user->name}] creating {$numForms} form(s)...");

                foreach ($assignedThemes as $theme) {
                    $def = $this->formDefs[$theme];
                    $fieldDefs = $this->fieldSets[$theme];

                    $form = Form::create([
                        'uuid'                 => (string) Str::uuid(),
                        'user_id'              => $user->id,
                        'title'                => $def['title'],
                        'description'          => $def['description'],
                        'slug'                 => 'dummy-' . $theme . '-' . Str::random(4),
                        'status'               => 'published',
                        'settings'             => [
                            'collect_ip'    => true,
                            'show_kbb_logo' => true,
                        ],
                        'confirmation_message' => 'Terima kasih, data Anda berhasil dikirim.',
                        'confirmation_type'    => 'message',
                        'limit_one_response'   => false,
                    ]);

                    $fieldRows = [];
                    foreach ($fieldDefs as $j => $field) {
                        $row = [];
                        $row['form_id'] = $form->id;
                        $row['section_id'] = null;
                        $row['type'] = $field['type']->value;
                        $row['label'] = $field['label'] ?? '';
                        $row['placeholder'] = $field['placeholder'] ?? null;
                        $row['help_text'] = $field['help_text'] ?? null;
                        $row['required'] = $field['required'] ?? false;
                        $row['options'] = isset($field['options']) ? json_encode($field['options']) : null;
                        $row['order'] = $j + 1;
                        $row['min_length'] = $field['min_length'] ?? null;
                        $row['max_length'] = $field['max_length'] ?? null;
                        $row['created_at'] = $now;
                        $row['updated_at'] = $now;
                        $fieldRows[] = $row;
                    }
                    FormField::insert($fieldRows);

                    $formFields = $form->fields()->whereNotIn('type', ['heading', 'paragraph', 'signature', 'file'])->get();
                    $submissionCount = $def['count'];

                    $submissionRows = [];
                    for ($s = 0; $s < $submissionCount; $s++) {
                        $submissionRows[] = [
                            'uuid'         => (string) Str::uuid(),
                            'form_id'      => $form->id,
                            'user_id'      => null,
                            'ip_address'   => $this->randomIP(),
                            'user_agent'   => $this->randomUserAgent(),
                            'submitted_at' => $this->randomDate($s),
                            'created_at'   => $now,
                            'updated_at'   => $now,
                        ];
                    }
                    FormSubmission::insert($submissionRows);

                    $insertedSubmissions = FormSubmission::where('form_id', $form->id)->orderBy('id')->get();

                    foreach ($insertedSubmissions as $s => $submission) {
                        foreach ($formFields as $field) {
                            $value = $this->generateFieldValue($field, $s, $submissionCount);
                            if ($value !== null && $value !== '') {
                                $allSubmissionData[] = [
                                    'submission_id' => $submission->id,
                                    'form_field_id' => $field->id,
                                    'value'         => $value,
                                    'created_at'    => $now,
                                    'updated_at'    => $now,
                                ];
                            }
                        }
                    }

                    $totalForms++;
                    $totalSubmissions += $submissionCount;
                }
            }

            if (!empty($allSubmissionData)) {
                foreach (array_chunk($allSubmissionData, 500) as $chunk) {
                    SubmissionData::insert($chunk);
                }
            }
        });

        $this->command->info('— — — — — — — — — — — — — — — — — — — —');
        $this->command->info("  Done! Created {$totalForms} forms, {$totalSubmissions} submissions.");
        $this->command->info('— — — — — — — — — — — — — — — — — — — —');
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

        if ($type === 'heading' || $type === 'paragraph' || $type === 'signature' || $type === 'file') {
            return null;
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
            $depan = $this->randomOf(array_merge(static::$namaDepanLaki, static::$namaDepanPerempuan));
            $belakang = $this->randomOf(static::$namaBelakang);
            return $depan . ' ' . $belakang;
        }
        if (str_contains($label, 'WhatsApp') || str_contains($label, 'Telepon') || str_contains($label, 'Telpon')) {
            return '08' . mt_rand(100000000, 999999999);
        }
        if (str_contains($label, 'Pekerjaan')) {
            return $this->randomOf(static::$pekerjaan);
        }
        if (str_contains($label, 'Lokasi')) {
            return $this->randomOf(static::$kecamatan) . ' - ' . $this->randomOf(['Kantor Bupati', 'Gedung D', 'Lantai 2 Ruang ' . mt_rand(201, 215), 'Gudang Utama', 'Ruang Server']);
        }
        if (str_contains($label, 'Pemohon') || str_contains($label, 'Pelapor')) {
            $depan = $this->randomOf(array_merge(static::$namaDepanLaki, static::$namaDepanPerempuan));
            $belakang = $this->randomOf(static::$namaBelakang);
            return $depan . ' ' . $belakang;
        }
        if (str_contains($label, 'Kegiatan') || str_contains($label, 'Barang')) {
            return $this->randomOf(static::$namaBarang);
        }
        if (str_contains($label, 'Instansi') || str_contains($label, 'Perusahaan')) {
            return $this->randomOf(['PT ' . $this->randomOf(static::$namaBelakang) . ' Sejahtera', 'CV ' . $this->randomOf(static::$namaBelakang) . ' Group', 'Pemerintah Kab. Bandung Barat', 'UPT ' . $this->randomOf(['Pendidikan', 'Kesehatan', 'Pertanian']) . ' ' . $this->randomOf(static::$kecamatan)]);
        }
        if (str_contains($label, 'Bidang') || str_contains($label, 'Unit')) {
            return $this->randomOf(['Bidang Pelayanan', 'Bidang Umum', 'Bidang Keuangan', 'Bidang Infrastruktur', 'Bidang Sosial', 'Subbag Umum', 'Subbag Kepegawaian']);
        }
        if (str_contains($label, 'Kode')) {
            return 'INV/' . date('Y') . '/' . str_pad(mt_rand(1, 999), 3, '0', STR_PAD_LEFT);
        }
        if (str_contains($label, 'Tamu')) {
            $depan = $this->randomOf(array_merge(static::$namaDepanLaki, static::$namaDepanPerempuan));
            $belakang = $this->randomOf(static::$namaBelakang);
            return $depan . ' ' . $belakang;
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
        if (str_contains($label, 'Deskripsi') || str_contains($label, 'uraian')) {
            return 'Kegiatan ' . $this->randomOf(['berjalan lancar', 'terlaksana dengan baik', 'sesuai rencana', 'diikuti oleh peserta yang antusias', 'memerlukan perbaikan']) . '. ' .
                $this->randomOf(['Dihadiri oleh ' . mt_rand(10, 50) . ' orang peserta.', 'Bertempat di Aula Kecamatan.', 'Dilaksanakan sesuai jadwal yang ditentukan.']);
        }
        if (str_contains($label, 'Tindak Lanjut')) {
            return $this->randomOf([
                'Akan dilakukan monitoring lanjutan ' . mt_rand(1, 4) . ' minggu lagi.',
                'Hasil kegiatan akan dilaporkan ke pimpinan.',
                'Perlu koordinasi lanjut dengan dinas terkait.',
                'Dokumentasi kegiatan akan dikirim ke bagian humas.',
            ]);
        }
        if (str_contains($label, 'Keperluan') || str_contains($label, 'Maksud') || str_contains($label, 'Tujuan')) {
            return $this->randomOf([
                'Koordinasi program kerja ' . $this->randomOf(static::$kecamatan),
                'Konsultasi mengenai pelayanan publik',
                'Pengajuan surat keterangan domisili',
                'Pembahasan anggaran kegiatan ' . $this->randomOf(['wisuda', 'pelatihan', 'sosialisasi']),
                'Silaturahmi dan koordinasi lintas sektor',
            ]);
        }
        return 'Isian ' . $label . ' untuk entri ke-' . ($subIndex + 1);
    }

    protected function generateNumberValue(string $label, FormField $field): string
    {
        if (str_contains($label, 'Anggota Keluarga') || str_contains($label, 'Tanggungan')) {
            return (string) mt_rand(1, 8);
        }
        if (str_contains($label, 'Nilai') && str_contains($label, '1-10')) {
            $weights = [1, 2, 3, 10, 15, 14, 12, 8, 5, 2];
            return (string) $this->weightedRandom([1, 2, 3, 4, 5, 6, 7, 8, 9, 10], $weights);
        }
        if (str_contains($label, 'Jumlah') || str_contains($label, 'Qty') || str_contains($label, 'Peserta')) {
            return (string) mt_rand(1, 25);
        }
        if (str_contains($label, 'Nilai Perolehan') || str_contains($label, 'Rupiah') || str_contains($label, 'Penghasilan')) {
            $amounts = [500000, 1000000, 1500000, 2500000, 3500000, 5000000];
            return (string) $this->randomOf($amounts);
        }
        if (str_contains($label, 'Estimasi')) {
            return (string) mt_rand(10, 500);
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
        if (str_contains($label, 'Pengadaan') || str_contains($label, 'Pinjam')) {
            $yearsAgo = mt_rand(0, 5);
            return now()->subYears($yearsAgo)->subDays(mt_rand(1, 365))->format('Y-m-d');
        }
        if (str_contains($label, 'Survey') || str_contains($label, 'Kunjungan') || str_contains($label, 'Pendataan')) {
            $daysAgo = mt_rand(0, 30);
            return now()->subDays($daysAgo)->format('Y-m-d');
        }
        if (str_contains($label, 'Pelaksanaan') || str_contains($label, 'Mulai')) {
            $daysAgo = mt_rand(1, 45);
            return now()->subDays($daysAgo)->format('Y-m-d');
        }
        if (str_contains($label, 'Selesai') || str_contains($label, 'Kembali')) {
            $daysAgo = mt_rand(0, 30);
            return now()->addDays(mt_rand(1, 14))->format('Y-m-d');
        }
        $daysAgo = mt_rand(0, 30);
        return now()->subDays($daysAgo)->format('Y-m-d');
    }

    protected function generateTimeValue(): string
    {
        return sprintf('%02d:%02d', mt_rand(7, 21), mt_rand(0, 11) * 5);
    }
}
