<?php
declare(strict_types=1);

namespace AppBundle\Service;

/**
 * Class PictureConverter
 * @package AppBundle\Service
 */
class PictureConverter
{
    /**
     * Convert picture to PNG
     * @param string $path
     * @return string
     */
    public static function convertPicture(string $path): string
    {
        try {
            $picture = new \Imagick(realpath($path));
            $picture->setFormat('png');
            $pathInfo = pathinfo($path);
            $newFileName = $pathInfo['dirname'] . '/' . $pathInfo['filename'] . '.png';
            $picture->writeImage($newFileName);

            return $newFileName;
        } catch (\Exception $exception) {
            return '';
        }
    }
}
