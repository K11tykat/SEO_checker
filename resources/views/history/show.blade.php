<!DOCTYPE html>
<html>
<head>
    <title>Детали проверки #{{ $audit->id }}</title>
    <style>
        body { font-family: Arial; max-width: 900px; margin: 30px auto; padding: 20px; }
        .card { border: 1px solid #ddd; padding: 15px; margin: 15px 0; border-radius: 8px; }
        .green { color: green; font-weight: bold; }
        .red { color: red; font-weight: bold; }
        .info { display: flex; padding: 5px 0; border-bottom: 1px solid #eee; }
        .label { width: 180px; font-weight: bold; color: #555; }
        .back { display: inline-block; margin-top: 20px; color: #0066cc; text-decoration: none; }
        .back:hover { text-decoration: underline; }
    </style>
</head>
<body>
    <h1>Детали проверки #{{ $audit->id }}</h1>
    <p>Дата: {{ $audit->created_at }}</p>

    @foreach($audit->urls as $url)
        <div class="card">
            <h3>{{ $url->url }}</h3>
            <p>HTTP код: {{ $url->http_code }}</p>

            @if($url->result)
                <div class="info">
                    <span class="label">H1:</span>
                    <span class="{{ $url->result->h1_is_valid ? 'green' : 'red' }}">
                        {{ $url->result->h1_is_valid ? 'Зеленый' : 'Красный' }}
                    </span>
                    @if($url->result->h1_error_reason)
                        <span style="color:#999; font-size:14px;">({{ $url->result->h1_error_reason }})</span>
                    @endif
                </div>

                <div class="info">
                    <span class="label">Title:</span>
                    <span class="{{ $url->result->title_is_valid ? 'green' : 'red' }}">
                        {{ $url->result->title_is_valid ? 'Зеленый' : 'Красный' }}
                    </span>
                    @if($url->result->title_error_reason)
                        <span style="color:#999; font-size:14px;">({{ $url->result->title_error_reason }})</span>
                    @endif
                    <span style="color:#666; font-size:14px;">Длина: {{ $url->result->title_length }}</span>
                </div>

                <div class="info">
                    <span class="label">Description:</span>
                    <span class="{{ $url->result->description_is_valid ? 'green' : 'red' }}">
                        {{ $url->result->description_is_valid ? 'Зеленый' : 'Красный' }}
                    </span>
                    @if($url->result->description_error_reason)
                        <span style="color:#999; font-size:14px;">({{ $url->result->description_error_reason }})</span>
                    @endif
                    <span style="color:#666; font-size:14px;">Длина: {{ $url->result->description_length }}</span>
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
                        <span style="color:#666; font-size:14px;">Форматы: {{ implode(', ', $url->result->schema_formats) }}</span>
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
                <p style="color:#f44336;">Нет данных</p>
            @endif
        </div>
    @endforeach

    <a href="{{ route('history.index') }}" class="back">← Назад к истории</a>
</body>
</html>