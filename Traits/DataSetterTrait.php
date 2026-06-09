<?php

declare(strict_types=1);

namespace Kotaru\SuluUtils\Traits;

use Kotaru\SuluUtils\Common\PropertyAccess;
use Kotaru\SuluUtils\Common\PropertyAccessor as PA;

trait DataSetterTrait
{
    public const SET_WITH_NULL = 1;
    public const SET_NULL_AS_ARRAY = 2;
    public const SET_WITH_NULL_AS_FALLBACK = 4;

    protected function setWithData(mixed $data, string $property = '', callable $setter, int $options = 1): static
    {
        $value = $data ? PA::get($data, $property, PA::RETURN_ENUM) : null;

        if ($value === PropertyAccess::IS_NULL) {
            if ($options & static::SET_WITH_NULL) {
                call_user_func($setter, null);
            } elseif ($options & static::SET_NULL_AS_ARRAY) {
                call_user_func($setter, []);
            }
        } elseif ($value === PropertyAccess::NOT_FOUND && ($options & static::SET_WITH_NULL_AS_FALLBACK)) {
            call_user_func($setter, null);
        } elseif ($value !== PropertyAccess::NOT_FOUND) {
            call_user_func($setter, $value);
        }

        return $this;
    }
}
