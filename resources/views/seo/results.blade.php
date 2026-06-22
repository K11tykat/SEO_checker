<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Результаты проверки</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <style>
        body { background: #f0f2f5; }
        .navbar-custom { background: linear-gradient(135deg, #1a1a2e, #16213e); }
        .navbar-custom .navbar-brand { color: #fff; font-weight: bold; font-size: 24px; }
        .navbar-custom .nav-link { color: rgba(255,255,255,0.8); }
        .navbar-custom .nav-link:hover { color: #fff; }
        .result-card { border: none; border-radius: 12px; box-shadow: 0 2px 12px rgba(0,0,0,0.08); transition: transform 0.2s; }
        .result-card:hover { transform: translateY(-2px); }
        .result-card .card-header { background: #f8f9fa; border-bottom: 1px solid #e9ecef; border-radius: 12px 12px 0 0; }
        .result-card .card-header h3 { font-size: 16px; color: #1a1a2e; word-break: break-all; }
        .badge-status { font-size: 12px; padding: 4px 12px; border-radius: 20px; }
        .badge-green { background: #d4edda; color: #155724; }
        .badge-red { background: #f8d7da; color: #721c24; }
        .badge-gray { background: #e9ecef; color: #6c757d; }
        .info-row { display: flex; padding: 6px 0; border-bottom: 1px solid #f1f3f5; }
        .info-row .label { width: 180px; font-weight: 600; color: #495057; flex-shrink: 0; }
        .info-row .value { flex: 1; }
        .btn-gradient-primary { background: linear-gradient(135deg, #667eea, #764ba2); color: white; border: none; }
        .btn-gradient-primary:hover { background: linear-gradient(135deg, #5a67d8, #6b3f9e); color: white; }
        .btn-gradient-success { background: linear-gradient(135deg, #11998e, #38ef7d); color: white; border: none; }
        .btn-gradient-success:hover { background: linear-gradient(135deg, #0e7a6e, #2bc96a); color: white; }
        .btn-sm { padding: 5px 15px; font-size: 12px; }
        .footer { text-align: center; padding: 20px; color: #a0aec0; font-size: 14px; border-top: 1px solid #e2e8f0; margin-top: 40px; }
        .url-list { display: flex; flex-wrap: wrap; gap: 6px; }
        .url-tag { background: #e9ecef; padding: 2px 10px; border-radius: 12px; font-size: 12px; color: #495057; }
        .card-actions { margin-top: 15px; padding-top: 15px; border-top: 1px solid #e9ecef; display: flex; gap: 10px; flex-wrap: wrap; }
    </style>
</head>
<body>

    <nav class="navbar navbar-expand-lg navbar-custom">
        <div class="container">
            <a class="navbar-brand" href="{{ route('history.index') }}">
                <i class="bi bi-search-heart"></i> SEO Checker
            </a>
            <div class="collapse navbar-collapse">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item"><a class="nav-link" href="{{ route('history.index') }}"><i class="bi bi-clock-history"></i> История</a></li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container py-4">

        <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
            <div>
                <h2 class="mb-0"><i class="bi bi-clipboard-check text-success"></i> Результаты проверки</h2>
                <p class="text-muted small mb-0"><i class="bi bi-calendar3"></i> {{ $audit->created_at }}</p>
            </div>
            <span class="badge badge-green fs-6">Завершена</span>
        </div>

        <div class="d-flex flex-wrap gap-2 mb-4">
            <form action="{{ route('results.save', $audit->id) }}" method="POST" style="display:inline;">
                @csrf
                <button type="submit" class="btn btn-gradient-success"><i class="bi bi-bookmark-plus"></i> Сохранить все отчеты</button>
            </form>
            <a href="{{ route('results.pdf', $audit->id) }}" class="btn btn-gradient-primary" target="_blank"><i class="bi bi-file-pdf"></i> Скачать PDF</a>
            <a href="{{ route('history.index') }}" class="btn btn-outline-secondary"><i class="bi bi-arrow-left"></i> Вернуться к истории</a>
        </div>

        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show"><i class="bi bi-check-circle"></i> {{ session('success') }} <button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
        @endif
        @if(session('error'))
            <div class="alert alert-danger alert-dismissible fade show"><i class="bi bi-exclamation-triangle"></i> {{ session('error') }} <button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
        @endif
        @if(session('info'))
            <div class="alert alert-info alert-dismissible fade show"><i class="bi bi-info-circle"></i> {{ session('info') }} <button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
        @endif

        @foreach($audit->urls as $url)
            <div class="card result-card mb-4">
                <div class="card-header d-flex justify-content-between align-items-center flex-wrap">
                    <h3 class="mb-0"><i class="bi bi-link-45deg text-primary"></i> {{ $url->url }}</h3>
                    <span class="badge bg-secondary">HTTP {{ $url->http_code }}</span>
                </div>
                <div class="card-body">
                    @if($url->redirect_final_url)
                        <p><i class="bi bi-arrow-right"></i> Редирект на: <strong>{{ $url->redirect_final_url }}</strong></p>
                    @endif

                    @if($url->result)
                        <div class="info-row"><span class="label">H1</span><span class="value"><span class="badge {{ $url->result->h1_is_valid ? 'badge-green' : 'badge-red' }}">{{ $url->result->h1_is_valid ? 'Зеленый' : 'Красный' }}</span> @if($url->result->h1_error_reason) <small class="text-muted">({{ $url->result->h1_error_reason }})</small> @endif</span></div>
                        <div class="info-row"><span class="label">Title</span><span class="value"><span class="badge {{ $url->result->title_is_valid ? 'badge-green' : 'badge-red' }}">{{ $url->result->title_is_valid ? 'Зеленый' : 'Красный' }}</span> @if($url->result->title_error_reason) <small class="text-muted">({{ $url->result->title_error_reason }})</small> @endif <small class="text-muted">Длина: {{ $url->result->title_length }}</small></span></div>
                        <div class="info-row"><span class="label">Description</span><span class="value"><span class="badge {{ $url->result->description_is_valid ? 'badge-green' : 'badge-red' }}">{{ $url->result->description_is_valid ? 'Зеленый' : 'Красный' }}</span> @if($url->result->description_error_reason) <small class="text-muted">({{ $url->result->description_error_reason }})</small> @endif <small class="text-muted">Длина: {{ $url->result->description_length }}</small></span></div>
                        <div class="info-row"><span class="label">Структура заголовков</span><span class="value"><span class="badge {{ $url->result->headings_valid ? 'badge-green' : 'badge-red' }}">{{ $url->result->headings_valid ? 'Валидна' : 'Невалидна' }}</span></span></div>
                        <div class="info-row"><span class="label">Внешние ссылки</span><span class="value">Всего: {{ $url->result->external_links_count }}, nofollow: {{ $url->result->external_links_nofollow }}, dofollow: {{ $url->result->external_links_dofollow }}</span></div>
                        <div class="info-row"><span class="label">Open Graph</span><span class="value"><span class="badge {{ $url->result->og_marker ? 'badge-green' : 'badge-red' }}">{{ $url->result->og_marker ? 'Есть' : 'Нет' }}</span></span></div>
                        <div class="info-row"><span class="label">Schema.org</span><span class="value"><span class="badge {{ $url->result->schema_marker ? 'badge-green' : 'badge-red' }}">{{ $url->result->schema_marker ? 'Есть' : 'Нет' }}</span> @if($url->result->schema_formats) <small class="text-muted">Форматы: {{ implode(', ', $url->result->schema_formats) }}</small> @endif</span></div>
                        <div class="info-row"><span class="label">robots.txt</span><span class="value"><span class="badge {{ $url->result->robots_marker ? 'badge-green' : 'badge-red' }}">{{ $url->result->robots_marker ? 'Есть' : 'Нет' }}</span></span></div>
                        <div class="info-row"><span class="label">sitemap.xml</span><span class="value"><span class="badge {{ $url->result->sitemap_marker ? 'badge-green' : 'badge-red' }}">{{ $url->result->sitemap_marker ? 'Есть' : 'Нет' }}</span></span></div>

                        <!-- КНОПКА ДЛЯ КАЖДОГО ОТЧЕТА -->
                        <div class="card-actions">
                            <form action="{{ route('save.report', $audit->id) }}" method="POST" style="display:inline;">
                                @csrf
                                <button type="submit" class="btn btn-success btn-sm">
                                    <i class="bi bi-bookmark-plus"></i> Сохранить отчёт
                                </button>
                            </form>
                        </div>

                    @else
                        <p class="text-danger"><i class="bi bi-exclamation-triangle"></i> Нет данных</p>
                    @endif
                </div>
            </div>
        @endforeach

        <div class="footer">
            <i class="bi bi-robot"></i> SEO Checker v1.0
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>