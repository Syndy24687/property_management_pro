<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Document extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'uploaded_by',
        'documentable_type',
        'documentable_id',
        'title',
        'file_path',
        'file_name',
        'mime_type',
        'file_size',
        'category',
    ];

    // ─── Relationships ──────────────────────────────────────────────

    /**
     * The owning documentable model (Property, Unit, Lease, User, MaintenanceRequest).
     */
    public function documentable(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * The user who uploaded this document.
     */
    public function uploader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

    // ─── Scopes ─────────────────────────────────────────────────────

    public function scopeOfCategory($query, string $category)
    {
        return $query->where('category', $category);
    }

    // ─── Helpers ────────────────────────────────────────────────────

    /**
     * Get human-readable file size.
     */
    public function getFormattedSizeAttribute(): string
    {
        $bytes = $this->file_size;
        if ($bytes >= 1048576) {
            return number_format($bytes / 1048576, 2) . ' MB';
        }
        if ($bytes >= 1024) {
            return number_format($bytes / 1024, 2) . ' KB';
        }

        return $bytes . ' B';
    }
}
