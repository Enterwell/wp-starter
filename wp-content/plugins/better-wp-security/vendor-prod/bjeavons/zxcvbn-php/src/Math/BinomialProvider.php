<?php
/**
 * @license MIT
 *
 * Modified using Strauss.
 * @see https://github.com/BrianHenryIE/strauss
 */

declare(strict_types=1);

namespace iThemesSecurity\Strauss\ZxcvbnPhp\Math;

interface BinomialProvider
{
    /**
     * Calculate binomial coefficient (n choose k).
     *
     * @param int $n
     * @param int $k
     * @return float
     */
    public function binom(int $n, int $k): float;
}