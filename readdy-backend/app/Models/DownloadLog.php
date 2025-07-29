<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DownloadLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_id', 'user_id', 'book_id', 'book_file_id', 'download_token',
        'ip_address', 'user_agent', 'status', 'initiated_at', 'started_at',
        'completed_at', 'expires_at', 'bytes_downloaded', 'total_bytes',
        'failure_reason', 'metadata'
    ];

    protected $casts = [
        'initiated_at' => 'datetime',
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
        'expires_at' => 'datetime',
        'bytes_downloaded' => 'integer',
        'total_bytes' => 'integer',
        'metadata' => 'array'
    ];

    // Status constants
    const STATUS_INITIATED = 'initiated';
    const STATUS_DOWNLOADING = 'downloading';
    const STATUS_COMPLETED = 'completed';
    const STATUS_FAILED = 'failed';
    const STATUS_EXPIRED = 'expired';

    /**
     * Get the order associated with this download
     */
    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    /**
     * Get the user associated with this download
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the book associated with this download
     */
    public function book(): BelongsTo
    {
        return $this->belongsTo(Book::class);
    }

    /**
     * Get the book file associated with this download
     */
    public function bookFile(): BelongsTo
    {
        return $this->belongsTo(BookFile::class);
    }

    /**
     * Scope for status
     */
    public function scopeByStatus($query, string $status)
    {
        return $query->where('status', $status);
    }

    /**
     * Scope for completed downloads
     */
    public function scopeCompleted($query)
    {
        return $query->where('status', self::STATUS_COMPLETED);
    }

    /**
     * Scope for failed downloads
     */
    public function scopeFailed($query)
    {
        return $query->whereIn('status', [self::STATUS_FAILED, self::STATUS_EXPIRED]);
    }

    /**
     * Scope for active downloads
     */
    public function scopeActive($query)
    {
        return $query->whereIn('status', [self::STATUS_INITIATED, self::STATUS_DOWNLOADING])
            ->where('expires_at', '>', now());
    }

    /**
     * Scope for expired downloads
     */
    public function scopeExpired($query)
    {
        return $query->where('expires_at', '<', now());
    }

    /**
     * Scope for recent downloads
     */
    public function scopeRecent($query, int $days = 7)
    {
        return $query->where('created_at', '>=', now()->subDays($days));
    }

    /**
     * Check if download is completed
     */
    public function isCompleted(): bool
    {
        return $this->status === self::STATUS_COMPLETED;
    }

    /**
     * Check if download failed
     */
    public function isFailed(): bool
    {
        return in_array($this->status, [self::STATUS_FAILED, self::STATUS_EXPIRED]);
    }

    /**
     * Check if download is active
     */
    public function isActive(): bool
    {
        return in_array($this->status, [self::STATUS_INITIATED, self::STATUS_DOWNLOADING]) && 
               $this->expires_at > now();
    }

    /**
     * Check if download is expired
     */
    public function isExpired(): bool
    {
        return $this->expires_at < now();
    }

    /**
     * Get download progress percentage
     */
    public function getProgressPercentage(): float
    {
        if (!$this->total_bytes || $this->total_bytes <= 0) {
            return 0;
        }

        return min(100, ($this->bytes_downloaded / $this->total_bytes) * 100);
    }

    /**
     * Get download duration in seconds
     */
    public function getDownloadDuration(): ?int
    {
        if (!$this->started_at || !$this->completed_at) {
            return null;
        }

        return $this->started_at->diffInSeconds($this->completed_at);
    }

    /**
     * Get download speed in bytes per second
     */
    public function getDownloadSpeed(): ?float
    {
        $duration = $this->getDownloadDuration();
        if (!$duration || $duration <= 0) {
            return null;
        }

        return $this->bytes_downloaded / $duration;
    }

    /**
     * Get formatted download speed
     */
    public function getFormattedDownloadSpeed(): string
    {
        $speed = $this->getDownloadSpeed();
        if (!$speed) {
            return 'N/A';
        }

        if ($speed >= 1024 * 1024) {
            return round($speed / (1024 * 1024), 2) . ' MB/s';
        } elseif ($speed >= 1024) {
            return round($speed / 1024, 2) . ' KB/s';
        } else {
            return round($speed, 2) . ' B/s';
        }
    }

    /**
     * Get formatted file size
     */
    public function getFormattedFileSize(): string
    {
        if (!$this->total_bytes) {
            return 'N/A';
        }

        if ($this->total_bytes >= 1024 * 1024 * 1024) {
            return round($this->total_bytes / (1024 * 1024 * 1024), 2) . ' GB';
        } elseif ($this->total_bytes >= 1024 * 1024) {
            return round($this->total_bytes / (1024 * 1024), 2) . ' MB';
        } elseif ($this->total_bytes >= 1024) {
            return round($this->total_bytes / 1024, 2) . ' KB';
        } else {
            return $this->total_bytes . ' B';
        }
    }

    /**
     * Get download statistics
     */
    public static function getStatistics(int $days = 30): array
    {
        $query = self::where('created_at', '>=', now()->subDays($days));

        return [
            'total_downloads' => $query->count(),
            'completed_downloads' => $query->completed()->count(),
            'failed_downloads' => $query->failed()->count(),
            'active_downloads' => $query->active()->count(),
            'expired_downloads' => $query->expired()->count(),
            'success_rate' => $query->count() > 0 ? 
                ($query->completed()->count() / $query->count()) * 100 : 0,
            'total_bytes_downloaded' => $query->completed()->sum('bytes_downloaded'),
            'average_download_time' => $query->completed()
                ->whereNotNull('started_at')
                ->whereNotNull('completed_at')
                ->avg(\DB::raw('TIMESTAMPDIFF(SECOND, started_at, completed_at)')),
            'by_book' => $query->selectRaw('book_id, COUNT(*) as count')
                ->groupBy('book_id')
                ->orderBy('count', 'desc')
                ->limit(10)
                ->pluck('count', 'book_id')
                ->toArray(),
            'by_status' => $query->selectRaw('status, COUNT(*) as count')
                ->groupBy('status')
                ->pluck('count', 'status')
                ->toArray()
        ];
    }
}
