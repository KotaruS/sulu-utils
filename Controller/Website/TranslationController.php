<?php

declare(strict_types=1);

namespace Kotaru\SuluUtils\Controller\Website;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Translation\TranslatorBagInterface;

class TranslationController extends AbstractController
{
    public function __construct(
        private TranslatorBagInterface $translatorBag,
    ) {
    }

    public function getTranslations(string $locale): Response
    {
        $catalogue = $this->translatorBag->getCatalogue($locale);

        $translations = $catalogue->all('website');

        if (0 === \count($translations)) {
            $translations = new \stdClass();
        }

        return new JsonResponse($translations);
    }

}
