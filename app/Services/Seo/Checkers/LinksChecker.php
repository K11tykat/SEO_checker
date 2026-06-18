<?php

namespace App\Services\Seo\Checkers;

use Symfony\Component\DomCrawler\Crawler;

class LinksChecker
{
    public function check(Crawler $crawler, string $baseUrl): array
    {
        $host = parse_url($baseUrl, PHP_URL_HOST);

        $externalCount = 0;
        $nofollowCount = 0;
        $dofollowCount = 0;

        $crawler->filter('a[href]')->each(function (Crawler $node) use ($host, &$externalCount, &$nofollowCount, &$dofollowCount) {
            $href = $node->attr('href');
            $parsedHref = parse_url($href);
            $hrefHost = $parsedHref['host'] ?? null;

            if ($hrefHost && $hrefHost !== $host) {
                $externalCount++;

                $rel = $node->attr('rel');
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