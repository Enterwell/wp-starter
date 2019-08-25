# Deployer configuration manual

Ovo je mali manual koji objašnjava kako postaviti deployer kako bi jednom elegantnom naredbom
iz konzole mogli deployati naš super kul site.

### Deployer files

Lista fileova koje deployer koristi:

    -  dep.bat                            
    -  deploy.php                         
    -  deployer                           
        - class-deployer-config.php       
        - deployer-config-public.json     
        - deployer-config-private.json
        - deployer-config-private-sample.json
        
Fileove koje deployer koristi možemo podijeliti u helpere i konfiguracijske fileove. Detaljnije o njima ispod:  
        
- **HELPERI** - ovo su fileovi koje generalno ne diramo (osim ako nemamo ideju kako ih poboljšati da ne budu ovako ružni), oni nam služe kako bi si olakšali korištenje deployera. To su:
    - `dep.bat` - Windows .bat file koji nam omogućuje da deployer koristimo naredbom `dep` iz komandne linije
    - `dep` - Bash file, omogućuje da se deployer pozove iz bash-a (za Linux uređanje)
    - `deploy.php` - file u kom se nalazi osnovna konfiguracija za deployer, ovdje možemo vidjeti koji se taskovi pozivaju, kojim redoslijedom, file u kom se konfigurira sam deployer.
    - `deployer/class-deployer-config.php` - helper klasa za manage konfiguracijama (javnom i privatnom)
    - `deployer/deployer-config-private-template.json` - template za stvaranje privatne konfiguracije

- **KONFIGURACIJA** - fileovi koji suže za konfiguriranje deploya za pojedine projekte, ove fileove mijenjamo za svaki projekt, a oni su:
    - `deployer/deployer-config-public.json` - javna konfiguracija projekta, ovdje zapisujemo podatke koji su zajednički svim ljudima koji rade na projektu
    - `deployer/deployer-config-private.json` - privatna konfiguracija projekta, ovdje su zapisani podaci koji su privatni za osobu koja radi projekt, **ovaj file nikad ne smije doći na .git repozitorij (ili igdje na internet)**

### Configuration data

Pregled podataka koji su potrebni za konfiguraciju:

- Projekt
    - `name` - `COMMON` - ime projekta 
- Git
    - `projectUrlWithCredentials` - `PRIVATE` - git adresa repozitorija projekta, skupa s git usernameom korisnika i git passwordom, u obliku 
    `https://{username}:{password}@bitbucket.org/enterwell/{repository-name}.git`
    - `deployBranch` - `COMMON` - git branch s kog se radi deploy, po dogovoru `prod` branch
    - `buildBranch` - `COMMON` - git branch na kom je build verzija softvera (glavni branch), po dogovoru `dev` branch
- SSH
    - `address` - `COMMON` - adresa remote hosta na koji se radi deploy (IP adresa)
    - `username` - `PRIVATE` - username korisnika remote hosta koji radi deploy (SSH username)
    - `privateKeyFilePath` - `PRIVATE` - putanja do privatnog ključa kojim se logira na SSH (korisnik koji deploya mora imati svoj privatni ključ)
- WordPress
    - `themeName` - `COMMON` - ime WordPress teme (koristi se za yarn install i build)
- Shared files - `COMMON` - array fileova koji su isti za sve deploy verzije (`wp-config.php` npr.)
- Shared dirs - `COMMON` - array foldera projekta koji su shared za sve deploy verzije (`wp-content/uploads` najčešće)
- Writable dirs - `COMMON` - array foldera po kojima se može pisati (nakon deploya) (`wp-content/uploads` najčešće)


### Requirements
Da bi ovo sve radilo kako spada moramo napraviti par koraka kako bi sve teklo mnogo glatko (kako teče inače).

##### SSH key
Da bi se deployer znao spojiti na server (bez da pita passworde) potrebno je napraviti SSH login key par bez passworda (mislim).
Uglavnom treba se stvoriti private i public key, public key se uploada na server i doda u `authorized_keys` za usera, a private stoji
na računalu korisnika koji se logira.
Detaljnije o tom na [linku](https://www.digitalocean.com/community/tutorials/how-to-set-up-ssh-keys--2)

##### Git credentials helper
Kako bi cijeli proces prelaska preko brancheva, automatskog mergea i commita ispravno radio potrebno nam je da git također ne pita
nikakve passworde i usernameove. To se jednostavno riješi tako da se u glavni `.gitconfig` file dodaju linije:
  
    [credential]
        helper = store
Ovim postavkama smo gitu rekli da idući put kad mu zatrebaju credentialsi za korisnika prvo pogleda u store, pa ako ih tamo ne nađe
tek onda pita da ih mi unosimo.

Sigurno se pitate pa ok gdje spremamo te podatke. Odgovor je **jednostavan**.

Podaci su spremljeni u fileu `.git-credentials` koji se nalazi u istom folderu kao i `.gitconfig` file. Vjerojatno je da taj file
ne postoji pa se mora stvoriti i u njega se moraju unijeti credentialsi za bitbucket u obliku:

    https://{bitbutcket-username}:{bitbucket-password}@{bitbucket.org}
    GENERALNO: {protokol}://{username}:{password}@{git-host}

Za svaki username (i host) se unosi nova linija sa credentialsima, tako da nam za sad file izgleda ovako:
    
    https://{coolUsername}:{coolPassword}@bitbucket.org 
    
**Što se sigurnosti tiče nije baš najsigurnije, ali za sad je ovako kako je**


##Changelog
Changelog deployera i ove dokumentacije:

- `v1.0` - _2017/11/23, Matej Bošnjak_ - Inicijalni dokument, deployer dodan u starter (samo dodan, ne testiran)
- `v1.1` - _2017/11/24, Matej Bošnjak_ - Dodana `bash` skripta, deployer se može pokrenuti sa `sh dep` ili `bash dep`
- `v1.2` - _2017/11/24, Matej Bošnjak_ - `sudo` dodan kad se radi clear starih releasova, bugfix

##Future plans
Plan je nekad u budućnosti napraviti ovo:

- [ ] Instalirati [Git Credentials Manager for Windows](https://github.com/Microsoft/Git-Credential-Manager-for-Windows) i upogoniti ga, da passwordi ne stoje u plaintextu.
