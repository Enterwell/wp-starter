<?php
/**
 * @license GPL-3.0-or-later
 *
 * Modified using Strauss.
 * @see https://github.com/BrianHenryIE/strauss
 */

namespace iThemesSecurity\Strauss\Patchstack;

use iThemesSecurity\Strauss\Patchstack\Extensions\ExtensionInterface;

class Request
{
    /**
     * The options of the engine.
     *
     * @var array
     */
    private $options;

    /**
     * The extension that will process specific logic for the CMS.
     *
     * @var ExtensionInterface
     */
    private $extension;

    /**
     * Creates a new request instance.
     *
     * @param  array $options
     * @return void
     */
    public function __construct($options, ExtensionInterface $extension)
    {
        $this->options = $options;
        $this->extension = $extension;
    }

    /**
     * Grab the request parameters we are trying to capture for the given rule.
     * 
     * @param mixed $parameter
     * @param array $data
     * @return mixed
     */
    public function getParameterValues($parameter, $data = [])
    {
        // For when a rule contains sub-rules.
        if (empty($parameter) || ctype_digit($parameter) || !is_array($data)) {
            return null;
        }

        // Explode on the dot to grab the proper key value.
        $t = explode('.', $parameter);
        $type = $t[0];

        if (count($data) == 0) {
            array_shift($t);
        }

        switch ($type) {
            case 'all':
                $data = [
                    'post' => $_POST,
                    'get' => $_GET,
                    'url' => isset($_SERVER['REQUEST_URI']) ? $_SERVER['REQUEST_URI'] : '',
                    'raw' => ['raw' => $this->getParameterValues('raw')]
                ];
                break;
            case 'log':
                return [
                    'post' => $_POST,
                    'files' => $_FILES,
                    'raw' => $this->getParameterValues('raw')
                ];
                break;
            case 'post':
                $data = $_POST;
                break;
            case 'get':
                $data = $_GET;
                break;
            case 'request':
                $data = $_REQUEST;
                break;
            case 'cookie':
                $data = $_COOKIE;
                break;
            case 'files':
                $data = $_FILES;
                break;
            case 'server':
                $data = $_SERVER;
                break;
            case 'raw':
                $data = @file_get_contents( 'php://input' );

                // Ignore if no data in payload.
                if (empty($data)) {
                    $data = [];
                    break;
                }

                // Determine if it's base64 encoded.
                if (is_string($data) && preg_match('%^[a-zA-Z0-9/+]*={0,2}$%', $data)) {
                    $decoded = base64_decode($data, true);
                    if ($decoded !== false) {
                        $encoding = mb_detect_encoding($decoded);
                        if (in_array($encoding, ['UTF-8', 'ASCII'], true) && $decoded !== false && base64_encode($decoded) === $data) {
                            $data = $decoded;
                        }
                    }
                }

                // Determine if it's JSON encoded.
                if (is_string($data)) {
                    $result = json_decode($data, true);
                    if (is_array($result)) {
                        $data = $result;
                    }
                }

                // If it's not an array, no need to continue.
                if (!is_array($data)) {
                    $data = [$data];
                }
            default:
                break;
        }

        // No need to continue if we don't have any data to match against.
        if (count($data) == 0) {
            return null;
        }

        // Special condition for the IP address.
        if ($type === 'server' && isset($t[0]) && $t[0] === 'ip') {
            return [$this->extension->getIpAddress()];
        }

        // For wildcard matching we handle it a bit differently.
        // We want to extract all wildcard matches and pass them as an array so we
        // can execute a firewall rule against all the fields that match.
        if (strpos($parameter, '*') !== false) {
            $values = $this->getValuesByWildcard($data, $parameter);
            return count($values) == 0 ? null : $values;
        }

        // Just one parameter we have to match against.
        if (count($t) === 1) {
            return isset($data[$t[0]]) ? [$data[$t[0]]] : null;
        }

        // For multidimensional arrays.
        $end  = $data;
        $skip = false;
        foreach ( $t as $var ) {
            if ( ! isset( $end[ $var ] ) ) {
                $skip = true;
                break;
            }
            $end = $end[ $var ];
        }

        return $skip ? null : [$end];
    }

