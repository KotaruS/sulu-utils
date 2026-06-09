<?php


namespace Kotaru\SuluUtils\Content\Toolbar;

use Sulu\Bundle\AdminBundle\Admin\View\ToolbarAction;

class TogglerToolbarAction extends ToolbarAction
{
    public function __construct(string $label, string $property, bool $activeByDefault)
    {
        parent::__construct(
            'sulu_admin.toggler',
            [
                'label' => $label,
                'property' => $property,
                'default' => $activeByDefault,
            ]
        );
    }
}
