<?php

namespace Kotaru\SuluUtils\Content\Type;

use Sulu\Component\Content\Compat\PropertyInterface;
use Sulu\Component\Content\Compat\PropertyParameter;
use Sulu\Component\Content\SimpleContentType;
use Symfony\Component\DependencyInjection\Attribute\AutoconfigureTag;

/**
 * @version 1.0.0
 * ContentType for single selects. Currently only supports radios.
 */
#[AutoconfigureTag('sulu.content.type', ['alias' => 'range'])]
class Range extends SimpleContentType
{
    public function __construct()
    {
        parent::__construct('range');
    }

    public function getDefaultParams(?PropertyInterface $property = null)
    {
        return [
            'default_value' => 0,
            'min' => 0,
            'max' => 100,
            'step' => 10,
            'titles' => false,
            'ticks' => true,
            'marks' => new PropertyParameter('marks', [], 'collection'),
        ];
    }
}
