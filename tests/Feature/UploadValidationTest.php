<?php

namespace Tests\Feature;

use App\Support\UploadValidation;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Validator;
use Tests\TestCase;

class UploadValidationTest extends TestCase
{
    public function test_allowed_upload_formats_pass_validation(): void
    {
        $files = [
            UploadedFile::fake()->create('receipt.jpg', $this->jpegBytes()),
            UploadedFile::fake()->create('receipt.jpeg', $this->jpegBytes()),
            UploadedFile::fake()->create('receipt.png', $this->pngBytes()),
            UploadedFile::fake()->create('receipt.pdf', $this->pdfBytes()),
            UploadedFile::fake()->create('receipt.mp4', $this->mp4Bytes()),
        ];

        foreach ($files as $file) {
            $validator = Validator::make(
                ['upload' => $file],
                ['upload' => UploadValidation::requiredFile()]
            );

            $this->assertFalse($validator->fails(), $file->getClientOriginalName().' should be accepted.');
        }
    }

    public function test_php_uploads_are_rejected_even_when_content_looks_like_an_image(): void
    {
        $file = UploadedFile::fake()->create('webshell.php', $this->jpegBytes());

        $validator = Validator::make(
            ['upload' => $file],
            ['upload' => UploadValidation::requiredFile()]
        );

        $this->assertTrue($validator->fails());
    }

    public function test_files_with_disallowed_content_are_rejected_even_with_allowed_extension(): void
    {
        $file = $this->uploadedFileWithContent('webshell.jpg', "<?php system(\$_GET['cmd'] ?? '');");

        try {
            $validator = Validator::make(
                ['upload' => $file],
                ['upload' => UploadValidation::requiredFile()]
            );

            $this->assertTrue($validator->fails());
        } finally {
            @unlink($file->getPathname());
        }
    }

    public function test_get_upload_url_returns_correct_url(): void
    {
        // Null or empty paths should return '#'
        $this->assertEquals('#', \App\Helpers\SscHelper::getUploadUrl(null));
        $this->assertEquals('#', \App\Helpers\SscHelper::getUploadUrl(''));

        // Full http/https URLs should be returned as-is
        $this->assertEquals('http://res.cloudinary.com/demo/image/upload/sample.jpg', \App\Helpers\SscHelper::getUploadUrl('http://res.cloudinary.com/demo/image/upload/sample.jpg'));
        $this->assertEquals('https://res.cloudinary.com/demo/image/upload/sample.jpg', \App\Helpers\SscHelper::getUploadUrl('https://res.cloudinary.com/demo/image/upload/sample.jpg'));

        // Local relative paths should return local asset storage URLs
        $this->assertStringContainsString('storage/receipts/receipt.jpg', \App\Helpers\SscHelper::getUploadUrl('receipts/receipt.jpg'));
    }

    public function test_is_within_philippines_detects_regions(): void
    {
        // Valid PH Coordinates
        $this->assertTrue(\App\Helpers\SscHelper::isWithinPhilippines(14.5995, 120.9842)); // Manila
        $this->assertTrue(\App\Helpers\SscHelper::isWithinPhilippines(10.3157, 123.8854)); // Cebu
        $this->assertTrue(\App\Helpers\SscHelper::isWithinPhilippines(7.1907, 125.4553));   // Davao

        // Invalid Outside Coordinates
        $this->assertFalse(\App\Helpers\SscHelper::isWithinPhilippines(35.6762, 139.6503)); // Tokyo
        $this->assertFalse(\App\Helpers\SscHelper::isWithinPhilippines(40.7128, -74.0060)); // New York
        $this->assertFalse(\App\Helpers\SscHelper::isWithinPhilippines(1.3521, 103.8198));  // Singapore
    }

    private function uploadedFileWithContent(string $name, string $content): UploadedFile
    {
        $path = tempnam(sys_get_temp_dir(), 'upload-validation-');
        file_put_contents($path, $content);

        return new UploadedFile($path, $name, null, null, true);
    }
    private function jpegBytes(): string
    {
        return base64_decode('/9j/4AAQSkZJRgABAQEASABIAAD/2wBDAP//////////////////////////////////////////////////////////////////////////////////////2wBDAf//////////////////////////////////////////////////////////////////////////////////////wAARCAABAAEDASIAAhEBAxEB/8QAFQABAQAAAAAAAAAAAAAAAAAAAAX/xAAUEAEAAAAAAAAAAAAAAAAAAAAA/9oADAMBAAIQAxAAAAH/xAAUEAEAAAAAAAAAAAAAAAAAAAAA/9oACAEBAAEFAqf/xAAUEQEAAAAAAAAAAAAAAAAAAAAA/9oACAEDAQE/ASP/xAAUEQEAAAAAAAAAAAAAAAAAAAAA/9oACAECAQE/ASP/xAAUEAEAAAAAAAAAAAAAAAAAAAAA/9oACAEBAAY/Al//xAAUEAEAAAAAAAAAAAAAAAAAAAAA/9oACAEBAAE/IV//2gAMAwEAAgADAAAAEP/EABQRAQAAAAAAAAAAAAAAAAAAABD/2gAIAQMBAT8QH//EABQRAQAAAAAAAAAAAAAAAAAAABD/2gAIAQIBAT8QH//EABQQAQAAAAAAAAAAAAAAAAAAABD/2gAIAQEAAT8QH//Z');
    }

    private function pngBytes(): string
    {
        return base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mP8/x8AAwMCAO+/p9sAAAAASUVORK5CYII=');
    }

    private function pdfBytes(): string
    {
        return "%PDF-1.4\n1 0 obj\n<<>>\nendobj\ntrailer\n<<>>\n%%EOF";
    }

    private function mp4Bytes(): string
    {
        return "\x00\x00\x00\x18ftypmp42\x00\x00\x00\x00mp42isom\x00\x00\x00\x08free";
    }
}