<?php

declare(strict_types=1);

namespace App\Factory;

use Symfony\Component\Filesystem\Exception\IOException;
use Symfony\Component\HttpKernel\Exception\UnsupportedMediaTypeHttpException;

class ImageFactory
{
    public static function getResource(string $filePath): \GdImage
    {
        if (!is_file($filePath)) {
            throw new IOException('File «' . $filePath . '» was not found');
        }
        $imageType = exif_imagetype($filePath);
        if ($imageType === false) {
            throw new IOException('File «' . $filePath . '» is not a picture');
        }

        $resource = match ($imageType) {
            IMAGETYPE_GIF => imagecreatefromgif($filePath),
            IMAGETYPE_JPEG => imagecreatefromjpeg($filePath),
            IMAGETYPE_PNG => imagecreatefrompng($filePath),
            IMAGETYPE_WBMP => imagecreatefromwbmp($filePath),
            IMAGETYPE_XBM => imagecreatefromxbm($filePath),
            default => false,
        };

        if ($resource === false) {
            throw new UnsupportedMediaTypeHttpException(sprintf('Picture of type %s is not supported', $imageType));
        }

        return $resource;
    }
}
