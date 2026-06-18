<?php

namespace App\Services\Seo\Checkers;

use Symfony\Component\DomCrawler\Crawler;

class TitleChecker
{
    public function check(Crawler $crawler): array
    {
        $tags = $crawler->filter('title');
        $count = $tags->count();

        if ($count === 0) {
            return [
                'marker' => 'red',
                'length' => 0,
                'error' => 'Тег <title> отсутствует на странице'
            ];
        }

        if ($count > 1) {
            return [
                'marker' => 'red',
                'length' => mb_strlen($tags->first()->text()),
                'error' => "Тег <title> найден в нескольких экземплярах (всего: {$count})"
            ];
        }

        $titleText = $tags->text();

        return [
            'marker' => 'green',
            'length' => mb_strlen($titleText),
            'error' => null
        ];
    }
}