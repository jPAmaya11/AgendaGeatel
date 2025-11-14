<?php

namespace App\Models;

use Spatie\Permission\Models\Permission as SpatiePermission;

use App\Models\Module;

class Permission extends SpatiePermission
{
    protected $fillable = [
        'name',
        'guard_name',
        'module_id',
    ];

    public function module()
    {
        return $this->belongsTo(Module::class);
    }
}
