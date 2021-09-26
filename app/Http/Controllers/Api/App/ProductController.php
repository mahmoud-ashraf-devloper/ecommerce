<?php

namespace App\Http\Controllers\Api\App;

use App\Http\Controllers\Controller;
use App\Http\Resources\{
    ProductCollection,
    ProductResource,
    ImageResource,
};
use App\Models\{
    Product,
    ProductImage
};
use App\Traits\{
    ApiResponse,
    upload
};
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use App\Models\Validations\ProductValidation;

class ProductController extends Controller
{
    private string $uploadPath = '/products';

    use ApiResponse, Upload;
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        // add where condition
        return $this->success(new ProductCollection(
                Product::with(['categories','productImages' ])
                ->paginate(env('PRODUCT_PAGINATION'))
        ));
    }


    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {

        // The response which will be returned after adding the product
        $response = [];
        // ProductImage model data
        $imageModelData = [];

        // url for the main image
        $mainImageUrl = '';
        // urls for the other images
        $moreImagesUrls = [];

        try {
            // validations
            $validator = (new ProductValidation())->validator($request->all());

            if($validator->fails()){
                return $this->error($validator->errors(), 400, 'Validation error');
            }

            if($request->hasFile('main_image') && $request->hasFile('more_images')){
                // uploading images via Upload Trait
                $mainImageUrl = $this->uploadSingleImage($request->file('main_image'), $this->uploadPath);
                $moreImagesUrls = $this->uploadMultiImage($request->file('more_images') , $this->uploadPath);
                
                // if the image did not uploaded "uploadSingleImage" and "uploadMultiImage" will return false
                if(!$moreImagesUrls || !$mainImageUrl){
                    return $this->error(['Images_error' => 'Can\'t Upload these Images Try again'] , 400 ,'Upload error');
                }


                // Validation is ok and images uploaded successfully
                DB::transaction(function() use ($imageModelData, $moreImagesUrls, $mainImageUrl, $validator) {
                    $response['product'] = new ProductResource(Product::create($validator->validated()));
                    $imageModelData[] = [
                        'product_id' => $response['product']->id,
                        'image_url'  => $mainImageUrl,
                        'is_main_image'   => true,
                    ];
    
                    // adding more images
                    foreach($moreImagesUrls as $url){
                        $imageModelData[] = [
                            'product_id' => $response['product']->id,
                            'image_url'  => $url,
                        ];
                    }
                    foreach($imageModelData as $modelData){
                        $response['images'][] = new ImageResource(ProductImage::Create($modelData));
                    }
                });
                return $this->success($response, 'Product Uploaded Successfully');
            }
        }catch (\Exception $e) {
            return $this->error($e->getMessage());
        }
  
    }


    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Product  $product
     * @return \Illuminate\Http\Response
     */
    public function show($product)
    {
        try {            
            $product = Product::find($product);
            if($product){
                $product =  new ProductResource($product->load(['categories', 'productImages','sizes']));
                
                $response = [
                    'product' => $product,
                ];
                return $this->success($response);
            }else {
                return $this->error([], 404, 'Product Not found');
            }
        } catch (\Exception $e) {
            return throw ($e);
        }
    }

    public function updateProductImages(Request $request, $productId, $productImageId)
    {
        try {
            $product = Product::find($productId);

            if(!$product){
                return $this->error([],404, 'The Product Does not Exists');
            }
            dd($product->productImages->pluck('id'));
            if(!in_array($productImageId, $product->productImages->pluck('id')->toArray())){
                return $this->error([],404, 'This image does not exists');
            }

            $image = ProductImage::find($productImageId);

            $oldImagePath = $image->image_url;

            $validator = (new ProductValidation())->singleImageValidator($request->only('image'));

            if($validator->fails()){
                return $this->error($validator->errors() ,400, 'validation error');
            }

            $uploadedUrl = $this->uploadSingleImage($request->file('image'), $this->uploadPath);
            if(!$uploadedUrl){
                return $this->error([] , 400, 'Faild To upload the image');
            }

            $updatedImage = $image->update(['image_url' => $uploadedUrl]);
            if($updatedImage){
                $this->removeImages($oldImagePath);
                return $this->success(new ImageResource($image), 'image Updated Successfully');
            }
        } catch (\Exception $e) {
            return $this->error($e->getMessage(), 400);
        }
    }

    public function setImageAsMainImage($productId, $imageId)
    {
        try {
            $product = Product::find($productId);

            if(!$product){
                return $this->error([],404, 'The Product Does not Exists');
            }

            if(!in_array($imageId, $product->productImages()->pluck('id')->toArray())){
                return $this->error([],404, 'This image does not exists');
            }

            $newMainImage = ProductImage::find($imageId);
            
            DB::transaction(function() use($newMainImage,$productId) {
                $oldMainImage = $this->getProductMainImage($productId);
                $oldMainImage->is_main_image = false;
                $oldMainImage->save();

                $newMainImage->is_main_image = true;
                $newMainImage->save();
            });

            return $this->success(new ImageResource(ProductImage::find($imageId)),'Image Updated Successfully');
        } catch (\Exception $e) {
            return $this->error($e->getMessage(),500);
        }

    }

    public function getProductMainImage($productId)
    {
        // return only the product main image.
        $mainImage = ProductImage::where('product_id' , $productId)->get()->reject(function($image){
            return $image->is_main_image === 0;
        });
        return new ImageResource($mainImage[key(reset($mainImage))]);
    }

    public function updateProductData(Request $request, $productId)
    {
        try {
            $productData = Product::find($productId);
            if(!$productData){
                return $this->error([], 404,'The Product which you are trying to update is now exists');
            }
            if(empty($request->all())){
                return $this->error([], 400,'There is nothing to update');
            }
            $validator = (new ProductValidation())->validator($request->all(), true);

            if($validator->fails()){
                return $this->error($validator->errors(), 400, 'Validation error');
            }
            $productData->update($validator->validated());

            return $this->success(Product::find($productId));

        } catch (\Illuminate\Database\QueryException $e) {
            return $this->error('There is no data provided to update', 400, 'validation error');
        } catch (\Exception $e) {
            return $this->error($e->getMessage());
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\ integer $product
     * @return \Illuminate\Http\Response
     */
    public function destroy($product)
    {
        try {
            $product = Product::find($product);
            if(!$product){
                return $this->error('Product Does not exists');
            }
            $productImages = $product->productImages;

            DB::transaction(function() use($product, $productImages){    
                if($product->delete()){
                    $productImages->map(function($image){
                        $image->delete();
                    });
                }
            });
            return $this->success($product);

        }catch (\Exception $e) {
            return $this->error($e->getMessage());
        }
    }

    public function getTrashedProducts()
    {
        try{
            $products = Product::with(['productImages'])->onlyTrashed()->get();
            return ProductResource::collection($products);
        }catch (\Exception $e) {
            return $this->error($e->getMessage());
        }
    }

    public function forceDelete($productId)
    {
        try{    
            $product = Product::withTrashed()->where('id', $productId)->first();
            if(!$product){
                return $this->error('This Product Does not exists');
            }
            $productImages = $product->productImages; 
            $imagesUrls    = $productImages->pluck('image_url')->toArray();
            $productImages->map(function($q){
                $q->forceDelete();
            });
            $this->removeImages($imagesUrls);
            if($product->forceDelete()){
                return $this->success('Product Deleted Successfully');
            }
        } catch(\Exception $e){
            return $this->error($e->getMessage());
        }
    }
}
