<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class CartResource extends JsonResource
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
            'count' => $this->count,
            'status' => $this->status,
            'product' => ProductResource::make($this->whenLoaded('product')),
            'color' => ColorResource::make($this->whenLoaded('color')),
            'size' => SizeResource::make($this->whenLoaded('size')),
        ];
    }
}
