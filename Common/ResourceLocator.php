<?php

declare(strict_types=1);

namespace Kotaru\SuluUtils\Common;

class ResourceLocator
{
    private string $url;
    private array $parameterTemplates = [];
    private array $parameters = [];
    private array $query = [];

    public function __construct(string $url, $parameters = [], $query = [])
    {
        $this->url = $url;
        $this->parameters = $parameters;
        $this->query = $query;
        $this->extractParams();
    }

    public function getUrl(): string
    {
        $url = $this->url;
        $hashParts = \explode('#', $url, 2);
        $url = $hashParts[0];
        $hash = $hashParts[1] ?? '';
        foreach ($this->parameterTemplates as $key) {
            if (
                !\str_contains($url, ':' . $key) ||
                !\array_key_exists($key, $this->parameters)
            ) {
                throw new \Exception(sprintf("Missing parameter value for key :%s in url '%s'", $key, $this->url));
            }
            $url = \str_replace(':' . $key, (string) $this->parameters[$key], $url);
        }
        [$querylessUrl] = \explode('?', $url, 2);
        $urlQuery = $this->getQueryParams($url);
        $query = http_build_query(\array_merge($urlQuery, $this->query));
        if (!empty($query)) {
            $url = $querylessUrl . '?' . $query;
        }
        if (!empty($hash)) {
            $url .= '#' . $hash;
        }
        return $url;
    }
    public function setParameter(string $key, $parameter): static
    {
        if (!\in_array($key, $this->parameterTemplates)) {
            throw new \InvalidArgumentException("Cannot set parameter for a key that's not present in the url.");
        }
        $this->parameters[$key] = $parameter;
        return $this;
    }
    public function setParameters(array $parameters): static
    {
        foreach ($parameters as $key => $parameter) {
            $this->setParameter($key, $parameter);
        }
        return $this;
    }
    public function setQuery(string $key, $query): static
    {
        $this->query[$key] = $query;
        return $this;
    }
    public function setQueries(array $queries): static
    {
        foreach ($queries as $key => $query) {
            $this->setQuery($key, $query);
        }
        return $this;
    }

    private function getQueryParams(string $url): array
    {
        [$url] = \explode('#', $url, 2);
        $querysplit = \explode('?', $url, 2);
        if (\count($querysplit) < 2 || empty($querysplit[1])) {
            return [];
        }
        $query = \str_replace('&amp;', '&', $querysplit[1]);
        $params = [];
        foreach (\explode('&', $query) as $chunk) {
            $param = \explode("=", $chunk);
            $params[urldecode($param[0])] = urldecode($param[1] ?? '');
        }
        return $params;
    }

    private function extractParams(): array
    {
        $matches = [];
        \preg_match_all('/(?<=:)(\\w+)(?=\\/|#|&|$)/', $this->url, $matches);
        $this->parameterTemplates = \array_unique($matches[1]);
        return $this->parameterTemplates;
    }

    public function __tostring(): string
    {
        return $this->getUrl();
    }
}
