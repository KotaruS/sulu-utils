<?php

declare(strict_types=1);

namespace Kotaru\SuluUtils\Doctrine;

use Doctrine\ORM\EntityManagerInterface;
use Sulu\Component\Rest\ListBuilder\CollectionRepresentation;
use Sulu\Component\Rest\RestHelperInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Sulu\Component\Security\Authorization\PermissionTypes;
use Sulu\Component\Rest\ListBuilder\ListRestHelperInterface;
use Sulu\Component\Rest\ListBuilder\PaginatedRepresentation;
use Sulu\Component\Rest\ListBuilder\Doctrine\DoctrineListBuilder;
use Sulu\Component\Rest\ListBuilder\Metadata\FieldDescriptorFactoryInterface;
use Sulu\Component\Rest\ListBuilder\Doctrine\DoctrineListBuilderFactoryInterface;
use Sulu\Component\Rest\ListBuilder\Doctrine\FieldDescriptor\DoctrineFieldDescriptor;

class DoctrineListRepresentationFactory
{
    public function __construct(
        private readonly RestHelperInterface $restHelper,
        private readonly ListRestHelperInterface $listRestHelper,
        private readonly DoctrineListBuilderFactoryInterface $listBuilderFactory,
        private readonly FieldDescriptorFactoryInterface $fieldDescriptorFactory,
        private readonly EntityManagerInterface $em,
    ) {
    }

    public function createListRepresentation(
        string $resourceKey,
        array $filters = [],
        array $parameters = [],
        array $includedFields = [],
    ): CollectionRepresentation {

        return $this->create(
            null,
            null,
            $resourceKey,
            false,
            $filters,
            $parameters,
            $includedFields,
        );
    }
    public function createPaginatedListRepresentation(
        string $resourceKey,
        array $filters = [],
        array $parameters = [],
        array $includedFields = [],
    ): PaginatedRepresentation {

        return $this->create(
            null,
            null,
            $resourceKey,
            true,
            $filters,
            $parameters,
            $includedFields,
        );
    }
    public function createProtectedListRepresentation(
        UserInterface $user,
        string $resourceKey,
        array $filters = [],
        array $parameters = [],
        array $includedFields = [],
    ): CollectionRepresentation {
        return $this->create(
            $user,
            null,
            $resourceKey,
            false,
            $filters,
            $parameters,
            $includedFields,
        );
    }
    public function createProtectedPaginatedListRepresentation(
        UserInterface $user,
        string $resourceKey,
        array $filters = [],
        array $parameters = [],
        array $includedFields = [],
    ): PaginatedRepresentation {
        return $this->create(
            $user,
            null,
            $resourceKey,
            true,
            $filters,
            $parameters,
            $includedFields,
        );
    }
    public function createLocalizedListRepresentation(
        string $locale,
        string $resourceKey,
        array $filters = [],
        array $parameters = [],
        array $includedFields = [],
    ): CollectionRepresentation {
        return $this->create(
            null,
            $locale,
            $resourceKey,
            false,
            $filters,
            $parameters,
            $includedFields,
        );
    }
    public function createLocalizedPaginatedListRepresentation(
        string $locale,
        string $resourceKey,
        array $filters = [],
        array $parameters = [],
        array $includedFields = [],
    ): PaginatedRepresentation {
        return $this->create(
            null,
            $locale,
            $resourceKey,
            true,
            $filters,
            $parameters,
            $includedFields,
        );
    }
    public function createProtectedLocalizedListRepresentation(
        UserInterface $user,
        string $locale,
        string $resourceKey,
        array $filters = [],
        array $parameters = [],
        array $includedFields = [],
    ): CollectionRepresentation {

        return $this->create(
            $user,
            $locale,
            $resourceKey,
            false,
            $filters,
            $parameters,
            $includedFields,
        );
    }
    public function createProtectedLocalizedPaginatedListRepresentation(
        UserInterface $user,
        string $locale,
        string $resourceKey,
        array $filters = [],
        array $parameters = [],
        array $includedFields = [],
    ): PaginatedRepresentation {

        return $this->create(
            $user,
            $locale,
            $resourceKey,
            true,
            $filters,
            $parameters,
            $includedFields,
        );
    }

    protected function create(
        ?UserInterface $user,
        ?string $locale,
        string $resourceKey,
        bool $paginated = true,
        array $filters = [],
        array $parameters = [],
        array $includedFields = [],
    ): PaginatedRepresentation|CollectionRepresentation {

        /** @var DoctrineFieldDescriptor[] $fieldDescriptors */
        $fieldDescriptors = $this->fieldDescriptorFactory->getFieldDescriptors($resourceKey);

        /** @var DoctrineListBuilder $listBuilder */
        $listBuilder = $this->listBuilderFactory->create($fieldDescriptors['id']->getEntityName());
        $this->restHelper->initializeListBuilder($listBuilder, $fieldDescriptors);

        if (false === $paginated) {
            $listBuilder->limit(null);
            $listBuilder->setCurrentPage(1);
        }

        if (null !== $locale) {
            $idsWithLocale = $this->em->getRepository($fieldDescriptors['id']->getEntityName())->getIdsWithLocalizedContent($locale);
            $listBuilder->setIds($idsWithLocale);
        }

        if (null !== $user) {
            $listBuilder->setPermissionCheck($user, PermissionTypes::VIEW);
        }

        foreach ($parameters as $key => $value) {
            $listBuilder->setParameter($key, $value);
        }

        foreach ($filters as $key => $value) {
            $listBuilder->where($fieldDescriptors[$key], $value);
        }

        foreach ($includedFields as $field) {
            $listBuilder->addSelectField($fieldDescriptors[$field]);
        }

        $items = $listBuilder->execute();

        // sort the items to reflect the order of the given ids if the list was requested to include specific ids
        $requestedIds = $this->listRestHelper->getIds();
        if (null !== $requestedIds) {
            $idPositions = \array_flip($requestedIds);

            \usort($items, fn($a, $b) => $idPositions[$a['id']] - $idPositions[$b['id']]);
        }

        if (false === $paginated) {
            return new CollectionRepresentation(
                $items,
                $resourceKey
            );
        }

        return new PaginatedRepresentation(
            $items,
            $resourceKey,
            (int) $listBuilder->getCurrentPage(),
            (int) $listBuilder->getLimit(),
            (int) $listBuilder->count(),
        );
    }
}
