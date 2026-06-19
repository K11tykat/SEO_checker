<?php

namespace Tests\Feature;

use App\Models\Audit;
use App\Services\Seo\ReportStorageService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class AuditFlowTest extends TestCase
{
    use RefreshDatabase;

    private function fakePages(): void
    {
        Http::fake([
            '*/robots.txt' => Http::response('User-agent: *', 200),
            '*/sitemap.xml' => Http::response('<urlset></urlset>', 200),
            '*' => Http::response(
                '<html><head><title>Заголовок</title>'
                .'<meta name="description" content="Описание страницы">'
                .'<meta property="og:title" content="og">'
                .'<script type="application/ld+json">{}</script>'
                .'</head><body><h1>H1</h1><h2>H2</h2>'
                .'<a href="https://external.com/x">внешняя</a></body></html>',
                200
            ),
        ]);
    }

    public function test_run_creates_completed_audit_with_results(): void
    {
        $this->fakePages();

        $response = $this->post('/audit/run', ['urls' => ['https://mysite.test']]);

        $audit = Audit::first();
        $this->assertNotNull($audit);
        $response->assertRedirect(route('audit.results', $audit->id));

        $this->assertDatabaseHas('audits', ['status' => 'completed']);
        $this->assertDatabaseHas('audit_urls', ['url' => 'https://mysite.test', 'http_code' => 200]);
        $this->assertDatabaseHas('audit_results', [
            'title_is_valid' => true,
            'og_marker' => true,
            'headings_valid' => true,
        ]);
    }

    public function test_results_page_renders(): void
    {
        $this->fakePages();
        $this->post('/audit/run', ['urls' => ['https://mysite.test']]);
        $audit = Audit::first();

        $this->get(route('audit.results', $audit->id))
            ->assertStatus(200)
            ->assertSee('https://mysite.test');
    }

    public function test_run_rejects_more_than_20_urls(): void
    {
        $urls = array_fill(0, 21, 'https://a.com');

        $this->post('/audit/run', ['urls' => $urls])
            ->assertSessionHasErrors('urls');
    }

    public function test_run_rejects_invalid_url(): void
    {
        $this->post('/audit/run', ['urls' => ['not-a-url']])
            ->assertSessionHasErrors('urls.0');
    }

    public function test_delete_removes_audit_and_its_results(): void
    {
        $this->fakePages();
        $this->post('/audit/run', ['urls' => ['https://mysite.test']]);
        $audit = Audit::first();

        $this->delete(route('audit.destroy', $audit->id))
            ->assertRedirect(route('history.index'));

        $this->assertDatabaseMissing('audits', ['id' => $audit->id]);
        $this->assertDatabaseMissing('audit_urls', ['audit_id' => $audit->id]);
        $this->assertSame(0, \App\Models\AuditResult::count());
    }

    public function test_pdf_export_returns_pdf_document(): void
    {
        if (! class_exists(\Barryvdh\DomPDF\Facade\Pdf::class)) {
            $this->markTestSkipped('Пакет barryvdh/laravel-dompdf ещё не установлен.');
        }

        $this->fakePages();
        $this->post('/audit/run', ['urls' => ['https://mysite.test']]);
        $audit = Audit::first();

        $response = $this->post(route('audit.pdf', $audit->id), ['url_ids' => []]);

        $response->assertStatus(200);
        $this->assertStringContainsString('application/pdf', $response->headers->get('content-type'));
    }
}
