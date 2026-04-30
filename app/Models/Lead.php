<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Lead extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'company_id',
        'meeting_date',
        'project_name',
        'brief_description',
        'project_scopes',
        'project_scope_other',
        'app_platforms',
        'project_kind',
        'project_stage',
        'is_online',
        'is_active',
        'has_domain',
        'domain_info',
        'has_server',
        'server_info',
        'expected_budget',
        'expected_deadline',
    ];

    protected $casts = [
        'meeting_date' => 'date',
        'project_scopes' => 'array',
        'app_platforms' => 'array',
        'is_online' => 'boolean',
        'is_active' => 'boolean',
        'has_domain' => 'boolean',
        'has_server' => 'boolean',
        'expected_budget' => 'decimal:2',
        'expected_deadline' => 'date',
        'deleted_at' => 'datetime',
    ];

    public function company()
    {
        return $this->belongsTo(Company::class);
    }
}

