<?php

namespace Kotaru\SuluUtils\Admin\Builder;

use Sulu\Bundle\AdminBundle\Admin\View\ResourceTabViewBuilder;
use Sulu\Bundle\AdminBundle\Admin\View\ViewBuilderInterface;


class GenericResourceTabBuilder extends ResourceTabViewBuilder implements ViewBuilderInterface
{
    public const TYPE = 'sulu_utils.generic_resource_tabs';

}
