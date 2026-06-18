<?php

namespace App\Services\Seo;

use Illuminate\Support\Facades\Http;

class RootFilesChecker
{

    public function checkFile(string $baseUrl, string $fileName): array
    {
        $parsedUrl = parse_url($baseUrl);
        $scheme = $parsedUrl['scheme'] ?? 'http';
        $host = $parsedUrl['host'] ?? '';
        
        if (empty($host)) {
            return [
                'marker' => 'red',
                'error' => 'Некорректный URL для проверки файла ' . $fileName
            ];
        }

        $rootUrl = "{$scheme}://{$host}/" . ltrim($fileName, '/');

        try {
            $response = Http::timeout(5)->get($rootUrl);

            if ($response->successful()) {
                return [
                    'marker' => 'green',
                    'error' => null
                ];
            }

            return [
                'marker' => 'red',
                'error' => "Файл не найден. Код ответа сервера: " . $response->status()
            ];

        } catch (\Exception $e) {
            return [
                'marker' => 'red',
                'error' => "Ошибка при запросе к файлу: " . $e->getMessage()
            ];
        }
    }
}