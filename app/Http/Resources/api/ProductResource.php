<?php

namespace App\Http\Resources\api;

use Illuminate\Http\Resources\Json\JsonResource;

class ProductResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'slug' => $this->slug,
            'brand' => $this->brand()->get(),
            'images'=>$this->images()->get(),
          'name' => $this->name,
          'description' => $this->description,
          'price' => $this->price,
            'attr'=> $this->attributes()->where('default',1)->first(),
          'cover' => $this->cover
        ];
    }
}
