<?php

namespace App\Traits;

use Illuminate\Support\Facades\Storage;

trait Transformer
{

    public static function transformCollection($collection)
    {
        $params = http_build_query(request()->except('page'));
        $next = $collection->nextPageUrl();
        $previous = $collection->previousPageUrl();
        if ($params) {
            if ($next) {
                $next .= "&{$params}";
            }
            if ($previous) {
                $previous .= "&{$params}";
            }
        }
        $meta = [
            "next" => (string)$next,
            "previous" => (string)$previous,
            "per_page" => (integer)$collection->perPage(),
            "total" => (integer)$collection->total()
        ];
        return $meta;
    }

}
