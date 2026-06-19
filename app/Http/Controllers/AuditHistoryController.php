<?php

namespace App\Http\Controllers;

use App\Models\Audit;
use App\Services\Seo\ReportStorageService;
use App\Services\Seo\SeoAuditRunner;
use Barryvdh\DomPDF\Facade\Pdf;
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
     * Экспорт выбранных отчётов проверки в PDF (открывается в новой вкладке).
     */
    public function exportPdf(Request $request, $auditId)
    {
        $audit = $this->storageService->getAuditDetail($auditId);

        $urlIds = $request->input('url_ids', []);
        $urls = ! empty($urlIds)
            ? $audit->urls->whereIn('id', $urlIds)
            : $audit->urls;

        $pdf = Pdf::loadView('pdf.report', [
            'audit' => $audit,
            'urls' => $urls,
        ]);

        return $pdf->stream("seo-report-{$audit->id}.pdf");
    }

    /**
     * Удаление проверки целиком (каскадом удаляются её URL и результаты).
     */
    public function destroy($id)
    {
        Audit::findOrFail($id)->delete();

        return redirect()->route('history.index')->with('success', 'Проверка удалена.');
    }
}