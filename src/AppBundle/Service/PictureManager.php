<?php
declare(strict_types=1);

namespace AppBundle\Service;
use Symfony\Component\HttpFoundation\File\Exception\FileNotFoundException;

/**
 * Class PictureManager
 * @package AppBundle\Service
 */
class PictureManager
{
    public function hasDuplicates()
    {

    }

    /**
     * Return an array with hashes with MD5 first and SHA1 second
     * @param string $path
     * @return array
     */
    public function getHashes(string $path) : array
    {
        if (!is_file($path)) {
            throw new FileNotFoundException($path);
        }

        return [ md5_file($path), sha1_file($path)];
    }
}