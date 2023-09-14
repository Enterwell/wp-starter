<?php
# This file contains strings extracted from each module's module.json file. These strings are used in the Settings pages.
# BEGIN MODULE: admin-user
__( 'Admin User', 'better-wp-security' );
__( 'An advanced tool that removes users with a username of “admin” or a user ID of “1”.', 'better-wp-security' );
__( 'Change Admin User', 'better-wp-security' );
__( 'Changes the username of the “admin” user.', 'better-wp-security' );
__( 'Run this tool to change the username of a user with the “admin” username. This may prevent unsophisticated attacks that assume the “admin” user exists.', 'better-wp-security' );
__( 'New Username', 'better-wp-security' );
__( 'Enter the new username for the “admin” user.', 'better-wp-security' );
__( 'Change User ID 1', 'better-wp-security' );
__( 'Changes the user ID for the first WordPress user.', 'better-wp-security' );
__( 'Run this tool to change the user ID of a user with a user ID of “1”. This may prevent unsophisticated attacks that assume the user with an ID of “1” is an administrator.', 'better-wp-security' );
# END MODULE: admin-user

# BEGIN MODULE: backup
__( 'Database Backups', 'better-wp-security' );
__( 'Manually create a database backup or schedule automatic database backups.', 'better-wp-security' );
__( 'Database backups can help you restore your database in the case of data corruption. However, it is not a complete backup and will not include your site files.', 'better-wp-security' );
__( 'Schedule Database Backups', 'better-wp-security' );
__( 'Backup Interval', 'better-wp-security' );
__( 'The number of days between database backups.', 'better-wp-security' );
__( 'Backup Method', 'better-wp-security' );
__( 'Select what we should do with your backup file. You can have it emailed to you, saved locally or both.', 'better-wp-security' );
__( 'Save Locally and Email', 'better-wp-security' );
__( 'Email Only', 'better-wp-security' );
__( 'Save Locally Only', 'better-wp-security' );
__( 'Backup Location', 'better-wp-security' );
__( 'The path on your machine where backup files should be stored. For added security, it is recommended you do not include it in your website root folder.', 'better-wp-security' );
__( 'Backups to Retain', 'better-wp-security' );
__( 'Limit the number of backups stored locally (on this server). Any older backups beyond this number will be removed. Enter “0” to retain all backups.', 'better-wp-security' );
__( 'Compress Backup Files', 'better-wp-security' );
__( 'By default, iThemes Security will zip backup files to reduce file size. You may need to turn this off if you are having problems with backups.', 'better-wp-security' );
__( 'Backup Tables', 'better-wp-security' );
__( 'Specify which tables should be included or excluded from backups. WordPress Core tables are always included.', 'better-wp-security' );
__( 'Last Run', 'better-wp-security' );
__( 'Scheduling', 'better-wp-security' );
__( 'Configuration', 'better-wp-security' );
__( 'Backup Tables', 'better-wp-security' );
__( 'Excluded Tables', 'better-wp-security' );
__( 'List of tables to exclude from each backup.', 'better-wp-security' );
__( 'Included Tables', 'better-wp-security' );
__( 'List of tables to include in each backup.', 'better-wp-security' );
# END MODULE: backup

# BEGIN MODULE: ban-users
__( 'Ban Users', 'better-wp-security' );
__( 'Block specific IP addresses and user agents from accessing the site.', 'better-wp-security' );
__( 'iThemes Security automatically adds an IP to the ban list once it meets the Ban Threshold requirements. The Ban Threshold setting can be adjusted in the [Global Settings](itsec://settings/configure/global). You can manually add IPs to the ban list from the Security Dashboard using the Banned Users card.', 'better-wp-security' );
__( 'blacklist', 'better-wp-security' );
__( 'Default Ban List', 'better-wp-security' );
__( 'As a getting-started point you can include the HackRepair.com ban list developed by Jim Walker.', 'better-wp-security' );
__( 'Enable Ban Lists', 'better-wp-security' );
__( 'Limit Banned IPs in Server Configuration Files', 'better-wp-security' );
__( 'Limiting the number of IPs blocked by the Server Configuration Files (.htaccess and nginx.conf) will help reduce the risk of a server timeout when updating the configuration file. If the number of IPs in the banned list exceeds the Server Configuration File limit, the additional IPs will be blocked using PHP. Blocking IPs at the server level is more efficient than blocking IPs at the application level using PHP.', 'better-wp-security' );
__( 'Ban User Agents', 'better-wp-security' );
__( 'Enter a list of user agents that will not be allowed access to your site. Add one user agent per-line.', 'better-wp-security' );
__( 'Custom Bans', 'better-wp-security' );
# END MODULE: ban-users

