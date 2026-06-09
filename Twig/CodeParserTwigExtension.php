<?php

declare(strict_types=1);

namespace Kotaru\SuluUtils\Twig;

use Twig\TwigFilter;
use Psr\Log\NullLogger;
use Kotaru\SuluUtils\Common\VideoParser;
use Psr\Log\LoggerInterface;
use Kotaru\SuluUtils\Common\CoordsConverter;
use Psr\Cache\CacheItemPoolInterface;
use Twig\Extension\AbstractExtension;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Component\HttpKernel\Exception\HttpException;

/**
 * @version 1.1.0
 * Transformer for extracting video ID or Embed URL from video hosting providers
 */
class CodeParserTwigExtension extends AbstractExtension
{

    public function __construct(
        private VideoParser $videoParser
    ) {
    }
    public function getFilters(): array
    {
        return [
            new TwigFilter('parse_iframes', [$this, 'parseIframe']),
        ];
    }
    public function getName(): string
    {
        return 'iframe_parser';
    }
    public function parseIframe(string $resource, string $height = '450px'): string
    {
        if (empty($resource)) {
            return '';
        }
        $source = $this->getIframeSource($resource);
        if (!$source) {
            return $resource;
        }
        $provider = $this->getProvider($source);
        $iframe = match (strtolower($provider)) {
            'google-maps' => $this->createMapsIframe($source, $height),
            'youtube' => $this->createYoutubeIframe($source, $height),
            'vimeo' => $this->createVimeoIframe($source, $height),
            default => $this->videoParser->getVideoIframe($resource, $height),
        };

        return $iframe;
    }

    public function getProvider(string $source): string
    {
        $provider = match (true) {
            (1 === \preg_match('/google\.com\/maps/i', $source, $match)) => 'google-maps',
            (1 === \preg_match('/(?:youtube\.com|youtu\.be)/i', $source, $match)) => 'youtube',
            (1 === \preg_match('/vimeo\.com/i', $source, $match)) => 'vimeo',
            default => 'unknown'
        };
        return $provider;
    }
    private function getIframeSource(string $resource): bool|string
    {
        // embed code
        if (1 === \preg_match('/src=\"\s*\K[^"]+/i', $resource, $match)) {
            return \trim($match[0]);
        }
        return false;
    }

    private function createMapsIframe(string $source, string $height): string
    {
        return \sprintf('<iframe src="%s" width="100%%" height="%s" frameborder="0" scrolling="no" marginheight="0" marginwidth="0" style="border:0;min-height:300px" allowfullscreen="" loading="lazy" referrerpolicy="no-referrer-when-downgrade"></iframe>', $source, $height);
    }

    private function createYoutubeIframe(string $source, string $height): string
    {
        $src = $this->videoParser->getYoutubeEmbedURL($source);
        return \sprintf(
            '<iframe src="%s" width="100%%" height="%s" title="YouTube video player" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share" referrerpolicy="no-referrer-when-downgrade" scrolling="no" marginheight="0" marginwidth="0" style="border:0;min-height:300px" allowfullscreen="" loading="lazy"></iframe>',
            $src,
            $height
        );
    }
    private function createVimeoIframe(string $source, string $height): string
    {
        $src = $this->videoParser->getVimeoEmbedURL($source);
        return \sprintf(
            '<iframe src="%s" width="100%%" height="%s" title="Vimeo video player" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share" referrerpolicy="no-referrer-when-downgrade" scrolling="no" marginheight="0" marginwidth="0" style="border:0;min-height:300px" allowfullscreen="" loading="lazy"></iframe>',
            $src,
            $height
        );
    }
}
