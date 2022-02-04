<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\Pack;
use App\Service\UploadManager;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\Flash\FlashBagInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Security;

#[Route('file')]
class FileController extends AbstractController
{
    #[Route('/new/{name}', name:'ftp_pack_list', defaults: ['name' => ''], methods:['GET'])]
    public function newAction(
        string $name,
        string $storagePath,
        FlashBagInterface $flashBag,
        Security $security,
        UploadManager $uploadManager
    ): Response {
        $tmpDir = $storagePath . '/pictures/ftp';
        // Read files from first level in temp dir
        $detectedFiles = (is_dir($tmpDir)) ? (scandir($tmpDir) ?: []) : [];

        if (!empty($name)) {
            if (!in_array($name, $detectedFiles)) {
                $flashBag->add('danger', 'Pack not found');
            } else {
                $pack = new Pack();
                $pack->setCreator($security->getUser());
                $pack->setStoragePath($tmpDir . '/');
                $pack->setName(pathinfo($name)['filename']);
                try {
                    if (is_dir($tmpDir . '/' . $name)) {
                        $pack = $uploadManager->uploadFileDir($tmpDir . '/' . $name, $pack);
                    } else {
                        $file = new File($tmpDir . '/' . $name);
                        $pack->setFile($file);

                        $pack = $uploadManager->upload($pack);
                        if ($pack->getFile() === null) {
                            throw new \LogicException('File is missing from pack');
                        }
                        $uploadManager->deleteFTPFile($pack->getFile());
                    }

                    return $this->redirectToRoute('pack_pre_show', array('id' => $pack->getId()));
                } catch (\Exception $e) {
                    $flashBag->add('danger', 'Unable to handle file upload');
                    $flashBag->add('danger', $e->getMessage());
                }
            }
        }

        $packs = [];
        foreach ($detectedFiles as $file) {
            if ($file === '.' || $file === '..') {
                continue;
            }
            $pack = [];
            $pack['name'] = $file;
            $pack['size'] = round(filesize($tmpDir . '/' . $file) / 1024 / 1024, 2); // Mo
            $packs[] = $pack;
        }

        return $this->render('file/index.html.twig', ['packs' => $packs]);
    }
}
