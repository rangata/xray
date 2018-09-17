<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class Res extends JsonResource
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
            'fullName' => $this->split_name(trim(str_replace('^', ' ', $this->tags[0]->value), '   '))[1] . ' ' . $this->split_name(trim(str_replace('^', ' ', $this->tags[0]->value), '   '))[0],
            'pid' => $this->publicId,
            'studies' => Studie::collection($this->Studies)
        ];
    }

    public function split_name($value)
    {
        $name = trim(str_replace('^', ' ', $this->tags[0]->value), '   ');
        $last_name = (strpos($name, ' ') === false) ? '' : preg_replace('#.*\s([\w-]*)$#', '$1', $name);
        $first_name = trim( preg_replace('#'.$last_name.'#', '', $name ) );
        return array($first_name, $last_name);
    }
}