# BEGIN MODULE: brute-force
__( 'Local Brute Force', 'better-wp-security' );
__( 'Protect your site against attackers that try to randomly guess login details to your site.', 'better-wp-security' );
__( 'If one had unlimited time and wanted to try an unlimited number of password combinations to get into your site they eventually would, right? This method of attack, known as a brute force attack, is something that WordPress is acutely susceptible to as, by default, the system doesn’t care how many attempts a user makes to login. It will always let you try again. Enabling login limits will ban the host user from attempting to login again after the specified bad login threshold has been reached.', 'better-wp-security' );
__( 'Automatically ban “admin” user', 'better-wp-security' );
__( 'Immediately ban a host that attempts to login using the “admin” username.', 'better-wp-security' );
__( 'Max Login Attempts Per Host', 'better-wp-security' );
__( 'The number of login attempts a user has before their host or computer is locked out of the system. Set to 0 to record bad login attempts without locking out the host.', 'better-wp-security' );
__( 'Max Login Attempts Per User', 'better-wp-security' );
__( 'The number of login attempts a user has before their username is locked out of the system. Note that this is different from hosts in case an attacker is using multiple computers. In addition, if they are using your login name you could be locked out yourself. Set to 0 to log bad login attempts per user without ever locking the user out (this is not recommended).', 'better-wp-security' );
__( 'Minutes to Remember Bad Login (check period)', 'better-wp-security' );
__( 'The number of minutes in which bad logins should be remembered.', 'better-wp-security' );
__( 'Login Attempts', 'better-wp-security' );
# END MODULE: brute-force

# BEGIN MODULE: content-directory
__( 'Change Content Directory', 'better-wp-security' );
__( 'Advanced feature to rename the wp-content directory to a different name.', 'better-wp-security' );
# END MODULE: content-directory

# BEGIN MODULE: core
__( 'Core', 'better-wp-security' );
__( 'Set Encryption Key', 'better-wp-security' );
__( 'Sets a secure key that iThemes Security uses to encrypt sensitive values like Two-Factor codes.', 'better-wp-security' );
__( 'iThemes Security will add a constant to your website’s <code>wp-config.php</code> file named <code>ITSEC_ENCRYPTION_KEY</code>.', 'better-wp-security' );
__( 'encryption', 'better-wp-security' );
__( 'Confirm Reset Key', 'better-wp-security' );
__( 'Confirm you want to reset the encryption key to a new value.', 'better-wp-security' );
__( 'Rotate Encryption Key', 'better-wp-security' );
__( 'Updates all encrypted values to use the new encryption key.', 'better-wp-security' );
__( 'If you’ve manually updated the <code>ITSEC_ENCRYPTION_KEY</code> constant in your website’s <code>wp-config.php</code> file, use this tool to update any existing encrypted values.', 'better-wp-security' );
__( 'encryption', 'better-wp-security' );
__( 'Previous Key', 'better-wp-security' );
__( 'Provide the previous value of <code>ITSEC_ENCRYPTION_KEY</code>.', 'better-wp-security' );
# END MODULE: core

# BEGIN MODULE: dashboard
__( 'Security Dashboard', 'better-wp-security' );
__( 'See a real-time overview of the security activity on your website with this dynamic dashboard.', 'better-wp-security' );
__( 'Enable Dashboard Creation', 'better-wp-security' );
__( 'Allow users to create new iThemes Security Dashboards.', 'better-wp-security' );
# END MODULE: dashboard

