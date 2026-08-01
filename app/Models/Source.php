<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Source extends Model
{
    use HasUuids;

    protected $fillable = [
        'workspace_id',
        'type',
        'identifier',
        'meta',
        'last_synced_at'
    ];

    

    public function workspace(): BelongsTo{

        return $this->belongsTo(Workspace::class);

    }
}
