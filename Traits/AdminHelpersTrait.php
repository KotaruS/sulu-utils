<?php

declare(strict_types=1);

namespace Kotaru\SuluUtils\Traits;

use Sulu\Bundle\AdminBundle\Admin\View\ToolbarAction;

trait AdminHelpersTrait
{

    protected function getToolbar(string $type, string $permission = null): ?ToolbarAction
    {
        if ($permission === null) {
            return new ToolbarAction($type);
        }

        if ($this->protect($permission)) {
            return new ToolbarAction($type);
        }
        return null;
    }

    /**
     * @return array<ToolbarAction>
     */
    protected function getToolbars(array $toolbars): array
    {
        $builtToolbars = [];
        foreach ($toolbars as [$type, $permission]) {
            $toolbar = $this->getToolbar($type, $permission);
            if ($toolbar) {
                $builtToolbars[] = $toolbar;
            }
        }
        return $builtToolbars;
    }
    protected function protect(string $permission): bool
    {
        return $this->securityChecker->hasPermission(static::SECURITY_CONTEXT, $permission);
    }
}
