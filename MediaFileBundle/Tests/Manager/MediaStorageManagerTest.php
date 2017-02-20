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
    protected $gaufrettefilesystem;
    protected $filesystemMap;
    protected $filesystem;
    protected $mediaDomain = 'domain';
    protected $relativeUrl = 'relativeUrl';

    /**
     * Set up the test
     */
    public function setUp()
    {
        $this->adapter = Phake::mock('Gaufrette\Adapter');

        $this->gaufrettefilesystem = Phake::mock('Gaufrette\Filesystem');
        Phake::when($this->gaufrettefilesystem)->getAdapter()->thenReturn($this->adapter);

        $this->filesystemMap = Phake::mock('Knp\Bundle\GaufretteBundle\FilesystemMap');
        Phake::when($this->filesystemMap)->get(Phake::anyParameters())->thenReturn($this->gaufrettefilesystem);

        $this->filesystem = Phake::mock('Symfony\Component\Filesystem\Filesystem');

        $router = Phake::mock('Symfony\Component\Routing\Generator\UrlGeneratorInterface');
        Phake::when($router)->generate(Phake::anyParameters())->thenReturn($this->relativeUrl);

        $this->mediaStorageManager =
            new MediaStorageManager($this->filesystemMap, 'someFileSystem', $this->filesystem, $router, $this->mediaDomain);
    }

    /**
     * @param string $key
     * @param string $filePath
     * @param bool   $delete
     * @param int    expectedDelete
     * @param int    expectedSize
     *
     * @dataProvider provideKeysAndContents
     */
    public function testUploadFile($key, $filePath, $delete, $expectedDelete, $expectedSize)
    {
        Phake::when($this->adapter)->write(Phake::anyParameters())->thenReturn($expectedSize);

        $size = $this->mediaStorageManager->uploadFile($key, $filePath, $delete);

        Phake::verify($this->adapter, Phake::times(1))->write(Phake::anyParameters());
        Phake::verify($this->filesystem, Phake::times($expectedDelete))->remove($filePath);
        $this->assertSame($size, $expectedSize);
    }

    /**
     * @return array
     */
    public function provideKeysAndContents()
    {
        return array(
            array('someKey', __FILE__, false, 0, 25),
            array('someKey', __FILE__, true, 1, 42),
        );
    }

    /**
     * Test uploadFile with no file
     *
     * @param string $key
     * @param string $filePath
     * @param bool   $delete
     *
     * @dataProvider provideKeysAndNoContent
     */
    public function testUploadFileWithNoFile($key, $filePath, $delete)
    {
        $this->setExpectedException('OpenOrchestra\MediaFileBundle\Exception\BadFileException');

        $this->mediaStorageManager->uploadFile($key, $filePath, $delete);
    }

    /**
     * @return array
     */
    public function provideKeysAndNoContent()
    {
        return array(
            array('someKey', './' , false),
            array('someKey', './', true),
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
     * Test getUrl
     *
     * @param string $key
     * @param string $expectedUrl
     *
     * @dataProvider provideUrl
     */
    public function testGetUrl($key, $expectedUrl)
    {
        $url = $this->mediaStorageManager->getUrl($key);

        $this->assertSame($expectedUrl, $url);
    }

    /**
     * 
     * @return array
     */
    public function provideUrl() {
        return array(
            array(null, null),
            array('randomKey', '//' . $this->mediaDomain . $this->relativeUrl),
        );
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
