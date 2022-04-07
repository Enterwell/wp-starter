# Gutenberg

Dio startera zaslužan za razvoj administrativnog dijela stranice, dijela preko kojeg unosimo content na WP stranice.
Zamišljen da kreira i strukturira sadržaj uz pomoć gotovih blokova, patterna i template-a. Budući da nam često ne 
odgovaraju već gotovi native blokovi za naše potrebe...

... tu nastupamo mi sa Gutenberg helperima objašnjenim u nastavku za olakšanje ovog development procesa.

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
konvencija je pisanje funkcijskih komponenata, u usporedbi s objektnim komponentama kako su pisane prije.  
Funkcijske komponente podrazumijevaju pisanje komponenata bez upotrebe klasa i nasljeđivanja funkcija, već korištenjem 
funkcija po ES6 standardu i upotrebu hookova. Ovdje neću ići u detalje ovoga, već se više o ovom može saznati [ovdje](https://reactjs.org/docs/hooks-reference.html), 
odnosno primjer jednog WP Gutenberg hooka (useSelect) je moguće vidjeti [ovdje](https://developer.wordpress.org/block-editor/reference-guides/packages/packages-core-data/).


#### 3.2.3. Folder public