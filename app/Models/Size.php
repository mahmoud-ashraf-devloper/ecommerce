<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Size extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = ['size','parent_id'];


    public $timestamps = false;

    public function products()
    {
        return $this->belongsToMany(Product::class, 'product_size');
    }

    public function children()
    {
        return $this->hasMany(Size::class, 'parent_id');
    }
}
