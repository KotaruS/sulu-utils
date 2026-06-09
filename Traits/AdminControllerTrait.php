<?php

declare(strict_types=1);

namespace Kotaru\SuluUtils\Traits;

use Symfony\Component\HttpFoundation\Request;

trait AdminControllerTrait
{
    protected const JSON_OPTIONS = ['json_encode_options' => JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_INVALID_UTF8_IGNORE];

    protected function load(int $id, Request $request)
    {
        return $this->getRepository()->findbyId($id, $this->getLocale($request));
    }

    protected function create(string $locale)
    {
        return $this->getRepository()->create($locale);
    }

    protected function flush(): void
    {
        $this->entityManager->flush();
    }

    protected function save($entity): void
    {
        $this->entityManager->persist($entity);
    }
    protected function detach($entity): void
    {
        $this->entityManager->detach($entity);
    }

    protected function remove(int $id): void
    {
        $this->getRepository()->remove($id);
    }

    public function getLocale(Request $request): ?string
    {
        return $request->query->has('locale') ? (string) $request->query->get('locale') : null;
    }
}
