<?php

namespace OpenOrchestra\MediaFileBundle\Tests\Manager;

use OpenOrchestra\MediaFileBundle\Manager\MediaStorageManager;
use Phake;
use ReflectionObject;

/**
 * Class MediaStorageManagerTest
 */
class MediaStorageManagerTest extends \PHPUnit_Framework_TestCase
{
    protected $mediaStorageManager;
    protected $adapter;
    protected $filesystem;
    protected $filesystemMap;

    /**
     * Set up the test
     */
    public function setUp()
    {
        $this->adapter = Phake::mock('Gaufrette\Adapter');

        $this->filesystem = Phake::mock('Gaufrette\Filesystem');
        Phake::when($this->filesystem)->getAdapter()->thenReturn($this->adapter);

        $this->filesystemMap = Phake::mock('Knp\Bundle\GaufretteBundle\FilesystemMap');
        Phake::when($this->filesystemMap)->get(Phake::anyParameters())->thenReturn($this->filesystem);

        $this->mediaStorageManager = new MediaStorageManager($this->filesystemMap, 'someFileSystem');
    }

    /**
     * @param string $key
     * @param string $filePath
     *
     * @dataProvider provideKeysAndContents
     */
    public function testUploadFile($key, $filePath)
    {
        $this->markTestSkipped("Skipped until media tests are rewrote");

        $this->mediaStorageManager->uploadFile($key, $filePath, false);

        Phake::verify($this->adapter, Phake::times(1))->write($key, $filePath);
    }

    /**
     * @return array
     */
    public function provideKeysAndContents()
    {
        return array(
            array('someKey', 'someContent')
        );
    }

    /**
     * @param string $key
     *
     * @dataProvider provideKeys
     */
    public function testGetFileContent($key)
    {
        $this->mediaStorageManager->getFileContent($key);

        Phake::verify($this->adapter, Phake::times(1))->read($key);
    }

    /**
     * @return array
     */
    public function provideKeys()
    {
        return array(
            array('someKey')
        );
    }

    /**
     * @param string $key
     *
     * @dataProvider provideKeys
     */
    public function testDeleteContent($key)
    {
        $this->mediaStorageManager->deleteContent($key);

        Phake::verify($this->adapter, Phake::times(1))->delete($key);
    }

    /**
     * @param string $key
     *
     * @dataProvider provideKeys
     */
    public function testExists($key)
    {
        $this->mediaStorageManager->exists($key);

        Phake::verify($this->adapter, Phake::times(1))->exists($key);
    }

    /**
     * Clean up object
     */
    protected function tearDown()
    {
        $refl = new ReflectionObject($this);
        foreach ($refl->getProperties() as $prop) {
            if (!$prop->isStatic() && 0 !== strpos($prop->getDeclaringClass()->getName(), 'PHPUnit_')) {
                $prop->setAccessible(true);
                $prop->setValue($this, null);
            }
        }
    }
}