# BEGIN MODULE: database-prefix
__( 'Change Database Table Prefix', 'better-wp-security' );
__( 'Changes the database table prefix that WordPress uses.', 'better-wp-security' );
__( 'By default, WordPress assigns the prefix wp_ to all tables in the database where your content, users, and objects exist. For potential attackers, this means it is easier to write scripts that can target WordPress databases as all the important table names for 95% of sites are already known. Changing the wp_ prefix makes it more difficult for tools that are trying to take advantage of vulnerabilities in other places to affect the database of your site. Before using this tool, we strongly recommend creating a backup of your database.', 'better-wp-security' );
# END MODULE: database-prefix

# BEGIN MODULE: email-confirmation
__( 'Email Confirmation', 'better-wp-security' );
# END MODULE: email-confirmation

# BEGIN MODULE: feature-flags
__( 'Feature Flags', 'better-wp-security' );
__( 'Feature Flags in iThemes Security allow you to try experimental features before they are released.', 'better-wp-security' );
__( 'Enabled Features', 'better-wp-security' );
__( 'Select which experimental features you’d like to enable.', 'better-wp-security' );
# END MODULE: feature-flags

# BEGIN MODULE: file-change
__( 'File Change', 'better-wp-security' );
__( 'Monitor the site for unexpected file changes.', 'better-wp-security' );
__( 'Even the best security practices can fail. The key to quickly spotting a security breach is monitoring file changes on your website.<br>While the type of damage malware causes on your website varies greatly, what it does can be boiled down to adding, removing, or modifying files.<br>File Change Detection scans your website’s files and alerts you when changes occur on your website.', 'better-wp-security' );
__( 'Excluded Files and Folders', 'better-wp-security' );
__( 'Enter a list of file paths to exclude from each File Change scan.', 'better-wp-security' );
__( 'Ignore File Types', 'better-wp-security' );
__( 'File types listed here will not be checked for changes. While it is possible to change files such as images it is quite rare and nearly all known WordPress attacks exploit php, js and other text files.', 'better-wp-security' );
__( 'Compare Files Online', 'better-wp-security' );
__( 'When any WordPress core file or file in an iThemes plugin or theme has been changed on your system, this feature will compare it with the version on WordPress.org or iThemes (as appropriate) to determine if the change was malicious. Currently this feature only works with WordPress core files, plugins on the WordPress.org directory and iThemes plugins and themes (plugins and themes from other sources will be added as available).', 'better-wp-security' );
__( 'Excluded Files', 'better-wp-security' );
__( 'Online Files', 'better-wp-security' );
# END MODULE: file-change

# BEGIN MODULE: file-permissions
__( 'File Permissions', 'better-wp-security' );
__( 'Lists file and directory permissions of key areas of the site.', 'better-wp-security' );
__( 'Check File Permissions', 'better-wp-security' );
# END MODULE: file-permissions

# BEGIN MODULE: file-writing
__( 'File Writing', 'better-wp-security' );
__( 'Server Config Rules', 'better-wp-security' );
__( 'View or flush the generated Server Config rules.', 'better-wp-security' );
__( 'The “Write to Files” setting must be enabled to automatically flush rules.', 'better-wp-security' );
__( 'wp-config.php Rules', 'better-wp-security' );
__( 'View or flush the generated wp-config.php rules.', 'better-wp-security' );
__( 'The “Write to Files” setting must be enabled to automatically flush rules.', 'better-wp-security' );
# END MODULE: file-writing

