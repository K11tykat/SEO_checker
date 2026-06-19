@extends('layouts.app')

@section('title', 'Результаты проверки #' . $audit->id)

@section('content')
    <h1>Результаты проверки #{{ $audit->id }}</h1>
    <p class="muted">Дата: {{ $audit->created_at->format('d.m.Y H:i') }}</p>

    @if(session('success'))
        <div class="flash flash-success">{{ session('success') }}</div>
    @endif

    <form method="POST" id="reportsForm">
        @csrf
        <p class="muted">Отметьте отчёты, которые хотите сохранить или скачать в PDF.</p>

        @foreach($audit->urls as $url)
            @include('partials.result-card', ['url' => $url, 'selectable' => true])
        @endforeach

        <div class="btn-row">
            <button type="submit" class="btn" formaction="{{ route('audit.pdf', $audit->id) }}" formtarget="_blank">
                Скачать PDF
            </button>
            <button type="submit" class="btn btn-secondary" formaction="{{ route('audit.save', $audit->id) }}">
                Сохранить в избранное
            </button>
        </div>
    </form>

    <p><a href="{{ route('history.index') }}">← На главную</a></p>
@endsection
