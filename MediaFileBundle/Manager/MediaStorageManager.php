<?php

namespace OpenOrchestra\MediaFileBundle\Manager;

use OpenOrchestra\MediaFileBundle\Exception\BadFileException;
use Knp\Bundle\GaufretteBundle\FilesystemMap;
use Symfony\Component\Filesystem\Filesystem;

/**
 * Class MediaStorageManager
 */
class MediaStorageManager
{
    protected $adapter;
    protected $fileSystem;

    /**
     * @param FilesystemMap $filesystemMap
     * @param string        $filesystemKey
     * @param Filesystem    $fileSystem
     */
    public function __construct(FilesystemMap $filesystemMap, $filesystemKey, Filesystem $fileSystem)
    {
        $this->adapter = $filesystemMap->get($filesystemKey)->getAdapter();
        $this->fileSystem = $fileSystem;
    }

    /**
     * Upload $filePath file with the key $key
     * 
     * @param string  $key
     * @param string  $filePath
     * @param boolean $deleteAfterUpload
     *
     * @return int The number of bytes written into the file
     * @throws BadFileException
     */
    public function uploadFile($key, $filePath, $deleteAfterUpload = true)
    {
        if (is_dir($filePath)) {
            throw new BadFileException();
        }

        $size =  $this->adapter->write($key, file_get_contents($filePath));

        if ($deleteAfterUpload) {
            $this->fileSystem->remove($filePath);
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
