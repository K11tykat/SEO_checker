<!DOCTYPE html>
<html>
<head>
    <title>История проверок</title>
    <style>
        body { font-family: Arial; max-width: 800px; margin: 50px auto; padding: 20px; }
        .card { border: 1px solid #ddd; padding: 15px; margin: 10px 0; border-radius: 8px; }
        .card a { color: #0066cc; text-decoration: none; }
        .card a:hover { text-decoration: underline; }
        .empty { color: #999; text-align: center; padding: 40px; }
    </style>
</head>
<body>
    <h1>История SEO-проверок</h1>

    @if(session('success'))
        <p style="color:green">{{ session('success') }}</p>
    @endif

    @if($audits->count() > 0)
        @foreach($audits as $audit)
            <div class="card">
                <p><strong>Проверка #{{ $audit->id }}</strong></p>
                <p>Дата: {{ $audit->created_at }}</p>
                <p>Статус: {{ $audit->status }}</p>
                <p>Страниц: {{ count($audit->urls) }}</p>
                <a href="{{ route('history.show', $audit->id) }}">Подробнее</a>
            </div>
        @endforeach

        {{ $audits->links() }}
    @else
        <div class="empty">
            <p>Пока нет ни одной проверки</p>
            <p><a href="/test-seo">Перейти к проверке</a></p>
        </div>
    @endif

    <p style="margin-top:20px;"><a href="/test-seo">Новая проверка</a></p>
</body>
</html>