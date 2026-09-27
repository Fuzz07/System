<?php

namespace App\Support;

use Illuminate\Validation\Rules\File;

/** The one upload rule for every user-uploaded file: PNG, JPG or JPEG images up to 5 MB. */
final class UploadValidation
{
    public const ALLOWED_EXTENSIONS = ['png', 'jpg', 'jpeg'];

    public const MAX_KILOBYTES = 5120;

    /** For file inputs: extensions for desktop pickers, MIME types for mobile/WebView pickers. */
    public const ACCEPT = '.png,.jpg,.jpeg,image/png,image/jpeg';

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