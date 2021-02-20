<?php

declare(strict_types=1);

namespace App\Service;

use App\Entity\Picture;
use App\Factory\ImageFactory;
use Imagick;

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

    public function createThumbnail(Picture $picture): void
    {
        $picturePath = $this->path->getPictureFullpath($picture);
        $image = ImageFactory::getResource($picturePath);
        $thumb = imagecreatetruecolor($this->thumbnailWidth, $this->thumbnailHeight);
        if ($thumb === false) {
            throw new \Exception(sprintf('Unable to create thumbnail for picture %s', $picture->getFilename()));
        }

        $ratio = max([($picture->getWidth() ?? 0) / $this->thumbnailWidth, ($picture->getHeight() ?? 0) / $this->thumbnailHeight]);

        $image = imagecrop($image, [
            'x' => floor(($picture->getWidth() - ($picture->getWidth() / $ratio)) / 2),
            'y' => floor(($picture->getHeight() - ($picture->getHeight() / $ratio)) / 2),
            'width' => $this->thumbnailWidth * $ratio,
            'height' => $this->thumbnailHeight * $ratio
        ]);

        if ($image === false) {
            throw new \Exception(sprintf('Unable to crop picture %s', $picture->getFilename()));
        }

        imagecopyresized(
            $thumb,
            $image,
            0,
            0,
            0,
            0,
            $this->thumbnailWidth,
            $this->thumbnailHeight,
            $picture->getWidth() ?? 0,
            $picture->getHeight() ?? 0
        );

        $name = uniqid('thumb_', true) . '.jpg';
        imagejpeg($thumb, $this->path->getThumbnailsDirectory() . $name);

        $picture->setThumbnail($name);
    }
}
