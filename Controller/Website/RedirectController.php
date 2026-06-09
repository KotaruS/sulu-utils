<?php

namespace Kotaru\SuluUtils\Controller\Website;

use Symfony\Component\HttpFoundation\Response;
use Sulu\Component\Content\Compat\StructureInterface;
use Sulu\Bundle\WebsiteBundle\Controller\DefaultController;

class RedirectController extends DefaultController
{

    public function indexAction(StructureInterface $structure, $preview = false, $partial = false)
    {
        $data = $this->getAttributes([], $structure, $preview);
        $request = $this->container->get('request_stack')->getCurrentRequest();

        if ($preview) {
            $viewTemplate = $structure->getView() . '.html.twig';
            $content = $this->renderPreview(
                $viewTemplate,
                $data
            );
            $response = new Response($content);

            // we need to set the content type ourselves here
            // else symfony will use the accept header of the client and the page could be cached with false content-type
            // see following symfony issue: https://github.com/symfony/symfony/issues/35694
            $mimeType = $request->getMimeType('html');

            if ($mimeType) {
                $response->headers->set('Content-Type', $mimeType);
            }
            return $response;
        }

        if (isset($data['content']['link'])) {
            $redirectTo = \in_array($data['view']['link']['provider'], ['media', 'page', 'external']) ? $data['content']['link'] : $request->getUriForPath('/') . $data['content']['link'];
            // dd($redirectTo);
            return $this->redirect($redirectTo, 301);
        }
        $response = new Response('');

        if (!$preview && $this->getCacheTimeLifeEnhancer()) {
            $this->getCacheTimeLifeEnhancer()->enhance($response, $structure);
        }

        return $response;

    }

}
