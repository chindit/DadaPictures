<?php

declare(strict_types=1);

namespace App\Service;

use App\Entity\Picture;
use Imagick;
use Intervention\Image\Facades\Image;

final class PictureConverter
{
    public function __construct(
        private int $thumbnailWidth,
        private int $thumbnailHeight,
        private Path $path
    ) {
    }

    /**
     * Convert picture to PNG
     */
    public static function convertPicture(string $path): string
    {
        try {
            $picture = new Imagick(realpath($path));
            $picture->setFormat('png');
            $pathInfo = pathinfo($path);
            $newFileName = $pathInfo['dirname'] . '/' . $pathInfo['filename'] . '.png';
            $picture->writeImage($newFileName);

            return $newFileName;
        } catch (\Exception $exception) {
            return '';
        }
    }

    public function createThumbnail(Picture $picture, bool $overwrite = false): void
    {
        $picturePath = $this->path->getPictureFullpath($picture);

        $name = $overwrite ? $picture->getThumbnail() : uniqid('thumb_', true) . '.jpg';
        Image::make($picturePath)
            ->fit($this->thumbnailWidth, $this->thumbnailHeight, function($constraint) {
                $constraint->respectRatio();
                $constraint->upsize();
            })
            ->save($this->path->getThumbnailsDirectory() . $name);

        $picture->setThumbnail($name);
    }
}
