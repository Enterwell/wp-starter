<?php
/**
 * @license GPL-3.0-or-later
 *
 * Modified using Strauss.
 * @see https://github.com/BrianHenryIE/strauss
 */

namespace iThemesSecurity\Strauss\Patchstack;

use iThemesSecurity\Strauss\Patchstack\Response;
use iThemesSecurity\Strauss\Patchstack\Request;
use iThemesSecurity\Strauss\Patchstack\Extensions\ExtensionInterface;

class Processor
{
    /**
     * The firewall rules to process.
     *
     * @var array
     */
    private $firewallRules = [];

    /**
     * The whitelist rules to process.
     *
     * @var array
     */
    private $whitelistRules = [];

    /**
     * The options of the engine.
     *
     * @var array
     */
    private $options = [
        'autoblockAttempts' => 10,
        'autoblockMinutes' => 30,
        'autoblockTime' => 60,
        'whitelistKeysRules' => [],
        'mustUsePluginCall' => false
    ];

    /**
     * The extension that will process specific logic for the CMS.
     *
     * @var ExtensionInterface
     */
    private $extension;

    /**
     * The captured request that needs to be inspected.
     *
     * @var Request
     */
    private $request;

    /**
     * The response that will be sent, depending on the action executed by the processor.
     *
     * @var Response
     */
    private $response;

    /**
     * Creates a new processor instance.
     *
     * @param ExtensionInterface $extension
     * @param array $firewallRules
     * @param array $whitelistRules
     * @param array $options
     * @return void
     */
    public function __construct(
        ExtensionInterface $extension,
        $firewallRules = [],
        $whitelistRules = [],
        $options = []
    ) {
        $this->extension = $extension;
        $this->firewallRules = $firewallRules;
        $this->whitelistRules = $whitelistRules;
        $this->options = array_merge($this->options, $options);

        $this->request = new Request($this->options, $this->extension);
        $this->response = new Response($this->options);
    }

    /**
     * Magic getter for the options.
     *
     * @param string $name
     * @return mixed
     */
    public function __get($name)
    {
        return isset($this->options[$name]) ? $this->options[$name] : null;
    }

    /**
     * Launch the firewall. First we determine if the user is blocked and whitelisted, then go through
     * all of the firewall rules.
     *
     * Will return true if $mustExit is false and all of the rules were processed without a positive detection.
     *
     * @param boolean $mustExit
     * @return boolean
     */
    public function launch($mustExit = true)
    {
        // Determine if the user is temporarily blocked from the site before we do anything else.
        $isWhitelisted = $this->extension->canBypass($this->mustUsePluginCall);
        if (!$isWhitelisted && $this->extension->isBlocked($this->autoblockMinutes, $this->autoblockTime, $this->autoblockAttempts)) {
            $this->extension->forceExit(22);
        }

        // Determine if the firewall and whitelist rules were parsed properly.
        if (!is_array($this->firewallRules) || !is_array($this->whitelistRules)) {
            return true;
        }

        // Determine if we have any firewall and/or whitelist rules loaded.
        if (count($this->firewallRules) == 0 && count($this->whitelistRules) == 0) {
            return true;
        }

        // Merge the rules together. First iterate through the whitelist rules because
        // we want to whitelist the request if there's a whitelist rule match.
        $rules = array_merge($this->whitelistRules, $this->firewallRules);

        // Iterate through all the firewall rules.
        foreach ($rules as $rule) {
            // Should never happen.
            if (!isset($rule['rules']) || empty($rule['rules'])) {
                continue;
            }

            // If this rule should respect the whitelist, we check this before we continue.
            if (isset($rule['bypass_whitelist']) && ($rule['bypass_whitelist'] === 0 || $rule['bypass_whitelist'] === false) && $isWhitelisted) {
                continue;
            }

            // If the rule contains matching type we cannot call during mu-plugins, skip.
            $hasWpAction = $this->hasWpAction($rule['rules']);
            if (defined('PS_FW_MU_RAN') && !$hasWpAction || $this->mustUsePluginCall && $hasWpAction) {
                continue;
            }

            // Execute the firewall rule.
            $rule_hit = $this->executeFirewall($rule['rules']);

            // If the payload did not match the rule, continue on to the next rule.
            if (!$rule_hit) {
                continue;
            }

            // Capture the POST data for logging purposes.
            if ($rule['type'] != 'WHITELIST') {
                $postData = $this->request->getParameterValues('log');
            }

            // Determine what action to perform.
            if ($rule['type'] == 'BLOCK') {
                $this->extension->logRequest($rule['id'], $postData, 'BLOCK');

                // Do we have to exit the page or simply return false?
                if ($mustExit) {
                    $this->extension->forceExit($rule['id']);
                } else {
                    return false;
                }
            } elseif ($rule['type'] == 'LOG') {
                $this->extension->logRequest($rule['id'], $postData, 'LOG');
            } elseif ($rule['type'] == 'REDIRECT') {
                $this->extension->logRequest($rule['id'], $postData, 'REDIRECT');
                $this->response->redirect($rule['type_params'], $mustExit);
            } elseif ($rule['type'] == 'WHITELIST') {
                return $mustExit;
            }
        }

        return true;
    }

