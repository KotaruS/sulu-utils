<?php

declare(strict_types=1);

namespace Kotaru\SuluUtils\Traits;

use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as JMS;

trait ExternalEntityTrait
{
    private bool $external = false;

    public function isExternal(): bool
    {
        return $this->external;
    }
    public function setExternal(bool $external): static
    {
        $this->external = $external;

        return $this;
    }
}
