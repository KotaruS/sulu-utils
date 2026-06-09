<?php

namespace Kotaru\SuluUtils\Common;

use JMS\Serializer\SerializationContext;

interface SerializerInterface
{

    public function serialize($data, ?SerializationContext $context = null): array;
}

