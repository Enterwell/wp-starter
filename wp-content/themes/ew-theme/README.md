# EW Theme 
U ovom fileu se nalazi kratki opis s uputama za development ew teme.

## Struktura foldera
- `.scripts` - pomoćne skripte koje olakšavaju development (skripta za dodavanje novog gutenberg bloka recimo)
- `assets`
	- `dist` - build javascripta i css-a
	- `fonts` - folder za fonteove, fontove držimo lokalno, ne dohvaćamo ih sa google fontsova
	- `gutenberg` - folder u kom se nalazi sve za gutenberg blokove koje koristimo
	- `images` - folder za sve slike (i ikone) projekta. Ako imamo puno slika onda ih organiziramo u foldere.
	- `js` - folder za sav JS u temu (bilo obični bilo react)
	- `styles` - folder za SCSS stilove teme
- `classes` - pomoćne klase teme (zadužene za load stilove i skripti, load gutenberga, dodavanje ew twig extensiona itd.)
- `gulp` - konfiguracija gulp-a
- `vendor` - twig i ostali php dependency-i projekta
- `view-models` - svaki view koji prikazuje neke podatke koji su spremljeni na backendu ima svoj view model. Zadaća view modela je da 
pripremi sve podatke koje trebamo za ispis. Svi view modeli i njihovi factory-i (ako su potrebni) se nalaze u ovom folderu i automatski
se includeaju.
- `views` - svi viewovi teme
	- `components` - komponente viewova. Komponenta je twig template koji koristimo na minimalno dvije različite stranice.
	- `pages` - viewovi za stranice 
