<?php

declare(strict_types=1);

namespace Kotaru\SuluUtils\Traits;

trait TagSetterTrait
{
    protected function setTags($entity, ?array $tags)
    {
        $entity->clearTags();
        if (empty($tags)) {
            return;
        }
        foreach ($tags as $tagName) {
            $tag = $this->tagManager->findOrCreateByName($tagName);
            if ($tag) {
                $entity->addTag($tag);
            }
        }
        return $entity;
    }
}
