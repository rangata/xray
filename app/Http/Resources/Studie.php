<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class Studie extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        return $this->publicId;
//            return [
//                'id' => $this->publicId,
//                'instances' => [
//                     $this->publicId,
//
//                ]
//            ];
    }
}
