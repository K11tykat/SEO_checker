<?php

namespace App\Http\Controllers;

use App\Models\SavedReport;
use App\Services\Seo\ReportStorageService;
use App\Services\Seo\SeoAuditRunner;
use Illuminate\Http\Request;

class AuditHistoryController extends Controller
{
    protected $storageService;

    public function __construct(ReportStorageService $storageService)
    {
        $this->storageService = $storageService;
    }

    /**
     * Главная страница: список исторических проверок.
     */
    public function index()
    {
        $audits = $this->storageService->getAuditHistory();
        return view('history.index', compact('audits'));
    }

    /**
     * Запуск новой проверки по введённым URL.
     */
    public function run(Request $request, SeoAuditRunner $runner)
    {
        $validated = $request->validate([
            'urls' => 'required|array|min:1|max:20',
            'urls.*' => 'required|url|max:2048',
        ], [
            'urls.required' => 'Добавьте хотя бы один URL для проверки.',
            'urls.max' => 'Можно проверить не более 20 страниц за один раз.',
            'urls.*.required' => 'URL не может быть пустым.',
            'urls.*.url' => 'Введите корректный URL (например, https://example.com).',
        ]);

        // убираем пустые значения и дубликаты
        $urls = array_values(array_unique(array_filter($validated['urls'])));

        $audit = $runner->run($urls);

        return redirect()->route('audit.results', $audit->id);
    }

    /**
     * Страница с результатами текущей (только что выполненной) проверки.
     */
    public function results($id)
    {
        $audit = $this->storageService->getAuditDetail($id);
        return view('audit.results', compact('audit'));
    }

    /**
     * Детальная страница исторической проверки.
     */
    public function show($id)
    {
        $audit = $this->storageService->getAuditDetail($id);
        return view('history.show', compact('audit'));
    }

    /**
     * Сохранение проверки в избранное (таблица saved_reports — уровень аудита).
     */
    public function saveToFavorites(Request $request, $auditId)
    {
        $audit = $this->storageService->getAuditDetail($auditId);

        SavedReport::firstOrCreate([
            'audit_id' => $audit->id,
            'user_id' => $request->user()?->id,
        ]);

        $count = count($request->input('url_ids', []));
        $message = $count > 0
            ? "Отчёт сохранён в избранное (выбрано отчётов: {$count})."
            : 'Отчёт сохранён в избранное.';

        return redirect()->route('audit.results', $audit->id)->with('success', $message);
    }
}