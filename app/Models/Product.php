<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Product extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = ['title', 'description', 'price', 'discount', 'published'];

    public function categories()
    {
        return $this->belongsToMany(Category::class, 'category_product');
    }

    public function sizes()
    {
        return $this->belongsToMany(Size::class, 'product_size');
    }

    public function colors()
    {
        return $this->belongsToMany(Color::class, 'color_product');
    }

    public function productImages()
    {
        return $this->hasMany(ProductImage::class, 'product_id')->withTrashed();
    }
}