   /**
     * Apply mutations to the captured value.
     * 
     * @param array $mutations
     * @param mixed $value
     * @return mixed
     */
    public function applyMutation($mutations, $value)
    {
        if (count($mutations) === 0) {
            return $value;
        }

        // Define the allowed mutations.
        // Array value contains the arguments to pass to the function as well as expected type.
        $allowed = [
            'json_encode' => [
                'args' => []
            ],
            'json_decode' => [
                'args' => [true],
                'type' => 'is_string'
            ],
            'base64_decode' => [
                'args' => [],
                'type' => 'is_string'
            ],
            'intval' => [
                'args' => [],
                'type' => 'is_scalar'
            ],
            'urldecode' => [
                'args' => [],
                'type' => 'is_string'
            ],
            'getArrayValues' => [
                'args' => [],
                'type' => 'is_array'
            ],
            'getShortcodeAtts' => [
                'args' => [],
                'type' => 'is_string'
            ],
            'getBlockAtts' => [
                'args' => [],
                'type' => 'is_string'
            ]
        ];

        // If it's not a whitelisted mutation, reject and return original value.
        foreach ($mutations as $mutation) {
            if (!isset($allowed[$mutation])) {
                return $value;
            }
        }

        // Apply the mutations in ascending order.
        try {
            foreach ($mutations as $mutation) {
                // In order to avoid errors if the wrong type of value is passed to the function.
                if (isset($allowed[$mutation]['type']) && !call_user_func($allowed[$mutation]['type'], $value)) {
                    continue;
                }

                // Call the function with given arguments.
                if ($mutation == 'getArrayValues') {
                    $value = $this->getArrayValues($value);
                } elseif ($mutation == 'getShortcodeAtts') {
                    $value = $this->getShortcodeAtts($value);
                } elseif ($mutation == 'getBlockAtts') {
                    $value = $this->getBlockAtts($value);
                } else {
                    $value = call_user_func_array($mutation, array_merge([$value], $allowed[$mutation]['args']));
                }
                
                // No need to continue in these scenarios.
                if (is_null($value) || $value === false || $value === 0) {
                    return $value;
                }
            }
        } catch (\Exception $e) {
            return $value;
        }

        return $value;
    }

    /**
     * Given an array, get all parameters which match a certain wildcard.
     * 
     * @param array $data
     * @param string $pattern
     * @return array
     */
    public function getValuesByWildcard($data, $pattern) {
        if (!is_array($data)) {
            return [];
        }

        // Split the pattern into segments
        $segments = explode('.', $pattern);
        array_shift($segments);
    
        // Initialize the results
        $results = [];
    
        // Start with the entire data array
        $currentData = [$data];
    
        // Check if the pattern ends with an asterisk and pop the last segment
        $endWithAsterisk = substr(end($segments), -1) === "*";
        if ($endWithAsterisk) {
            $lastSegment = rtrim(array_pop($segments), '*');
        }
    
        // Loop through each segment
        foreach ($segments as $segment) {
            $newData = [];
    
            // Check if the segment contains an asterisk
            if ($segment === '*') {
                foreach ($currentData as $dataItem) {
                    if (is_array($dataItem)) {
                        foreach ($dataItem as $subItem) {
                            $newData[] = $subItem;
                        }
                    }
                }
            } else {
                // Loop through each data item
                foreach ($currentData as $dataItem) {
                    // Ensure the data item is an array and contains the segment
                    if (is_array($dataItem) && isset($dataItem[$segment])) {
                        $newData[] = $dataItem[$segment];
                    }
                }
            }
    
            // Replace the current data with the new data
            $currentData = $newData;
        }
    
        if ($endWithAsterisk) {
            $finalData = [];
            foreach ($currentData as $dataItem) {
                if (is_array($dataItem)) {
                    foreach ($dataItem as $key => $subItem) {
                        if (strpos($key, $lastSegment) === 0) {
                            $finalData[] = $subItem;
                        }
                    }
                }
            }
            $currentData = $finalData;
        }
    
        // Loop through the current data to fetch the results
        foreach ($currentData as $dataItem) {
            if (is_array($dataItem)) {
                foreach ($dataItem as $value) {
                    $results[] = $value;
                }
            } else {
                $results[] = $dataItem;
            }
        }
    
        // Return the results
        return $results;
    }

    /**
     * Given an array, multi-dimensional or not, extract all of its values.
     * 
     * @param array $data
     * @param string $glue
     * @param string $type
     * @return string|array
     */
    public function getArrayValues($data, $glue = '&', $type = 'string')
    {
        // If we want to return a single line string.
        if ($type == 'string') {
            $ret = '';
            foreach ($data as $key => $item) {
                if (empty($item)) {
                    continue;
                }
    
                if (is_array($item)) {
                    $ret .= $this->getArrayValues($item, $glue) . $glue;
                } else {
                    $ret .= $key . '=' . $item . $glue;
                }
            }
    
            return substr($ret, 0, 0 - strlen($glue));
        }
        
        // Or a single dimension array with each value its own entry.
        $ret = [];
        foreach ($data as $key => $item) {
            if (empty($item)) {
                continue;
            }

            if (is_array($item)) {
                $ret = array_merge($ret, $this->getArrayValues($item, $glue, 'array'));
            } else {
                $ret[] = $item;
            }
        }

        return $ret;
    }

