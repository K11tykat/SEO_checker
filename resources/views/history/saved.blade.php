<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Сохраненные отчеты</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
</head>
<body>
    <div class="container py-5">
        <h1 class="mb-4"><i class="bi bi-bookmark-star text-warning"></i> Сохраненные отчеты</h1>

        @if($savedReports->count() > 0)
            <div class="row">
                @foreach($savedReports as $saved)
                    <div class="col-md-6 col-lg-4 mb-4">
                        <div class="card shadow-sm">
                            <div class="card-body">
                                <h5 class="card-title text-primary">
                                    <i class="bi bi-file-earmark-text"></i> Проверка #{{ $saved->audit->id ?? 'Удалено' }}
                                </h5>
                                <p class="card-text small text-muted">
                                    <i class="bi bi-calendar3"></i> {{ $saved->created_at }}
                                </p>
                                <p class="card-text">
                                    <i class="bi bi-link-45deg"></i> Страниц: {{ count($saved->audit->urls ?? []) }}
                                </p>
                                <a href="{{ route('history.show', $saved->audit_id) }}" class="btn btn-outline-primary btn-sm">
                                    <i class="bi bi-eye"></i> Посмотреть
                                </a>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        @else
            <div class="alert alert-info">
                <i class="bi bi-info-circle"></i> У вас нет сохраненных отчетов
            </div>
        @endif

        <a href="{{ route('history.index') }}" class="btn btn-secondary mt-3">
            <i class="bi bi-arrow-left"></i> Назад к истории
        </a>
    </div>
</body>
</html>