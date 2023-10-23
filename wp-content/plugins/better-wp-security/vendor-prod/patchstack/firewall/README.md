# Patchstack Firewall Engine #

This repository contains the firewall engine of Patchstack.
It can be implemented inside of other content management systems to provide firewall functionality.

## Implementation ##
Implementation is simple, and examples can be seen in `/tests/FirewallTest.php`

Example firewall rules can be seen in `/tests/data/Rules.json`

Example whitelist rules can be seen in `/tests/data/Whitelist.json`

```php
use Patchstack\Processor;
use Patchstack\Extensions\WordPress\Extension;

// Load the firewall rules, whitelist rules and settings from some place.
$firewallRules = [];
$whitelistRules = [];
$settings = [];

// Setup the firewall rules processor.
$firewall = new Processor(
    new Extension(),
    $firewallRules,
    $whitelistRules,
    $settings
);

// And launch it. If a request was a hit with a firewall rule, it will automatically stop execution.
$firewall->launch();
```

## Functionality ##
This firewall engine can parse and understand JSON based firewall rules. These JSON based firewall rules allow you to match against parameters, match against multiple conditions being true, apply mutations (e.g., JSON decode or base64 decode) to payloads and compare against output of PHP functions.

For example, instead of having to write a regular expression to determine if a certain GET (query parameter) variable is a number or not to prevent SQL injection, we can simply create a firewall rule that contains the following JSON rule.

```json
[
   {
      "parameter":"get.pid",
      "match":{
         "type":"ctype_digit",
         "value":false
      }
   }
]
```
In this case the firewall rule will check if the query parameter pid is set in the URL and if the return value of PHP's ctype_digit function against this parameter is false, will block the request. At the bottom of this document are more examples.

## Performance ##

Of course, performance is also a concern, you don't want to slow down the sites of your users considerably due to a firewall. It's not a secret that many firewall plugins slow down the site due to unoptimized code or weird functionality of the firewall engine. We also decided to focus on performance of the new firewall engine. For example, if a rule contains a specific parameter to match against, we determine if this parameter is present first before we continue processing the firewall rule conditions.

We performed a test against the firewall engine and supplied it with 100 firewall rules. Of course, in no realistic scenario would a site have to process that many firewall rules, but it gives you an idea on what the performance impact might be.

Process time:
PHP 5.6: 0.0020, or about 2 milliseconds
PHP 7.3: 0.0013 seconds, or about 1.3 milliseconds
PHP 7.4: 0.00017 seconds, or about 0.17 milliseconds
PHP 8+: 0.00011 seconds, or about 0.11 milliseconds

These numbers are subject to change slightly as we are still making optimizations and adjustments. However, compared to the previous PHP code-based firewall rules, it's 10 times faster.

## Extension ##

The new firewall engine's library allows you to create an extension to define and override how certain functions work. The extension requires you to implement a few functions:

- How requests are logged after it had matched against a firewall rule
- Who can bypass the entire firewall. E.g., whitelisted user roles of WordPress
- What kind of requests can bypass the entire firewall. E.g., certain keywords in the request
- Determining if a visitor is blocked due to hitting X matches against a firewall rule within a certain amount of minutes
- What should happen if a request has been blocked. (e.g., show a blocked page)
- How the IP address of the visitor should be captured (this can greatly differ between hosts and environments). Must be handled properly or visitors can spoof their IP address through headers such as X-Forwarded-For and bypass certain restrictions.
- How to determine if the current request is a file upload request

You can create an extension to control how some of the functions of the firewall interact and work.

## Concerns ##

A potential concern we have heard before is that because it's a PHP based firewall and integrated into WordPress, would it not miss certain vulnerabilities? That is possible, but after several years we have come to the conclusion that this only affects a very minimal number of vulnerabilities. 

