<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    /** @use HasFactory<\Database\Factories\ProductFactory> */
    use HasFactory;
    protected $fillable = [
        'name',
        'sku',
        'price',
        'stock_on_hand',
        'reorder_threshold',
        'status','tags'
    ];
    protected $casts = [
        'tags' => 'array',
    ];
    public function scopeSearch($query, $term)
    {
        if (! $term) return $query;
        $term = trim($term);
        return $query->where(function($q) use ($term) {
            $q->where('name', 'like', "%{$term}%")
            ->orWhere('sku', 'like', "%{$term}%");
        });
    }

    public function scopeStatus($query, $status)
    {
        if (! in_array($status, ['active', 'inactive'])) return $query;
        return $query->where('status', $status);
    }

    public function scopeTag($query, $tag)
    {
        if (! $tag) return $query;
        return $query->whereJsonContains('tags', $tag);
    }
}
