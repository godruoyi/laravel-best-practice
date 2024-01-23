<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\ResourceCollection as BaseResourceCollection;
use Illuminate\Support\Arr;

class ResourceCollection extends BaseResourceCollection
{
    /**
     * Rewrite this method for simple paginate response format, all the
     * PaginateResource should be extends this one.
     */
    public function paginationInformation($request, $paginated, $default): array
    {
        return [
            'meta' => Arr::except($paginated, [
                'data',
                'links',
                'first_page_url',
                'last_page_url',
                'prev_page_url',
                'next_page_url',
                'path',
            ]),
        ];
    }
}