We have to make a choice between simplicity (easy integration into WordPress vs having to do weird hacks to the webserver configuration such as Apache's auto_prepend_file which would increase load considerably as every single thing requested on the site passes through it) and coverage (which would be between 99% and 99.9%). Hooking into the *init* hook of WordPress with hook order set to *~PHP_INT_MAX* (as early as possible) is sufficient for *nearly* all vulnerabilities.

## Who do I talk to? ##

* dave.jong@patchstack.com

## Rule Examples ##
Below are more examples of these JSON based rules with more advanced conditions.

These kind of firewall rules are also significantly easier and faster to create than regular expressions. Of course, as seen in the examples above, one rule can contain multiple rules stacked with different conditions.

Check if an array ($_POST['usernames'][]) contains any values from given array.
```json
[
    {
        "parameter":"post.usernames",
        "match":{
            "type":"array_in_array",
            "value":[
                "test",
                "admin"
            ]
        }
    }
]
```

Check if a value ($_GET['user']) is not in an array
```json
[
    {
        "parameter":"get.user",
        "match":{
            "type":"not_in_array",
            "value":[
                "admin"
            ]
        }
    }
]
```

Check if the URL matches a regex
```json
[
    {
        "parameter":"server.REQUEST_URI",
        "match":{
            "type":"regex",
            "value":"\/(\\\/something\\\/)\/msi"
        }
    }
]
```

Check if a value ($_GET['id']) is not a number or is less than 100
```json
[
    {
        "parameter":"get.pid",
        "match":{
            "type":"ctype_digit",
            "value":false
        }
    },
    {
        "parameter":"get.pid",
        "match":{
            "type":"less_than",
            "value":100
        }
    }
]
```

Check if a query parameter (test) is present in the URL
```json
[
    {
        "parameter":"get.test",
        "match":{
            "type":"isset"
        }
    }
]
```

Check if $_POST['backdoor'] == mybackdoor and user-agent contains some_backdoor_agent
```json
[
    {
        "parameter":"post.backdoor",
        "match":{
            "type":"equals",
            "value":"mybackdoor"
        },
        "inclusive":true
    },
    {
        "parameter":"server.HTTP_USER_AGENT",
        "match":{
            "type":"contains",
            "value":"some_backdoor_agent"
        },
        "inclusive":true
    }
]
```

Check if $_POST['payload'] contains a base64(json()) encoded payload with user_role array key equaling to administrator
```json
[
    {
        "parameter":"post.payload",
        "mutations":[
            "base64_decode",
            "json_decode"
        ],
        "match":{
            "type":"array_key_value",
            "key":"user_role",
            "match":{
                "type":"equals",
                "value":"administrator"
            }
        }
    }
]
```

Check if $_GET['action'] or $_POST['action'] contains a value part of an array of values AND if the user is not an administrator
```json
[
    {
        "parameter":"rules",
        "rules":[
            {
                "parameter":"get.action",
                "match":{
                    "type":"in_array",
                    "value":[
                        "restaurant_system_customize_button",
                        "restaurant_system_insert_dialog"
                    ]
                }
            },
            {
                "parameter":"post.action",
                "match":{
                    "type":"in_array",
                    "value":[
                        "restaurant_system_customize_button",
                        "restaurant_system_insert_dialog"
                    ]
                }
            }
        ],
        "inclusive":true
    },
    {
        "parameter":false,
        "match":{
            "type":"current_user_cannot",
            "value":"administrator"
        },
        "inclusive":true
    }
]
```

Check if the user's IP address is in a list (e.g. whitelist)
Note that the server.ip parameter is a special computed property and retrieves the IP address through the extension that is attached to the library. This IP grabbing function can be adjusted to your needs.
```json
[
    {
        "parameter":"server.ip",
        "match":{
            "type":"in_array",
            "value":[
                "127.0.0.1"
            ]
        }
    }
]
```

Check if a certain value is present anywhere in the request ($_GET, $_POST, $_SERVER['REQUEST_URI'], raw POST data)
```json
[
    {
        "parameter":"all",
        "mutations":[
            "getArrayValues"
        ],
        "match":{
            "type":"regex",
            "value":"\/(\\\/something\\\/)\/msi"
        }
    }
]
```

Check if an uploaded file ($_FILES['img']) contains the PHP opening tag in the contents
```json
[
    {
        "parameter":"files.img",
        "match":{
            "type":"file_contains",
            "match":{
                "type":"contains",
                "value":"<?php"
            }
        }
    }
]
```

Check if the swp_debug parameter is set to load_options and the current user is not an administrator.
https://patchstack.com/database/vulnerability/social-warfare/wordpress-social-warfare-plugin-3-5-2-unauthenticated-remote-code-execution-rce-vulnerability
```json
[
    {
        "parameter":"get.swp_debug",
        "match":{
            "type":"equals",
            "value":"load_options"
        },
        "inclusive":true
    },
    {
        "parameter":false,
        "match":{
            "type":"current_user_cannot",
            "value":"administrator"
        },
        "inclusive":true
    }
]
```