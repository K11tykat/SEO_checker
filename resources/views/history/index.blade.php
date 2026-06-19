@extends('layouts.app')

@section('title', 'История SEO-проверок')

@section('content')
    <h1>История SEO-проверок</h1>

    @if(session('success'))
        <div class="flash flash-success">{{ session('success') }}</div>
    @endif

    @if($errors->any())
        <div class="errors">
            Проверьте ввод:
            <ul>
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="btn-row">
        <button class="btn" onclick="document.getElementById('checkModal').showModal()">Начать проверку</button>
    </div>

    @if($audits->count() > 0)
        @foreach($audits as $audit)
            <div class="card">
                <p><strong>Проверка #{{ $audit->id }}</strong></p>
                <p class="muted">Дата: {{ $audit->created_at->format('d.m.Y H:i') }}</p>
                <p class="muted">Статус: {{ $audit->status }} · Страниц: {{ count($audit->urls) }}</p>
                <div class="btn-row" style="margin:8px 0 0;">
                    <a href="{{ route('history.show', $audit->id) }}">Подробнее →</a>
                    <form method="POST" action="{{ route('audit.destroy', $audit->id) }}"
                          onsubmit="return confirm('Удалить проверку #{{ $audit->id }}? Это действие необратимо.')"
                          style="margin-left:auto;">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-danger">Удалить</button>
                    </form>
                </div>
            </div>
        @endforeach

        {{ $audits->links() }}
    @else
        <div class="empty">
            <p>Пока нет ни одной проверки.</p>
            <p>Нажмите «Начать проверку», чтобы выполнить первую.</p>
        </div>
    @endif

    {{-- Модальное окно ввода URL --}}
    <dialog id="checkModal">
        <form method="POST" action="{{ route('audit.run') }}">
            @csrf
            <div class="modal-head">Введите URL страниц для проверки</div>
            <div class="modal-body">
                <p class="muted">Можно добавить до 20 страниц.</p>
                <div id="urlList">
                    <div class="url-row">
                        <input type="url" name="urls[]" placeholder="https://example.com" required>
                        <button type="button" class="url-remove" onclick="removeUrl(this)">×</button>
                    </div>
                </div>
                <button type="button" class="link-add" id="addUrlBtn" onclick="addUrl()">+ Добавить страницу</button>
            </div>
            <div class="modal-foot">
                <button type="button" class="btn btn-secondary" onclick="document.getElementById('checkModal').close()">Отмена</button>
                <button type="submit" class="btn">Запустить</button>
            </div>
        </form>
    </dialog>
@endsection

@section('scripts')
<script>
    const MAX_URLS = 20;

    function addUrl() {
        const list = document.getElementById('urlList');
        if (list.children.length >= MAX_URLS) {
            alert('Можно добавить не более ' + MAX_URLS + ' страниц.');
            return;
        }
        const row = document.createElement('div');
        row.className = 'url-row';
        row.innerHTML = '<input type="url" name="urls[]" placeholder="https://example.com" required>' +
            '<button type="button" class="url-remove" onclick="removeUrl(this)">&times;</button>';
        list.appendChild(row);
        toggleAddBtn();
    }

    function removeUrl(btn) {
        const list = document.getElementById('urlList');
        if (list.children.length > 1) {
            btn.parentElement.remove();
        }
        toggleAddBtn();
    }

    function toggleAddBtn() {
        const list = document.getElementById('urlList');
        document.getElementById('addUrlBtn').disabled = list.children.length >= MAX_URLS;
    }
</script>
@endsection
