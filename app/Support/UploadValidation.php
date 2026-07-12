<?php

namespace App\Support;

use Illuminate\Validation\Rules\File;

final class UploadValidation
{
    public const ALLOWED_EXTENSIONS = ['jpg', 'jpeg', 'png', 'pdf', 'mp4'];

    public const MAX_KILOBYTES = 5120;

    public static function optionalFile(): array
    {
        return ['nullable', self::allowedFile()];
    }

    public static function requiredFile(): array
    {
        return ['required', self::allowedFile()];
    }

    private static function allowedFile(): File
    {
        return File::types(self::ALLOWED_EXTENSIONS)
            ->extensions(self::ALLOWED_EXTENSIONS)
            ->max(self::MAX_KILOBYTES);
    }
}