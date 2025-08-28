<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class ArticleRevisionResource extends JsonResource
{
    public static $wrap = 'revision';

    public function toArray($request): array
    {
        return [
            'id'          => $this->id,
            'title'       => $this->title,
            'description' => $this->description,
            'body'        => $this->body,
            'slug'        => $this->slug,
            'createdAt'   => $this->created_at,
            'updatedAt'   => $this->updated_at,
            'user'        => $this->user ? [
                'id'       => $this->user->id,
                'username' => $this->user->username,
                'bio'      => $this->user->bio,
                'image'    => $this->user->image,
            ] : null,
        ];
    }
}
