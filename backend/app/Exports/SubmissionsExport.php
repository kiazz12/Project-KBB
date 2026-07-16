<?php

namespace App\Exports;

use App\Models\Form;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class SubmissionsExport implements FromCollection, WithColumnWidths, WithHeadings, WithStyles
{
    public function __construct(protected Form $form) {}

    public function collection(): Collection
    {
        $fields = $this->form->fields()->orderBy('order')->get();

        return $this->form->submissions()->with('data')->latest()->get()->map(function ($submission) use ($fields) {
            $data = $submission->data->keyBy('form_field_id');

            $row = [
                'Submission UUID' => $submission->uuid,
                'Submitted At' => $submission->submitted_at,
            ];

            foreach ($fields as $field) {
                $row[$field->label] = $data->get($field->id)?->value ?? '';
            }

            return $row;
        });
    }

    public function headings(): array
    {
        $fields = $this->form->fields()->orderBy('order')->get();

        return array_merge(['Submission UUID', 'Submitted At'], $fields->pluck('label')->toArray());
    }

    public function columnWidths(): array
    {
        return [
            'A' => 38,
            'B' => 22,
        ];
    }

    public function styles(Worksheet $sheet): array
    {
        return [
            1 => ['font' => ['bold' => true], 'fill' => ['fillType' => 'solid', 'startColor' => '1E3A8A']],
        ];
    }
}
