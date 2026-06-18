<?php

namespace App\Http\Controllers;

use App\Services\Seo\ReportStorageService;
use Illuminate\Http\Request;

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
}