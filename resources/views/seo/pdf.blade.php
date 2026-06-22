<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>SEO Отчет</title>
    <style>
        body { font-family: DejaVu Sans, Arial, sans-serif; font-size: 12px; padding: 20px; }
        h1 { color: #333; border-bottom: 2px solid #0066cc; padding-bottom: 10px; }
        h2 { color: #0066cc; margin-top: 20px; border-bottom: 1px solid #ddd; padding-bottom: 5px; }
        .card { border: 1px solid #ddd; padding: 15px; margin: 10px 0; border-radius: 5px; }
        .green { color: #28a745; }
        .red { color: #dc3545; }
        .info { padding: 5px 0; border-bottom: 1px solid #eee; }
        .label { font-weight: bold; color: #555; display: inline-block; width: 180px; }
        .footer { margin-top: 30px; font-size: 10px; color: #999; text-align: center; border-top: 1px solid #ddd; padding-top: 10px; }
        .badge-green { color: #155724; }
        .badge-red { color: #721c24; }
    </style>
</head>
<body>
    <h1>Отчет SEO-проверки</h1>
    <p><strong>ID проверки:</strong> #{{ $audit->id }}</p>
    <p><strong>Дата:</strong> {{ $audit->created_at }}</p>
    <p><strong>Статус:</strong> Завершена</p>

    @foreach($audit->urls as $url)
        <div class="card">
            <h2>{{ $url->url }}</h2>
            <p><strong>HTTP код:</strong> {{ $url->http_code }}</p>
            @if($url->redirect_final_url)
                <p><strong>Редирект на:</strong> {{ $url->redirect_final_url }}</p>
            @endif

            @if($url->result)
                <div class="info">
                    <span class="label">H1:</span>
                    <span class="{{ $url->result->h1_is_valid ? 'green' : 'red' }}">
                        {{ $url->result->h1_is_valid ? 'Зеленый' : 'Красный' }}
                    </span>
                    @if($url->result->h1_error_reason)
                        <span style="color:#999;">({{ $url->result->h1_error_reason }})</span>
                    @endif
                </div>

                <div class="info">
                    <span class="label">Title:</span>
                    <span class="{{ $url->result->title_is_valid ? 'green' : 'red' }}">
                        {{ $url->result->title_is_valid ? 'Зеленый' : 'Красный' }}
                    </span>
                    @if($url->result->title_error_reason)
                        <span style="color:#999;">({{ $url->result->title_error_reason }})</span>
                    @endif
                    <span style="color:#666;">Длина: {{ $url->result->title_length }}</span>
                </div>

                <div class="info">
                    <span class="label">Description:</span>
                    <span class="{{ $url->result->description_is_valid ? 'green' : 'red' }}">
                        {{ $url->result->description_is_valid ? 'Зеленый' : 'Красный' }}
                    </span>
                    @if($url->result->description_error_reason)
                        <span style="color:#999;">({{ $url->result->description_error_reason }})</span>
                    @endif
                    <span style="color:#666;">Длина: {{ $url->result->description_length }}</span>
                </div>

                <div class="info">
                    <span class="label">Структура заголовков:</span>
                    <span class="{{ $url->result->headings_valid ? 'green' : 'red' }}">
                        {{ $url->result->headings_valid ? 'Валидна' : 'Невалидна' }}
                    </span>
                </div>

                <div class="info">
                    <span class="label">Внешние ссылки:</span>
                    <span>Всего: {{ $url->result->external_links_count }}, nofollow: {{ $url->result->external_links_nofollow }}, dofollow: {{ $url->result->external_links_dofollow }}</span>
                </div>

                <div class="info">
                    <span class="label">Open Graph:</span>
                    <span class="{{ $url->result->og_marker ? 'green' : 'red' }}">
                        {{ $url->result->og_marker ? 'Есть' : 'Нет' }}
                    </span>
                </div>

                <div class="info">
                    <span class="label">Schema.org:</span>
                    <span class="{{ $url->result->schema_marker ? 'green' : 'red' }}">
                        {{ $url->result->schema_marker ? 'Есть' : 'Нет' }}
                    </span>
                    @if($url->result->schema_formats)
                        <span style="color:#666;">Форматы: {{ implode(', ', $url->result->schema_formats) }}</span>
                    @endif
                </div>

                <div class="info">
                    <span class="label">robots.txt:</span>
                    <span class="{{ $url->result->robots_marker ? 'green' : 'red' }}">
                        {{ $url->result->robots_marker ? 'Есть' : 'Нет' }}
                    </span>
                </div>

                <div class="info">
                    <span class="label">sitemap.xml:</span>
                    <span class="{{ $url->result->sitemap_marker ? 'green' : 'red' }}">
                        {{ $url->result->sitemap_marker ? 'Есть' : 'Нет' }}
                    </span>
                </div>
            @else
                <p style="color:#dc3545;">Нет данных</p>
            @endif
        </div>
    @endforeach

    <div class="footer">
        Отчет сгенерирован автоматически {{ date('Y-m-d H:i:s') }}
    </div>
</body>
</html>