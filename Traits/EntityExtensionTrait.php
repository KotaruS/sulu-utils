<?php
declare(strict_types=1);

namespace Kotaru\SuluUtils\Traits;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\DBAL\Types\Types;

trait EntityExtensionTrait
{
    private array $extension = [];

    public function getExtension(): array
    {
        return $this->extension;
    }
    public function addExtension(string $key, $content): static
    {
        $this->extension[$key] = $content;
        return $this;
    }
    public function removeExtension(string $key): static
    {
        if (isset($this->extension[$key])) {
            unset($this->extension[$key]);
        }
        return $this;
    }
    public function setExtension(array $extension): static
    {
        $this->extension = $extension;
        return $this;
    }
}
