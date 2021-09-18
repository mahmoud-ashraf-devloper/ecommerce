<?php

namespace App\Http\Controllers\Api\App;

use App\Http\Controllers\Controller;
use App\Http\Resources\{
    ProductCollection,
    ProductResource,
    ImageResource
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
use Illuminate\Support\Facades\Validator;

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
            // validation 
            $rules = [
                'title'=> 'required|min:6',
                'main_image'=> 'required|image|mimes:jpeg,png', 
                'description'=> 'required|min:10',
                'more_images' => 'required',
                'more_images.*' => 'image|mimes:jpeg,png',
            ];

            $validator = Validator::make($request->all(), $rules);
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
                    // we will add sizes here when it's done
                ];
                return $this->success($response);
            }else {
                return $this->error([], 404, 'Product Not found');
            }
        } catch (\Exception $e) {
            return $this->error($e->getMessage());
        }
    }

    // /products/update/images/{id}
    public function updateProductImages(Request $request, $productImage)
    {
        try {
            $image = ProductImage::find($productImage);
            if(!$image){
                return $this->error([],404, 'Image Does not Exists');
            }

            $oldImagePath = $image->image_url;

            $rules = ['image' => 'required|image|mimes:jpeg,jpg,png',];

            $validator = Validator::make($request->only('image'), $rules);
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

    public function setImageAsMainImage($imageId)
    {
        try {
            $newMainImage = ProductImage::find($imageId);
            // setting old image to false 
            $this->getProductMainImage($newMainImage->product_id)->update(['is_main_image' => false]);
            // setting new image to true 
            $newMainImage->update(['is_main_image' => true]);

            return $this->success(new ImageResource(ProductImage::find($imageId)),'Image Updated Successfully');
        } catch (\Exception $e) {
            return $this->error([],404, 'This Image does not exists');
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

            $validator = Validator::make($request->all(),[
                'title' => 'string|min:10|max:255',
                'description' => 'string|min:255',
                'published' => 'bool',
            ]);

            if($validator->fails()){
                return $this->error($validator->errors(), 400, 'Validation error');
            }
            $productData->update($validator->validated());

            return $this->success(Product::find($productId));

        } catch (\Illuminate\Database\QueryException $e) {
            return $this->error('There is no data provided to update', 400, 'validation error');
        } catch (\Exception $e) {
            throw $e;
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

            if($product->delete()){
                $productImages->map(function($image){
                    $image->delete();
                });
                return $this->success($product);
            }
        }catch (\Exception $e) {
            return $this->error($e->getMessage());
        }
    }

    public function getTrashedProducts()
    {
        $products = Product::with(['productImages'])->onlyTrashed()->get();
        return ProductResource::collection($products);
    }

    public function forceDelete($productId)
    {
        try{    
            $product = Product::withTrashed()->where('id', $productId)->first();
            if(!$product){
                return $this->error('This Product Does not exists');
            }
            $productImages = $product->productImages; 
            $imagesUrls = $productImages->pluck('image_url')->toArray();
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
