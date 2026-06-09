<?php

declare(strict_types=1);

namespace Kotaru\SuluUtils\Traits;

use JMS\Serializer\Annotation as JMS;
use Doctrine\Common\Collections\Collection;
use Sulu\Bundle\TagBundle\Tag\TagInterface;

trait TagTrait
{

    public function getTagsResolved(): array
    {
        return !$this->tags->isEmpty() ? \array_map(fn($tag) => $tag->getName(), $this->tags->toArray()) : [];
    }

    /**  @return Collection<int, TagInterface> */
    public function getTags(): Collection
    {
        return $this->tags;
    }
    public function hasTag(TagInterface $tag): bool
    {
        return $this->tags->contains($tag);
    }
    public function setTags(Collection $tags): static
    {
        $this->tags = $tags;
        return $this;
    }
    public function addTag(TagInterface $tag): static
    {
        if (!$this->tags->contains($tag)) {
            $this->tags->add($tag);
        }

        return $this;
    }
    public function clearTags(): static
    {
        $this->tags->clear();
        return $this;
    }
}
