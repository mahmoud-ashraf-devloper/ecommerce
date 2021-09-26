<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Color extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = ['color', 'parent_id'];


    public function products()
    {
        return $this->belongsToMany(Product::class, 'color_product');
    }

    public function children()
    {
        return $this->hasMany(Color::class, 'parent_id');
    }
}
