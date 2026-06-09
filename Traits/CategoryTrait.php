<?php

declare(strict_types=1);

namespace Kotaru\SuluUtils\Traits;


use JMS\Serializer\Annotation as JMS;
use Doctrine\Common\Collections\Collection;
use Sulu\Bundle\CategoryBundle\Entity\CategoryInterface;

trait CategoryTrait
{

    /**  @return Collection<int, CategoryInterface> */
    public function getCategories(): Collection
    {
        return $this->categories;
    }

    public function getCategoriesResolved(): array
    {
        $locale = $this->hasLocale() ? $this->getLocale() : null;
        $resolveCategory = function (CategoryInterface $category) use (&$resolveCategory, $locale): ?array {
            if (!$category->getChildren()->isEmpty()) {
                \array_map($resolveCategory, $category->getChildren()->toArray());
            }
            $categoryTranslation = $category->findTranslationByLocale($locale ?? $category->getDefaultLocale());
            $categoryTitle = false === $categoryTranslation
                ? $categoryTranslation = $category->findTranslationByLocale($category->getDefaultLocale())->getTranslation()
                : $categoryTranslation->getTranslation();
            return [
                'id' => $category->getId(),
                'key' => $category->getKey(),
                'title' => $categoryTitle,
            ];
        };
        return !$this->categories->isEmpty() ? \array_map($resolveCategory, $this->categories->toArray()) : [];
    }
    public function hasCategory(CategoryInterface $category): bool
    {
        return $this->categories->contains($category);
    }
    public function setCategories(Collection $categories): static
    {
        $this->categories = $categories;
        return $this;
    }
    public function addCategory(CategoryInterface $category): static
    {
        if (!$this->categories->contains($category)) {
            $this->categories->add($category);
        }

        return $this;
    }
    public function clearCategories(): static
    {
        $this->categories->clear();
        return $this;
    }
}
