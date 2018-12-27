<?php
namespace TestBundle\Service;

use Doctrine\ORM\EntityManagerInterface;
use GuzzleHttp\Psr7\Stream;
use League\Flysystem\Adapter\Polyfill\NotSupportingVisibilityTrait;
use League\Flysystem\Adapter\Polyfill\StreamedCopyTrait;
use League\Flysystem\AdapterInterface;
use League\Flysystem\Config;
use TestBundle\Entity\File;

class PdoFlySystem implements AdapterInterface
{
    use StreamedCopyTrait;
    use NotSupportingVisibilityTrait;

    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }

    /**
     * {@inheritdoc}
     */
    public function write($path, $contents, Config $config, $size = 0)
    {
        $file = $this->em->getRepository(File::class)->findOneByName($path);
        if(empty($file))
        {
            $file = new File();
            $file->setName($path);
        }

        $file->setContent($contents);
        $this->em->persist($file);
        $this->em->flush();

        return $this->normalizeObject($file);
    }

    /**
     * {@inheritdoc}
     */
    public function writeStream($path, $resource, Config $config)
    {
        return $this->write($path, new Stream($resource), $config, fstat($resource)['size']);
    }

    /**
     * {@inheritdoc}
     */
    public function update($path, $contents, Config $config)
    {
        return $this->write($path, $contents, $config);
    }

    /**
     * {@inheritdoc}
     */
    public function updateStream($path, $resource, Config $config)
    {
        return $this->write($path, new Stream($resource), $config, fstat($resource)['size']);
    }

    /**
     * {@inheritdoc}
     */
    public function rename($path, $newpath)
    {
        $file = $this->em->getRepository(File::class)->findOneByName($path);
        if(empty($file))
        {
            return false;
        }

        $file->setName($newpath);
        $this->em->flush();

        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function delete($path)
    {

        $file = $this->em->getRepository(File::class)->findOneByName($path);
        if(empty($file))
        {
            return false;
        }

        $this->em->remove($file);
        $this->em->flush();
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function deleteDir($dirname)
    {
        return false;
    }

    /**
     * {@inheritdoc}
     */
    public function createDir($dirname, Config $config)
    {
        return false;
    }

    /**
     * {@inheritdoc}
     */
    public function has($path)
    {
        $file = $this->em->getRepository(File::class)->findOneByName($path);
        if(empty($file))
        {
            return false;
        }

        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function read($path)
    {
        $file = $this->em->getRepository(File::class)->findOneByName($path);

        if(empty($file))
        {
            return false;
        }

        $data = $this->normalizeObject($file);

        if(is_string($file->getContent()))
        {
            $data["contents"] = $file->getContent();
        }
        elseif(is_resource($file->getContent()))
        {
            $data["contents"] = stream_get_contents($file->getContent());
        }

        return $data;
    }

    /**
     * {@inheritdoc}
     */
    public function readStream($path)
    {
        $file = $this->em->getRepository(File::class)->findOneByName($path);
        if(empty($file))
        {
            return false;
        }

        $data = $this->normalizeObject($file);


        if(is_string($file->getContent()))
        {
            $stream = fopen('php://memory','r+');
            fwrite($stream, $file->getContent());
            rewind($stream);
            $data['stream'] = $stream;
        }
        elseif(is_resource($file->getContent()))
        {
            $data["stream"] = $file->getContent();
        }

        return $data;
    }

    /**
     * {@inheritdoc}
     */
    public function listContents($directory = '', $recursive = false)
    {
        return [];
    }

    /**
     * {@inheritdoc}
     */
    public function getMetadata($path)
    {
        $file = $this->em->getRepository(File::class)->findOneByName($path);
        if(empty($file))
        {
            return false;
        }
        return $this->normalizeObject($file);
    }

    /**
     * {@inheritdoc}
     */
    public function getSize($path)
    {
        return $this->getMetadata($path);
    }

    /**
     * {@inheritdoc}
     */
    public function getMimetype($path)
    {
        return $this->getMetadata($path);
    }

    /**
     * {@inheritdoc}
     */
    public function getTimestamp($path)
    {
        return $this->getMetadata($path);
    }

    /**
     * Normalize Openstack "StorageObject" object into an array
     *
     * @return array
     */
    protected function normalizeObject(File $file)
    {
        $size = 0;
        if(is_string($file->getContent()))
        {
            $size = strlen($file->getContent());
        }
        elseif(is_resource($file->getContent()))
        {
            $size = fstat($file->getContent())['size'];
        }

        return [
            'type'      => 'file',
            'dirname'   => $file->getName(),
            'path'      => $file->getName(),
            'timestamp' => "0",
            'mimetype'  => "application/octet-stream",
            'size'      => $size
        ];
    }
}