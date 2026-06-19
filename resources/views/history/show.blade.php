@extends('layouts.app')

@section('title', 'Детали проверки #' . $audit->id)

@section('content')
    <h1>Детали проверки #{{ $audit->id }}</h1>
    <p class="muted">Дата: {{ $audit->created_at->format('d.m.Y H:i') }}</p>

    <form method="POST" action="{{ route('audit.pdf', $audit->id) }}" target="_blank">
        @csrf
        <p class="muted">Отметьте отчёты для выгрузки в PDF.</p>

        @foreach($audit->urls as $url)
            @include('partials.result-card', ['url' => $url, 'selectable' => true])
        @endforeach

        <div class="btn-row">
            <button type="submit" class="btn">Скачать PDF</button>
        </div>
    </form>

    <p><a href="{{ route('history.index') }}">← Назад к истории</a></p>
@endsection
