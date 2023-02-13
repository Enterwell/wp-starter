<h1 align="center">
  <a style="display: inline-block;" href="https://enterwell.net/" target="_blank">
    <img src="https://enterwell.net/wp-content/uploads/2022/12/android-chrome-256x256-1.png" alt="php" width="96" />
  </a>
  <p>Enterwell WP starter</p>
</h1>
<p align="center">Start a new project with <b>backend</b> (PHP), <b>frontend</b> (jQuery, React, Sass, Webpack) and<br> <b>Wordpress CMS</b> to take care of them all.</p>
<div align="center">
  <a style="display: inline-block;" href="https://www.php.net/" target="_blank">
    <img src="https://cdn.jsdelivr.net/gh/devicons/devicon/icons/php/php-plain.svg" alt="php" width="30" />
  </a>
  <a style="display: inline-block;" href="https://wordpress.org/" target="_blank">
    <img src="https://cdn.jsdelivr.net/gh/devicons/devicon/icons/wordpress/wordpress-original.svg" alt="wordpress" width="30" />
  </a>
  <a style="display: inline-block;" href="https://jquery.com/" target="_blank">
    <img src="https://cdn.jsdelivr.net/gh/devicons/devicon/icons/jquery/jquery-plain-wordmark.svg" alt="jquery" width="30" />
  </a>
  <a style="display: inline-block;" href="https://reactjs.org/" target="_blank">
    <img src="https://cdn.jsdelivr.net/gh/devicons/devicon/icons/react/react-original-wordmark.svg" alt="react" width="30" />
  </a>
  <a style="display: inline-block;" href="https://sass-lang.com/" target="_blank">
    <img src="https://cdn.jsdelivr.net/gh/devicons/devicon/icons/sass/sass-original.svg" alt="sass" width="30" />
  </a>
  <a style="display: inline-block;" href="https://symfony.com/doc/current/frontend.html" target="_blank">
    <img src="https://cdn.jsdelivr.net/gh/devicons/devicon/icons/webpack/webpack-original.svg" alt="webpack" width="30" />
  </a>
  <a style="display: inline-block;" href="https://www.mysql.com/" target="_blank">
    <img src="https://cdn.jsdelivr.net/gh/devicons/devicon/icons/mysql/mysql-original-wordmark.svg" alt="mysql" width="30" />
  </a>
  <a style="display: inline-block;" href="https://getcomposer.org/" target="_blank">
    <img src="https://cdn.jsdelivr.net/gh/devicons/devicon/icons/composer/composer-original.svg" alt="composer" width="30" />
  </a>
  <a style="display: inline-block;" href="https://yarnpkg.com/" target="_blank">
    <img src="https://cdn.jsdelivr.net/gh/devicons/devicon/icons/yarn/yarn-original-wordmark.svg" alt="yarn" width="30" />
  </a>
  <a style="display: inline-block;" href="https://www.ansible.com/" target="_blank">
    <img src="https://cdn.jsdelivr.net/gh/devicons/devicon/icons/ansible/ansible-original-wordmark.svg" alt="ansible" width="30" />
  </a>
</div>

<div align="center">

