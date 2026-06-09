<?php

/*
 * This file is part of Sulu.
 *
 * (c) Sulu GmbH
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Kotaru\SuluUtils\Manager;

use Doctrine\ORM\EntityManagerInterface;
use Sulu\Bundle\MediaBundle\Entity\Collection;
use Sulu\Bundle\MediaBundle\Entity\File;
use Sulu\Bundle\MediaBundle\Entity\FileVersion;
use Sulu\Bundle\MediaBundle\Entity\MediaInterface;
use Sulu\Bundle\MediaBundle\Media\FormatManager\FormatManagerInterface;
use Sulu\Bundle\MediaBundle\Media\Storage\StorageInterface;
use Sulu\Bundle\TrashBundle\Application\TrashManager\TrashManagerInterface;
use Sulu\Component\Security\Authorization\PermissionTypes;
use Sulu\Component\Security\Authorization\SecurityCondition;
use Sulu\Component\Security\Authorization\SecurityCheckerInterface;

/**
 * Custom part of the sulu media manager.
 */
class SuluMediaManager
{

    public function __construct(
        private EntityManagerInterface $em,
        protected StorageInterface $storage,
        private FormatManagerInterface $formatManager,
        private ?SecurityCheckerInterface $securityChecker = null,
        private ?TrashManagerInterface $trashManager = null
    ) {
    }

    public function delete(MediaInterface $mediaEntity, bool $flush = false): void
    {
        $hasId = $mediaEntity->getId() !== null;

        /** @var File $file */
        foreach ($mediaEntity->getFiles() as $file) {
            /** @var FileVersion $fileVersion */
            foreach ($file->getFileVersions() as $fileVersion) {
                if ($hasId) {
                    $this->formatManager->purge(
                        $mediaEntity->getId(),
                        $fileVersion->getName(),
                        $fileVersion->getMimeType()
                    );
                }

                $this->storage->remove($fileVersion->getStorageOptions());

                foreach ($fileVersion->getMeta() as $fileVersionMeta) {
                    // this will trigger massive-search deindex
                    $this->em->remove($fileVersionMeta);
                }
                foreach ($fileVersion->getFormatOptions() as $formatOptions) {
                    $this->em->detach($formatOptions);
                }
                $this->em->detach($fileVersion);
            }
            $this->em->detach($file);
        }

        $this->em->remove($mediaEntity);

        if (true === $flush) {
            $this->em->flush();
        }
    }

    /**
     * Non flushing trashing
     */
    public function trash(MediaInterface $mediaEntity, $checkSecurity = false, bool $flush = false)
    {
        $hasId = $mediaEntity->getId() !== null;

        if ($checkSecurity) {
            $this->securityChecker->checkPermission(
                new SecurityCondition(
                    'sulu.media.collections',
                    null,
                    Collection::class,
                    $mediaEntity->getCollection()->getId()
                ),
                PermissionTypes::DELETE
            );
        }

        if (null !== $this->trashManager) {
            $this->trashManager->store(MediaInterface::RESOURCE_KEY, $mediaEntity);
        }

        /** @var File $file */
        foreach ($mediaEntity->getFiles() as $file) {
            /** @var FileVersion $fileVersion */
            foreach ($file->getFileVersions() as $fileVersion) {
                if ($hasId) {
                    $this->formatManager->purge(
                        $mediaEntity->getId(),
                        $fileVersion->getName(),
                        $fileVersion->getMimeType()
                    );
                }

                $this->storage->remove($fileVersion->getStorageOptions());

                foreach ($fileVersion->getMeta() as $fileVersionMeta) {
                    // this will trigger massive-search deindex
                    $this->em->remove($fileVersionMeta);
                }
                foreach ($fileVersion->getFormatOptions() as $formatOptions) {
                    $this->em->detach($formatOptions);
                }
                $this->em->detach($fileVersion);
            }
            $this->em->detach($file);
        }

        $this->em->remove($mediaEntity);


        // $this->domainEventCollector->collect(
        //     new MediaRemovedEvent($mediaEntity->getId(), $collectionId, $mediaTitle, $locale)
        // );
        if (true === $flush) {
            $this->em->flush();
        }
    }

}
