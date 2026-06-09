<?php

declare(strict_types=1);

namespace Kotaru\SuluUtils\Traits;

trait CategorySetterTrait
{

    protected function setCategories($entity, ?array $ids)
    {
        $entity->clearCategories();
        if (empty($ids)) {
            return;
        }
        $categories = $this->categoryRepository->findCategoriesByIds($ids);

        foreach ($categories as $category) {
            $entity->addCategory($category);
        }
        return $entity;
    }
}
