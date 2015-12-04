<?php

namespace OpenOrchestra\MediaFileBundle\Manager;

use Knp\Bundle\GaufretteBundle\FilesystemMap;

/**
 * Class MediaStorageManager
 */
class MediaStorageManager
{
    protected $adapter;

    /**
     * @param FilesystemMap $filesystemMap
     * @param string        $filesystem
     */
    public function __construct(FilesystemMap $filesystemMap, $filesystem)
    {
        $this->adapter = $filesystemMap->get($filesystem)->getAdapter();
    }

    /**
     * Upload a file with gaufrette with the key $key and content $filecontent
     * 
     * @param string $key
     * @param string $filecontent
     *
     * @return integer|boolean The number of bytes that were written into the file
     */
    public function uploadContent($key, $filecontent)
    {
        return $this->adapter->write($key, $filecontent);
    }

    /**
     * Get content the $key file from storage
     * 
     * @param string $key
     *
     * @return string|boolean if cannot read content
     */
    public function getFileContent($key)
    {
        return $this->adapter->read($key);
    }

    /**
     * Download in $downloadDir the $key file from storage
     * 
     * @param $key
     * @param $downloadDir
     */
    public function downloadFile($key, $downloadDir)
    {
        if ($this->exists($key)) {
            $downloadedFilePath = $downloadDir . DIRECTORY_SEPARATOR . $key;
            $fileHandler = fopen($downloadedFilePath, 'a');
            fwrite($fileHandler, $this->getFileContent($key));
            fclose($fileHandler);

            return $downloadedFilePath;
        }

        return '';
    }

    /**
     * @param string $key
     *
     * @return bool
     */
    public function deleteContent($key)
    {
        return $this->adapter->delete($key);
    }

    /**
     * @param string $key
     *
     * @return bool
     */
    public function exists($key)
    {
        return $this->adapter->exists($key);
    }
}
