<?php

declare(strict_types=1);

namespace Kotaru\SuluUtils\Traits;

use JMS\Serializer\Annotation as JMS;
use Doctrine\ORM\Mapping as ORM;

trait LocaleTrait
{
    private string $defaultLocale;

    private string $locale;

    public function getLocale(): string
    {
        return $this->locale;
    }

    public function hasLocale(): bool
    {
        return isset($this->locale);
    }

    public function setLocale(string $locale): static
    {
        $this->locale = $locale;

        return $this;
    }

    public function getDefaultLocale(): string
    {
        return $this->defaultLocale;
    }
    public function setDefaultLocale(string $defaultLocale): static
    {
        $this->defaultLocale = $defaultLocale;

        return $this;
    }
}
