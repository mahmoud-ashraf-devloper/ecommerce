<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\{
    User,
    Admin,
    Category,
    Color,
    Product,
    Size
};
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        $userNum = 20;
        $categoriesNum = 20;
        $sizesNum = 20;
        $productNum = 100;
        $colorNum = 10;
        $pivotProductSizeNum = $productNum * 2;
        $pivotCategoryProductNum = $productNum * 2;


        User::create([
            'name' => 'User',
            'email' => 'user@user.com',
            'password' => Hash::make('password'),
        ]);

        Admin::create([
            'name' => 'Admin',
            'email' => 'admin@admin.com',
            'password' => Hash::make('password'),
        ]);

        User::factory($userNum - 1)->create();
        Category::factory($categoriesNum)->create();
        Size::factory($sizesNum)->create();
        Product::factory($productNum)->create();
        Color::factory($colorNum)->create();

        // for($i = 1; $i<= $pivotProductSizeNum; $i++){
        //     DB::table('product_size')->insert([
        //         'size_id' => rand(1, $sizesNum),
        //         'product_id' => $i,
        //     ]);
        // }

        for($i = 1; $i<= $pivotCategoryProductNum; $i++){

            DB::table('category_product')->insert([
                'product_id' => rand(1, $productNum),
                'category_id' => rand(1, $categoriesNum),
            ]);
        }
    }
}
