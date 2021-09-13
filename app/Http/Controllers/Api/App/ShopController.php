<?php

namespace App\Http\Controllers\Api\App;

use App\Http\Controllers\Controller;

use Illuminate\Http\Request;

class ShopController extends Controller
{
    public function index()
    {
        return view('site.pages.shop');
    }
}
