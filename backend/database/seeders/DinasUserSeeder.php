<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DinasUserSeeder extends Seeder
{
    public function run(): void
    {
        User::updateOrCreate(
            ['email' => 'admin@dinas.com'],
            [
                'name' => 'Super Admin KBB',
                'password' => Hash::make('admin12345'),
                'role' => 'super_admin',
            ]
        );

        $accounts = [
            ['name' => 'Kecamatan Batujajar', 'slug' => 'batujajar'],
            ['name' => 'Kecamatan Cihampelas', 'slug' => 'cihampelas'],
            ['name' => 'Kecamatan Cikalongwetan', 'slug' => 'cikalongwetan'],
            ['name' => 'Kecamatan Cililin', 'slug' => 'cililin'],
            ['name' => 'Kecamatan Cipatat', 'slug' => 'cipatat'],
            ['name' => 'Kecamatan Cipeundeuy', 'slug' => 'cipeundeuy'],
            ['name' => 'Kecamatan Cipongkor', 'slug' => 'cipongkor'],
            ['name' => 'Kecamatan Cisarua', 'slug' => 'cisarua'],
            ['name' => 'Kecamatan Gununghalu', 'slug' => 'gununghalu'],
            ['name' => 'Kecamatan Lembang', 'slug' => 'lembang'],
            ['name' => 'Kecamatan Ngamprah', 'slug' => 'ngamprah'],
            ['name' => 'Kecamatan Padalarang', 'slug' => 'padalarang'],
            ['name' => 'Kecamatan Parongpong', 'slug' => 'parongpong'],
            ['name' => 'Kecamatan Rongga', 'slug' => 'rongga'],
            ['name' => 'Kecamatan Saguling', 'slug' => 'saguling'],
            ['name' => 'Kecamatan Sindangkerta', 'slug' => 'sindangkerta'],
            ['name' => 'Dinas Pendidikan', 'slug' => 'disdik'],
            ['name' => 'Dinas Kesehatan', 'slug' => 'dinkes'],
            ['name' => 'Dinas Sosial', 'slug' => 'dinsos'],
            ['name' => 'Dinas Tenaga Kerja dan Transmigrasi', 'slug' => 'disnaker'],
            ['name' => 'Dinas Ketahanan Pangan dan Pertanian', 'slug' => 'dkpp'],
            ['name' => 'Dinas Perikanan dan Peternakan', 'slug' => 'diskanak'],
            ['name' => 'Dinas Lingkungan Hidup', 'slug' => 'dlh'],
            ['name' => 'Dinas Kependudukan dan Pencatatan Sipil', 'slug' => 'disdukcapil'],
            ['name' => 'Dinas Pemberdayaan Masyarakat dan Desa', 'slug' => 'pmd'],
            ['name' => 'Dinas Pengendalian Penduduk Keluarga Berencana Pemberdayaan Perempuan dan Perlindungan Anak', 'slug' => 'dp2kbppa'],
            ['name' => 'Dinas Perhubungan', 'slug' => 'dishub'],
            ['name' => 'Dinas Komunikasi Informatika dan Statistik', 'slug' => 'diskominfo'],
            ['name' => 'Dinas Koperasi Usaha Kecil dan Menengah', 'slug' => 'dinkop'],
            ['name' => 'Dinas Penanaman Modal dan Pelayanan Terpadu Satu Pintu', 'slug' => 'dpmptsp'],
            ['name' => 'Dinas Kepemudaan dan Olahraga', 'slug' => 'dispora'],
            ['name' => 'Dinas Arsip dan Perpustakaan', 'slug' => 'disarpus'],
            ['name' => 'Dinas Pariwisata dan Kebudayaan', 'slug' => 'disparbud'],
            ['name' => 'Dinas Pekerjaan Umum dan Tata Ruang', 'slug' => 'putr'],
            ['name' => 'Dinas Perumahan dan Kawasan Permukiman', 'slug' => 'disperkim'],
            ['name' => 'Dinas Pemadam Kebakaran dan Penyelamatan', 'slug' => 'damkar'],
            ['name' => 'Dinas Perindustrian dan Perdagangan', 'slug' => 'disperindag'],
            ['name' => 'Badan Perencanaan Pembangunan Penelitian dan Pengembangan Daerah', 'slug' => 'bappeda'],
            ['name' => 'Badan Keuangan dan Aset Daerah', 'slug' => 'bkad'],
            ['name' => 'Badan Pendapatan Daerah', 'slug' => 'bapenda'],
            ['name' => 'Badan Kepegawaian dan Pengembangan Sumber Daya Manusia', 'slug' => 'bkpsdm'],
            ['name' => 'Badan Kesatuan Bangsa dan Politik', 'slug' => 'kesbangpol'],
            ['name' => 'Badan Penanggulangan Bencana Daerah', 'slug' => 'bpbd'],
            ['name' => 'Sekretariat Daerah', 'slug' => 'sekda'],
            ['name' => 'Sekretariat Dewan Perwakilan Rakyat Daerah', 'slug' => 'setwan'],
            ['name' => 'Inspektorat Daerah', 'slug' => 'inspektorat'],
            ['name' => 'Satuan Polisi Pamong Praja', 'slug' => 'satpolpp'],
        ];

        foreach ($accounts as $acct) {
            User::updateOrCreate(
                ['email' => "admin@{$acct['slug']}.com"],
                [
                    'name' => $acct['name'],
                    'password' => Hash::make('admin12345'),
                    'role' => 'admin',
                ]
            );
        }
    }
}
