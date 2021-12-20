#FILEOVI KOJE TREBA DELETEATI
##PLUGIN
- *admin/views/events/event-dates-meta-box.php*
- *classes/class-event.php*
- *classes/class-location.php*
- *controllers/class-location-controller.php*
- *repositories/class-events-repository.php*
- *repositories/class-location-repository.php*
- *services/class-events-service.php*
- *services/class-locations-service.php*
- Unutar *includes/class-enterwell-plugin.php* sve pozive gore navedenih fileova
- Unutar *class-entewell-plugin-activator.php* dio koji stvara tablice *\$events_table* i *\$locations_table*

##TEMA
- *assets/fonts* - pobrisati folder *Raleway* i njegovo postavljanje u fileu *fonts.scss*
- *assets/images/favicon* - promijeniti ikonu
- *assets/js/pages/home-page/home-page.js* - pobrisati cijelog ili pobrisati samo funkcije i elemente specifične za starter projekt (u slučaju da planirate koristiti taj file)
- *assets/js/services/api-service.js* - pobrisati ako nije potreban (ako ne poslužujete podatke preko API-ja)
- *assets/styles/* - pobrisati sve stilove koji se ne koriste
- *view-models/* - pobrisati sve view modele koji se ne koriste (ostaviti *MenuViewModel.php* i *BaseViewModel.php*)
- *views/pages* - pobrisati sve pageve koji se ne koriste
- *404.php*, *archive.php*, *home.php*, *page.php*, *search.php*, *single.php* - pobrisati pageve koji se ne koriste