<?php

namespace Kotaru\SuluUtils\Content\Type;

use PHPCR\NodeInterface;
use Jackalope\NodeType\NodeProcessor;
use Sulu\Component\Content\SimpleContentType;
use Sulu\Component\Content\Compat\PropertyInterface;
use Sulu\Component\Content\ContentTypeExportInterface;
use Symfony\Component\DependencyInjection\Attribute\AutoconfigureTag;
use Symfony\Component\DependencyInjection\Attribute\Autowire;

/**
 * @version 1.0.0
 * Multiple Location points Content-type
 */
#[AutoconfigureTag('sulu.content.type', ['alias' => 'map_points'])]
class MapPoints extends SimpleContentType
{

    public function __construct(
        #[Autowire(param: 'location.default_center')]
        private array $defaultLatLng,
        #[Autowire(param: 'location.default_zoom')]
        private int $defaultZoom,
    ) {
        parent::__construct('map_points');
    }

    public function exportData($propertyValue)
    {
        if (false === \is_string($propertyValue)) {
            return '';
        }

        return $propertyValue;
    }


    public function getViewData(PropertyInterface $property)
    {
        return ['zoom' => $property->getValue()['zoom'] ?? $this->defaultZoom ?? 1];
    }

    public function getContentData(PropertyInterface $property)
    {
        $defaultLat = $this->defaultLatLng[0];
        $defaultLng = $this->defaultLatLng[1];
        $params = $property->getParams();
        if (isset($params['center'])) {
            [$defaultLat, $defaultLng] = \explode(',', $params['center']);
        }
        if (isset($property->getValue()['points'])) {
            $points = $property->getValue()['points'];
            return \array_map(fn($point) => [
                'lat' => $point['lat'] ?? (float) $defaultLat,
                'lng' => $point['long'] ?? (float) $defaultLng,
                'title' => $point['title'] ?? null,
                'street' => $point['street'] ?? null,
                'number' => $point['number'] ?? null,
                'code' => $point['code'] ?? null,
                'town' => $point['town'] ?? null,
                'country' => $point['country'] ?? null,
            ], $points);
        }
        return [];
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
