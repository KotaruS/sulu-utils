<?php

namespace Kotaru\SuluUtils\Admin\Builder;

use Sulu\Bundle\AdminBundle\Admin\View\FormViewBuilder;
use Sulu\Bundle\AdminBundle\Admin\View\ViewBuilderInterface;


class GenericFormBuilder extends FormViewBuilder implements ViewBuilderInterface
{
    public const TYPE = 'sulu_utils.generic_form';

}
