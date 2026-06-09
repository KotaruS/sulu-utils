<?php

declare(strict_types=1);

namespace Kotaru\SuluUtils\Twig;

use Twig\Extension\AbstractExtension;
use Twig\TwigTest;

/**
 * @version 1.0.0
 * This is just a simple Twig Extension for sorting by array key.
 */
class TestExtrasExtension extends AbstractExtension
{
    public function getTests(): array
    {
        return [
            new TwigTest('instanceof', [$this, 'isInstanceOf']),
            new TwigTest('number', [$this, 'isNumber']),
        ];
    }

    /**
     * Checks whether the provided value is instance of the class
     * @param mixed $subject
     * @param class-string $class
     * @return bool
     */
    public function isInstanceOf($subject, $class = ''): bool
    {
        return is_a($subject, $class, true);
    }

    /**
     * Tests whether the number is number or can be converted to number (set strict option to true to allow only real numbers)
     * @param mixed $subject
     * @param bool $strict
     * @return bool
     */
    public function isNumber($subject, bool $strict = false): bool
    {
        if (true === $strict) {
            return is_int($subject) || is_float($subject) || is_long($subject);
        }
        return is_numeric($subject);
    }
}
