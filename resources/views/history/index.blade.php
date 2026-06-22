<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>История проверок</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <style>
        body { background: #f0f2f5; }
        .navbar-custom { background: linear-gradient(135deg, #1a1a2e, #16213e); }
        .navbar-custom .navbar-brand { color: #fff; font-weight: bold; font-size: 24px; }
        .navbar-custom .nav-link { color: rgba(255,255,255,0.8); }
        .navbar-custom .nav-link:hover { color: #fff; }
        .card-audit { transition: transform 0.2s, box-shadow 0.2s; cursor: pointer; }
        .card-audit:hover { transform: translateY(-3px); box-shadow: 0 8px 25px rgba(0,0,0,0.12); }
        .status-badge { font-size: 12px; padding: 4px 12px; border-radius: 20px; }
        .status-completed { background: #d4edda; color: #155724; }
        .status-processing { background: #fff3cd; color: #856404; }
        .status-failed { background: #f8d7da; color: #721c24; }
        .btn-primary-custom { background: linear-gradient(135deg, #667eea, #764ba2); border: none; }
        .btn-primary-custom:hover { background: linear-gradient(135deg, #5a67d8, #6b3f9e); }
        .modal-content-custom { border-radius: 16px; border: none; box-shadow: 0 20px 60px rgba(0,0,0,0.3); }
        .modal-header-custom { background: linear-gradient(135deg, #1a1a2e, #16213e); color: white; border-radius: 16px 16px 0 0; }
        .btn-close-white { filter: brightness(0) invert(1); }
        .url-item { background: #f8f9fa; border-radius: 8px; padding: 8px 12px; margin-bottom: 6px; display: flex; justify-content: space-between; align-items: center; }
        .url-item .url-text { word-break: break-all; flex: 1; }
        .empty-state { text-align: center; padding: 60px 20px; }
        .empty-state i { font-size: 64px; color: #cbd5e0; }
        .empty-state h3 { color: #4a5568; margin-top: 20px; }
        .empty-state p { color: #a0aec0; }
        .footer { text-align: center; padding: 20px; color: #a0aec0; font-size: 14px; border-top: 1px solid #e2e8f0; margin-top: 40px; }
    </style>
</head>
<body>

    <!-- НАВИГАЦИЯ -->
    <nav class="navbar navbar-expand-lg navbar-custom">
        <div class="container">
            <a class="navbar-brand" href="{{ route('history.index') }}">
                <i class="bi bi-search-heart"></i> SEO Checker
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="{{ route('history.index') }}">
                            <i class="bi bi-clock-history"></i> История
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="{{ route('saved.reports') }}">
                            <i class="bi bi-bookmark-star"></i> Сохраненные
                        </a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container py-4">

        <!-- ЗАГОЛОВОК И КНОПКА -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2 class="mb-0"><i class="bi bi-clock-history text-primary"></i> История проверок</h2>
            <button class="btn btn-primary-custom btn-lg" onclick="openModal()">
                <i class="bi bi-plus-circle"></i> Начать проверку
            </button>
        </div>

        <!-- СООБЩЕНИЯ -->
        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="bi bi-check-circle"></i> {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        <!-- СПИСОК ПРОВЕРОК -->
        @if($audits->count() > 0)
            <div class="row">
                @foreach($audits as $audit)
                    <div class="col-md-6 col-lg-4 mb-4">
                        <div class="card card-audit h-100 shadow-sm">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-start">
                                    <h5 class="card-title text-primary">
                                        <i class="bi bi-file-earmark-text"></i> Проверка #{{ $audit->id }}
                                    </h5>
                                    <span class="status-badge status-{{ $audit->status }}">
                                        {{ $audit->status == 'completed' ? '✅ Завершена' : ($audit->status == 'processing' ? '⏳ В процессе' : '❌ Ошибка') }}
                                    </span>
                                </div>
                                <p class="card-text text-muted small">
                                    <i class="bi bi-calendar3"></i> {{ $audit->created_at }}
                                </p>
                                <p class="card-text">
                                    <i class="bi bi-link-45deg"></i> Страниц: <strong>{{ count($audit->urls) }}</strong>
                                </p>
                                <a href="{{ route('history.show', $audit->id) }}" class="btn btn-outline-primary btn-sm w-100">
                                    <i class="bi bi-eye"></i> Подробнее
                                </a>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
            <div class="d-flex justify-content-center">
                {{ $audits->links() }}
            </div>
        @else
            <div class="empty-state">
                <i class="bi bi-inbox"></i>
                <h3>Пока нет ни одной проверки</h3>
                <p>Начните с создания новой SEO-проверки</p>
                <button class="btn btn-primary-custom" onclick="openModal()">
                    <i class="bi bi-plus-circle"></i> Создать проверку
                </button>
            </div>
        @endif

        <div class="footer">
            <i class="bi bi-robot"></i> SEO Checker v1.0
        </div>
    </div>

    <!-- МОДАЛЬНОЕ ОКНО -->
    <div class="modal fade" id="seoModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content modal-content-custom">
                <div class="modal-header modal-header-custom">
                    <h5 class="modal-title"><i class="bi bi-search"></i> Проверка SEO параметров</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="seoForm" action="{{ route('run.check') }}" method="POST">
                        @csrf
                        <div class="mb-3">
                            <label class="form-label fw-bold">Введите URL страниц для проверки:</label>
                            <div class="input-group">
                                <input type="url" id="urlInput" class="form-control" placeholder="https://example.com">
                                <button type="button" class="btn btn-primary" onclick="addUrl()">Добавить</button>
                            </div>
                            <small class="text-muted"><i class="bi bi-info-circle"></i> Максимум 20 URL</small>
                            <div id="errorMessage" class="text-danger small mt-1"></div>
                        </div>

                        <div id="urlList" class="mb-3" style="max-height:250px; overflow-y:auto;">
                            <p class="text-muted text-center">Добавьте URL для проверки</p>
                        </div>

                        <button type="submit" class="btn btn-success w-100" id="submitBtn">
                            <i class="bi bi-play-fill"></i> Запустить проверку
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        let urls = [];
        const modal = new bootstrap.Modal(document.getElementById('seoModal'));

        function openModal() {
            modal.show();
            document.getElementById('urlInput').focus();
        }

        function closeModal() {
            modal.hide();
        }

        function addUrl() {
            const input = document.getElementById('urlInput');
            const url = input.value.trim();
            const errorEl = document.getElementById('errorMessage');
            errorEl.textContent = '';

            if (!url) { errorEl.textContent = 'Введите URL'; return; }
            if (urls.length >= 20) { errorEl.textContent = 'Максимум 20 URL'; return; }
            try { new URL(url); } catch { errorEl.textContent = 'Введите корректный URL'; return; }
            if (urls.includes(url)) { errorEl.textContent = 'Этот URL уже добавлен'; return; }

            urls.push(url);
            input.value = '';
            renderUrls();
        }

        function removeUrl(index) {
            urls.splice(index, 1);
            renderUrls();
        }

        function renderUrls() {
            const container = document.getElementById('urlList');
            if (urls.length === 0) {
                container.innerHTML = '<p class="text-muted text-center">Добавьте URL для проверки</p>';
                return;
            }
            container.innerHTML = urls.map((url, index) => `
                <div class="url-item">
                    <span class="url-text">${index + 1}. ${url}</span>
                    <button type="button" class="btn btn-danger btn-sm" onclick="removeUrl(${index})">
                        <i class="bi bi-x"></i>
                    </button>
                </div>
            `).join('');

            const form = document.getElementById('seoForm');
            form.querySelectorAll('input[name="urls[]"]').forEach(el => el.remove());
            urls.forEach(url => {
                const input = document.createElement('input');
                input.type = 'hidden';
                input.name = 'urls[]';
                input.value = url;
                form.appendChild(input);
            });
        }

        document.getElementById('urlInput').addEventListener('keypress', function(e) {
            if (e.key === 'Enter') { e.preventDefault(); addUrl(); }
        });

        renderUrls();
    </script>
</body>
</html>