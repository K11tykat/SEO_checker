<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\Seo\PageDownloader;
use App\Services\Seo\RootFilesChecker;
use App\Services\Seo\Checkers\TitleChecker;
use App\Services\Seo\Checkers\DescriptionChecker;
use App\Services\Seo\Checkers\HeadingChecker;
use App\Services\Seo\Checkers\LinksChecker;
use App\Services\Seo\Checkers\MicrodataChecker;
use App\Services\Seo\ReportStorageService;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Models\AuditResult;
use App\Models\SavedReport;
use App\Models\Audit;


class SeoCheckController extends Controller
{
    protected $storageService;

    public function __construct(ReportStorageService $storageService)
    {
        $this->storageService = $storageService;
    }

    // Страница с результатами
    public function results($auditId)
    {
        $audit = $this->storageService->getAuditDetail($auditId);
        return view('seo.results', compact('audit'));
    }

public function run(Request $request)
{
    $request->validate([
        'urls' => 'required|array|max:20',
        'urls.*' => 'url'
    ]);

    $urls = $request->input('urls');
    $audit = $this->storageService->createAudit();

    $downloader = new PageDownloader();
    $fileChecker = new RootFilesChecker();
    $titleChecker = new TitleChecker();
    $descriptionChecker = new DescriptionChecker();
    $headingChecker = new HeadingChecker();
    $linksChecker = new LinksChecker();
    $microdataChecker = new MicrodataChecker();

    foreach ($urls as $url) {
        $pageData = $downloader->download($url);
        
        if (!$pageData['success']) {
            $this->storageService->saveResultForUrl($audit, $url, [
                'http_code' => 0,
                'redirect_url' => null,
                'h1' => ['valid' => false, 'reason' => 'Страница не загружена'],
                'title' => ['valid' => false, 'reason' => 'Страница не загружена', 'length' => 0],
                'description' => ['valid' => false, 'reason' => 'Страница не загружена', 'length' => 0],
                'headings_valid' => false,
                'external_links_count' => 0,
                'external_links_nofollow' => 0,
                'external_links_dofollow' => 0,
                'og_marker' => false,
                'schema_marker' => false,
                'schema_formats' => [],
                'robots_marker' => false,
                'sitemap_marker' => false,
            ]);
            continue;
        }

        $crawler = $pageData['crawler'];

        $titleResult = $titleChecker->check($crawler);
        $descriptionResult = $descriptionChecker->check($crawler);
        $headingResult = $headingChecker->check($crawler);
        $linksResult = $linksChecker->check($crawler, $url);
        $microdataResult = $microdataChecker->check($crawler);
        
        $robotsResult = $fileChecker->checkFile($url, 'robots.txt');
        $sitemapResult = $fileChecker->checkFile($url, 'sitemap.xml');

        // Обработка H1
        $h1Valid = false;
        $h1Reason = null;
        if (isset($headingResult['h1'])) {
            $h1Valid = $headingResult['h1']['marker'] === 'green';
            $h1Reason = $headingResult['h1']['error'] ?? null;
        }

        // Обработка структуры заголовков
        $headingsValid = false;
        if (isset($headingResult['structure'])) {
            $headingsValid = $headingResult['structure']['marker'] === 'green';
        }

        // Обработка Open Graph
        $ogMarker = false;
        if (isset($microdataResult['open_graph'])) {
            $ogMarker = $microdataResult['open_graph']['marker'] === 'green';
        }

        // Обработка Schema.org
        $schemaMarker = false;
        $schemaFormats = [];
        if (isset($microdataResult['schema_org'])) {
            $schemaMarker = $microdataResult['schema_org']['marker'] === 'green';
            $schemaFormats = $microdataResult['schema_org']['formats'] ?? [];
        }

        $this->storageService->saveResultForUrl($audit, $url, [
            'http_code' => $pageData['status_code'] ?? 0,
            'redirect_url' => $pageData['final_url'] ?? null,
            'h1' => [
                'valid' => $h1Valid,
                'reason' => $h1Reason
            ],
            'title' => [
                'valid' => $titleResult['marker'] === 'green',
                'reason' => $titleResult['error'] ?? null,
                'length' => $titleResult['length'] ?? 0
            ],
            'description' => [
                'valid' => $descriptionResult['marker'] === 'green',
                'reason' => $descriptionResult['error'] ?? null,
                'length' => $descriptionResult['length'] ?? 0
            ],
            'headings_valid' => $headingsValid,
            'external_links_count' => $linksResult['external_links_count'] ?? 0,
            'external_links_nofollow' => $linksResult['nofollow_count'] ?? 0,
            'external_links_dofollow' => $linksResult['dofollow_count'] ?? 0,
            'og_marker' => $ogMarker,
            'schema_marker' => $schemaMarker,
            'schema_formats' => $schemaFormats,
            'robots_marker' => $robotsResult['exists'] ?? false,
            'sitemap_marker' => $sitemapResult['exists'] ?? false,
        ]);
    }

    $this->storageService->completeAudit($audit);
    return redirect()->route('results', ['auditId' => $audit->id]);
}

    // Сохранение в избранное (расширение 6a)
    public function saveToFavorites($auditId)
    {
        return redirect()->back()->with('success', 'Отчет сохранен в БД');
    }

    // Скачать PDF (расширение 6b)
    public function downloadPdf($auditId)
    {
        $audit = $this->storageService->getAuditDetail($auditId);
        $pdf = Pdf::loadView('seo.pdf', compact('audit'));
        return $pdf->download('seo-report-' . $auditId . '.pdf');
    }


public function saveReport($auditId)
{
    $audit = Audit::find($auditId);
    
    if (!$audit) {
        return redirect()->back()->with('error', 'Проверка не найдена');
    }
    
    $exists = SavedReport::where('audit_id', $auditId)->exists();
    
    if ($exists) {
        return redirect()->back()->with('info', 'Этот отчет уже сохранен');
    }
    
    SavedReport::create([
        'audit_id' => $auditId,
        'user_id' => auth()->id() ?? null,
    ]);
    
    return redirect()->back()->with('success', 'Отчет сохранен в БД');
}
}