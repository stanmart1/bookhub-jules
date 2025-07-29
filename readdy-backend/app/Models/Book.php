<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Book extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'subtitle',
        'author',
        'isbn',
        'publisher',
        'publication_date',
        'language',
        'page_count',
        'word_count',
        'description',
        'excerpt',
        'cover_image',
        'price',
        'original_price',
        'is_free',
        'is_featured',
        'is_bestseller',
        'is_new_release',
        'status',
        'rating_average',
        'rating_count',
        'view_count',
        'download_count',
    ];

    protected $casts = [
        'publication_date' => 'date',
        'price' => 'decimal:2',
        'original_price' => 'decimal:2',
        'is_free' => 'boolean',
        'is_featured' => 'boolean',
        'is_bestseller' => 'boolean',
        'is_new_release' => 'boolean',
        'rating_average' => 'decimal:2',
    ];

    /**
     * Get the book's files.
     */
    public function files()
    {
        return $this->hasMany(BookFile::class);
    }

    /**
     * Get the book's categories.
     */
    public function categories()
    {
        return $this->belongsToMany(Category::class, 'book_category')
                    ->withPivot('primary')
                    ->withTimestamps();
    }

    /**
     * Get the users who own this book.
     */
    public function owners()
    {
        return $this->belongsToMany(User::class, 'user_library')
                    ->withPivot('purchase_date', 'purchase_price', 'payment_method', 'transaction_id', 'is_gift', 'gift_from')
                    ->withTimestamps();
    }

    /**
     * Get the reading progress for this book.
     */
    public function readingProgress()
    {
        return $this->hasMany(ReadingProgress::class);
    }

    /**
     * Get the bookmarks for this book.
     */
    public function bookmarks()
    {
        return $this->hasMany(Bookmark::class);
    }

    /**
     * Get the reviews for this book.
     */
    public function reviews()
    {
        return $this->hasMany(Review::class);
    }

    /**
     * Get the users who have this book in their wishlist.
     */
    public function wishlistedBy()
    {
        return $this->belongsToMany(User::class, 'wishlist_items')
                    ->withPivot('added_at', 'notes')
                    ->withTimestamps();
    }

    /**
     * Scope for featured books.
     */
    public function scopeFeatured($query)
    {
        return $query->where('is_featured', true);
    }

    /**
     * Scope for bestsellers.
     */
    public function scopeBestsellers($query)
    {
        return $query->where('is_bestseller', true);
    }

    /**
     * Scope for new releases.
     */
    public function scopeNewReleases($query)
    {
        return $query->where('is_new_release', true);
    }

    /**
     * Scope for published books.
     */
    public function scopePublished($query)
    {
        return $query->where('status', 'published');
    }
}
