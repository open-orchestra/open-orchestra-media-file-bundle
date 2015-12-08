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
     * Upload $filePath file with the key $key
     * 
     * @param string  $key
     * @param string  $filePath
     * @param boolean $deleteAfterUpload
     *
     * @return int The number of bytes written into the file
     */
    public function uploadFile($key, $filePath, $deleteAfterUpload = true)
    {
        if (is_dir($filePath)) {
            return 0;
        }

        $size =  $this->adapter->write($key, file_get_contents($filePath));

        if ($deleteAfterUpload) {
            unlink($filePath);
        }

        return $size;
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
     * @param string $key
     * @param string $downloadDir
     * 
     * @return string
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