# BEGIN MODULE: global
__( 'Global Settings', 'better-wp-security' );
__( 'Configure basic settings that control how iThemes Security functions.', 'better-wp-security' );
__( 'Changes made to the Global Settings are applied globally throughout the plugin settings. For example, the Lockout & Lockout messages settings are used by all of the iThemes Security Lockout features.', 'better-wp-security' );
__( 'Write to Files', 'better-wp-security' );
__( 'Allow iThemes Security to write to wp-config.php and .htaccess automatically. If disabled, you will need to place configuration options in those files manually.', 'better-wp-security' );
__( 'NGINX Conf File', 'better-wp-security' );
__( 'This path must be writable by your website. For added security, it is recommended you do not include it in your website root folder.', 'better-wp-security' );
__( 'Minutes to Lockout', 'better-wp-security' );
__( 'The length of time a host or user will be locked out from this site after hitting the limit of bad logins. The default setting of 15 minutes is recommended as increasing it could prevent attackers from being banned.', 'better-wp-security' );
__( 'Days to Remember Lockouts', 'better-wp-security' );
__( 'How many days should iThemes Security remember a lockout. This does not affect the logs generated when creating a lockout.', 'better-wp-security' );
__( 'Ban Repeat Offender', 'better-wp-security' );
__( 'Should iThemes Security permanently add a locked out IP address to the “Ban Users” list after reaching the configured “Ban Threshold”.', 'better-wp-security' );
__( 'Ban Threshold', 'better-wp-security' );
__( 'The number of lockouts iThemes Security must remember before permanently banning the attacker.', 'better-wp-security' );
__( 'Host Lockout Message', 'better-wp-security' );
__( 'The message to display when a computer (host) has been locked out.', 'better-wp-security' );
__( 'User Lockout Message', 'better-wp-security' );
__( 'The message to display to a user when their account has been locked out.', 'better-wp-security' );
__( 'Community Lockout Message', 'better-wp-security' );
__( 'The message to display to a user when their IP has been flagged as bad by the iThemes network.', 'better-wp-security' );
__( 'Automatically Temporarily Authorize Hosts', 'better-wp-security' );
__( 'Whenever an administrator user accesses the website, iThemes Security will prevent locking out that computer for 24 hours.', 'better-wp-security' );
__( 'Authorized Hosts', 'better-wp-security' );
__( 'Enter a list of hosts that should not be locked out by iThemes Security.', 'better-wp-security' );
__( 'whitelist', 'better-wp-security' );
__( 'How should event logs be kept', 'better-wp-security' );
__( 'iThemes Security can log events in multiple ways, each with advantages and disadvantages. Database Only puts all events in the database with your posts and other WordPress data. This makes it easy to retrieve and process but can be slower if the database table gets very large. File Only is very fast but the plugin does not process the logs itself as that would take far more resources. For most users or smaller sites Database Only should be fine. If you have a very large site or a log processing software then File Only might be a better option.', 'better-wp-security' );
__( 'Database Only', 'better-wp-security' );
__( 'File Only', 'better-wp-security' );
__( 'Both', 'better-wp-security' );
__( 'Days to Keep Database Logs', 'better-wp-security' );
__( 'The number of days database logs should be kept.', 'better-wp-security' );
__( 'Days to Keep File Logs', 'better-wp-security' );
__( 'The number of days file logs should be kept. File logs will additionally be rotated once the file hits 10MB. Set to 0 to only use log rotation.', 'better-wp-security' );
__( 'Path to Log Files', 'better-wp-security' );
__( 'This path must be writable by your website. For added security, it is recommended you do not include it in your website root folder.', 'better-wp-security' );
__( 'Proxy Detection', 'better-wp-security' );
__( 'Determine how iThemes Security determines your visitor’s IP addresses. Choose the Security Check Scan to let iThemes Security identify malicious IPs attacking your website accurately.', 'better-wp-security' );
__( 'Proxy Header', 'better-wp-security' );
__( 'Select the header your Proxy Server uses to forward the client IP address. If you don’t know the header, you can contact your hosting provider or select the header that has your IP Address.', 'better-wp-security' );
__( 'Allow Data Tracking', 'better-wp-security' );
__( 'Allow iThemes to track plugin usage via anonymous data.', 'better-wp-security' );
__( 'Hide Security Menu in Admin Bar', 'better-wp-security' );
__( 'Remove the Security Messages Menu from the admin bar. Notifications will only appear on the iThemes Security dashboard and settings pages.', 'better-wp-security' );
__( 'Show Error Codes', 'better-wp-security' );
__( 'Each error message in iThemes Security has an associated error code that can help diagnose an issue. Changing this setting to “Yes” causes these codes to display. This setting should be left set to “No” unless iThemes Security support requests that you change it.', 'better-wp-security' );
__( 'Lockouts', 'better-wp-security' );
__( 'Lockout Messages', 'better-wp-security' );
__( 'Authorized Hosts', 'better-wp-security' );
__( 'Logging', 'better-wp-security' );
__( 'IP Detection', 'better-wp-security' );
__( 'UI Tweaks', 'better-wp-security' );
__( 'Manage iThemes Security', 'better-wp-security' );
__( 'Allow users to manage iThemes Security.', 'better-wp-security' );
__( 'Identify Server IPs', 'better-wp-security' );
__( 'Determines the list of IP addresses your server uses when making HTTP requests.', 'better-wp-security' );
__( 'The correct list of server IPs is important to prevent erroneous Lockouts and Trusted Devices errors.', 'better-wp-security' );
# END MODULE: global

