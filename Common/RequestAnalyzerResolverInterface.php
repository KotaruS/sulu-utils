<?php

namespace Kotaru\SuluUtils;

use Sulu\Component\Webspace\Analyzer\RequestAnalyzerInterface;

/**
 * Interface for request analyzer resolver.
 */
interface RequestAnalyzerResolverInterface
{
    /**
     * Resolves the request analyzer to an array.
     *
     * @return array
     */
    public function resolve(RequestAnalyzerInterface $requestAnalyzer);
}
