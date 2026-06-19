{{--
    Карточка результата проверки одного URL.
    Ожидает: $url (App\Models\AuditUrl c загруженным ->result).
    Необязательно: $selectable (bool) — показывать ли чекбокс выбора для PDF/сохранения.
--}}
@php($r = $url->result)
<div class="card">
    @if(!empty($selectable))
        <div class="checkbox-cell">
            <label>
                <input type="checkbox" name="url_ids[]" value="{{ $url->id }}" checked>
                Включить этот отчёт
            </label>
        </div>
    @endif

    <h3 style="margin:6px 0;">{{ $url->url }}</h3>

    <div class="info">
        <span class="label">Код ответа сервера:</span>
        <span class="{{ $url->http_code >= 200 && $url->http_code < 400 ? 'green' : 'red' }}">
            {{ $url->http_code ?: '—' }}
        </span>
        @if($url->redirect_final_url)
            <span class="muted">Редирект на: {{ $url->redirect_final_url }}</span>
        @endif
    </div>

    @if($r)
        <div class="info">
            <span class="label">Заголовок H1:</span>
            <span class="{{ $r->h1_is_valid ? 'green' : 'red' }}">{{ $r->h1_is_valid ? 'Зелёный' : 'Красный' }}</span>
            @if($r->h1_error_reason)<span class="muted">({{ $r->h1_error_reason }})</span>@endif
        </div>

        <div class="info">
            <span class="label">Структура заголовков:</span>
            <span class="{{ $r->headings_valid ? 'green' : 'red' }}">{{ $r->headings_valid ? 'Валидна' : 'Невалидна' }}</span>
        </div>

        <div class="info">
            <span class="label">Тег &lt;title&gt;:</span>
            <span class="{{ $r->title_is_valid ? 'green' : 'red' }}">{{ $r->title_is_valid ? 'Зелёный' : 'Красный' }}</span>
            @if($r->title_error_reason)<span class="muted">({{ $r->title_error_reason }})</span>@endif
            <span class="muted">Длина: {{ $r->title_length }}</span>
        </div>

        <div class="info">
            <span class="label">Мета &lt;description&gt;:</span>
            <span class="{{ $r->description_is_valid ? 'green' : 'red' }}">{{ $r->description_is_valid ? 'Зелёный' : 'Красный' }}</span>
            @if($r->description_error_reason)<span class="muted">({{ $r->description_error_reason }})</span>@endif
            <span class="muted">Длина: {{ $r->description_length }}</span>
        </div>

        <div class="info">
            <span class="label">Внешние ссылки:</span>
            <span>Всего: {{ $r->external_links_count }}, nofollow: {{ $r->external_links_nofollow }}, dofollow: {{ $r->external_links_dofollow }}</span>
        </div>

        <div class="info">
            <span class="label">Open Graph:</span>
            <span class="{{ $r->og_marker ? 'green' : 'red' }}">{{ $r->og_marker ? 'Есть' : 'Нет' }}</span>
        </div>

        <div class="info">
            <span class="label">Schema.org:</span>
            <span class="{{ $r->schema_marker ? 'green' : 'red' }}">{{ $r->schema_marker ? 'Есть' : 'Нет' }}</span>
            @if($r->schema_formats)<span class="muted">Форматы: {{ implode(', ', $r->schema_formats) }}</span>@endif
        </div>

        <div class="info">
            <span class="label">robots.txt:</span>
            <span class="{{ $r->robots_marker ? 'green' : 'red' }}">{{ $r->robots_marker ? 'Есть' : 'Нет' }}</span>
        </div>

        <div class="info">
            <span class="label">sitemap.xml:</span>
            <span class="{{ $r->sitemap_marker ? 'green' : 'red' }}">{{ $r->sitemap_marker ? 'Есть' : 'Нет' }}</span>
        </div>
    @else
        <p class="red">Нет данных по проверке.</p>
    @endif
</div>
