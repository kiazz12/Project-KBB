<?php

namespace Database\Seeders;

use App\Enums\FieldType;
use App\Models\Form;
use App\Models\FormField;
use App\Models\FormSection;
use App\Models\FormSubmission;
use App\Models\SubmissionData;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class KbbPresensiSeeder extends Seeder
{
    public function run(): void
    {
        $csvPath = storage_path('app/daftar_hadir.csv');

        if (! file_exists($csvPath)) {
            $this->command->warn('CSV file not found at: '.$csvPath);

            return;
        }

        $this->command->info('--- Creating Presensi form...');

        $form = $this->createForm();

        $this->command->info('--- Creating sections...');

        $sections = $this->createSections($form);

        $this->command->info('--- Creating form fields...');

        $fields = $this->createFields($form, $sections);

        $this->command->info('--- Importing participants and creating submissions...');

        $this->importAndCreateSubmissions($form, $fields, $csvPath);

        $this->command->info('--- Done! Form slug: '.$form->slug);
        $this->command->info('--- Admin URL: /forms/'.$form->id);
        $this->command->info('--- Public URL: /form/'.$form->slug);
    }

    private function createForm(): Form
    {
        $user = User::where('role', 'super_admin')->first();

        $slug = 'presensi-transfer-knowledge';
        $baseSlug = $slug;
        $counter = 1;
        while (Form::where('slug', $slug)->exists()) {
            $slug = $baseSlug.'-'.$counter;
            $counter++;
        }

        return Form::create([
            'uuid' => (string) Str::uuid(),
            'user_id' => $user->id,
            'title' => 'Form Presensi - Transfer Knowledge Teknologi Terbaru (Smart Governance & Smart Society) Batch 2 Tahun 2026',
            'description' => 'Daftar Hadir Peserta Kegiatan Transfer Knowledge Teknologi Terbaru (Smart Governance & Smart Society) Batch 2 Tahun 2026 - Komdigi',
            'slug' => $slug,
            'status' => 'published',
            'settings' => [
                'collect_ip' => true,
                'show_kbb_logo' => true,
            ],
            'confirmation_message' => 'Terima kasih! Presensi Anda telah berhasil dicatat. Silakan download bukti presensi dari admin panel.',
            'confirmation_type' => 'message',
            'limit_one_response' => true,
        ]);
    }

    private function createSections(Form $form): Collection
    {
        $now = now();

        $sectionDefs = [
            ['title' => 'Informasi Kegiatan', 'description' => 'Transfer Knowledge Teknologi Terbaru Batch 2 Tahun 2026', 'order' => 1],
            ['title' => 'Data Peserta', 'description' => 'Isi data diri Anda', 'order' => 2],
            ['title' => 'Presensi & Keuangan', 'description' => 'Data kehadiran dan perhitungan uang saku', 'order' => 3],
            ['title' => 'Verifikasi & Tanda Tangan', 'description' => 'Tanda tangan digital', 'order' => 4],
        ];

        $sectionRows = [];
        foreach ($sectionDefs as $def) {
            $sectionRows[] = array_merge($def, [
                'form_id' => $form->id,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }

        FormSection::insert($sectionRows);

        return $form->sections()->orderBy('order')->get();
    }

    private function createFields(Form $form, Collection $sections): Collection
    {
        $now = now();
        $infoSection = $sections->firstWhere('title', 'Informasi Kegiatan');
        $pesertaSection = $sections->firstWhere('title', 'Data Peserta');
        $presensiSection = $sections->firstWhere('title', 'Presensi & Keuangan');
        $verifikasiSection = $sections->firstWhere('title', 'Verifikasi & Tanda Tangan');

        $fieldDefs = [
            [
                'type' => FieldType::Text->value,
                'label' => 'Tanggal Pelaksanaan',
                'default_value' => '14 Juli 2026',
                'section_id' => $infoSection->id,
                'order' => 1,
            ],
            [
                'type' => FieldType::Text->value,
                'label' => 'Link Zoom',
                'default_value' => 'https://s.komdigi.go.id/transferknowledgebatch2',
                'section_id' => $infoSection->id,
                'order' => 2,
            ],

            [
                'type' => FieldType::Text->value,
                'label' => 'Role',
                'required' => true,
                'options' => ['Peserta', 'Pengawas/Penyedia', 'Narasumber'],
                'section_id' => $pesertaSection->id,
                'order' => 3,
            ],
            [
                'type' => FieldType::Text->value,
                'label' => 'Nama Lengkap',
                'placeholder' => 'Nama lengkap',
                'required' => true,
                'section_id' => $pesertaSection->id,
                'order' => 4,
            ],
            [
                'type' => FieldType::Text->value,
                'label' => 'Jabatan',
                'placeholder' => 'Jabatan di instansi',
                'required' => true,
                'section_id' => $pesertaSection->id,
                'order' => 5,
            ],
            [
                'type' => FieldType::Text->value,
                'label' => 'OPD / Institusi',
                'placeholder' => 'Asal instansi/OPD',
                'required' => true,
                'section_id' => $pesertaSection->id,
                'order' => 6,
            ],
            [
                'type' => FieldType::Text->value,
                'label' => 'No. Induk Pegawai (NIP)',
                'placeholder' => 'NIP atau NIK',
                'required' => true,
                'min_length' => 16,
                'max_length' => 18,
                'section_id' => $pesertaSection->id,
                'order' => 7,
            ],

            [
                'type' => FieldType::Text->value,
                'label' => 'Hari ke-',
                'placeholder' => 'Contoh: 1, 2, 3',
                'required' => true,
                'section_id' => $presensiSection->id,
                'order' => 8,
            ],
            [
                'type' => FieldType::Date->value,
                'label' => 'Tanggal Kehadiran',
                'required' => true,
                'section_id' => $presensiSection->id,
                'order' => 9,
            ],
            [
                'type' => FieldType::Time->value,
                'label' => 'Jam Masuk',
                'placeholder' => 'Pilih jam masuk',
                'required' => true,
                'section_id' => $presensiSection->id,
                'order' => 10,
            ],
            [
                'type' => FieldType::Time->value,
                'label' => 'Jam Keluar',
                'placeholder' => 'Pilih jam keluar',
                'section_id' => $presensiSection->id,
                'order' => 11,
            ],
            [
                'type' => FieldType::Radio->value,
                'label' => 'Status Kehadiran',
                'required' => false,
                'options' => ['Hadir', 'Izin', 'Sakit', 'Tanpa Keterangan'],
                'is_admin_only' => true,
                'section_id' => $presensiSection->id,
                'order' => 12,
            ],
            [
                'type' => FieldType::Text->value,
                'label' => 'Lokasi Kehadiran',
                'placeholder' => 'Contoh: Kantor Dinas Kominfotik KBB',
                'section_id' => $presensiSection->id,
                'order' => 13,
            ],
            [
                'type' => FieldType::Number->value,
                'label' => 'Jumlah Uang Saku',
                'placeholder' => 'Masukkan jumlah uang saku',
                'required' => true,
                'default_value' => '105000',
                'section_id' => $presensiSection->id,
                'order' => 14,
            ],
            [
                'type' => FieldType::Computed->value,
                'label' => 'PPh 21 (5%)',
                'help_text' => 'Otomatis: 5% dari Jumlah Uang Saku',
                'section_id' => $presensiSection->id,
                'order' => 15,
            ],
            [
                'type' => FieldType::Computed->value,
                'label' => 'Jumlah Diterima',
                'help_text' => 'Otomatis: Jumlah Uang Saku - PPh 21',
                'section_id' => $presensiSection->id,
                'order' => 16,
            ],

            [
                'type' => FieldType::Signature->value,
                'label' => 'Tanda Tangan Peserta',
                'help_text' => 'Tanda tangan elektronik Anda',
                'required' => true,
                'section_id' => $verifikasiSection->id,
                'order' => 17,
            ],
            [
                'type' => FieldType::Paragraph->value,
                'label' => 'Dengan mengirimkan form ini, saya menyatakan bahwa data yang saya isi adalah benar dan saya telah mengikuti kegiatan Transfer Knowledge Teknologi Terbaru (Smart Governance & Smart Society) Batch 2 Tahun 2026.',
                'section_id' => $verifikasiSection->id,
                'order' => 18,
            ],
        ];

        $fieldRows = [];
        foreach ($fieldDefs as $def) {
            $row = array_merge($def, [
                'form_id' => $form->id,
                'placeholder' => $def['placeholder'] ?? null,
                'help_text' => $def['help_text'] ?? null,
                'required' => $def['required'] ?? false,
                'options' => isset($def['options']) ? json_encode($def['options']) : null,
                'min_length' => $def['min_length'] ?? null,
                'max_length' => $def['max_length'] ?? null,
                'default_value' => $def['default_value'] ?? null,
                'formula' => $def['formula'] ?? null,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
            $fieldRows[] = $row;
        }

        FormField::insert($fieldRows);

        $createdFields = $form->fields()->orderBy('order')->get();

        $uangSakuField = $createdFields->firstWhere('label', 'Jumlah Uang Saku');
        $pph21Field = $createdFields->firstWhere('label', 'PPh 21 (5%)');
        $jumlahDiterimaField = $createdFields->firstWhere('label', 'Jumlah Diterima');

        if ($pph21Field && $uangSakuField) {
            $pph21Field->update([
                'formula' => [
                    'ref_field_id' => $uangSakuField->id,
                    'operation' => 'multiply',
                    'value' => 0.05,
                ],
            ]);
        }

        if ($jumlahDiterimaField && $uangSakuField && $pph21Field) {
            $jumlahDiterimaField->update([
                'formula' => [
                    'ref_field_id' => $uangSakuField->id,
                    'operation' => 'subtract',
                    'ref_field_id_2' => $pph21Field->id,
                ],
            ]);
        }

        return $createdFields;
    }

    private function importAndCreateSubmissions(Form $form, Collection $fields, string $csvPath): void
    {
        $participants = [];
        $handle = fopen($csvPath, 'r');
        $header = fgetcsv($handle);

        while (($row = fgetcsv($handle)) !== false) {
            if (count($row) < 4) {
                continue;
            }

            $tanggal = $row[5] ?? null;
            if ($tanggal && preg_match('/(\d{2})-(\d{2})-(\d{4})/', $tanggal, $m)) {
                $tanggal = "{$m[3]}-{$m[2]}-{$m[1]}";
            } else {
                $tanggal = null;
            }

            $participants[] = [
                'no' => (int) $row[0],
                'nama' => $row[1] ?? '',
                'jabatan' => $row[2] ?? '',
                'opd_institusi' => $row[3] ?? '',
                'role' => $row[4] ?? '',
                'tanggal_presensi' => $tanggal,
            ];
        }
        fclose($handle);

        $uangSakuField = $fields->firstWhere('label', 'Jumlah Uang Saku');
        $pph21Field = $fields->firstWhere('label', 'PPh 21 (5%)');
        $jumlahDiterimaField = $fields->firstWhere('label', 'Jumlah Diterima');
        $tanggalField = $fields->firstWhere('label', 'Tanggal Pelaksanaan');
        $tanggalKehadiranField = $fields->firstWhere('label', 'Tanggal Kehadiran');

        $uangSakuAmount = 105000;
        $pph21Amount = (int) ($uangSakuAmount * 0.05);
        $jumlahDiterimaAmount = $uangSakuAmount - $pph21Amount;

        $now = now();
        $submissionRows = [];
        $submissionDataRows = [];

        foreach ($participants as $p) {
            $submissionRows[] = [
                'uuid' => (string) Str::uuid(),
                'form_id' => $form->id,
                'user_id' => null,
                'ip_address' => null,
                'user_agent' => 'imported-from-digika',
                'submitted_at' => $now,
                'created_at' => $now,
                'updated_at' => $now,
            ];
        }

        foreach (array_chunk($submissionRows, 500) as $chunk) {
            FormSubmission::insert($chunk);
        }

        $allSubmissions = FormSubmission::where('form_id', $form->id)
            ->where('user_agent', 'imported-from-digika')
            ->orderBy('id')
            ->get();

        foreach ($allSubmissions as $i => $submission) {
            $p = $participants[$i] ?? null;
            if (! $p) {
                continue;
            }

            $fieldMap = [
                'Role' => $p['role'] ?: 'Peserta',
                'Nama Lengkap' => $p['nama'],
                'Jabatan' => $p['jabatan'],
                'OPD / Institusi' => $p['opd_institusi'],
                'Tanggal Pelaksanaan' => '14 Juli 2026',
                'Link Zoom' => 'https://s.komdigi.go.id/transferknowledgebatch2',
                'Hari ke-' => '1',
                'Tanggal Kehadiran' => $p['tanggal_presensi'] ?? '2026-07-14',
                'Status Kehadiran' => $p['role'] === 'Peserta' ? 'Hadir' : 'Hadir',
                'Lokasi Kehadiran' => 'Virtual (Zoom Meeting)',
                'Jam Masuk' => '08:00',
                'Jam Keluar' => '17:00',
                'Jumlah Uang Saku' => (string) $uangSakuAmount,
                'PPh 21 (5%)' => (string) $pph21Amount,
                'Jumlah Diterima' => (string) $jumlahDiterimaAmount,
            ];

            foreach ($fields as $field) {
                if ($field->type->value === 'heading') {
                    continue;
                }
                if ($field->type->value === 'paragraph') {
                    continue;
                }
                if ($field->type->value === 'signature') {
                    continue;
                }
                if ($field->type->value === 'computed') {
                    continue;
                }

                $value = $fieldMap[$field->label] ?? '';
                if ($value !== '') {
                    $submissionDataRows[] = [
                        'submission_id' => $submission->id,
                        'form_field_id' => $field->id,
                        'value' => $value,
                        'created_at' => $now,
                        'updated_at' => $now,
                    ];
                }
            }
        }

        foreach (array_chunk($submissionDataRows, 500) as $chunk) {
            SubmissionData::insert($chunk);
        }

        $this->command->info('  Created '.count($allSubmissions).' submissions with pre-filled data.');
    }
}
