<?php

namespace App\Services\Seo\Checkers;

use Symfony\Component\DomCrawler\Crawler;

class MicrodataChecker
{
    public function check(Crawler $crawler): array
    {
        return [
            'open_graph' => $this->checkOpenGraph($crawler),
            'schema_org' => $this->checkSchemaOrg($crawler)
        ];
    }

    private function checkOpenGraph(Crawler $crawler): array
    {
        // Ищем мета-теги, у которых свойство начинается с "og:"
        $ogTags = $crawler->filter('meta[property^="og:"]');

        if ($ogTags->count() > 0) {
            return ['marker' => 'green', 'error' => null];
        }

        return ['marker' => 'red', 'error' => 'Микроразметка Open Graph не найдена'];
    }

    private function checkSchemaOrg(Crawler $crawler): array
    {
        $formats = [];

        // 1. Проверяем формат JSON-LD
        $jsonLdTags = $crawler->filter('script[type="application/ld+json"]');
        if ($jsonLdTags->count() > 0) {
            $formats[] = 'JSON-LD';
        }

        // 2. Проверяем формат Microdata (наличие атрибута itemscope)
        $microdataTags = $crawler->filter('[itemscope]');
        if ($microdataTags->count() > 0) {
            $formats[] = 'Microdata';
        }

        // 3. Проверяем формат RDFa (наличие атрибута vocab или typeof)
        $rdfaTags = $crawler->filter('[vocab], [typeof]');
        if ($rdfaTags->count() > 0) {
            $formats[] = 'RDFa';
        }

        if (!empty($formats)) {
            return [
                'marker' => 'green',
                'formats' => $formats,
                'error' => null
            ];
        }

        return [
            'marker' => 'red',
            'formats' => [],
            'error' => 'Микроразметка Schema.org не найдена'
        ];
    }
}