# BEGIN MODULE: hibp
__( 'Refuse Compromised Passwords', 'better-wp-security' );
__( 'Require users to use passwords which do not appear in any password breaches tracked by Have I Been Pwned. Plaintext passwords are never sent to Have I Been Pwned. Instead, 5 characters of the hashed password are sent over an encrypted connection to their API. ', 'better-wp-security' );
# END MODULE: hibp

# BEGIN MODULE: hide-backend
__( 'Hide Backend', 'better-wp-security' );
__( 'Change the login URL of your site.', 'better-wp-security' );
__( 'The Hide Backend feature isn’t fool proof, and your new login URL could still be exposed by WordPress Core, Plugins, or Themes when printing links to the login page. For example Privacy Request Confirmations or front-end login forms. We recommend using more robust security features like Two-Factor Authentication to secure your WordPress login page.', 'better-wp-security' );
__( 'Hide Backend', 'better-wp-security' );
__( 'Enable the hide backend feature.', 'better-wp-security' );
__( 'Login Slug', 'better-wp-security' );
__( 'The login url slug cannot be “login”, “admin”, “dashboard”, or “wp-login.php” as these are use by default in WordPress.', 'better-wp-security' );
__( 'Register Slug', 'better-wp-security' );
__( 'Enable Redirection', 'better-wp-security' );
__( 'Redirect users to a custom location on your site, instead of throwing a 403 (forbidden) error.', 'better-wp-security' );
__( 'Redirection Slug', 'better-wp-security' );
__( 'The slug to redirect users to when they attempt to access wp-admin while not logged in.', 'better-wp-security' );
__( 'Custom Login Action', 'better-wp-security' );
__( 'WordPress uses the “action” variable to handle many login and logout functions. By default this plugin can handle the normal ones but some plugins and themes may utilize a custom action (such as logging out of a private post). If you need a custom action please enter it here.', 'better-wp-security' );
__( 'URLs', 'better-wp-security' );
__( 'Redirection', 'better-wp-security' );
__( 'Advanced', 'better-wp-security' );
# END MODULE: hide-backend

# BEGIN MODULE: malware-scheduling
__( 'Site Scan Scheduling', 'better-wp-security' );
__( 'Protect your site with automated site scans. When this feature is enabled, the site will be automatically scanned twice a day. If a problem is found, an email is sent to select users.', 'better-wp-security' );
# END MODULE: malware-scheduling

# BEGIN MODULE: network-brute-force
__( 'Network Brute Force', 'better-wp-security' );
__( 'Join a network of sites that reports and protects against bad actors on the internet.', 'better-wp-security' );
__( 'If one had unlimited time and wanted to try an unlimited number of password combinations to get into your site they eventually would, right? This method of attack, known as a brute force attack, is something that WordPress is acutely susceptible to as, by default, the system doesn’t care how many attempts a user makes to login. It will always let you try again. Enabling login limits will ban the host user from attempting to login again after the specified bad login threshold has been reached.', 'better-wp-security' );
__( 'Ban Reported IPs', 'better-wp-security' );
__( 'Automatically ban IPs reported as a problem by the network.', 'better-wp-security' );
__( 'API Key', 'better-wp-security' );
__( 'Email Address', 'better-wp-security' );
__( 'Receive Email Updates', 'better-wp-security' );
__( 'Get the weekly WordPress Vulnerability Report and more WordPress security updates sent to your inbox.', 'better-wp-security' );
__( 'API Configuration', 'better-wp-security' );
# END MODULE: network-brute-force

