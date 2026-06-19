<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="utf-8">
    <style>
        /* DejaVu Sans входит в dompdf и поддерживает кириллицу */
        * { font-family: 'DejaVu Sans', sans-serif; }
        body { color: #222; font-size: 12px; }
        h1 { font-size: 20px; margin-bottom: 4px; }
        .meta { color: #666; font-size: 11px; margin-bottom: 16px; }
        .card { border: 1px solid #ccc; border-radius: 6px; padding: 10px 12px; margin-bottom: 14px; page-break-inside: avoid; }
        .url { font-size: 14px; font-weight: bold; margin-bottom: 8px; word-break: break-all; }
        table { width: 100%; border-collapse: collapse; }
        td { padding: 4px 0; border-bottom: 1px solid #eee; vertical-align: top; }
        td.label { width: 38%; font-weight: bold; color: #555; }
        .green { color: #1a7f37; font-weight: bold; }
        .red { color: #d1242f; font-weight: bold; }
        .muted { color: #888; }
    </style>
</head>
<body>
    <h1>Отчёт SEO-проверки #{{ $audit->id }}</h1>
    <div class="meta">Дата и время проверки: {{ $audit->created_at->format('d.m.Y H:i') }}</div>

    @foreach($urls as $url)
        @php($r = $url->result)
        <div class="card">
            <div class="url">{{ $url->url }}</div>
            <table>
                <tr>
                    <td class="label">Код ответа сервера</td>
                    <td>
                        <span class="{{ $url->http_code >= 200 && $url->http_code < 400 ? 'green' : 'red' }}">{{ $url->http_code ?: '—' }}</span>
                        @if($url->redirect_final_url)<span class="muted"> → редирект на: {{ $url->redirect_final_url }}</span>@endif
                    </td>
                </tr>
                @if($r)
                    <tr>
                        <td class="label">Заголовок H1</td>
                        <td>
                            <span class="{{ $r->h1_is_valid ? 'green' : 'red' }}">{{ $r->h1_is_valid ? 'Зелёный' : 'Красный' }}</span>
                            @if($r->h1_error_reason)<span class="muted"> ({{ $r->h1_error_reason }})</span>@endif
                        </td>
                    </tr>
                    <tr>
                        <td class="label">Структура заголовков</td>
                        <td><span class="{{ $r->headings_valid ? 'green' : 'red' }}">{{ $r->headings_valid ? 'Валидна' : 'Невалидна' }}</span></td>
                    </tr>
                    <tr>
                        <td class="label">Тег &lt;title&gt;</td>
                        <td>
                            <span class="{{ $r->title_is_valid ? 'green' : 'red' }}">{{ $r->title_is_valid ? 'Зелёный' : 'Красный' }}</span>
                            <span class="muted"> · длина: {{ $r->title_length }}</span>
                            @if($r->title_error_reason)<span class="muted"> ({{ $r->title_error_reason }})</span>@endif
                        </td>
                    </tr>
                    <tr>
                        <td class="label">Мета &lt;description&gt;</td>
                        <td>
                            <span class="{{ $r->description_is_valid ? 'green' : 'red' }}">{{ $r->description_is_valid ? 'Зелёный' : 'Красный' }}</span>
                            <span class="muted"> · длина: {{ $r->description_length }}</span>
                            @if($r->description_error_reason)<span class="muted"> ({{ $r->description_error_reason }})</span>@endif
                        </td>
                    </tr>
                    <tr>
                        <td class="label">Внешние ссылки</td>
                        <td>Всего: {{ $r->external_links_count }}, nofollow: {{ $r->external_links_nofollow }}, dofollow: {{ $r->external_links_dofollow }}</td>
                    </tr>
                    <tr>
                        <td class="label">Open Graph</td>
                        <td><span class="{{ $r->og_marker ? 'green' : 'red' }}">{{ $r->og_marker ? 'Есть' : 'Нет' }}</span></td>
                    </tr>
                    <tr>
                        <td class="label">Schema.org</td>
                        <td>
                            <span class="{{ $r->schema_marker ? 'green' : 'red' }}">{{ $r->schema_marker ? 'Есть' : 'Нет' }}</span>
                            @if($r->schema_formats)<span class="muted"> · форматы: {{ implode(', ', $r->schema_formats) }}</span>@endif
                        </td>
                    </tr>
                    <tr>
                        <td class="label">robots.txt</td>
                        <td><span class="{{ $r->robots_marker ? 'green' : 'red' }}">{{ $r->robots_marker ? 'Есть' : 'Нет' }}</span></td>
                    </tr>
                    <tr>
                        <td class="label">sitemap.xml</td>
                        <td><span class="{{ $r->sitemap_marker ? 'green' : 'red' }}">{{ $r->sitemap_marker ? 'Есть' : 'Нет' }}</span></td>
                    </tr>
                @else
                    <tr><td colspan="2" class="red">Нет данных по проверке.</td></tr>
                @endif
            </table>
        </div>
    @endforeach
</body>
</html>
