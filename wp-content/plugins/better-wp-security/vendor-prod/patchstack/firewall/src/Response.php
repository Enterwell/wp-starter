<?php
/**
 * @license GPL-3.0-or-later
 *
 * Modified using Strauss.
 * @see https://github.com/BrianHenryIE/strauss
 */

namespace iThemesSecurity\Strauss\Patchstack;

class Response
{
    /**
     * The options of the engine.
     *
     * @var array
     */
    private $options;

    /**
     * Creates a new request instance.
     *
     * @param  array $options
     * @return void
     */
    public function __construct($options = [])
    {
        $this->options = $options;
    }

    /**
     * Perform a redirect if the request must be redirected to somewhere else.
     *
     * @param  string  $redirectTo
     * @param  boolean $mustExit
     * @return void
     */
    public function redirect($redirectTo = '', $mustExit = true)
    {
        // Don't redirect an invalid URL.
        if (!$redirectTo || filter_var($redirectTo, FILTER_VALIDATE_URL) === false) {
            return false;
        }

        // Perform the redirect.
        header('Location: ' . $redirectTo, true, 302);

        // In some scenarios we might want to control if the script should exit executing.
        if ($mustExit) {
            exit;
        }
    }
}
