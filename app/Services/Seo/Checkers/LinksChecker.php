<?php

namespace App\Services\Seo\Checkers;

use Symfony\Component\DomCrawler\Crawler;

class LinksChecker
{
    public function check(Crawler $crawler, string $baseUrl): array
    {
        // Получаем хост текущего проверяемого сайта (например, laravel.com)
        $host = parse_url($baseUrl, PHP_URL_HOST);

        $externalCount = 0;
        $nofollowCount = 0;
        $dofollowCount = 0;

        // Фильтруем все теги <a>, у которых есть атрибут href
        $crawler->filter('a[href]')->each(function (Crawler $node) use ($host, &$externalCount, &$nofollowCount, &$dofollowCount) {
            $href = $node->attr('href');
            $parsedHref = parse_url($href);
            $hrefHost = $parsedHref['host'] ?? null;

            // Ссылка внешняя, если у нее есть хост и он не совпадает с хостом нашего сайта
            if ($hrefHost && $hrefHost !== $host) {
                $externalCount++;

                $rel = $node->attr('rel');
                // Проверяем наличие nofollow в атрибуте rel
                if ($rel && str_contains(strtolower($rel), 'nofollow')) {
                    $nofollowCount++;
                } else {
                    $dofollowCount++;
                }
            }
        });

        return [
            'external_links_count' => $externalCount,
            'nofollow_count' => $nofollowCount,
            'dofollow_count' => $dofollowCount
        ];
    }
}