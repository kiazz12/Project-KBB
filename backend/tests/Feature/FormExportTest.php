<?php

namespace Tests\Feature;

use App\Models\Form;
use App\Models\FormField;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class FormExportTest extends TestCase
{
    use RefreshDatabase;

    private User $owner;

    private Form $form;

    protected function setUp(): void
    {
        parent::setUp();

        $this->owner = User::factory()->create();

        $this->form = Form::create([
            'uuid' => Str::uuid(),
            'user_id' => $this->owner->id,
            'title' => 'Form Export',
            'slug' => 'form-export-'.Str::random(6),
            'status' => 'published',
        ]);

        FormField::create([
            'form_id' => $this->form->id,
            'type' => 'text',
            'label' => 'Nama Lengkap',
            'required' => true,
            'order' => 1,
        ]);
    }

    public function test_form_show_page_exposes_xlsx_download_button(): void
    {
        $response = $this->actingAs($this->owner)->get("/forms/{$this->form->id}");

        $response->assertOk();
        $response->assertSee('Download Excel (XLSX)');
        $response->assertSee(route('forms.export.xlsx', $this->form), false);
    }

    public function test_xlsx_export_streams_spreadsheet(): void
    {
        $response = $this->actingAs($this->owner)->get("/forms/{$this->form->id}/export/xlsx");

        $response->assertOk();
        $response->assertHeader(
            'content-type',
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'
        );
        $this->assertStringContainsString(
            '.xlsx',
            (string) $response->headers->get('content-disposition')
        );
    }

    public function test_csv_export_streams_csv(): void
    {
        $response = $this->actingAs($this->owner)->get("/forms/{$this->form->id}/export/csv");

        $response->assertOk();
        $this->assertStringContainsString(
            'text/csv',
            (string) $response->headers->get('content-type')
        );
    }

    public function test_pdf_export_streams_pdf(): void
    {
        $response = $this->actingAs($this->owner)->get("/forms/{$this->form->id}/export/pdf");

        $response->assertOk();
        $this->assertStringContainsString(
            'application/pdf',
            (string) $response->headers->get('content-type')
        );
    }
}
