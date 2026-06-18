<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AuditResult extends Model
{
    protected $fillable = [
        'audit_url_id',
        'h1_is_valid',
        'h1_error_reason',
        'title_is_valid',
        'title_error_reason',
        'title_length',
        'description_is_valid',
        'description_error_reason',
        'description_length',
        'headings_valid',
        'external_links_count',
        'external_links_nofollow',
        'external_links_dofollow',
        'og_marker',
        'schema_marker',
        'schema_formats',
        'robots_marker',
        'sitemap_marker'
    ];

    protected $casts = [
        'schema_formats' => 'array',
    ];

    public function auditUrl()
    {
        return $this->belongsTo(AuditUrl::class);
    }
}