<?php

namespace Kotaru\SuluUtils\Content\Type;

use PHPCR\NodeInterface;
use Sulu\Component\Content\Compat\PropertyInterface;
use Sulu\Component\Content\ContentTypeExportInterface;
use Sulu\Component\Content\SimpleContentType;

/**
 * @version 1.0.0
 * Location Content-type override
 */
class Location extends SimpleContentType implements ContentTypeExportInterface
{

    public function __construct(
        private int $defaultZoom,
    ) {
        parent::__construct('location');
    }

    public function getViewData(PropertyInterface $property)
    {
        return ['zoom' => $property->getValue()['zoom'] ?? $this->defaultZoom ?? 1];
    }

    public function getContentData(PropertyInterface $property)
    {
        $point = $property->getValue();
        return [
            'lat' => $point['lat'] ?? null,
            'lng' => $point['long'] ?? null,
            'title' => $point['title'] ?? null,
            'street' => $point['street'] ?? null,
            'number' => $point['number'] ?? null,
            'code' => $point['code'] ?? null,
            'town' => $point['town'] ?? null,
            'country' => $point['country'] ?? null,
        ];
    }

    public function exportData($propertyValue)
    {
        if (false === \is_string($propertyValue)) {
            return '';
        }

        return $propertyValue;
    }
    /**
     * Prepares value for database.
     */
    protected function encodeValue($value)
    {
        if (\is_array($value)) {
            return \json_encode($value);
        }
        return $value;
    }
    /**
     * Decodes value from database.
     */
    protected function decodeValue($value)
    {
        return \json_decode($value, true) ?? null;
    }
}
