<?php

namespace App\Http\Controllers;

use App\Services\Seo\ReportStorageService;
use Illuminate\Http\Request;
use App\Models\SavedReport;

class AuditHistoryController extends Controller
{
    protected $storageService;

    public function __construct(ReportStorageService $storageService)
    {
        $this->storageService = $storageService;
    }

    public function index()
    {
        $audits = $this->storageService->getAuditHistory();
        return view('history.index', compact('audits'));
    }

    public function show($id)
    {
        $audit = $this->storageService->getAuditDetail($id);
        return view('history.show', compact('audit'));
    }

    public function saveToFavorites($auditId)
    {
        return redirect()->back()->with('success', 'Отчет сохранен в БД');
    }


public function savedReports()
{
    $savedReports = SavedReport::with('audit')->orderBy('created_at', 'desc')->get();
    return view('history.saved', compact('savedReports'));
}
}