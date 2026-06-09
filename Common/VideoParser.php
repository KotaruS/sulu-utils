<?php

namespace Kotaru\SuluUtils\Common;

use Symfony\Component\HttpFoundation\RequestStack;

class VideoParser
{
    public function __construct(
        private RequestStack $requestStack
    ) {
    }

    public function getVideoId(string $resource, ?string $provider = ''): string
    {
        return match (\strtolower($provider)) {
            'vimeo' => $this->getVimeoId($resource),
            'youtube' => $this->getYoutubeId($resource),
            default => null
        };
    }
    public function getVideoEmbedURL(string $resource, ?string $provider = ''): string
    {
        $url = match (\strtolower($provider)) {
            'vimeo' => $this->getVimeoEmbedURL($resource),
            'youtube' => $this->getYoutubeEmbedURL($resource),
            default => $this->getEmbedURL($resource),
        };
        return false === $url ? '' : $url;
    }
    public function getVideoIframe(string $source, string $height): string
    {
        $src = $this->getVideoEmbedURL($source);
        return \sprintf(
            '<iframe src="%s" width="100%%" height="%s" title="Vimeo video player" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share" referrerpolicy="no-referrer-when-downgrade" scrolling="no" marginheight="0" marginwidth="0" style="border:0;min-height:300px" allowfullscreen="" loading="lazy"></iframe>',
            $src,
            $height
        );
    }
    private function getEmbedURL(string $resource): bool|string
    {
        // embed code
        if (1 === \preg_match('/src=\"\s*\K[^"]+/i', $resource, $match)) {
            return \trim($match[0]);
        }
        return false;
    }

    public function getVideoProvider(string $src): ?string
    {
        $provider = match (true) {
            null !== $this->getYoutubeId($src, true) => 'youtube',
            null !== $this->getVimeoId($src, true) => 'vimeo',
            default => null,
        };
        return $provider;
    }
    public function getYoutubeId(string $resource, bool $strict = false): string
    {
        // website url
        if (1 === \preg_match('/(?<=youtube.com\/watch\?v=)([^?]*)(?=\&|#|$)/i', $resource, $match)) {
            return \trim($match[1]);
        }
        // embed url
        if (1 === \preg_match('/(?<=youtube.com\/embed\/)([^?]*)(?=\?|#|$)/i', $resource, $match)) {
            return \trim($match[1]);
        }
        // share url
        if (1 === \preg_match('/(?<=youtu.be\/)([^?]*)(?=\?|#|$)/i', $resource, $match)) {
            return \trim($match[1]);
        }

        return $strict === true ? null : $resource;
    }
    public function getYoutubeEmbedURL(string $src): string
    {
        $request = $this->requestStack->getCurrentRequest();

        if (\preg_match('/^https?:\\/\\//', $src)) {
            $src = $this->getYoutubeId($src);
        }

        $params = [
            'autoplay' => 0,
            'enablejsapi' => 1,
            'cc_lang_pref' => $request->getLocale(),
            'hl' => $request->getLocale(),
            'origin' => $request->getHost(),
            'showinfo' => 0,
            'iv_load_policy' => 3,
            'rel' => 0,
            'mute' => 0,
            'color' => 'red',
            'playsinline' => 1,
            'fs' => 1,
        ];
        $paramString = \http_build_query($params);
        return \sprintf('https://www.youtube-nocookie.com/embed/%s?%s', $src, $paramString);
    }


    public function getVimeoId(string $resource, bool $strict = false): string
    {
        // embed code
        if (1 === \preg_match('/(?<=vimeo.com\/video\/)([^?]*)(?=\?|#|$)/i', $resource, $match)) {
            return \trim($match[1]);
        }
        // share url
        if (1 === \preg_match('/(?<=vimeo.com\/)([^?]*)(?=\?|#|$)/i', $resource, $match)) {
            return \trim($match[1]);
        }

        return $strict === true ? null : $resource;
    }

    public function getVimeoEmbedURL(string $src): string
    {
        if (\preg_match('/^https?:\\/\\//', $src)) {
            $src = $this->getVimeoId($src);
        }

        $params = [
            'badge' => 0,
            'title' => 0,
            'autopause' => 0,
            'portrait' => 0,
            'dnt' => 1,
            'playsinline' => 0,
            'byline' => 0,
        ];
        $paramString = http_build_query($params);
        return \sprintf('https://player.vimeo.com/video/%s?%s', $src, $paramString);
    }
}
