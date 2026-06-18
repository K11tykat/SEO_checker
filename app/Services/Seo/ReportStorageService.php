<?php

namespace App\Services\Seo;

use App\Models\Audit;
use App\Models\AuditUrl;
use App\Models\AuditResult;
use Illuminate\Support\Facades\DB;

class ReportStorageService
{
    public function createAudit(int $userId = null): Audit
    {
        return Audit::create([
            'user_id' => $userId,
            'status' => 'processing',
        ]);
    }

    public function saveResultForUrl(Audit $audit, string $url, array $data): AuditUrl
    {
        return DB::transaction(function () use ($audit, $url, $data) {
            $auditUrl = AuditUrl::create([
                'audit_id' => $audit->id,
                'url' => $url,
                'http_code' => $data['http_code'] ?? null,
                'redirect_final_url' => $data['redirect_url'] ?? null,
            ]);

            AuditResult::create([
                'audit_url_id' => $auditUrl->id,
                'h1_is_valid' => $data['h1']['valid'] ?? false,
                'h1_error_reason' => $data['h1']['reason'] ?? null,
                'title_is_valid' => $data['title']['valid'] ?? false,
                'title_error_reason' => $data['title']['reason'] ?? null,
                'title_length' => $data['title']['length'] ?? null,
                'description_is_valid' => $data['description']['valid'] ?? false,
                'description_error_reason' => $data['description']['reason'] ?? null,
                'description_length' => $data['description']['length'] ?? null,
                'headings_valid' => $data['headings_valid'] ?? false,
                'external_links_count' => $data['external_links_count'] ?? 0,
                'external_links_nofollow' => $data['external_links_nofollow'] ?? 0,
                'external_links_dofollow' => $data['external_links_dofollow'] ?? 0,
                'og_marker' => $data['og_marker'] ?? false,
                'schema_marker' => $data['schema_marker'] ?? false,
                'schema_formats' => $data['schema_formats'] ?? [],
                'robots_marker' => $data['robots_marker'] ?? false,
                'sitemap_marker' => $data['sitemap_marker'] ?? false,
            ]);

            return $auditUrl;
        });
    }

    public function completeAudit(Audit $audit): void
    {
        $audit->update(['status' => 'completed']);
    }

    public function getAuditHistory(int $perPage = 15)
    {
        return Audit::with(['urls.result'])
            ->where('status', 'completed')
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);
    }

    public function getAuditDetail(int $auditId): Audit
    {
        return Audit::with(['urls.result'])->findOrFail($auditId);
    }
}