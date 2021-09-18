<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ProductImage extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'image_product';

    protected $fillable = ['product_id', 'image_url','is_main_image'];

    public function product()
    {
        return $this->belongsTo(Product::class,'product_id');
    }
}
