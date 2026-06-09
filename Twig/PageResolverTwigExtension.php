<?php

namespace Kotaru\SuluUtils\Twig;

use Twig\TwigFunction;
use Twig\Extension\AbstractExtension;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Sulu\Component\DocumentManager\DocumentManagerInterface;

/**
 * @version 1.0.0
 * Provides the content_path function to generate real urls for frontend.
 */
class PageResolverTwigExtension extends AbstractExtension
{


    private DocumentManagerInterface $documentManager;

    private string $defaultLocale;

    public function __construct(
        DocumentManagerInterface $documentManager,
        string $defaultLocale,
    ) {
        $this->documentManager = $documentManager;
        $this->defaultLocale = $defaultLocale;
    }

    public function getFunctions()
    {
        return [
            new TwigFunction('sulu_resolve_page', [$this, 'getContentPath']),
        ];
    }

    public function getContentPath($uuid, $locale = 'cs')
    {

        $url = null;
        $page = $this->documentManager->find($uuid, $locale);
        if (!$page) {
            return $uuid;
        }
        $structure = $page->getStructure();
        $path = $structure->getProperty('url')->getValue();
        return $locale === $this->defaultLocale ? (string) $path : '/' + $locale + $path;
    }


}