![GitHub last commit](https://img.shields.io/github/last-commit/Enterwell/wp-starter?label=Last%20commit)
[![GitHub issues](https://img.shields.io/github/issues/Enterwell/wp-starter?color=0088ff)](https://github.com/Enterwell/wp-starter/issues)
[![GitHub contributors](https://img.shields.io/github/contributors/Enterwell/wp-starter)](https://github.com/Enterwell/wp-starter/graphs/contributors)
[![GitHub pull requests](https://img.shields.io/github/issues-pr/Enterwell/wp-starter?color=0088ff)](https://github.com/Enterwell/wp-starter/pulls)

</div>

## ⚡ Run project

First, go ahead and see if your environment meets the recommended [requirements 🔨](#-requirements).

Clone this project into your web projects directory (`/var/www/`):
```
git clone https://github.com/Enterwell/wp-starter.git
```
> 🔔 If you already have a git repo for your new project, clone this starter into that project, move everything except .git 
> folder from starter folder to your project root folder and remove (now empty) starter folder from your project.

Add your wanted project local URL to hosts file (`/etc/hosts`):
```
127.0.0.1 starter.local
```
Add new virtual host configuration file to your webserver (`/etc/nginx/sites-available`)
```nginx
server {
        listen 80;
        listen [::]:80;

        # Limit file upload to 8 MB
        client_max_body_size 8M;

        # Route to project code root (where page is loaded from)
        root /var/www/<repo_name>;
        index index.html index.htm index.php;
        
        # Sets domain for this configuration
        # Every request with this domain will be routed to this conf
        server_name starter.local;

        # Run all static files directly
        location / {
                try_files $uri $uri/ /index.php?$args;
        }

        # Handle .php files through some PHP service
        location ~ \.php$ {
                include snippets/fastcgi-php.conf;
                # Run PHP files through PHP-FPM service
                # Change version if needed
                fastcgi_pass unix:/run/php/php8.0-fpm.sock;
        }
}
```
> 🔔 Update conf file based on your wanted local domain, project folder and PHP version and link it to enabled sites 
> `sudo ln -s /etc/nginx/sites-available/starter.local /etc/nginx/sites-enabled/starter.local`. Reload web server afterwards: 
> `sudo service nginx reload`

Rename files, folders, namespaces etc. by editing `$ROOT/.scripts/config.js`. Please keep up with the naming of each one 
of them based on the current convention used (noted in the file).

Install node packages in **project root** folder (`$ROOT`):
```
yarn install
```

Run node script that'll rename all your files/folders and create project structure:
```
yarn init-project
```

Navigate to **plugin folder** (`$ROOT/wp-content/<plugin_name>`) and install composer dependencies:
```
composer install
```

Navigate to **theme folder** (`$ROOT/wp-content/<theme_name>`) and install composer and node dependencies:
```
composer install
yarn install
```

Continue by starting the webpack server (in `$ROOT/wp-content/<theme_name>`) that'll automatically open the page in browser:
```
yarn start
```

You are welcomed with WordPress installation steps that are self-descriptive to fill out on your own.
> 🔔 You'll be prompted with entering a database for your project. If you haven't done that already, create an empty database 
> (usually named **wp_<project_name>**) and enter database access credentials (usually root with empty password, based on your conf)

Outcome of the previous installation is the `wp-config.php` file in project root (`$ROOT`). We'll make this changes in it in order 
to see debug information while developing:
```php
//define( 'WP_DEBUG', false );

/* Add any custom values between this line and the "stop editing" line. */
define( 'WP_DEBUG', true );
define( 'WP_DEBUG_DISPLAY', true );
define( 'WP_DEBUG_LOG', true );
/* That's all, stop editing! Happy publishing. */
```

That's it! 🎉 Login to WordPress dashboard and turn on your plugin and your theme.
> 🔔 To clean up installation scripts and files we no longer need, run `bash cleanup.sh` in project root (`$ROOT`)

Good luck developing! 🖥️

## 📖 Table of contents

- [⚡ Run project](#-run-project)
- [🔨 Requirements](#-requirements)
- [🏗 Build project](#-build-project)
- [🚀 Deploy project](#-deploy-project)
- [🧪 Testing](#-testing)
- [⚠ License](#-license)

## 🔨 Requirements

List of recommended (and tested) environment requirements:
- Linux distribution (native or WSL)
- Nginx v1.18.0+
- MySQL/MariaDB distribution v10.6.11+ (or MySQL alternative)
- PHP v8.0+ with PHP-FPM
- Composer v2+
- NodeJS v16+ (LTS)
- Git
> 🔔 It is possible to run this on XAMPP, Laragon (and on Apache) or other environments with other versions, but this is 
> the tested and most used stack (LEMP)

## 🪄 Purpose and capabilities

> ⛏ TODO: explain purpose of this starter, what can you do with it, short summary of its architecture and what does each part of it do, 
> link to [Gutenberg documentation](wp-content/themes/ew-theme/assets/gutenberg/README.md) (and translate it to ENG)

## 🏛 Project structure

> ⛏ TODO: visualize project structure, where are files meant to be created and worked on

## 🏗 Build project

> 🔔 There is an [Azure Pipelines YAML file](azure-pipelines-build.yml) prepared for building the project that you can use 
> instead of doing the next steps manually

Due to being an interpreted language, PHP does not need to be built. On the other hand, we need Composer dependencies installed 
for production purposes. To remove dev packages used in development, run the command in plugin and theme folders (`$PLUGIN_DIR` and `$THEME_DIR`):
```
composer update --no-dev
```

We do use Javascript that's wrapped with Webpack. 
Webpack files are built and served in `$THEME_DIR/assets/dist` folder by running the command in theme folder (`$THEME_DIR`):
```
yarn build
```

There are also some files in the project that we don't need on the server. To see which ones those are, please check the
[Removed unused files for artifact build](azure-pipelines-build.yml) script in the pipeline file.
> ⚠ That script is meant to be run on the build agent environment. Cherry-pick which commands you want to run because the 
> script deletes all git files, documentation etc. that you need in your repository.

That's it 🥳

## 🚀 Deploy project
After [🏗 building the project](#-build-project), your files are ready to be transferred to your public environment.

> ⛏ TODO: prepare ansible script for setting up environments.

## 🧪 Testing

Due to using a couple of technologies together in this starter project, they are tested in somewhat different ways. 
We'll explain each one of them: which technologies are tests ran on, how are they written and how to run them.

### PHP
All server-based programming logic is usually written in PHP in a custom plugin developed for each project. That's why 
the testing for this is prepared on a plugin basis in plugin folder. There is some logic written in PHP in theme as well 
but theme typically calls plugin methods and functions, so we don't often test these (which we should).

PHP code is tested with [PHPUnit](https://phpunit.de/) and based on practises pushed by WordPress. It is based on the 
same thing but WordPress added its bits and pieces to make it "better".

> 📚 **Useful links**
> - [PHPUnit documentation](https://phpunit.readthedocs.io/en/9.6/index.html)
> - [Wordpress PHPUnit overview](https://make.wordpress.org/core/handbook/testing/automated-testing/writing-phpunit-tests/)
> - [Premise on which PHPUnit is set up in this starter](https://make.wordpress.org/cli/handbook/misc/plugin-unit-tests/)

#### Setup
To start testing your code with PHPUnit, we need an empty WordPress installation and an empty database. Here are some 
steps to get you to that point:
> 🔔 [Requirements](https://make.wordpress.org/cli/handbook/misc/plugin-unit-tests/#running-tests-locally): Linux environment, svn package, git package (and PHP of course)
- navigate to `$ROOT/wp-content/plugins/ewplugin` and run `composer install` to install PHPUnit
- in the same folder run `bash bin/install-wp-tests.sh wp_starter-test root '' localhost latest`
  - this will install you a fresh WordPress installation to `$PLUGIN_DIR/tests/tmp` and WordPress testing tools. It also 
    creates a database based on parameter sent to the command above
    - **wp_wp-starter-test** is the name of the test database (all data will be deleted!)
    - **root** is the MySQL username
    - **''** is the MySQL user password
    - **localhost** is the MySQL server host
    - **latest** is the WordPress version; could also be 3.7, 3.6.2 etc.
- run tests with `./vendor/bin/phpunit` in `$PLUGIN_DIR`

When environment is set up once for a project, every next test run is triggered by calling 
```
./vendor/bin/phpunit
```  
> 🔔 Tests need to be named as `test-` and have to be saved as a `.php` file.

|                                                                                                                                                  **Unit**                                                                                                                                                   |                                                                                                                                                                                                                                                                  **Integration**                                                                                                                                                                                                                                                                   |
|:-----------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------:|:--------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------:|
|                                                                                                                                          `$PLUGIN_DIR/tests/unit`                                                                                                                                           |                                                                                                                                                                                                                                                          `$PLUGIN_DIR/tests/integration`                                                                                                                                                                                                                                                           |
|                                                                                                                                       extend `WP_UnitTestCase` class                                                                                                                                        |                                                                                                                                                                                                                                                          extend `Plugin_Test_Case` class                                                                                                                                                                                                                                                           |
| Typically used to test isolated parts of the code that are not integrated with other services, repositories etc. These are usually some **helper classes, functions** etc. They extend the `WP_UnitTestCase` class that gives the access to assertion functions, fixtures etc. (more in Useful Links above) | In this context, tests that test the operability of features as a whole. In plugin, this would be all tests with programming logic that talks to databases and other third-party services, API endpoint tests, creation of permanent objects etc. Everything that will confirm us that our bigger feature is working even when smaller parts of the feature are refactored (not always the case). They extend the `Plugin_Test_Case` class that wraps`WP_UnitTestCase` with common logic (like activating the plugin and creating database tables) |

### Manual
Testing manually while developing and before production is also a must-do because we still can't cover 100% of project 
with tests, and it's not possible to test every case. This is usually the case for presentation side of the project (styling).

#### Mobile
Project can be run so that everything is proxied to one port that we can then access from our mobile devices to test it 
in real environment.
- navigate to `$PROJECT_DIR/wp-content/themes/ew-theme` and start webpack server with `yarn start`
- in the same folder run `yarn start-mobile` that starts Browsersync package
- command will show you the **External** address that you can access from your mobile phone

#### Desktop
This is usually done while developing. It is often them case that developers open their websites in different browsers to check 
if everything looks the same based on different browser engines.
> 🔔 You can check which features work on which browsers (and their versions) on [CanIUse website](https://caniuse.com/).

## ⚠ License
Project is licensed under [GNU Public License, **GPL v2 (or later)**](license.txt). This is a [requirement by WordPress](https://wordpress.org/about/license/) 
because all plugins and themes are considered a derivative work.