<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BookFile extends Model
{
    protected $fillable = [
        'book_id',
        'file_type',
        'file_path',
        'file_size',
        'duration',
        'is_primary',
    ];

    protected $casts = [
        'file_size' => 'integer',
        'duration' => 'integer',
        'is_primary' => 'boolean',
    ];

    /**
     * Get the book that owns the file.
     */
    public function book(): BelongsTo
    {
        return $this->belongsTo(Book::class);
    }

    /**
     * Get the file type as a human-readable string.
     */
    public function getFileTypeNameAttribute(): string
    {
        return match($this->file_type) {
            'epub' => 'EPUB',
            'pdf' => 'PDF',
            'mobi' => 'MOBI',
            'audio' => 'Audio Book',
            default => ucfirst($this->file_type),
        };
    }

    /**
     * Get the file size in human-readable format.
     */
    public function getFormattedFileSizeAttribute(): string
    {
        $bytes = $this->file_size;
        $units = ['B', 'KB', 'MB', 'GB'];

        for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
            $bytes /= 1024;
        }

        return round($bytes, 2) . ' ' . $units[$i];
    }

    /**
     * Get the duration in human-readable format (for audio books).
     */
    public function getFormattedDurationAttribute(): ?string
    {
        if (!$this->duration) {
            return null;
        }

        $hours = floor($this->duration / 3600);
        $minutes = floor(($this->duration % 3600) / 60);
        $seconds = $this->duration % 60;

        if ($hours > 0) {
            return sprintf('%02d:%02d:%02d', $hours, $minutes, $seconds);
        }

        return sprintf('%02d:%02d', $minutes, $seconds);
    }

    /**
     * Scope to get primary files.
     */
    public function scopePrimary($query)
    {
        return $query->where('is_primary', true);
    }

    /**
     * Scope to get files by type.
     */
    public function scopeOfType($query, $type)
    {
        return $query->where('file_type', $type);
    }
} 