<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Product extends Model
{
    use HasFactory;
    const STATUS_ACTIVE = 'active';
    const STATUS_DRAFT  = 'draft';

    protected $fillable = [
        'name', 'slug', 'description', 'image_path', 'price', 'sale_price',
        'quantity', 'status', 'category_id',
    ];

    protected $casts = [
        'price' => 'float',
        'quantity' => 'int',
    ];

    protected $appends = [
        'image_url',
        'formatted_price',
        'permalink',
    ];

    protected $perPage = 20;

    protected static function booted()
    {
        static::creating(function(Product $product) {
            $slug = Str::slug($product->name);

            $count = Product::where('slug', 'LIKE', "{$slug}%")->count();
            if ($count) {
                $slug .= '-' . ($count + 1);
            }
            $product->slug = $slug;
        });

    }



    public static function validateRules()
    {
        return [
            'name' => 'required|max:255',
            'category_id' => 'required|int|exists:categories,id',
            'description' => 'nullable',
            'image' => 'nullable|image|dimensions:min_width=300,min_height=300',
            'price' => 'nullable|numeric|min:0',
            'sale_price' => 'nullable|numeric|min:0',
            'quantity' => 'nullable|int|min:0',
            'sku' => 'nullable|unique:products,sku',
            'status' => 'in:' . self::STATUS_ACTIVE . ',' . self::STATUS_DRAFT,
        ];
    }

    // image_url
    public function getImageUrlAttribute()
    {
        if (!$this->image_path) {
            return asset('images/placeholder.png');
        }

        if (stripos($this->image_path, 'http') === 0) {
            return $this->image_path;
        }

        return asset('uploads/' . $this->image_path);
    }

    // Mutators: set{AttributeName}Attribute
    public function setNameAttribute($value)
    {
        $this->attributes['name'] = Str::title($value);
        $this->attributes['slug'] = Str::slug($value);
    }



    public function getPermalinkAttribute()
    {
        return route('product.details', $this->slug);
    }

    public function category()
    {
        return $this->belongsTo(Category::class, 'category_id', 'id')
            ->withDefault();
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'id')
            ->withDefault();
    }


}
