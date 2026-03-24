<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MediaItem extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'batch_id',
        'raw_data',
        'product_name',
        'artist_or_director',
        'media_format',
        'genre',
        'condition',
    ];

    /**
     * Interact with the media format attribute.
     */
    protected function mediaFormat(): Attribute
    {
        return Attribute::make(
            set: function (?string $value) {
                if (is_null($value)) {
                    return null;
                }

                $acronyms = ['cd', 'dvd', 'vhs', 'pc'];
                $normalizedValue = strtolower($value);

                if (in_array($normalizedValue, $acronyms)) {
                    return strtoupper($value);
                }

                return ucwords($normalizedValue);
            }
        );
    }

    /**
     * Get the batch that owns the media item.
     */
    public function batch(): BelongsTo
    {
        return $this->belongsTo(Batch::class);
    }
}
