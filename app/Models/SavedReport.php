<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SavedReport extends Model
{
    protected $fillable = ['audit_id', 'user_id'];

    public function audit()
    {
        return $this->belongsTo(Audit::class);
    }
}