<?php

namespace App\Services\Seo\Checkers;

use Symfony\Component\DomCrawler\Crawler;

class DescriptionChecker
{
    public function check(Crawler $crawler): array
    {
        $tags = $crawler->filter('meta[name="description"]');
        $count = $tags->count();

        if ($count === 0) {
            return [
                'marker' => 'red',
                'length' => 0,
                'error' => 'Мета-тег <description> отсутствует на странице'
            ];
        }

        if ($count > 1) {
            $firstDescription = $tags->first()->attr('content') ?? '';
            return [
                'marker' => 'red',
                'length' => mb_strlen($firstDescription),
                'error' => "Мета-тег <description> найден в нескольких экземплярах (всего: {$count})"
            ];
        }

        $descriptionText = $tags->attr('content') ?? '';

        return [
            'marker' => 'green',
            'length' => mb_strlen($descriptionText),
            'error' => null
        ];
    }
}