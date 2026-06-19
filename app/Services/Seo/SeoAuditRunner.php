<?php

namespace App\Services\Seo;

use App\Models\Audit;
use App\Services\Seo\Checkers\DescriptionChecker;
use App\Services\Seo\Checkers\HeadingChecker;
use App\Services\Seo\Checkers\LinksChecker;
use App\Services\Seo\Checkers\MicrodataChecker;
use App\Services\Seo\Checkers\TitleChecker;

/**
 * Оркестратор SEO-проверки: связывает загрузку страницы, чекеры,
 * нормализацию и сохранение в БД в единый сквозной поток.
 */
class SeoAuditRunner
{
    public function __construct(
        private PageDownloader $downloader,
        private RootFilesChecker $rootFilesChecker,
        private TitleChecker $titleChecker,
        private DescriptionChecker $descriptionChecker,
        private HeadingChecker $headingChecker,
        private LinksChecker $linksChecker,
        private MicrodataChecker $microdataChecker,
        private ResultNormalizer $normalizer,
        private ReportStorageService $storage,
    ) {}

    /**
     * Прогоняет все URL через проверки и сохраняет результат как Audit.
     *
     * @param  string[]  $urls
     */
    public function run(array $urls, ?int $userId = null): Audit
    {
        $audit = $this->storage->createAudit($userId);

        foreach ($urls as $url) {
            $data = $this->checkUrl($url);
            $this->storage->saveResultForUrl($audit, $url, $data);
        }

        $this->storage->completeAudit($audit);

        return $audit->load('urls.result');
    }

    /**
     * Выполняет все проверки одного URL и возвращает данные в формате хранилища.
     */
    private function checkUrl(string $url): array
    {
        $pageData = $this->downloader->download($url);

        if (! $pageData['success']) {
            return $this->normalizer->normalizeFailure($pageData);
        }

        $crawler = $pageData['crawler'];

        $checks = [
            'title' => $this->titleChecker->check($crawler),
            'description' => $this->descriptionChecker->check($crawler),
            'heading' => $this->headingChecker->check($crawler),
            'links' => $this->linksChecker->check($crawler, $url),
            'microdata' => $this->microdataChecker->check($crawler),
            'robots' => $this->rootFilesChecker->checkFile($url, 'robots.txt'),
            'sitemap' => $this->rootFilesChecker->checkFile($url, 'sitemap.xml'),
        ];

        return $this->normalizer->normalize($url, $pageData, $checks);
    }
}