# BEGIN MODULE: notification-center
__( 'Notification Center', 'better-wp-security' );
__( 'Manage and configure email notifications sent by iThemes Security related to various settings modules.', 'better-wp-security' );
__( 'Using the Notification Center, you can set the default recipients, enable the security digest email, customize email notifications, and more.', 'better-wp-security' );
__( 'From Email', 'better-wp-security' );
__( 'iThemes Security will send notifications from this email address. Leave blank to use the WordPress default.', 'better-wp-security' );
__( 'Default Recipients', 'better-wp-security' );
__( 'Set the default recipients for any admin-facing notifications.', 'better-wp-security' );
# END MODULE: notification-center

# BEGIN MODULE: password-requirements
__( 'Password Requirements', 'better-wp-security' );
__( 'Requiring strong and refusing compromised passwords is the first step in securing your login page.', 'better-wp-security' );
__( 'Brute force attacks rely on people reusing weak passwords. Password Requirements allow you to force selected users to create a strong password that hasn’t already been compromised.', 'better-wp-security' );
__( 'Requirement Settings', 'better-wp-security' );
# END MODULE: password-requirements

# BEGIN MODULE: privacy
__( 'Privacy', 'better-wp-security' );
# END MODULE: privacy

# BEGIN MODULE: wordpress-salts
__( 'WordPress Salts', 'better-wp-security' );
__( 'Change WordPress Salts', 'better-wp-security' );
__( 'Changes the WordPress salts used to secure cookies and security tokens.', 'better-wp-security' );
__( 'This shouldn’t be done periodically, but only if you suspect your site may have been compromised. This will force all users to login again.', 'better-wp-security' );
# END MODULE: wordpress-salts

# BEGIN MODULE: security-check-pro
__( 'Security Check Pro', 'better-wp-security' );
__( 'Detects the correct way to identify user IP addresses based on your server configuration by making an API request to iThemes.com servers. No user information is sent to iThemes. [Read our Privacy Policy](https://ithemes.com/privacy-policy/).', 'better-wp-security' );
__( 'Detects the correct way to identify user IP addresses based on your server configuration.', 'better-wp-security' );
# END MODULE: security-check-pro

# BEGIN MODULE: site-scanner
__( 'Site Scanner', 'better-wp-security' );
# END MODULE: site-scanner

# BEGIN MODULE: ssl
__( 'Enforce SSL', 'better-wp-security' );
__( 'Enforces that all connections to the website are made over SSL/TLS.', 'better-wp-security' );
__( 'Require SSL', 'better-wp-security' );
__( 'Redirect All HTTP Page Requests to HTTPS', 'better-wp-security' );
__( 'Front End SSL Mode', 'better-wp-security' );
__( 'Enables secure SSL connection for the front-end (public parts of your site). Turning this off will disable front-end SSL control, turning this on "Per Content" will place a checkbox on the edit page for all posts and pages (near the publish settings) allowing you to turn on SSL for selected pages or posts. Selecting "Whole Site" will force the whole site to use SSL.', 'better-wp-security' );
__( 'SSL for Dashboard', 'better-wp-security' );
__( 'Forces all dashboard access to be served only over an SSL connection.', 'better-wp-security' );
# END MODULE: ssl

# BEGIN MODULE: strong-passwords
__( 'Strong Passwords', 'better-wp-security' );
__( 'Force users to use strong passwords as rated by the WordPress password meter.', 'better-wp-security' );
__( 'Strong Passwords', 'better-wp-security' );
__( 'Force users to use strong passwords as rated by the WordPress password meter.', 'better-wp-security' );
# END MODULE: strong-passwords

# BEGIN MODULE: sync-connect
__( 'Sync Connect', 'better-wp-security' );
# END MODULE: sync-connect

