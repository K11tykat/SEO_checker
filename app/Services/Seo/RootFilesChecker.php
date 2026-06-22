<?php

namespace App\Services\Seo;

use Illuminate\Support\Facades\Http;

class RootFilesChecker
{
    public function checkFile($url, $filename)
    {
        // Получаем корневой URL
        $parsedUrl = parse_url($url);
        $baseUrl = $parsedUrl['scheme'] . '://' . $parsedUrl['host'];
        
        // Формируем полный URL к файлу
        $fileUrl = rtrim($baseUrl, '/') . '/' . ltrim($filename, '/');
        
        try {
            $response = Http::timeout(5)->get($fileUrl);
            
            // Проверяем, что файл существует (код 200)
            $exists = $response->status() === 200;
            
            return [
                'exists' => $exists,
                'url' => $fileUrl,
                'status_code' => $response->status()
            ];
        } catch (\Exception $e) {
            return [
                'exists' => false,
                'url' => $fileUrl,
                'error' => $e->getMessage()
            ];
        }
    }
}