<?php

namespace Kotaru\SuluUtils\Link;

use Sulu\Bundle\MarkupBundle\Markup\Link\LinkItem;
use Sulu\Bundle\MarkupBundle\Markup\Link\LinkProviderInterface;
use Symfony\Component\DependencyInjection\Attribute\AutoconfigureTag;

class LocalLinkProvider implements LinkProviderInterface
{
    /**
     * {@inheritdoc}
     */
    public function getConfiguration()
    {
        return null;
    }

    /**
     * {@inheritdoc}
     */
    public function preload(array $hrefs, $locale, $published = true)
    {
        if (0 === count($hrefs)) {
            return [];
        }

        foreach ($hrefs as $hash) {
            $result[] = new LinkItem($hash, 'hash', '#' . $hash, true);
        }

        return $result;
    }
}