# BEGIN MODULE: system-tweaks
__( 'System Tweaks', 'better-wp-security' );
__( 'Make changes to the server configuration for this site.', 'better-wp-security' );
__( 'Increase security by restricting file access and PHP execution on your server. This can help mitigate arbitrary file upload vulnerabilities from gaining complete control of your server.', 'better-wp-security' );
__( 'Protect System Files', 'better-wp-security' );
__( 'Prevent public access to readme.html, readme.txt, wp-config.php, install.php, wp-includes, and .htaccess. These files can give away important information on your site and serve no purpose to the public once WordPress has been successfully installed.', 'better-wp-security' );
__( 'Disable Directory Browsing', 'better-wp-security' );
__( 'Prevents users from seeing a list of files in a directory when no index file is present.', 'better-wp-security' );
__( 'Disable PHP in Uploads', 'better-wp-security' );
__( 'Disable PHP execution in the uploads directory. This blocks requests to maliciously uploaded PHP files in the uploads directory.', 'better-wp-security' );
__( 'Disable PHP in Plugins', 'better-wp-security' );
__( 'Disable PHP execution in the plugins directory. This blocks requests to PHP files inside plugin directories that can be exploited directly.', 'better-wp-security' );
__( 'Disable PHP in Themes', 'better-wp-security' );
__( 'Disable PHP execution in the themes directory. This blocks requests to PHP files inside theme directories that can be exploited directly.', 'better-wp-security' );
__( 'File Access', 'better-wp-security' );
__( 'PHP Execution', 'better-wp-security' );
# END MODULE: system-tweaks

# BEGIN MODULE: two-factor
__( 'Two-Factor', 'better-wp-security' );
__( 'Two-Factor Authentication greatly increases the security of your WordPress user account by requiring additional information beyond your username and password in order to log in.', 'better-wp-security' );
__( 'Two-Factor authentication is a tried and true security method and will stop most automated bot attacks on the WordPress login. Once Two-Factor Authentication is enabled here, users can visit their profile to enable two-factor for their account.', 'better-wp-security' );
__( '2fa', 'better-wp-security' );
__( 'multi-factor', 'better-wp-security' );
__( 'mfa', 'better-wp-security' );
__( 'Authentication Methods Available to Users', 'better-wp-security' );
__( 'iThemes Security supports multiple two-factor methods: mobile app, email, and backup codes. Selecting “All Methods” is highly recommended so that users can use the method that works the best for them.', 'better-wp-security' );
__( 'All Methods (recommended)', 'better-wp-security' );
__( 'All Except Email', 'better-wp-security' );
__( 'Select Methods Manually', 'better-wp-security' );
__( 'Select Available Methods', 'better-wp-security' );
__( 'Disable on First Login', 'better-wp-security' );
__( 'This simplifies the sign up flow for users that require two-factor to be enabled for their account.', 'better-wp-security' );
__( 'On-board Welcome Text', 'better-wp-security' );
__( 'Customize the text shown to users at the beginning of the Two-Factor On-Board flow.', 'better-wp-security' );
__( 'Methods', 'better-wp-security' );
__( 'Setup Flow', 'better-wp-security' );
__( 'Skip Two-Factor Onboarding', 'better-wp-security' );
__( 'By default, when a user logs in via the WordPress Login Page, iThemes Security will prompt them to setup Two-Factor. Optionally, you can skip the two-factor authentication on-boarding process for certain users. Users can still manually enroll in two-factor through their WordPress admin profile.', 'better-wp-security' );
__( 'Application Passwords', 'better-wp-security' );
__( 'Use Application Passwords to allow authentication without providing your actual password when using non-traditional login methods such as XML-RPC or the REST API. They can be easily revoked, and can never be used for traditional logins to your website.', 'better-wp-security' );
# END MODULE: two-factor

# BEGIN MODULE: user-groups
__( 'User Groups', 'better-wp-security' );
__( 'User Groups allow you to enable security features for specific sets of users.', 'better-wp-security' );
__( 'User Groups allow you to view and manage the security settings that affect how people interact with your site. Enabling security features per group gives you the flexibility to apply the right level of security to the right people.</br>If a user belongs to multiple groups, all settings enabled in those groups will be applied to that user.', 'better-wp-security' );
# END MODULE: user-groups