    /**
     * Execute the firewall rules.
     * 
     * @param array $rules
     * @return bool
     */
    public function executeFirewall($rules)
    {
        // Count number of inclusive rules, if any.
        $inclusiveCount = 0;
        if (count($rules) > 1) {
            $inclusiveCount = $this->getInclusiveCount($rules);
        }

        // Keep track of how many inclusive rule hits.
        $inclusiveHits = 0;

        // Loop through all of the conditions for this rule.
        foreach ($rules as $rule) {
            // Parameter must always be present.
            if (!isset($rule['parameter'])) {
                continue;
            }

            // Cast to an array so we can iterate through all parameters.
            if (!is_array($rule['parameter'])) {
                $parameters = [$rule['parameter']];
            } else {
                $parameters = $rule['parameter'];
            }

            // Iterate through all parameters.
            foreach ($parameters as $parameter) {
                // Extract the value of the paramater that we want.
                $values = $this->request->getParameterValues($parameter);
                if (is_null($values) && $parameter !== false && $parameter != 'rules') {
                    continue;
                }

                // For special parameter values we just set the array to a single null value.
                if ($parameter === false || $parameter == 'rules') {
                    $values = [null];
                }

                // For all field matches, we want to execute the rule against it.
                foreach ($values as $value) {
                    // Apply mutations, if any.
                    if (isset($rule['mutations']) && is_array($rule['mutations'])) {
                        $value = $this->request->applyMutation($rule['mutations'], $value);
                        if (is_null($value)) {
                            continue;
                        }
                    }

                    // Perform the matching.
                    if (isset($rule['match']) && is_array($rule['match']) || isset($rule['rules'])) {

                        // Do we have to process child-rules?
                        if (isset($rule['rules'])) {
                            $match = $this->executeFirewall($rule['rules']);
                        } else {
                            $match = $this->matchParameterValue($rule['match'], $value);
                        }

                        // Is the rule a match?
                        if ($match) {
                            // In case there are multiple rules, they may require chained AND conditions.
                            if ($inclusiveCount <= 1 || !isset($rule['inclusive']) || $rule['inclusive'] !== true) {
                                return true;
                            } else {
                                $inclusiveHits++;
                                break 2;
                            }
                        }
                    }
                }
            }
        }

        // In case we hit all of the AND conditions.
        if ($inclusiveCount > 1 && $inclusiveHits >= $inclusiveCount) {
            return true;
        }

        return false;
    }

    /**
     * Get the number of inclusive rules as part of the rule group.
     * 
     * @param array $rules
     * @return int
     */
    public function getInclusiveCount($rules)
    {
        if (count($rules) == 1) {
            return 1;
        }

        $count = 0;
        foreach ($rules as $rule) {
            if (isset($rule['inclusive']) && $rule['inclusive'] === true) {
                $count++;
            }
        }

        return $count;
    }

