<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SubscriptionPlan extends Model
{
    use HasUuids;

    protected $fillable = ['name', 'duration_months', 'duration_days', 'price', 'monthly_token_limit', 'monthly_repo_limit', 'storage_limit_mb', 'is_free', 'active'];

    protected function casts(): array
    {
        return ['price' => 'decimal:2', 'is_free' => 'boolean', 'active' => 'boolean'];
    }

    public function subscriptions(): HasMany
    {
        return $this->hasMany(UserSubscription::class);
    }
}
