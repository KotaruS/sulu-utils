<?php

declare(strict_types=1);

namespace Kotaru\SuluUtils\Entity;

use Kotaru\SuluUtils\Traits\IdTrait;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as JMS;
use Kotaru\SuluUtils\Repository\SettingsRepository;

class Setting
{
    use IdTrait;

    private ?string $settingKey = null;

    private ?array $content = null;

    public function getKey(): ?string
    {
        return $this->settingKey;
    }

    public function setKey(?string $key): static
    {
        $this->settingKey = $key;
        return $this;
    }

    public function getContent(): ?array
    {
        return $this->content;
    }
    public function setContent(?array $content): static
    {
        $this->content = $content;
        return $this;
    }

}
