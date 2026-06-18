<?php

use Illuminate\Support\Facades\Route;
use App\Services\Seo\PageDownloader;
use App\Services\Seo\RootFilesChecker;

use App\Services\Seo\Checkers\TitleChecker;
use App\Services\Seo\Checkers\DescriptionChecker;
use App\Services\Seo\Checkers\HeadingChecker;
use App\Services\Seo\Checkers\LinksChecker;
use App\Services\Seo\Checkers\MicrodataChecker;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/test-seo', function() {
    $url = 'https://laravel.com'; 
    
    $downloader = new PageDownloader();
    $fileChecker = new RootFilesChecker();
    
    $titleChecker = new TitleChecker();
    $descriptionChecker = new DescriptionChecker();
    $headingChecker = new HeadingChecker();
    $linksChecker = new LinksChecker();
    $microdataChecker = new MicrodataChecker();
    
    $pageData = $downloader->download($url);
    
    if (!$pageData['success']) {
        return response()->json(['error' => $pageData['error']], 400);
    }
    
    $crawler = $pageData['crawler'];
    
    $titleResult = $titleChecker->check($crawler);
    $descriptionResult = $descriptionChecker->check($crawler);
    $headingResult = $headingChecker->check($crawler);
    $linksResult = $linksChecker->check($crawler, $url);
    $microdataResult = $microdataChecker->check($crawler);
    
    $robotsResult = $fileChecker->checkFile($url, 'robots.txt');
    $sitemapResult = $fileChecker->checkFile($url, 'sitemap.xml');
    
    return response()->json([
        'target_url' => $url,
        'server_response' => [
            'status_code' => $pageData['status_code'],
            'final_url' => $pageData['final_url'],
        ],
        'html_checks' => [
            'title' => $titleResult,
            'description' => $descriptionResult,
            'h1_tag' => $headingResult['h1'],
            'headings_structure' => $headingResult['structure'],
            'external_links' => $linksResult,
            'microdata' => $microdataResult
        ],
        'seo_files' => [
            'robots' => $robotsResult,
            'sitemap' => $sitemapResult,
        ]
    ]);
});

use App\Http\Controllers\AuditHistoryController;

Route::get('/history', [AuditHistoryController::class, 'index'])->name('history.index');
Route::get('/history/{id}', [AuditHistoryController::class, 'show'])->name('history.show');
Route::post('/history/{id}/save', [AuditHistoryController::class, 'saveToFavorites'])->name('history.save');

Route::get('/test-audit', function () {
    $service = new \App\Services\Seo\ReportStorageService();
    $audit = $service->createAudit();

    $service->saveResultForUrl($audit, 'https://example.com', [
        'http_code' => 200,
        'h1' => ['valid' => true, 'reason' => null],
        'title' => ['valid' => true, 'reason' => null, 'length' => 45],
        'description' => ['valid' => true, 'reason' => null, 'length' => 120],
        'headings_valid' => true,
        'external_links_count' => 5,
        'external_links_nofollow' => 2,
        'external_links_dofollow' => 3,
        'og_marker' => true,
        'schema_marker' => true,
        'schema_formats' => ['JSON-LD', 'Microdata'],
        'robots_marker' => true,
        'sitemap_marker' => true,
    ]);

    $service->completeAudit($audit);
    return 'Тестовая проверка создана. Перейди на /history';
});