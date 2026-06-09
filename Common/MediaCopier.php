<?php

declare(strict_types=1);

namespace Kotaru\SuluUtils\Common;

use Kotaru\SuluUtils\Domain\Event\MediaCopiedEvent;
use Doctrine\ORM\EntityManagerInterface;
use Sulu\Bundle\MediaBundle\Entity\File;
use Sulu\Component\PHPCR\PathCleanupInterface;
use Sulu\Bundle\MediaBundle\Entity\MediaInterface;
use Sulu\Bundle\MediaBundle\Media\Storage\StorageInterface;
use Sulu\Bundle\MediaBundle\Entity\MediaRepositoryInterface;
use Sulu\Bundle\MediaBundle\Media\Manager\MediaManagerInterface;
use Sulu\Bundle\MediaBundle\Media\TypeManager\TypeManagerInterface;
use Sulu\Bundle\ActivityBundle\Application\Collector\DomainEventCollectorInterface;

class MediaCopier
{
    public const int IS_PATH = 1;

    public function __construct(
        protected MediaManagerInterface $mediaManager,
        protected MediaRepositoryInterface $mediaRepository,
        protected StorageInterface $storage,
        private EntityManagerInterface $entityManager,
        private DomainEventCollectorInterface $domainEventCollector,
        private PathCleanupInterface $pathCleaner,
        protected TypeManagerInterface $typeManager,
    ) {
    }


    public function getCopy(MediaInterface $media): ?MediaInterface
    {
        $file = $this->getFile($media);
        $fileVersion = $file->getLatestFileVersion();
        $locale = $fileVersion->getDefaultMeta()->getLocale();
        $fileOptions = $fileVersion->getStorageOptions();

        $filePath = $this->storage->getPath($fileOptions);
        $tempFile = $this->createTemporaryFile($filePath);

        $storageOptions = $this->storage->save(
            $tempFile,
            $this->getNormalizedFileName($fileVersion->getName())
        );
        \unlink($tempFile);

        $newMedia = $this->mediaRepository->createNew();
        $newMedia->setCreator($media->getCreator());
        $newMedia->setChanger($media->getChanger());
        $newMedia->setType($media->getType());
        $newMedia->setCollection($media->getCollection());
        $this->entityManager->persist($newMedia);


        $newFile = new File();
        $newFile->setCreator($file->getCreator());
        $newFile->setChanger($file->getChanger());
        $newFile->setVersion(1);
        $newFile->setMedia($newMedia);
        $this->entityManager->persist($newFile);

        $newFileVersion = clone $fileVersion;
        $this->entityManager->persist($newFileVersion);
        $newFileVersion->setVersion(1);
        $newFileVersion->setStorageOptions($storageOptions);
        $newFileVersion->setChanged(new \DateTime());
        $newFileVersion->setChanger($fileVersion->getCreator());
        $newFileVersion->setCreated(new \DateTime());
        $newFileVersion->setCreator($fileVersion->getCreator());
        $newFileVersion->setDownloadCounter(0);
        $newFileVersion->setFile($newFile);

        $newFile->addFileVersion($newFileVersion);
        $newMedia->addFile($newFile);

        $this->entityManager->persist($newMedia);

        if (null !== $media->getPreviewImage()) {
            $newMedia->setPreviewImage($this->getCopy($media->getPreviewImage()));
        }

        // $this->domainEventCollector->collect(
        //   new MediaCopiedEvent($newMedia, $locale)
        // );

        $this->entityManager->flush();
        return $newMedia;
    }

    private function getFile(MediaInterface $media): ?File
    {
        foreach ($media->getFiles() as $file) {
            return $file;
        }
    }

    /**
     * Returns file name without special characters and preserves file extension.
     */
    private function getNormalizedFileName(string $originalFileName): string
    {
        if (false !== \strpos($originalFileName, '.')) {
            $pathParts = \pathinfo($originalFileName);
            $fileName = $this->pathCleaner->cleanup($pathParts['filename']);
            $fileName .= '.' . $pathParts['extension'];
        } else {
            $fileName = $this->pathCleaner->cleanup($originalFileName);
        }

        return $fileName;
    }

    /**
     * Create temporary resource which will removed on fclose or end of process.
     *
     * @return resource
     */
    private function createTemporaryResource(string $content)
    {
        $tempResource = \fopen('php://memory', 'r+');
        \fwrite($tempResource, $content);
        \rewind($tempResource);

        return $tempResource;
    }

    /**
     * Returns the path to a temporary file containing the given content.
     *
     * @param resource|string $file
     */
    private function createTemporaryFile($file, int $options = 0): string
    {
        if (!is_resource($file)) {
            $resource = ($this::IS_PATH & $options) === 0 ? \fopen($file, 'r') : $this->createTemporaryResource($file);
        } else {
            $resource = $file;
        }
        $tempPath = \tempnam(\sys_get_temp_dir(), 'media-clone');
        $tempResource = \fopen($tempPath, 'w');

        \stream_copy_to_stream($resource, $tempResource);

        return $tempPath;
    }
}
