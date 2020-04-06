<?php

namespace App\Http\Resources\api\User;

use Illuminate\Http\Resources\Json\JsonResource;

class AddressResource extends JsonResource
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
            'address_id' => $this->id,
            'address_1' => $this->address_1,
            'address_2' => $this->address_2,
            'phone' => $this->phone,
            'city' => $this->city,
            'lat long' => $this->latLong
        ];
    }
}
