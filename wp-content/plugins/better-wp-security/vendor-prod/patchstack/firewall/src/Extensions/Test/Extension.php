<?php
/**
 * @license GPL-3.0-or-later
 *
 * Modified using Strauss.
 * @see https://github.com/BrianHenryIE/strauss
 */

namespace iThemesSecurity\Strauss\Patchstack\Extensions\Test;

use iThemesSecurity\Strauss\Patchstack\Extensions\ExtensionInterface;

class Extension implements ExtensionInterface
{
    /**
     * Log the request, this can be of type BLOCK, LOG or REDIRECT.
     *
     * @param  int    $ruleId
     * @param  string $bodyData
     * @param  string $blockType
     * @return void
     */
    public function logRequest($ruleId, $bodyData, $blockType)
    {
        return true;
    }

    /**
     * Determine if the current visitor can bypass the firewall.
     * If $isMuCall is true, we MUST avoid any function calls that checks the current authorization of the user,
     * this includes current_user_can. Otherwise, a fatal error is thrown.
     *
     * @param bool $isMuCall
     * @return bool
     */
    public function canBypass($isMuCall)
    {
        return false;
    }

    /**
     * Determine if the visitor is blocked from the website.
     *
     * @param  int $minutes
     * @param  int $blockTime
     * @param  int $attempts
     * @return bool
     */
    public function isBlocked($minutes, $blockTime, $attempts)
    {
        return false;
    }

    /**
     * Force exit the page when a request has been blocked.
     *
     * @param  int $ruleId
     * @return void
     */
    public function forceExit($ruleId)
    {
        exit;
    }

    /**
     * Get the IP address of the request.
     *
     * @return string
     */
    public function getIpAddress()
    {
        return '127.0.0.1';
    }

    /**
     * Determine if the request should not go through the firewall.
     *
     * @param array $whitelistRules
     * @param array $request
     */
    public function isWhitelisted($whitelistRules, $request)
    {
        return false;
    }

    /**
     * Get the hostname of the environment.
     * This is only used for open redirect vulnerabilities.
     * 
     * @return string
     */
    public function getHostName()
    {
        return 'wordpress.test';
    }

    /**
     * Determine if the current request is a file upload request.
     *
     * @return boolean
     */
    public function isFileUploadRequest()
    {
        return false;
    }
}
