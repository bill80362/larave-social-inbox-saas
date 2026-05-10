<?php

namespace App\Models;

use App\Enums\Platform;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['workspace_id', 'platform', 'name', 'platform_account_id', 'credentials', 'is_active'])]
class Channel extends Model
{
    use HasFactory;

    protected static function booted(): void
    {
        static::addGlobalScope('workspace', function (Builder $query) {
            if (auth()->hasUser()) {
                $query->where('workspace_id', auth()->user()->workspace_id);
            }
        });
    }

    protected function casts(): array
    {
        return [
            'platform' => Platform::class,
            'credentials' => 'encrypted',
            'is_active' => 'boolean',
        ];
    }

    public function workspace(): BelongsTo
    {
        return $this->belongsTo(Workspace::class);
    }

    public function contacts(): HasMany
    {
        return $this->hasMany(Contact::class);
    }

    public function conversations(): HasMany
    {
        return $this->hasMany(Conversation::class);
    }
}
