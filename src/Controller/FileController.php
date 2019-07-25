<?php
declare(strict_types=1);

namespace AppBundle\Controller;


use AppBundle\Entity\Pack;
use AppBundle\Service\UploadManager;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Class FileController
 * @package AppBundle\Controller
 * @Route("file")
 */
class FileController extends Controller
{
    /**
     * List current packs in temporary directory
     *
     * @Route("/new/{name}", name="ftp_pack_list", defaults={"name": ""})
     * @Method("GET")
     * @param string $name
     * @return Response
     */
    public function newAction(string $name = ""): Response
    {
        $tmpDir = $this->getParameter('kernel.root_dir') . '/../web/pictures/ftp';
        // Read files from first level in temp dir
        $detectedFiles = (is_dir($tmpDir)) ? scandir($tmpDir) : [];

        if (!empty($name)) {
            if (!in_array($name, $detectedFiles)) {
                $this->get('session')->getFlashBag()->add('danger', 'Pack not found');
            } else {
                $pack = new Pack();
                $pack->setCreator($this->getUser());
                $pack->setStoragePath($tmpDir . '/');
                $pack->setName(pathinfo($name)['filename']);
                /** @var UploadManager $uploadManager */
                $uploadManager = $this->get(UploadManager::class);
                try {
                    if (is_dir($tmpDir . '/' . $name)) {
                        $pack = $uploadManager->uploadFileDir($tmpDir . '/' . $name, $pack);
                    } else {
                        $file = new File($tmpDir . '/' . $name);
                        $pack->setFile($file);

                        $pack = $uploadManager->upload($pack);

                        $uploadManager->deleteFTPFile($pack->getFile());
                    }

                    return $this->redirectToRoute('pack_pre_show', array('id' => $pack->getId()));
                } catch (\Exception $e) {
                    $this->get('session')->getFlashBag()->add('danger', 'Unable to handle file upload');
                    $this->get('session')->getFlashBag()->add('danger', $e->getMessage());
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

        return $this->render(':file:index.html.twig', ['packs' => $packs]);
    }
}