    /**
     * With the given parameter value, attempt to match it.
     * 
     * @param mixed $match
     * @param mixed $value
     * @return bool
     */
    public function matchParameterValue($match, $value)
    {
        // Take some of the parameters for easy access.
        $matchType = isset($match['type']) ? $match['type'] : null;
        $matchValue = isset($match['value']) ? $match['value'] : null;

        // Perform a match depending on the given match type.
        // If a scalar matches another scalar (loose).
        if ($matchType == 'equals' && is_scalar($value) && is_scalar($matchValue)) {
            return $matchValue == $value;
        }

        // If a scalar matches another scaler (strict).
        if ($matchType == 'equals_strict' && is_scalar($value) && is_scalar($matchValue)) {
            return $matchValue === $value;
        }

        // If a scalar is bigger than another scalar.
        if ($matchType == 'more_than' && is_scalar($value) && is_scalar($matchValue)) {
            return $value > $matchValue;
        }

        // If a scalar is less than another scalar.
        if ($matchType == 'less_than' && is_scalar($value) && is_scalar($matchValue)) {
            return $value < $matchValue;
        }

        // If the parameter is present at all.
        if ($matchType == 'isset') {
            return true;
        }

        // If a scalar is a ctype alnum with underscores, dashes and spaces.
        if ($matchType == 'ctype_special' && is_scalar($value) && $value != '') {
            $value = str_replace([' ', '_', '-', ','], '', $value);
            $isClean = (bool) (@preg_match('/^[\w$\x{0080}-\x{FFFF}]*$/u', $value) > 0);
            return $isClean === $matchValue;
        }

        // If a scaler is a ctype digit.
        if ($matchType == 'ctype_digit' && is_scalar($value) && $value != '') {
            return @ctype_digit($value) === $matchValue;
        }

        // If a scaler is a ctype alnum.
        if ($matchType == 'ctype_alnum' && is_scalar($value) && $value != '') {
            $isClean = (bool) (@preg_match('/^[\w$\x{0080}-\x{FFFF}]*$/u', $value) > 0);
            return $isClean === $matchValue;
        }

        // If a scalar is numeric.
        if ($matchType == 'is_numeric' && is_scalar($value) && $value != '') {
            return @is_numeric($value) === $matchValue;
        }

        // If a scalar contains a value.
        if (($matchType == 'contains' || $matchType == 'stripos') && is_scalar($value)) {
            return @stripos($value, $matchValue) !== false;
        }

        // If a scalar does not contain a value.
        if ($matchType == 'not_contains' && is_scalar($value)) {
            return @stripos($value, $matchValue) === false;
        }

        // If a scalar contains single or double quotes.
        if (($matchType == 'quotes' || $matchType == 'inline_js_xss') && is_scalar($value)) {
            return @stripos($value, '"') !== false || @stripos($value, "'") !== false;
        }

        // If a string matches a regular expression.
        if ($matchType == 'regex' && is_string($matchValue) && is_scalar($value)) {
            return @preg_match($matchValue, @urldecode($value)) === 1;
        }

        // If the user does not have a WP privilege.
        if ($matchType == 'current_user_cannot' && is_scalar($matchValue) && function_exists('current_user_can') && function_exists('wp_get_current_user') && !$this->mustUsePluginCall) {
            return @!current_user_can($matchValue);
        }

        // If a value is in an array.
        if ($matchType == 'in_array' && !is_array($value) && is_array($matchValue)) {
            return @in_array($value, $matchValue);
        }

        // If a value is not in an array.
        if ($matchType == 'not_in_array' && !is_array($value) && is_array($matchValue)) {
            return @!in_array($value, $matchValue);
        }

        // If an array of values is in another array of values.
        if ($matchType == 'array_in_array' && is_array($value) && is_array($matchValue)) {
            return @count(@array_intersect($value, $matchValue)) > 0;
        }

        // If a specific parameter key matches a sub-match condition.
        if ($matchType == 'array_key_value' && isset($match['key'], $match['match'])) {

            // To support arrays for the key matching type value.
            $keys = is_array($match['key']) ? $match['key'] : [$match['key']];

            // Iterate through all keys.
            foreach ($keys as $key) {
                $values = $this->request->getParameterValues($key, $value);
                if (!is_array($values)) {
                    continue;
                }
    
                foreach ($values as $val) {
                    if ($this->matchParameterValue($match['match'], $val)) {
                        return true;
                    }
                }
            }

            return false;
        }

        // If the user provided value does not match the current hostname.
        if ($matchType == 'hostname' && is_string($value)) {
            if (empty($value)) {
                return false;
            }

            // If there's no protocol we add it.
            if (substr($value, 0, 4) != 'http') {
                $value = 'https://' . $value;
            }

            // We only care about the hostname.
            $host = @parse_url($value, PHP_URL_HOST);
            if (!$host) {
                return true;
            }

            return $host !== $this->extension->getHostName();
        }

        // If any of the uploaded files in the parameter matches a sub-match condition.
        if ($matchType == 'file_contains' && isset($match['match'])) {
            // Extract all tmp_names.
            if (isset($value['tmp_name'])) {
                $files = $value['tmp_name'];
                if (!is_array($files)) {
                    $files = [$files];
                }
            } else {
                $files = array_column($value, 'tmp_name');
            }
            
            // No need to continue if there are no files.
            if (is_array($files) && count($files) === 0) {
                return false;
            }

            // Cast all tmp_names to a single-dimension array.
            $files = $this->request->getArrayValues($files, '', 'array');
            if (is_array($files) && count($files) === 0) {
                return false;
            }

            // Get the contents of the files.
            $contents = '';
            foreach ($files as $file) {
                $contents .= (string) @file_get_contents($file);
            }

            // Now attempt to match it.
            return $this->matchParameterValue($match['match'], $contents);
        }

        // If a scalar passes a run through wp_kses_post.
        if ($matchType == 'general_xss' && is_scalar($value) && function_exists('wp_kses_post')) {
            return $value != @\wp_kses_post($value);
        }

        // If a scalar passes a run through inline_js_xss.
        if ($matchType == 'inline_xss' && is_scalar($value)) {
            if (@stripos($value, '"') === false && @stripos($value, "'") === false) {
                return false;
            }
        
            if (@stripos($value, '>') !== false || @stripos($value, '=') !== false) {
                return true;
            }
        
            return false;
        }

        return false;
    }

    /**
     * Determine if the rules contain an action that should not be executed under the mu-plugins context.
     * 
     * @param array $rules
     * @return boolean
     */
    private function hasWpAction($rules)
    {
        $functions = ['current_user_cannot'];

        if (isset($rules['rules'])) {
            if ($this->hasWpAction($rules['rules'])) {
                return true;
            }
        }

        foreach ($rules as $rule) {
            if (!isset($rule['match'], $rule['match']['type'])) {
                continue;
            }

            if (in_array($rule['match']['type'], $functions)) {
                return true;
            }
        }

        return false;
    }
}
