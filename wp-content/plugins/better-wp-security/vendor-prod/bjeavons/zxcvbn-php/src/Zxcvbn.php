<?php
/**
 * @license MIT
 *
 * Modified using Strauss.
 * @see https://github.com/BrianHenryIE/strauss
 */

declare(strict_types=1);

namespace iThemesSecurity\Strauss\ZxcvbnPhp;

/**
 * The main entry point.
 *
 * @see  zxcvbn/src/main.coffee
 */
class Zxcvbn
{
    /**
     * @var
     */
    protected $matcher;

    /**
     * @var
     */
    protected $scorer;

    /**
     * @var
     */
    protected $timeEstimator;

    /**
     * @var
     */
    protected $feedback;

    public function __construct()
    {
        $this->matcher = new \iThemesSecurity\Strauss\ZxcvbnPhp\Matcher();
        $this->scorer = new \iThemesSecurity\Strauss\ZxcvbnPhp\Scorer();
        $this->timeEstimator = new \iThemesSecurity\Strauss\ZxcvbnPhp\TimeEstimator();
        $this->feedback = new \iThemesSecurity\Strauss\ZxcvbnPhp\Feedback();
    }

    public function addMatcher(string $className): self
    {
        $this->matcher->addMatcher($className);

        return $this;
    }

    /**
     * Calculate password strength via non-overlapping minimum entropy patterns.
     *
     * @param string $password   Password to measure
     * @param array  $userInputs Optional user inputs
     *
     * @return array Strength result array with keys:
     *               password
     *               entropy
     *               match_sequence
     *               score
     */
    public function passwordStrength(string $password, array $userInputs = []): array
    {
        $timeStart = microtime(true);

        $sanitizedInputs = array_map(
            function ($input) {
                return mb_strtolower((string) $input);
            },
            $userInputs
        );

        // Get matches for $password.
        // Although the coffeescript upstream sets $sanitizedInputs as a property,
        // doing this immutably makes more sense and is a bit easier
        $matches = $this->matcher->getMatches($password, $sanitizedInputs);

        $result = $this->scorer->getMostGuessableMatchSequence($password, $matches);
        $attackTimes = $this->timeEstimator->estimateAttackTimes($result['guesses']);
        $feedback = $this->feedback->getFeedback($attackTimes['score'], $result['sequence']);

        return array_merge(
            $result,
            $attackTimes,
            [
                'feedback'  => $feedback,
                'calc_time' => microtime(true) - $timeStart
            ]
        );
    }
}