    /**
     * Given a string, fetch all shortcodes and its attributes.
     * 
     * @param string $value
     * @return array
     */
    public function getShortcodeAtts($value)
    {
        // For rare cases where this may not be defined.
        if (!function_exists('shortcode_parse_atts')) {
            return [];
        }

        // The regular expression used by WordPress core to fetch shortcodes and its attributes.
        preg_match_all(
            '/\[(\[?)(.*?)(?![\w-])([^\]\/]*(?:\/(?!\])[^\]\/]*)*?)(?:(\/)\]|\](?:([^\[]*+(?:\[(?!\/\2\])[^\[]*+)*+)\[\/\2\])?)(\]?)/',
            $value,
            $shortcodes,
            PREG_SET_ORDER
        );

        // No matches.
        if (count($shortcodes) == 0) {
            return [];
        }

        // Iterate through all shortcodes and fetch their attributes.
        $return = [];
        foreach ($shortcodes as $shortcode) {
            if (!isset($shortcode[2], $shortcode[3], $shortcode[5])) {
                continue;
            }

            // Merge together if the shortcode occurs more than once.
            if (isset($return[$shortcode[2]])) {

                // Shortcode index must not be a string.
                if (is_string($return[$shortcode[2]])) {
                    continue;
                }

                $atts = @\shortcode_parse_atts($shortcode[3]);
                foreach ($atts as $key => $value) {
                    if (isset($return[$shortcode[2]][$key])) {
                        $return[$shortcode[2]][$key] = $return[$shortcode[2]][$key] . $value;
                    } else {
                        $return[$shortcode[2]][$key] = $value;
                    }
                }
            } else {
                $return[$shortcode[2]] = @\shortcode_parse_atts($shortcode[3]);
            }
        }

        return $return; 
    }

    /**
     * Given a string, fetch all blocks and its attributes.
     * 
     * @param string $value
     * @return array
     */
    public function getBlockAtts($value)
    {
        // For rare cases where this may not be defined.
        if (!function_exists('parse_blocks')) {
            return [];
        }

        // Parse the blocks using the WordPress core function.
        $blocks = @\parse_blocks($value);
        if (count($blocks) === 0) {
            return [];
        }

        // Get the inner blocks recursively.
        $return = $this->getInnerBlocks($blocks, []);

        // Return the blocks.
        return $return;
    }

    /**
     * Given an array of inner blocks, fetch all of its attributes and merge together.
     * 
     * @param array $innerBlocks
     * @return array
     */
    private function getInnerBlocks(array $innerBlocks, array $currentBlocks)
    {
        foreach ($innerBlocks as $block) {
            // Essential values to have.
            if (!isset($block['blockName'], $block['attrs'])) {
                continue;
            }

            // If the block already exists, merge all the atts together.
            if (isset($currentBlocks[$block['blockName']])) {
                $currentBlocks[$block['blockName']] = $this->mergeArraysConcatenateValues($currentBlocks[$block['blockName']], $block['attrs']);
            } else {
                $currentBlocks[$block['blockName']] = $block['attrs'];
            }

            // In case the block has innerblocks, we need to fetch them all and merge them together.
            if (isset($block['innerBlocks']) && is_array($block['innerBlocks']) && count($block['innerBlocks']) > 0) {
                $currentBlocks = $this->getInnerBlocks($block['innerBlocks'], $currentBlocks);
            }
        }

        return $currentBlocks;
    }

    /**
     * Given 2 ararys, merge them together while concatenating its values.
     * 
     * @param array $array1
     * @param array $array2
     * @return array
     */
    public function mergeArraysConcatenateValues($array1, $array2) {
        if (!is_array($array1) || !is_array($array2)) {
            return [];
        }
    
        foreach ($array2 as $key => $value2) {
            if (array_key_exists($key, $array1)) {
                if (is_array($value2) && is_array($array1[$key])) {
                    // If both values are arrays, recursively merge them
                    $array1[$key] = $this->mergeArraysConcatenateValues($array1[$key], $value2);
                } else {
                    // Concatenate values if keys are identical
                    $array1[$key] .= $value2;
                }
            } else {
                // If the key doesn't exist in array1, simply add it
                $array1[$key] = $value2;
            }
        }
    
        return $array1;
    }
}
