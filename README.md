<h1 align="center">
  <p>Enterwell WP starter</p>
  <div>
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
</h1>

<div align="center">

![GitHub last commit](https://img.shields.io/github/last-commit/Enterwell/wp-starter?label=Last%20commit)
[![GitHub issues](https://img.shields.io/github/issues/Enterwell/wp-starter?color=0088ff)](https://github.com/Enterwell/wp-starter/issues)
[![GitHub contributors](https://img.shields.io/github/contributors/Enterwell/wp-starter)](https://github.com/Enterwell/wp-starter/graphs/contributors)
[![GitHub pull requests](https://img.shields.io/github/issues-pr/Enterwell/wp-starter?color=0088ff)](https://github.com/Enterwell/wp-starter/pulls)

</div>

# POKRETANJE NOVOG PROJEKTA

- U *xampp/htdocs* folderu klonirati prazan repozitorij projekta koji pokrećete.
- Unutar kloniranog foldera klonirati [ew-wp-starter](https://enterwell.visualstudio.com/WordPress%20starter/_git/ew-wp-starter).
- Prebaciti sve fileove osim *.git* foldera iz ew-wp-startera u root folder novog projekta i izbrisati ew-wp-starter (sada prazni) folder.
- Dodati projekt u *vhosts* i *hosts* fileove. Pretpostavimo da se projekt zove *New project*, da želimo da host name bude *new-project.local* i da se projekt nalazi u folderu *C:\xampp\htdocs\new-project*. Tada je potrebno:

    U *C:\Windows\System32\drivers\etc\hosts* dodati liniju    

        127.0.0.1	new-project.local

    U *C:\xampp\apache\conf\extra\httpd-vhosts.conf* dodati sljedeći dio koda:

        <VirtualHost *:80>                            
            DocumentRoot "C:\xampp\htdocs\new-project"                
            ServerName new-project.local                          
            <Directory "C:\xampp\htdocs\new-project">          
                Order allow,deny    
                Allow from all 
            </Directory>
        </VirtualHost>
        
    **Napomena**: Ako ste prije ovog koraka imali pokrenut Apache, restartajte ga kako bi uvažio promjene koje su napravljene.

- Pokrenuti rename plugina i teme uz pomoć sljedećih koraka:
    1. U fileu *init/config.js* postaviti vrijednosti koje odgovaraju trenutnom projektu. Vrijednosti koje su defaultno postavljene su vrijednosti koje su trenutno aktivne u kôdu startera i treba slijediti njihov naming (npr. ako je postavljena vrijednost nazvana camelCaseom, nazovimo tako i novu vrijednost). Slijedi pregled config vrijednosti:
        - namespace - na vrhu php fileova često navodimo namespace koji nam osigurava da se naše varijable ne poklapaju s varijablama nekog drugog projekta (npr. stvaramo li klasu *Event*, ona vrlo vjerojatno već postoji u nekom drugom projektu - ali za klasu *EwStarter\Event* rizik preklapanja je zanemariv)
        - pluginNameForFileNames - unutar plugina postoji više fileova koji u svom imenu sadrže ime plugina, a pošto fileove imenujemo stavljajući minus između riječi, ovdje treba unijeti ime plugina u obliku *enterwell-plugin*
        - pluginNameForFunctions - postoje i php funkcije koje u svom nazivu sadrže ime plugina (npr. *activate_enterwell_plugin*), a kako php funkcijama u nazivu riječi odvajamo underscoreom, ovdje treba zapisati varijantu imena u obliku *enterwell_plugin*
        - baseRoute - (objašnjenje uz primjer projekta s host nameom *new-project.local*) osnovna ruta za API za naš primjer je *new-project.local/wp-json/wp-np/v1/*. U ovoj varijabli uređujemo *wp-np* dio stringa. On treba biti kratak, tako da je dobar izbor za ovu vrijednost ‘wp-’ + neki akronim imena projekta (primjerice, New project - *wp-np*). Dio ‘wp-’ ne mijenjamo.
        - themeNameForFileNames - odabir imena teme u obliku u kojemu su riječi odvojene minusom (inicijalno *enterwell-theme*)
        - webAppServerDomain - domena na kojoj će se posluživati naša aplikacija (odgovara host nameu koji smo unijeli u hosts i vhosts u 4. koraku)
        - artifactName - naziv artifacta unutar azure-pipelines.yml, ukoliko naziv ima više riječi postaviti ime u camelCase obliku (npr. *ewStarter*)
    2. Pokrenuti `yarn install` u root folderu da se instaliraju dependency-i potrebni za starter
    3. Pokrenuti starter skriptu koja će iz startera i konfiguracije napraviti strukturu projekta `yarn init-project`
        
- Provjeriti je li sve OK:
    - Napraviti `yarn install` u temi
    - Pokrenuti `yarn start` naredbu u temi koja će pokrenuti webpack server
- U browseru otići na *new-project.local* i tamo proći kroz WordPress instalaciju: bit će potrebno unijeti ime baze koja će biti korištena, postaviti username (root) i password (‘’). Ostale podatke ostaviti kako jesu. Na sljedećem koraku unijeti podatke o stranici (mogu se naknadno mijenjati) te o svom useru (username, mail, password - na lokalnoj bazi slobodno staviti weak password).
- Napraviti sljedeću zamjenu:

    U *wp-config.php* liniju:

        define( 'WP_DEBUG', false );

    zamijeniti sa sljedećim linijama:

        define( 'EW_DEV', true );
        define( 'WP_DEBUG', true );
        define( 'WP_DEBUG_DISPLAY', true );
        define( 'WP_DEBUG_LOG', true );
- Logirati se u WordPress s postavljenim usernameom i passwordom. Po defaultu će biti postavljena tema *twentynineteen* koja ne postoji u našem starteru pa u wp-adminu treba na appearance->themes izabrati odgovarajuću temu.
- Također, u wp-adminu upaliti plugin

Ako nema errora, unutar root foldera pokrenuti naredbu `bash cleanup.sh` koja će očistiti projekt od fileova koji su nam bili potrebni za renaming i nakon toga više nemaju svrhu u projektu.
- Proći kroz [fileove koji se trebaju obrisati](https://enterwell.visualstudio.com/WordPress%20starter/_git/ew-wp-starter?path=%2Ffiles-to-delete.txt&version=GBdev.readme). Ako niste sigurni hoće li vam određeni file trebati u razvoju, slobodno ga ostavite pa na kraju projekta još jednom prođite kroz fileove i izbrišite viškove tad kad znate da vam nisu od koristi.
- Početi s programiranjem :)

## Testing
Due to using a couple of technologies together in this starter project, they are tested in somewhat different ways. 
We'll explain each one of them: which technologies are tests ran on, how are they written and how to run them.

### PHP
All server-based programming logic is usually written in PHP in a custom plugin developed for each project. That's why 
the testing for this is prepared on a plugin basis in plugin folder. There is some logic written in PHP in theme as well 
but theme typically calls plugin methods and functions, so we don't often test these (which we should).

PHP code is tested with [PHPUnit](https://phpunit.de/) and based on practises pushed by WordPress. It is based on the 
same thing but WordPress added its bits and pieces to make it "better".

**Useful links**
- [PHPUnit documentation](https://phpunit.readthedocs.io/en/9.6/index.html)
- [Wordpress PHPUnit overview](https://make.wordpress.org/core/handbook/testing/automated-testing/writing-phpunit-tests/)
- [Premise on which PHPUnit is set up in this starter](https://make.wordpress.org/cli/handbook/misc/plugin-unit-tests/)

#### Setup
To start testing your code with PHPUnit, we need an empty WordPress installation and an empty database. Here are some 
steps to get you to that point:
- [Requirements](https://make.wordpress.org/cli/handbook/misc/plugin-unit-tests/#running-tests-locally): Linux environment, svn package, git package (and php of course)
- navigate to `PROJECT_DIR/wp-content/plugins/ewplugin` and run `composer install` to install PHPUnit
- in the same folder run `bash bin/install-wp-tests.sh wp_wp-starter-test root '' localhost latest`
  - this will install you a fresh WordPress installation to `PLUGIN_DIR/tests/tmp` and WordPress testing tools. It also 
    creates a database based on parameter sent to the command above
    - wp_wp-starter-test is the name of the test database (all data will be deleted!)
    - root is the MySQL username
    - '' is the MySQL user password
    - localhost is the MySQL server host
    - latest is the WordPress version; could also be 3.7, 3.6.2 etc.
- run tests with `./vendor/bin/phpunit` in PLUGIN_DIR

When environment is set up once for a project, every next test run is triggered by calling `./vendor/bin/phpunit`.  
Tests need to be named as `test-` and have to be saved as a `.php` file.

#### Unit
Unit tests are written in the `PLUGIN_DIR/tests/unit` folder. Unit tests are typically used to test isolated parts of 
the code that are not integrated with other services, repositories etc. These are usually some helper classes, functions etc. 
They extend the `WP_UnitTestCase` class that gives the access to assertion functions, fixtures etc. (more in Useful Links above)

#### Integration
Integration tests in this context are tests that test the operability of features as a whole, placed in `PLUGIN_DIR/tests/integration`. 
In plugin, this would be all tests with programming logic that talks to databases and other third-party services, API 
endpoint tests, creation of permanent objects etc. Everything that will confirm us that our bigger feature is working even 
when smaller parts of the feature are refactored (not always the case). They extend the `Plugin_Test_Case` class that wraps 
`WP_UnitTestCase` with common logic (like activating the plugin and creating database tables).

### Manual
Testing manually while developing and before production is also a must-do because we still can't cover 100% of project 
with tests, and it's not possible to test every case.

#### Mobile
Project can be run so that everything is proxied to one port that we can then access from our mobile devices to test it 
in real environment.
- navigate to `PROJECT_DIR/wp-content/themes/ew-theme` and start webpack server with `yarn start`
- in the same folder run `yarn start-mobile` that starts Browsersync package
- command will show you the **External** address that you can access from your mobile phone