# BEGIN MODULE: wordpress-tweaks
__( 'WordPress Tweaks', 'better-wp-security' );
__( 'Make changes to the default behavior of WordPress.', 'better-wp-security' );
__( 'Increase the security of your website by removing the ability to edit files from the WordPress dashboard and limiting how APIs and users access your site.', 'better-wp-security' );
__( 'Disable File Editor', 'better-wp-security' );
__( 'Disables the WordPress file editor for plugins and themes. Once activated you will need to manually edit files using FTP or other tools.', 'better-wp-security' );
__( 'XML-RPC', 'better-wp-security' );
__( 'The WordPress XML-RPC API allows external services to access and modify content on the site. Common example of services that make use of XML-RPC are [the Jetpack plugin](https://jetpack.com), [the WordPress mobile apps](https://wordpress.org/mobile/), and [pingbacks](https://wpbeg.in/IiI0sh). If the site does not use a service that requires XML-RPC, select the “Disable XML-RPC” setting as disabling XML-RPC prevents attackers from using the feature to attack the site.', 'better-wp-security' );
__( 'Disable XML-RPC', 'better-wp-security' );
__( 'XML-RPC is disabled on the site. This setting is highly recommended if Jetpack, the WordPress mobile app, pingbacks, and other services that use XML-RPC are not used.', 'better-wp-security' );
__( 'Disable Pingbacks', 'better-wp-security' );
__( 'Only disable pingbacks. Other XML-RPC features will work as normal. Select this setting if you require features such as Jetpack or the WordPress Mobile app.', 'better-wp-security' );
__( 'Enable XML-RPC', 'better-wp-security' );
__( 'XML-RPC is fully enabled and will function as normal. Use this setting only if the site must have unrestricted use of XML-RPC.', 'better-wp-security' );
__( 'Allow Multiple Authentication Attempts per XML-RPC Request', 'better-wp-security' );
__( 'By default, the WordPress XML-RPC API allows hundreds of username and password guesses per request. Turn off this setting to prevent attackers from exploiting this feature.', 'better-wp-security' );
__( 'REST API', 'better-wp-security' );
__( 'The WordPress REST API is part of WordPress and provides developers with new ways to manage WordPress. By default, it could give public access to information that you believe is private on your site.', 'better-wp-security' );
__( 'Default Access', 'better-wp-security' );
__( 'Access to REST API data is left as default. Information including published posts, user details, and media library entries is available for public access.', 'better-wp-security' );
__( 'Restricted Access', 'better-wp-security' );
__( 'Restrict access to most REST API data. This means that most requests will require a logged in user or a user with specific privileges, blocking public requests for potentially-private data. We recommend selecting this option.', 'better-wp-security' );
__( 'Login with Email Address or Username', 'better-wp-security' );
__( 'By default, WordPress allows users to log in using either an email address or username. This setting allows you to restrict logins to only accept email addresses or usernames.', 'better-wp-security' );
__( 'Email Address and Username', 'better-wp-security' );
__( 'Allow users to log in using their user’s email address or username. This is the default WordPress behavior.', 'better-wp-security' );
__( 'Email Address Only', 'better-wp-security' );
__( 'Users can only log in using their user’s email address. This disables logging in using a username.', 'better-wp-security' );
__( 'Username Only', 'better-wp-security' );
__( 'Users can only log in using their user’s username. This disables logging in using an email address.', 'better-wp-security' );
__( 'Force Unique Nickname', 'better-wp-security' );
__( 'This forces users to choose a unique nickname when updating their profile or creating a new account which prevents bots and attackers from easily harvesting user’s login usernames from the code on author pages. Note this does not automatically update existing users as it will affect author feed urls if used.', 'better-wp-security' );
__( 'Disable Extra User Archives', 'better-wp-security' );
__( 'Disables a user’s author page if their post count is 0. This makes it harder for bots to determine usernames by disabling post archives for users that don’t write content for your site.', 'better-wp-security' );
__( 'API Access', 'better-wp-security' );
__( 'Users', 'better-wp-security' );
# END MODULE: wordpress-tweaks