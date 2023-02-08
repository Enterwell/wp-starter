# Gutenberg

Dio startera zaslužan za razvoj administrativnog dijela stranice, dijela preko kojeg unosimo content na WP stranice.
Zamišljen da kreira i strukturira sadržaj uz pomoć gotovih blokova, patterna i template-a. Budući da nam često ne 
odgovaraju već gotovi native blokovi za naše potrebe...

... tu nastupamo mi sa Gutenberg helperima objašnjenim u nastavku za olakšanje ovog development procesa.

###### Brze naredbe
Kreiraj novi block:  
```
yarn add-block ime-blocka
```

Kreiraj novu komponentu:  
```
yarn add-component ime-komponente
```

Kreiraj novi format:  
```
yarn add-format ime-formata
```  

###### Brzi linkovi
[Gutenberg Git source](https://github.com/WordPress/gutenberg)  
[Gutenberg demo](https://wordpress.org/gutenberg/)  
[Gutenberg dokumentacija](https://developer.wordpress.org/block-editor/)  
[Gutenberg block atributi](https://developer.wordpress.org/block-editor/reference-guides/block-api/block-attributes/)  
[Gutenberg block manifest](https://developer.wordpress.org/block-editor/reference-guides/block-api/block-metadata/)  
[Gutenberg block template](https://developer.wordpress.org/block-editor/reference-guides/block-api/block-templates/)  
[Gutenberg richtext komponenta](https://developer.wordpress.org/block-editor/reference-guides/richtext/)  
[Gutenberg core komponente](https://developer.wordpress.org/block-editor/reference-guides/components/)  
[Gutenberg libraries](https://developer.wordpress.org/block-editor/reference-guides/packages/)  
[Gutenberg data sources](https://developer.wordpress.org/block-editor/reference-guides/data/)  
[Gutenberg komponente unofficial](https://wp-gb.com/)  
[Dashicons](https://developer.wordpress.org/resource/dashicons)

## Table of contents

- [Gutenberg](#gutenberg)
  - [1. Osnovna ideja](#1-osnovna-ideja)
  - [2. Način korištenja](#2-nain-koritenja)
  - [3. Struktura](#3-struktura)
    - [3.1. Gutenberg manifest.json](#31-gutenberg-manifestjson)
    - [3.2. Blokovi (blocks folder)](#32-blokovi-blocks-folder)
      - [3.2.1. Datoteka *manifest.json*](#321-datoteka-manifestjson)
      - [3.2.2. Folder *admin*](#322-folder-admin)
        - [Block SCSS module](#block-scss-module)
        - [Block JS skripta](#block-js-skripta)
        - [Block -editor partial](#block--editor-partial)
        - [Block -toolbar partial](#block--toolbar-partial)
        - [Block -options partial](#block--options-partial)
        - [Dobre prakse](#dobre-prakse)
      - [3.2.3. Folder public](#323-folder-public)
        - [BLock twig](#block-twig)
        - [Block SCSS](#block-scss)
        - [Block JS skripta (public)](#block-js-skripta-public)
      - [3.2.4. Datoteka block-styles.scss](#324-datoteka-block-stylesscss)
    - [3.3. Komponente (components folder)](#33-komponente-components-folder)
      - [3.3.1. Datoteka *manifest.json*](#331-datoteka-manifestjson)
      - [3.3.2. Folder admin](#332-folder-admin)
        - [Component SCSS module](#component-scss-module)
        - [Component skripta](#component-skripta)
        - [Component -options i -toolbar skripte](#component--options-i--toolbar-skripte)
      - [3.3.3. Folder public](#333-folder-public)
        - [Component twig](#component-twig)
      - [3.3.4. Datoteka component-styles.scss](#334-datoteka-component-stylesscss)
    - [3.4. Format types (format-types folder)](#34-format-types-format-types-folder)
      - [3.4.1. Format JS skripta](#341-format-js-skripta)
      - [3.4.2. Format SCSS module](#342-format-scss-module)
      - [3.4.3. Format SCSS](#343-format-scss)
      - [3.4.4. Datoteka format-type-styles.scss](#344-datoteka-format-type-stylesscss)

## 1. Osnovna ideja

Ideja Gutenberga je da razvijamo stranice na način da unosimo Gutenberg blokove sa sadržajem kako bi ih mogli 
dinamički mijenjati bez potrebe za developmentom. Svaki uneseni blok će imati svoj izgled na FE strani, definiran 
dizajnom. Baziran je na React-u, tako da mnogo funkcionalnosti i mogućnosti React-a rade i u Gutenbergu.

Kako se mi i dalje bavimo developmentom i radimo custom stranice po potrebama i željama klijenata, dizajn core WP 
blokova nam često (nikad) ne odgovara. Također, skoro je nemoguće kreirati strukturu tih blokova kroz administraciju 
onako kako je zamišljena dizajnom.

## 2. Način korištenja

Gutenberg u starteru smo zamislili da koristimo uz pomoć blokova, komponenti i tipova formata za komponente.

**Blokovi** će biti sekcije(ili manji elementi) na našim stranicama koji će biti sačinjene od sadržaja unesenog preko 
komponenata ili drugih blokova.

**Komponente** su gotovi funkcionalni elementi koji će nam omogućiti unos sadržaja u blokove.

**Formati** su vizualne ili programske izmjene na komponentama koje mijenjaju izgled ili ponašanje komponente.

## 3. Struktura

### 3.1. Gutenberg manifest.json

Datoteka u kojoj se nalaze osnovne informacije o Gutenbergu našeg startera.  
U njemu se nalaze sljedeći podaci:
- `projectNamespace`: namespace koji se pridodaje svakom našem Gutenberg blocku i formatu; čini puno ime block-a, npr. 
ew-starter/custom-block
- `blocksCategory`
    - `title`: naziv kategorije po kojoj će se organizirati blockovi iz našeg startera
    - `slug`: jedinstveni tekstualni identifikator za kategoriju

Datoteka je kreirana kao .json kako bi mogli po potrebi u JS skriptama pristupati tim podacima.

### 3.2. Blokovi (blocks folder)

Za kreiranje novog bloka koristimo sljedeću naredbu u *root* folderu teme:

`yarn add-block ime-blocka`

**Napomena!: naziv bloka kod kreiranja mora biti u kebab-case formatu.**

Blokovi se sastoje od točno specificirane strukture koje se potrebno pridržavati kako bi radilo automatsko loadanje 
svih skripti i stilova. Kod generiranja blocka *yarn* naredbom, kreira se njegov folder podijeljen na **admin**, 
**public** i **manifest.json** dio.

#### 3.2.1. Datoteka *manifest.json*

Sadrži bitne informacije potrebne za registriranje Gutenberg bloka. Da ne nabrajamo sad ovdje sve, oni bitni se mogu 
vidjeti u *example-block* u Starteru. Listu svih propertya-a koje funkcija za registraciju prepoznaje moguće je 
vidjeti [ovdje](https://developer.wordpress.org/block-editor/reference-guides/block-api/block-metadata/), a način 
definiranja i deklariranja atributa moguće je vidjeti [ovdje](https://developer.wordpress.org/block-editor/reference-guides/block-api/block-attributes/).

Property vrijedni spomena:
- `components`: ovo je custom property definiran samo za naše potrebe, nije dio službenih podataka nužnih za 
registraciju
    - u njemu definiramo koje smo custom komponente koristili u bloku kako bi block znao koje dodatne atribute 
    registrirati. Naime, kako nebi pisali handlere za sve promjene koje se u komponenti zbivaju i spremali te podatke
     u block ručno, u pozadini se na ovaj način registriraju atributi komponente u atribute bloka (pomnije objašnjeno
      kod komponenata).
    - primjer: 
    ```
    "components": {
        "main": "image",
        "secondary": "image",
        "blockHeading": "heading"
    }
    ```      
    - u primjeru key označava jedinstveni identifikator korištene komponente, a value označava komponentu korištenu u
     bloku
    - **Napomena!** Ovo je potrebno raditi samo za naše custom komponente. Komponente uključene iz Wordpress 
    library-a i 
    dalje handleamo kroz handlere preko callbackova (onChange...)
- `attributes`: lista svih custom atributa u koje spremamo podatke unesene kroz administraciju. Key predstavlja 
njegovo ime, a value je objekt s njegovom deklaracijom ([više o tome ovdje](https://developer.wordpress.org/block-editor/reference-guides/block-api/block-attributes/)).
    - kod registracije blocka se ovdje spremaju i atributi komponenata navedenih iznad u *components* property-u. Npr. 
    ako komponenta image ima definiran `imageUrl` atribut, a heading `text` atribut, u blok će se dodati atributi: 
    `mainImageUrl`, `secondaryImageUrl` i `blockHeadingText` (primjer baziran na prošlom primjeru za *components*)
    
#### 3.2.2. Folder *admin*

U admin folderu se nalaze datoteke potrebne za korištenje u admin/editor dijelu Wordpressa. Za admin dio blocka, 
potrebno je imati .js datoteke koje se ponašaju kao React komponente. Gutenberg je baziran na Reactu, a blockovi su 
obične React komponente.

##### Block SCSS module

U rootu admin foldera se nalazi *.module.scss* datoteka u kojoj se nalaze stilovi koji uređuju izgled blocka u 
Gutenberg editoru. Importa se u .js skriptu blocka i koristi kao modul.

##### Block JS skripta

U rootu admin foldera se također nalazi i glavna JS skripta bloka koje se automatski registrira u Gutenberg editor.

Bitne stvari u ovoj skripti:
- skripta mora obavezno exportati block po default-u (`export default`) kako bi ju loader za blockove znao učitati
- uključuje partiale ovoga blocka i smješta ih u njihova mjesta u Gutenberg Editoru
    - `-editor.js` partial stoji u rootu blocka
    - `-options.js` partial stoji u *InspectorControls* komponenti kako bi se prikazao na desnoj strani Gutenberg 
    editora
    - `-toolbar.js` partial stoji u *BlockControls* komponenti kako bi bio prikazan u Toolbaru blocka
- nikakva ključna logika ne bi se smjela pisati ovdje, sva logika blocka bi trebala stajati u partial skriptama

##### Block -editor partial

Editor partial je ona glavna skripta blocka koja se prikazuje u contentu Gutenberg editora. Cilj je da se u contentu 
prikazuje samo prezentacijski dio blocka, sa što manje logike unosa i izmjene. Za te stvari imamo druge partiale. U 
glavnoj skripti prosljeđujemo sve propse ovdje kako bi partial imao svu mogućnost čitanja i uređivanja atributa blocka.

##### Block -toolbar partial

Toolbar partial je skripta koja će se prikazivat u toolbaru blocka. Cilj je da tu stavljamo akcije koje mijenjaju 
vizualni izgled blocka, ili neke druge akcije koje je smisleno stavljati u toolbar. Više o tome što staviti u toolbar
 se može pročitati [ovdje](https://developer.wordpress.org/block-editor/how-to-guides/block-tutorial/block-controls-toolbar-and-sidebar/).

##### Block -options partial

Options partial je skripta koja se prikazuje u desnoj strani Gutenberg editora kada je block fokusiran. Ovdje treba 
svrstavati što više logike vezane za unos i izmjenu atributa blocka. Također, bitno je da se koriste Wordpress 
PanelBody i PanelRow komponente kako bi sve bilo pregledno i intuitivno.

Ukoliko nam neki od ovih partiala nisu nužni, potrebno ih je samo izbrisati i ne uključiti u glavnoj skripti blocka.  

##### Dobre prakse

Budući da je Gutenberg baziran na Reactu, držat ćemo se zadnjih konvencija koje definira sam React. Jedna ključna 
konvencija je pisanje funkcijskih komponenata, u usporedbi s klasnim komponentama kako su pisane prije.  
Funkcijske komponente podrazumijevaju pisanje komponenata bez upotrebe klasa i nasljeđivanja funkcija, već korištenjem 
funkcija po ES6 standardu i upotrebu hookova. Ovdje neću ići u detalje ovoga, već se više o ovom može saznati [ovdje](https://reactjs.org/docs/hooks-reference.html), 
odnosno primjer jednog WP Gutenberg hooka (useSelect) je moguće vidjeti [ovdje](https://developer.wordpress.org/block-editor/reference-guides/packages/packages-core-data/).


#### 3.2.3. Folder public

Public folder se sastoji od datoteka potrebnih za prikaz blocka na frontendu, dio prezentiran krajnjem korisniku. 
Sastoji se od twig datoteke koja rendera HTML, scss datoteke stilova i js datoteke za potencijalnu JS logiku blocka.

##### BLock twig

Twig datoteku koristimo za vizualni prikaz blocka na frontendu. Ovdje nema ništa specijalno vrijedno spomena. U 
*attributes* varijabli u twigu je moguće pristupiti svim block atributima, a u *innerContent* varijabli se može 
pristupiti raw HTML kodu inner blockova.

##### Block SCSS

Stilovi blocka za frontend dio stranice. Ova datoteka se automatski dodaje u datoteku koja uključuje stilove svih 
blockova (block-styles.scss).

##### Block JS skripta (public)

Skripta za JS logiku na frontendu vezana uz sami block. Ova skripta se dodaje dinamički na stranicu samo kada je 
block dodan na nju, ne dodaje se uvijek i na sve stranice.

#### 3.2.4. Datoteka block-styles.scss

U ovu datoteku se dodaju sve datoteke public stilova blockova. Ako smo kreirali block preko `add-block` naredbe, ovo 
se odvija samo po sebi.

### 3.3. Komponente (components folder)

Za kreiranje nove komponente koristimo sljedeću naredbu u *root* folderu teme:

`yarn add-component ime-komponente`

**Napomena!: naziv komponente kod kreiranja mora biti u kebab-case formatu.**

Komponente se sastoje od točno specificirane strukture koje se potrebno pridržavati kako bi radilo automatsko loadanje 
svih skripti i stilova. Kod generiranja komponente *yarn* naredbom, kreira se njezin folder podijeljen na **admin**, 
**public** i **manifest.json** dio.

#### 3.3.1. Datoteka *manifest.json*

Sadrži informacije o našoj komponenti. Ova datoteka je napravljena samo kako bi pratila strukturu blockova i kako bi 
pristup informacijama bio jednostavniji. Naime, ona se ne koristi za registraciju kao što se koristi kod blockova, 
jer su komponente obične JS React komponente koje se uključuju u blockove.  
Manifest.json datoteku kod komponenata koristimo kako bi definirali atribute koje komponenta koristi, kako bi block 
znao koje atribute uključiti u svoje kod registracije, dodavajući im jedinstveni prefix.

Ovo je ukratko objašnjeno i kod atributa blocka, ali da ponovimo:
- u manifest.json-u blocka definiramo korištene komponente sa jedinstvenim prefixom za tu komponentu (key components 
propertya)
- kod registracije blocka, block prolazi po manifest.json-u tih komponenata i dodaje atribute komponenata u atribute 
blocka dodavajući im prefix. Npr. ako je komponenta *image* imala atribut `imageUrl`, a block joj je definirao prefix
 `main`, tada se blocku dodao atribut `mainImageUrl`.
 
Atribute u komponenti definiramo po istom principu kao i [atribute blocka](https://developer.wordpress.org/block-editor/reference-guides/block-api/block-attributes/).
 
#### 3.3.2. Folder admin
 
Admin folder prati sličnu strukturu kao i kod blockova, samo što nema jedne skripte koja uključuje sve partiale, već 
se oni uključuju u blocku u svakom od partiala pojedinačno.

##### Component SCSS module

Kao i kod blockova, ovo je datoteka stilova koja se uključuje kao modul za dodavanje stilova komponenti. U njoj 
pišemo stilove za sve partiale komponente.

##### Component skripta

JS skripta koja se naziva isto kao i komponenta predstavlja skirptu koja se dodaje u -editor partial blocka u kojem 
se koristi. Mogla bi biti nazvana *component-editor.js* budući da i to jest, ali radi jednostavnosti i intuitivnosti 
korištenja se zove samo *component.js*.

Komponenta kao takva je obična React komponenta koja nužno ne exposea-a handlere za promjenu stanja, već sva svoja 
stanja sprema izravno u atribute blocka koji su joj proslijeđeni.

Kod dodavanja komponente u block potrebno je napraviti sljedeće korake:
- u manifest.json blocka dodati dependency na tu komponentu u *components* property
- u block skripti importati komponentu (`import Component from...`)
- instancirati komponentu `<Component prefix='main' {...props}/>`
    - `prefix` property predstavlja jedinstveni identifikator komponente koji se nadodaje ispred njezinih atributa, a
     prethodno smo ga definirali u manifest.json-u blocka u components property-u
    - `props` su svi property-i blocka proslijeđeni komponenti kako bi ona mogla ažurirati svoje atribute koji su 
    pohranjeni u atributima blocka
    
Bitno je napomenuti kako je potrebno svaku komponentu po defaultu exportati kroz `withAttr` wrapper funkciju. Ova 
funkcija je definirana u helper skripti i ona nam radi mapiranja atributa. Naime, kako se nama atributi komponente ne
 spremaju pod imenom koje je definirano u manifestu komponente, već sa prefixom, *withAttr* funkcija nam napravi ta 
 mapiranja u atribute s prefixom, i iz atributa s prefixom, sama po sebi, kako se mi nebi zamarali s time u našim 
 komponentama.
 
Postoje i komponente koje u sebi sadrže drugi content (vidi *container*). One nisu automatski zatvorene već sadrže 
content koji se pohranjuje u props *children*.
 
##### Component -options i -toolbar skripte

Isti princip kao i kod blockova. Skripte koje daju funkcionalnost komponenti u toolbaru ili desnom izborniku. Također
 moraju biti exportane kroz withAttr funkciju i koriste se po potrebi. Moguće je da komponentama neće trebati ove 
 obje skripte.
 
#### 3.3.3. Folder public

Public folder je također strukturiran po istom principu kao i kod blockova. Sadrži twig i stilsku datoteku.

##### Component twig

Kada koristimo komponentu unutar blocka, ne želimo svaki puta pisat frontend izgled za nju. Tu nam super uskače twig 
sa svojim mogućnostima. Ako znamo da smo u bloku koristili neku komponentu (jer smo to napisali u njegovoj JS 
skripti), istu takvu strukturu možemo pratiti i u twigu blocka.

Kada radimo komponentu, napravimo twig za nju u kojem iščitamo njezine atribute i strukturiramo ih po želji i dizajnu. 
U blocku učitamo komponentu na mjestu kojem želimo prosljeđujući joj njezin prefix i atribute blocka. Npr. `{% 
include 'gutenberg/components/image/public/image.twig' with {prefix: 'main', attributes: attributes} %}`.  
Kako su i dalje atributi komponenata pohranjeni u blocku sa prefixom, mi u komponenti želimo raditi s pravim imenima 
atributa komponente, pa smo napravili jednu twig funkciju koja će nam iščitat te atribute. Na početku svake funkcije 
napišemo sljedeće: `{% set imageUrl = get_attr(prefix, 'imageUrl', attributes) %}`.

Bitno je napomenuti kako neke komponente u sebi imaju djecu, odnosno drugi content. Primjer takve komponente je 
*container* (pogledati u starteru). Ona se uključuje u block sa `{% embed %}`, te se content te komponente dodaje u 
njezin `{% block %}`.

###### Component SCSS

Datoteka sa stilovima za frontend. Dodaje se automatski u component-styles.scss kod kreiranja komponente preko yarn 
naredbe.

#### 3.3.4. Datoteka component-styles.scss

U ovu datoteku se dodaju sve datoteke public stilova komponenti. Ako smo kreirali komponentu preko `add-component` 
naredbe, ovo se odvija samo po sebi.

### 3.4. Format types (format-types folder)

Za kreiranje novog formata koristimo sljedeću naredbu u *root* folderu teme:

`yarn add-format ime-formata`

**Napomena!: naziv formata kod kreiranja mora biti u kebab-case formatu.**

Format tipovi se sastoje od točno specificirane strukture koje se potrebno pridržavati kako bi radilo automatsko 
loadanje svih skripti i stilova. Kod generiranja formata *yarn* naredbom, kreiraju se 3 datoteke: js datoteka s 
logikom tipa formata, scss datoteka s stilovima za editor i scss datoteka sa stilovima za frontend.

#### 3.4.1. Format JS skripta

JS skripta koja se automatski dodaje u editor, a sadrži logiku za kreiranje novog tipa formata.

Tipovi formata (format types), u nastavku **formati**, služe za dodjeljivanje vizualnih, ali i funkcionalnih značajki
 richtext blockovima i komponentama. Format dodaje button(našu React komponentu) u toolbar komponente koji 
 klikom dodaje tu funkcionalnost označenom dijelu richtexta. Nećemo ovo objašnjavati u detalje jer je logika vrlo 
 jednostavna (vidi primjer *uppercase* formata u starteru).
 
Svaki format mora imati na dnu poziv registerFormatType funkcije kojom dodjeljuje našu napisanu React componentu u 
*edit* parametru, i definira uz pomoć drugih atributa kako će se format ponašati. Npr. uppercase format dodaje span 
element oko označenog dijela texta i spanu dodaje klasu `gf-uppercase`.

Više o tome u [dokumentaciji](https://developer.wordpress.org/block-editor/how-to-guides/format-api/).

#### 3.4.2. Format SCSS module

Stilovi dodani kao modul u komponentu. Budući da klasu na format dodajemo izravno kao string, kako se ona nebi 
obradila kroz webpack dodavajući joj jedinstveni identifikator, tu klasu dodamo u `:global` wrapper unutar datoteke.

#### 3.4.3. Format SCSS

Stilovi za frontend ovog formata. Sadrže stilove za formate koje smo definirali kako bi bili vidljivi i na frontend 
dijelu stranice. Automatski se dodaje u format-type-styles.scss datoteku.

#### 3.4.4. Datoteka format-type-styles.scss

Uključuje sve public stilove formata. U nju se automatski dodaju datoteke formata kod kreiranja formata sa 
`add-format` yarn naredbom.