<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Chunks extends Model
{
    use HasUuids;

    protected $fillable = [
        'source_id',
        'file_path',
        'content',
    ];

    public function sources(): BelongsTo{

        return $this->belongsTo(Source::class);
        
    }

}
