<?php

declare(strict_types=1);

namespace Kotaru\SuluUtils\Twig;

use Kotaru\SuluUtils\Repository\SettingsRepository;
use Ramsey\Uuid\Uuid;
use Sulu\Bundle\MediaBundle\Api\Media;
use Symfony\Component\Serializer\Encoder\DecoderInterface;
use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;
use Twig\TwigFunction;

/**
 * @version 1.1 – useful items
 */
class UtilsExtension extends AbstractExtension
{
    public function __construct(
        private string $storagePath,
        private DecoderInterface $serializer,
        private SettingsRepository $settingsRepository,
    ) {
    }

    public function getFilters(): array
    {
        return [
            new TwigFilter('json_decode', [$this, 'jsonDecode']),
            new TwigFilter('format_bytes', [$this, 'formatBytes']),
            new TwigFilter('get_contents', [$this, 'getFileContents']),
            new TwigFilter('set_index_data', [$this, 'setArrayIndex']),
        ];
    }
    public function getFunctions(): array
    {
        return [
            new TwigFunction('uuid', [$this, 'generateUniqueId']),
            new TwigFunction('get_setting', [$this, 'getSetting']),
        ];
    }

    public function jsonDecode($json, $depth = 512, $flags = 0): mixed
    {
        return json_decode($json, true, $depth, $flags);
    }
    public function getFileContents(string|Media $file): mixed
    {
        $fileVersion = $file->getFileVersion();
        $opts = $fileVersion->getStorageOptions();
        $path = $this->storagePath . '/' . $opts['segment'] . '/' . $opts['fileName'];
        $contents = file_get_contents($path);
        if ($contents) {
            $mimeType = $fileVersion->getMimeType();
            switch ($mimeType) {
                case 'text/csv':
                    return $this->serializer->decode($contents, 'csv');
                case 'application/json':
                    return $this->serializer->decode($contents, 'json');
                default:

                    return $contents;

            }
        }
        return null;
    }
    public function setArrayIndex(array $data, int $index, mixed $content, bool $append = false): array
    {
        if ($append and isset($data[$index])) {
            $data[$index] = [...$data[$index], ...$content];
            return $data;
        }
        $data[$index] = $content;
        return $data;

    }
    public function getSetting(?string $settingKey = null): mixed
    {
        if (null === $settingKey) {
            return $settingKey;
        }
        $setting = $this->settingsRepository->findByKey($settingKey);
        if ($setting) {
            return $setting->getContent();
        }
        return $settingKey;
    }

    public function generateUniqueId($length = 32): string
    {
        $uuid = bin2hex(Uuid::uuid4()->getBytes());
        return \substr((string) $uuid, 0, $length);
    }
    public function formatBytes($bytes, $precision = 2, $SI = true)
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        $multiplier = true === $SI ? 1000 : 1024;
        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log($multiplier));
        $pow = min($pow, count($units) - 1);

        $bytes /= pow($multiplier, $pow);

        return round($bytes, $precision) . ' ' . $units[$pow];
    }
}
