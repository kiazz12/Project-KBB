<?php

namespace Database\Seeders;

use App\Enums\FieldType;
use App\Models\Form;
use App\Models\FormField;
use App\Models\FormSubmission;
use App\Models\Participant;
use App\Models\SubmissionData;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class KbbPesertaSeeder extends Seeder
{
    public function run(): void
    {
        $csvPath = database_path('app/daftar_hadir.csv');

        if (!file_exists($csvPath)) {
            $csvPath = storage_path('app/daftar_hadir.csv');
        }

        if (!file_exists($csvPath)) {
            $this->command->warn('CSV file not found. Skipping participant import.');
            return;
        }

        $this->command->info('--- Importing participants from CSV...');

        $participants = $this->importCsv($csvPath);

        $this->command->info("  Imported " . count($participants) . " participants.");

        $this->command->info('--- Creating form "Tanda Terima Uang Saku Peserta"...');

        $form = $this->createForm();

        $this->command->info('--- Creating form fields...');

        $fields = $this->createFields($form);

        $this->command->info('--- Creating submissions from participants...');

        $this->createSubmissions($form, $fields, $participants);

        $this->command->info('--- Done! Form slug: ' . $form->slug);
    }

    private function importCsv(string $path): array
    {
        $participants = [];
        $handle = fopen($path, 'r');
        $header = fgetcsv($handle);

        while (($row = fgetcsv($handle)) !== false) {
            if (count($row) < 4) continue;

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

        DB::table('participants')->truncate();
        foreach (array_chunk($participants, 500) as $chunk) {
            DB::table('participants')->insert($chunk);
        }

        return Participant::all()->toArray();
    }

    private function createForm(): Form
    {
        $user = User::where('role', 'super_admin')->first();

        $slug = 'tanda-terima-uang-saku';
        $baseSlug = $slug;
        $counter = 1;
        while (Form::where('slug', $slug)->exists()) {
            $slug = $baseSlug . '-' . $counter;
            $counter++;
        }

        return Form::create([
            'uuid' => (string) Str::uuid(),
            'user_id' => $user->id,
            'title' => 'Tanda Terima Uang Saku Peserta',
            'description' => 'Sosialisasi Pemanfaatan Portal Pelayanan Pemerintah Daerah yang Terintegrasi di Kabupaten Bandung Barat',
            'slug' => $slug,
            'status' => 'published',
            'settings' => [
                'collect_ip' => true,
                'show_kbb_logo' => true,
            ],
            'confirmation_message' => 'Terima kasih! Tanda terima uang saku Anda telah berhasil dikirim.',
            'confirmation_type' => 'message',
            'limit_one_response' => false,
        ]);
    }

    private function createFields(Form $form): \Illuminate\Database\Eloquent\Collection
    {
        $now = now();

        $fieldDefs = [
            [
                'type' => FieldType::Heading->value,
                'label' => 'Data Peserta',
                'help_text' => 'Data diambil dari daftar hadir DiGi.Ka.',
                'order' => 1,
            ],
            [
                'type' => FieldType::Text->value,
                'label' => 'Nama Peserta',
                'placeholder' => 'Nama lengkap peserta',
                'required' => true,
                'order' => 2,
            ],
            [
                'type' => FieldType::Text->value,
                'label' => 'Instansi/Utusan',
                'placeholder' => 'Asal instansi/OPD',
                'required' => true,
                'order' => 3,
            ],
            [
                'type' => FieldType::Text->value,
                'label' => 'Jabatan',
                'placeholder' => 'Jabatan peserta',
                'required' => true,
                'order' => 4,
            ],
            [
                'type' => FieldType::Heading->value,
                'label' => 'Data Keuangan',
                'help_text' => 'Isi jumlah uang saku yang diterima',
                'order' => 5,
            ],
            [
                'type' => FieldType::Number->value,
                'label' => 'Jumlah Uang Saku',
                'placeholder' => 'Masukkan jumlah uang saku',
                'required' => true,
                'default_value' => '105000',
                'order' => 6,
            ],
            [
                'type' => FieldType::Computed->value,
                'label' => 'PPh 21 (5%)',
                'help_text' => 'Otomatis: 5% dari Jumlah Uang Saku',
                'required' => false,
                'order' => 7,
            ],
            [
                'type' => FieldType::Computed->value,
                'label' => 'Jumlah Diterima',
                'help_text' => 'Otomatis: Jumlah Uang Saku - PPh 21',
                'required' => false,
                'order' => 8,
            ],
            [
                'type' => FieldType::Heading->value,
                'label' => 'Verifikasi',
                'help_text' => 'Isi NIK dan tanda tangan untuk verifikasi',
                'order' => 9,
            ],
            [
                'type' => FieldType::Text->value,
                'label' => 'NIK',
                'placeholder' => '16 digit NIK',
                'required' => true,
                'min_length' => 16,
                'max_length' => 16,
                'order' => 10,
            ],
            [
                'type' => FieldType::Signature->value,
                'label' => 'Tanda Tangan',
                'help_text' => 'Tanda tangan elektronik',
                'required' => true,
                'order' => 11,
            ],
        ];

        $fieldRows = [];
        foreach ($fieldDefs as $def) {
            $row = array_merge($def, [
                'form_id' => $form->id,
                'section_id' => null,
                'placeholder' => $def['placeholder'] ?? null,
                'help_text' => $def['help_text'] ?? null,
                'required' => $def['required'] ?? false,
                'options' => null,
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

    private function createSubmissions(Form $form, \Illuminate\Database\Eloquent\Collection $fields, array $participants): void
    {
        $uangSakuField = $fields->firstWhere('label', 'Jumlah Uang Saku');
        $pph21Field = $fields->firstWhere('label', 'PPh 21 (5%)');
        $jumlahDiterimaField = $fields->firstWhere('label', 'Jumlah Diterima');

        $uangSakuAmount = 105000;
        $pph21Amount = $uangSakuAmount * 0.05;
        $jumlahDiterimaAmount = $uangSakuAmount - $pph21Amount;

        $now = now();
        $submissionRows = [];
        $submissionDataRows = [];

        foreach ($participants as $p) {
            $submissionId = null;
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
            if (!$p) continue;

            $fieldMap = [
                'Nama Peserta' => $p['nama'],
                'Instansi/Utusan' => $p['opd_institusi'],
                'Jabatan' => $p['jabatan'],
                'Jumlah Uang Saku' => (string) $uangSakuAmount,
                'PPh 21 (5%)' => (string) (int) $pph21Amount,
                'Jumlah Diterima' => (string) (int) $jumlahDiterimaAmount,
            ];

            foreach ($fields as $field) {
                if ($field->type->value === 'heading') continue;
                if ($field->type->value === 'signature') continue;

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

        $this->command->info("  Created " . count($allSubmissions) . " submissions with pre-filled data.");
    }
}
