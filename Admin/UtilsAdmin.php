<?php

declare(strict_types=1);

namespace Kotaru\SuluUtils\Admin;

use Kotaru\SuluUtils\Traits\AdminHelpersTrait;
use Sulu\Bundle\AdminBundle\Admin\Admin;
use Sulu\Component\Security\Authorization\PermissionTypes;

class UtilsAdmin extends Admin
{
    use AdminHelpersTrait;

    public const SECURITY_CONTEXT_GROUP = 'Utils';

    public const ADMIN_KEY = 'sulu_utils';

    public const SECURITY_CONTEXT = 'settings';


    public function __construct(
        private array $defaultCenter,
        private int $defaultZoom,
        private array $editorStyles=[],
    ) {
    }


    public function getConfigKey(): ?string
    {
        return static::ADMIN_KEY;
    }
    public function getConfig(): ?array
    {
        return [
            'location' => [
                'default_zoom' => $this->defaultZoom ?? 1,
                'default_center' => $this->defaultCenter ?? [0, 0],
            ],
            'styles' => $this->editorStyles
        ];
    }

    public function getSecurityContexts(): array
    {
        return [
            static::SULU_ADMIN_SECURITY_SYSTEM => [
                static::SECURITY_CONTEXT_GROUP => [
                    static::SECURITY_CONTEXT => [
                        PermissionTypes::VIEW,
                        PermissionTypes::ADD,
                        PermissionTypes::EDIT,
                        PermissionTypes::DELETE,
                    ],
                ],
            ],
        ];
    }


}
