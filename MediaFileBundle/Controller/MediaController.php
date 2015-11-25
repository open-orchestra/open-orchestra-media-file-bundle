<?php

namespace OpenOrchestra\MediaFileBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use Sensio\Bundle\FrameworkExtraBundle\Configuration as Config;

/**
 * Class MediaController
 */
class MediaController extends Controller
{
    /**
     * Send a media stored via the UploadedFileManager
     *
     * @Config\Route("/{key}", name="open_orchestra_media_get")
     * @Config\Method({"GET"})
     *
     * @return Response
     */
    public function getAction($key)
    {
        $uploadedMediaManager = $this->get('open_orchestra_media_file.manager.uploaded_media');
        $fileContent = $uploadedMediaManager->getFileContent($key);

        $finfo = finfo_open(FILEINFO_MIME);
        $mimetype = finfo_buffer($finfo, $fileContent);
        finfo_close($finfo);

        $response = new Response();
        $response->headers->set('Content-Type', $mimetype);
        $response->headers->set('Content-Length', strlen($fileContent));
        $response->setContent($fileContent);
        $response->setPublic();
        $response->setMaxAge(2629743);

        return $response;
    }
}
