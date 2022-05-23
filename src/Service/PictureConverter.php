<?php

declare(strict_types=1);

namespace App\Service;

use App\Entity\Picture;
use Imagick;
use Intervention\Image\Constraint;
use Intervention\Image\ImageManager;

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
        $imageManager = new ImageManager(['driver' => 'gd']);
        $image = $imageManager
            ->make($picturePath);
        $isVertical = $image->height() > $image->width();
        $image->resize(
            !$isVertical ? null : $this->thumbnailWidth,
            !$isVertical ? $this->thumbnailHeight : null,
            function (Constraint $constraint) {
                $constraint->aspectRatio();
            }
        )
            ->crop($this->thumbnailWidth, $this->thumbnailHeight)
            ->save($this->path->getThumbnailsDirectory() . $name);

        $picture->setThumbnail($name);
    }

	public function rotate(Picture $picture, bool $isCounterClockWise = false): void
	{
		$imageManager = new ImageManager(['driver' => 'gd']);
		$image = $imageManager
			->make($this->path->getPictureFullpath($picture));
		$image->rotate($isCounterClockWise ? 90.0 : -90.0)
			->save();
	}
}
