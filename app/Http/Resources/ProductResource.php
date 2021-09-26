<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class ProductResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'description' => $this->description,
            'created_at' => $this->created_at->diffForHumans(),
            'category' => CategoryResource::collection($this->whenLoaded('categories')),
            'sizes'=> SizeResource::collection($this->whenLoaded('sizes')),
            'colors'=> ColorResource::collection($this->whenLoaded('colors')),
            'image' => ImageResource::collection($this->whenLoaded('productImages')),
        ]; 
    }
}
