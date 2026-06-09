<?php

declare(strict_types=1);

namespace Kotaru\SuluUtils\Traits;

use JMS\Serializer\Annotation as JMS;
use Doctrine\ORM\Mapping as ORM;

trait IdTrait
{
    private ?int $id = null;

    public function getId(): ?int
    {
        return $this->id;
    }
}
