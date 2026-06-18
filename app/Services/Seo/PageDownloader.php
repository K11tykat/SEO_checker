<?php

namespace App\Services\Seo;

use Illuminate\Support\Facades\Http;
use Symfony\Component\DomCrawler\Crawler;
use Exception;

class PageDownloader
{

    public function download(string $url): array
    {
        try {
            $response = Http::withOptions([
                'allow_redirects' => [
                    'track_redirects' => true
                ],
                'timeout' => 10,
            ])->get($url);

            $statusCode = $response->status();
            
            $redirectLog = $response->handlerStats()['redirect_log'] ?? [];
            $finalUrl = !empty($redirectLog) ? (string) end($redirectLog) : $url;

            return [
                'success' => true,
                'status_code' => $statusCode,
                'final_url' => $finalUrl,
                'html' => $response->body(),
                'crawler' => new Crawler($response->body()),
                'error' => null
            ];

        } catch (Exception $e) {
            return [
                'success' => false,
                'status_code' => 0,
                'final_url' => $url,
                'html' => '',
                'crawler' => null,
                'error' => 'Не удалось подключиться к серверу: ' . $e->getMessage()
            ];
        }
    }
}