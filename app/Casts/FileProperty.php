<?php

namespace App\Casts;

use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Storage;

class FileProperty implements CastsAttributes
{
    /**
     * Transforms the
     *
     * @param  array<string, mixed>  $attributes
     */
    public function get(Model $model, string $key, mixed $value, array $attributes): array
    {
        $parsedFiles = json_decode($value, true);
        return Arr::map($parsedFiles, function ($parsedFile) {
            $realPath = str_replace('/storage', '', $parsedFile);

            return [
                'path' => $parsedFile,
                'type' => Storage::mimeType($realPath),
            ];
        });
    }

    /**
     * Prepare the given value for storage.
     *
     * @param  array<string, mixed>  $attributes
     */
    public function set(Model $model, string $key, mixed $value, array $attributes): mixed
    {
        return $value;
    }
}
