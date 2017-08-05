<?php
declare(strict_types=1);

namespace AppBundle\Service;


use AppBundle\Factory\ImageFactory;

/**
 * Class PictureConverter
 * @package AppBundle\Service
 */
class PictureConverter
{
    /**
     * Convert picture to PNG
     * @param string $path
     * @return bool
     */
    public static function convertPicture(string $path) : string
    {
        try {
            $picture = ImageFactory::getResource($path);
            $pathinfo = pathinfo($path);
            $newFileName = $pathinfo['dirname'] . '/' . $pathinfo['filename'] . '.png';
            imagepng($picture, $newFileName);

            return $newFileName;
        } catch (\Exception $exception) {
            return '';
        }
    }
}