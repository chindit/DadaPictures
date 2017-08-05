<?php
declare(strict_types=1);

namespace AppBundle\Factory;

use Symfony\Component\Filesystem\Exception\IOException;

/**
 * Class ImageFactory
 * @package AppBundle\Factory
 */
class ImageFactory
{
    /**
     * Return GD resource from file path
     * @param string $filePath
     * @return resource
     */
    public static function getResource(string $filePath)
    {
        if (!is_file($filePath)) {
            throw new IOException('File «' . $filePath . '» was not found');
        }
        $imageType = exif_imagetype($filePath);
        if ($imageType === false) {
            throw new IOException('File «' . $filePath . '» is not a picture');
        }

        switch ($imageType) {
            case IMAGETYPE_GIF:
                return imagecreatefromgif($filePath);
            case IMAGETYPE_JPEG:
                return imagecreatefromjpeg($filePath);
            case IMAGETYPE_PNG:
                return imagecreatefrompng($filePath);
            case IMAGETYPE_WBMP:
                return imagecreatefromwbmp($filePath);
            case IMAGETYPE_XBM:
                return imagecreatefromxbm($filePath);
            default:
                throw new \InvalidArgumentException('This type of picture is not supported');
        }
    }
}
