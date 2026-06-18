<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AuditUrl extends Model
{
    protected $fillable = ['audit_id', 'url', 'http_code', 'redirect_final_url'];

    public function audit()
    {
        return $this->belongsTo(Audit::class);
    }

    public function result()
    {
        return $this->hasOne(AuditResult::class);
    }
}