<?php

namespace App\Services\Seo\Checkers;

use Symfony\Component\DomCrawler\Crawler;

class HeadingChecker
{
    public function check(Crawler $crawler): array
    {
        // 1. Проверка одиночного H1
        $h1Tags = $crawler->filter('h1');
        $h1Count = $h1Tags->count();
        
        $h1Result = [
            'marker' => 'green',
            'error' => null
        ];

        if ($h1Count === 0) {
            $h1Result = ['marker' => 'red', 'error' => 'Заголовок h1 отсутствует на странице'];
        } elseif ($h1Count > 1) {
            $h1Result = ['marker' => 'red', 'error' => "Заголовок h1 найден в нескольких экземплярах (всего: {$h1Count})"];
        }

        // 2. Проверка структуры заголовков h1-h6
        $structureResult = $this->checkStructure($crawler);

        return [
            'h1' => $h1Result,
            'structure' => $structureResult
        ];
    }

    private function checkStructure(Crawler $crawler): array
    {
        // Собираем все заголовки по порядку их появления в DOM
        $headings = $crawler->filter('h1, h2, h3, h4, h5, h6')->each(function (Crawler $node) {
            return [
                'level' => (int) substr($node->nodeName(), 1),
                'text' => $node->text()
            ];
        });

        if (empty($headings)) {
            return ['marker' => 'green', 'error' => null]; // Заголовков нет — нарушений нет
        }

        // Правило 1: Первым должен идти h1
        if ($headings[0]['level'] !== 1) {
            return ['marker' => 'red', 'error' => "Структура невалидна: первым на странице встретился заголовок h{$headings[0]['level']}, а должен быть h1"];
        }

        // Правило 2: Соблюдение иерархии (например, h4 не может идти сразу после h2, минуя h3)
        $maxAllowedLevel = 1;

        foreach ($headings as $heading) {
            $currentLevel = $heading['level'];

            if ($currentLevel > $maxAllowedLevel + 1) {
                return [
                    'marker' => 'red',
                    'error' => "Нарушена иерархия: заголовок h{$currentLevel} идет в обход последовательности (ожидался h" . ($maxAllowedLevel + 1) . " или выше)"
                ];
            }

            // Обновляем максимальный уровень, до которого мы "докопались"
            if ($currentLevel > $maxAllowedLevel) {
                $maxAllowedLevel = $currentLevel;
            }
        }

        return ['marker' => 'green', 'error' => null];
    }
}