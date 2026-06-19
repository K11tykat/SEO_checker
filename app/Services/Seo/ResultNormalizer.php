<?php

namespace App\Services\Seo;

/**
 * Приводит «сырые» результаты чекеров (формат marker/error) к формату,
 * который ожидает ReportStorageService::saveResultForUrl (valid/reason + плоские ключи).
 *
 * Это слой-адаптер между двумя частями проекта, написанными с разными соглашениями.
 */
class ResultNormalizer
{
    /**
     * @param  array  $pageData  результат PageDownloader::download()
     * @param  array  $checks    [
     *     'title'       => TitleChecker::check(),
     *     'description' => DescriptionChecker::check(),
     *     'heading'     => HeadingChecker::check(),   // ['h1' => ..., 'structure' => ...]
     *     'links'       => LinksChecker::check(),
     *     'microdata'   => MicrodataChecker::check(), // ['open_graph' => ..., 'schema_org' => ...]
     *     'robots'      => RootFilesChecker::checkFile(...),
     *     'sitemap'     => RootFilesChecker::checkFile(...),
     * ]
     */
    public function normalize(string $url, array $pageData, array $checks): array
    {
        $statusCode = $pageData['status_code'] ?? 0;
        $finalUrl = $pageData['final_url'] ?? null;

        $heading = $checks['heading'] ?? [];
        $microdata = $checks['microdata'] ?? [];
        $links = $checks['links'] ?? [];

        return [
            'http_code' => $statusCode,
            // конечный URL сохраняем только если был реальный редирект
            'redirect_url' => ($finalUrl && $finalUrl !== $url) ? $finalUrl : null,

            'h1' => $this->marker($heading['h1'] ?? null),
            'title' => $this->markerWithLength($checks['title'] ?? null),
            'description' => $this->markerWithLength($checks['description'] ?? null),

            'headings_valid' => $this->isGreen($heading['structure'] ?? null),

            'external_links_count' => $links['external_links_count'] ?? 0,
            'external_links_nofollow' => $links['nofollow_count'] ?? 0,
            'external_links_dofollow' => $links['dofollow_count'] ?? 0,

            'og_marker' => $this->isGreen($microdata['open_graph'] ?? null),
            'schema_marker' => $this->isGreen($microdata['schema_org'] ?? null),
            'schema_formats' => $microdata['schema_org']['formats'] ?? [],

            'robots_marker' => $this->isGreen($checks['robots'] ?? null),
            'sitemap_marker' => $this->isGreen($checks['sitemap'] ?? null),
        ];
    }

    /**
     * Результат для случая, когда страница не загрузилась (download success === false).
     */
    public function normalizeFailure(array $pageData): array
    {
        return [
            'http_code' => $pageData['status_code'] ?? 0,
            'redirect_url' => null,
            'h1' => ['valid' => false, 'reason' => $pageData['error'] ?? 'Страница недоступна'],
            'title' => ['valid' => false, 'reason' => 'Страница недоступна', 'length' => 0],
            'description' => ['valid' => false, 'reason' => 'Страница недоступна', 'length' => 0],
            'headings_valid' => false,
            'external_links_count' => 0,
            'external_links_nofollow' => 0,
            'external_links_dofollow' => 0,
            'og_marker' => false,
            'schema_marker' => false,
            'schema_formats' => [],
            'robots_marker' => false,
            'sitemap_marker' => false,
        ];
    }

    private function marker(?array $result): array
    {
        return [
            'valid' => $this->isGreen($result),
            'reason' => $result['error'] ?? null,
        ];
    }

    private function markerWithLength(?array $result): array
    {
        return [
            'valid' => $this->isGreen($result),
            'reason' => $result['error'] ?? null,
            'length' => $result['length'] ?? 0,
        ];
    }

    private function isGreen(?array $result): bool
    {
        return ($result['marker'] ?? null) === 'green';
    }
}
