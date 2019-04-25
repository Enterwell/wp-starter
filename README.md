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
        - pluginNameForClassNames - unutar fileova postoje klase koje u svom nazivu sadrže ime plugina, a njih nazivamo CamelCaseom, pa ovdje treba unijeti ime u obliku *EnterwellPlugin*
        - pluginNameForFunctions - postoje i php funckije koje u svom nazivu sadrže ime plugina (npr. *activate_enterwell_plugin*), a kako php funkcijama u nazivu riječi odvajamo underscoreom, ovdje treba zapisati varijantu imena u obliku *enterwell_plugin*
        - abstractControllerFileName - da bi WP REST API controller (*AController.php*) funkcionirao kako je zamišljeno, treba napraviti apstraktnu klasu koja ovisi o samom pluginu. Ovdje uređujemo ime filea koji sadrži tu klasu. Ime je oblika *class-a[imeplugina]-controller.php*. (inicijalno *class-aewstarter-controller.php*)
        - baseRoute - (objašnjenje uz primjer projekta s host nameom *new-project.local*) osnovna ruta za API za naš primjer je *new-project.local/wp-json/wp-np/v1/*. U ovoj varijabli uređujemo *wp-np* dio stringa. On treba biti kratak, tako da je dobar izbor za ovu vrijednost ‘wp-’ + neki akronim imena projekta (primjerice, New project - *wp-np*). Dio ‘wp-’ ne mijenjamo.
        - themeNameForFileNames - odabir imena teme u obliku u kojemu su riječi odvojene minusom (inicijalno *enterwell-theme*)
        - webAppServerAddress - URL na kojem će se posluživati naša aplikacija (odgovara host nameu koji smo unijeli u hosts i vhosts u 4. koraku)
    2. U terminalu se pozicionirati unutar root foldera i pokrenuti naredbu `bash init.sh`
- U browseru otići na *localhost* i tamo proći kroz WordPress instalaciju: bit će potrebno unijeti ime baze koja će biti korištena, postaviti username (root) i password (‘’). Ostale podatke ostaviti kako jesu. Na sljedećem koraku unijeti podatke o stranici (mogu se naknadno mijenjati) te o svom useru (username, mail, password - na lokalnoj bazi slobodno staviti weak password).
- Logirati se u WordPress s postavljenim usernameom i passwordom. Po defaultu će biti postavljena tema *twentynineteen* koja ne postoji u našem starteru pa u wp-adminu treba na appearance->themes izabrati odgovarajuću temu.
- Napraviti sljedeću zamjenu:

    U *wp-config.php* liniju:


        define( 'WP_DEBUG', false );



    zamijeniti sa sljedećim linijama:

        define( 'EW_DEV', true );
        define( 'WP_DEBUG', true );
        define( 'WP_DEBUG_DISPLAY', true );
        define( 'WP_DEBUG_LOG', true );


- Provjeriti je li sve OK:
    - Napraviti `yarn install` u temi
    - Pokrenuti `yarn start` naredbu u temi
    - U wp-adminu upaliti plugin

    Ako nema errora, unutar root foldera pokrenuti naredbu `bash cleanup.sh` koja će očistiti projekt od fileova koji su nam bili potrebni za renaming i nakon toga više nemaju svrhu u projektu.
- Proći kroz [fileove koji se trebaju obrisati](https://enterwell.visualstudio.com/WordPress%20starter/_git/ew-wp-starter?path=%2Ffiles-to-delete.txt&version=GBdev.readme). Ako niste sigurni hoće li vam određeni file trebati u razvoju, slobodno ga ostavite pa na kraju projekta još jednom prođite kroz fileove i izbrišite viškove tad kad znate da vam nisu od koristi.
- Početi s programiranjem :)
