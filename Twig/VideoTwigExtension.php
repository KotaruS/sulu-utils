<?php

declare(strict_types=1);

namespace Kotaru\SuluUtils\Twig;

use Kotaru\SuluUtils\Common\VideoParser;
use Twig\TwigFilter;
use Twig\Extension\AbstractExtension;

/**
 * @version 1.0.0
 * Transformer for extracting video ID or Embed URL from video hosting providers
 */
class VideoTwigExtension extends AbstractExtension
{
    public function __construct(
        private VideoParser $videoParser
    ) {
    }
    public function getFilters()
    {
        return [
            new TwigFilter('video_url', [$this, 'getVideoEmbedURL']),
            new TwigFilter('video_id', [$this, 'getVideoId']),
        ];
    }
    public function getName(): string
    {
        return 'video_provider_parser';
    }
    public function getVideoId(string $resource, ?string $provider = null): string
    {
        $provider = !empty($provider) ? $provider : $this->videoParser->getVideoProvider($resource);

        return $this->videoParser->getVideoId($resource, $provider) ?? '';
    }
    public function getVideoEmbedURL(string $resource, ?string $provider = null): string
    {
        $provider = !empty($provider) ? $provider : $this->videoParser->getVideoProvider($resource);
        return $this->videoParser->getVideoEmbedURL($resource, $provider) ?? '';
    }
}
