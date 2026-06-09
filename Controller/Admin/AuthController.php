<?php

declare(strict_types=1);

namespace Kotaru\SuluUtils\Controller\Admin;

use Sulu\Bundle\PageBundle\Admin\PageAdmin;
use Sulu\Component\DocumentManager\DocumentManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Sulu\Bundle\AdminBundle\Admin\View\ViewRegistry;
use Sulu\Component\Security\Authorization\PermissionTypes;
use Sulu\Component\Security\Authorization\SecurityCondition;
use Sulu\Component\Content\Document\Behavior\SecurityBehavior;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Sulu\Component\Security\Authorization\SecurityCheckerInterface;
use Sulu\Component\DocumentManager\Exception\DocumentNotFoundException;

class AuthController extends AbstractController
{
    public function __construct(
        private DocumentManagerInterface $documentManager,
        private SecurityCheckerInterface $securityChecker,
        private UrlGeneratorInterface $urlGenerator,
        private ?ViewRegistry $viewRegistry = null,
    ) {
    }

    public function checkPage(string $id, Request $request): Response
    {
        $locale = $this->getLocale($request);

        try {
            $document = $this->documentManager->find(
                $id,
                $locale,
                [
                    'load_ghost_content' => false,
                    'structure_type' => null,
                ]
            );

            $this->securityChecker->checkPermission(
                $this->getSecurityCondition($request, $document),
                PermissionTypes::EDIT
            );
            $webspace = $document->getWebspaceName();

            $user = $this->getUser();
            $view = $this->viewRegistry->findViewByName(PageAdmin::EDIT_FORM_VIEW);
            $path = str_replace([':webspace', ':locale', ':id'], [$webspace, $locale, $id], $view->getPath());

            return $this->json(data: [
                'status' => 'Access Granted',
                'user_locale' => $user->getLocale(),
                'edit_url' => $request->getSchemeAndHttpHost() . '/admin/#' . $path,
            ]);
        } catch (DocumentNotFoundException $ex) {
            return $this->json(data: ['status' => 'Document not found'], status: 404);
        }
    }

    private function getSecurityCondition(Request $request, $document = null): SecurityCondition
    {
        return new SecurityCondition(
            PageAdmin::getPageSecurityContext($document->getWebspaceName()),
            $this->getLocale($request),
            SecurityBehavior::class,
            $request->get('id')
        );
    }

    public function getLocale(Request $request): ?string
    {
        return $request->query->has('locale')
            ? (string) $request->query->get('locale')
            : null;
    }
}
