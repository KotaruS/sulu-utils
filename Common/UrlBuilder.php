<?php

declare(strict_types=1);

namespace Kotaru\SuluUtils\Common;

class UrlBuilder
{
    private ResourceLocator $url;

    public function create(string $url, $parameters = [], $query = []): static
    {
        $this->url = new ResourceLocator($url, $parameters, $query);
        return $this;
    }
    public function getUrl(): string
    {
        return (string) $this->url;
    }
    public function setParameter(string $key, $parameter): static
    {
        $this->url->setParameter($key, $parameter);
        return $this;
    }
    public function setParameters(array $parameters): static
    {
        $this->url->setParameters($parameters);
        return $this;
    }
    public function setQuery(string $key, $query): static
    {
        $this->url->setQuery($key, $query);
        return $this;
    }
    public function setQueries(array $queries): static
    {
        $this->url->setQueries($queries);
        return $this;
    }
}
