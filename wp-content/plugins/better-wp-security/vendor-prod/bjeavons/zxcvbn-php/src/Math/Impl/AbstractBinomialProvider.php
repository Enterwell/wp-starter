<?php
/**
 * @license MIT
 *
 * Modified using Strauss.
 * @see https://github.com/BrianHenryIE/strauss
 */

declare(strict_types=1);

namespace iThemesSecurity\Strauss\ZxcvbnPhp\Math\Impl;

use iThemesSecurity\Strauss\ZxcvbnPhp\Math\BinomialProvider;

abstract class AbstractBinomialProvider implements BinomialProvider
{
    public function binom(int $n, int $k): float
    {
        if ($k < 0 || $n < 0) {
            throw new \DomainException("n and k must be non-negative");
        }

        if ($k > $n) {
            return 0;
        }

        // $k and $n - $k will always produce the same value, so use smaller of the two
        $k = min($k, $n - $k);

        return $this->calculate($n, $k);
    }

    abstract protected function calculate(int $n, int $k): float;
}