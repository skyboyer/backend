Test exercise for PHP Developer position.

You can launch the project with 'docker-compose up' command. Then open http://localhost/index.php/<route_path>

Used: DB - SQLite, SELECT2 with AJAX, bootstrap-datepicker.


Pages:

/localhost/index.php/product - list and filter of products with links to adding/editing products.

/localhost/index.php/person - list and filter of users with links to adding/editing persons.

/localhost/index.php/person/like/product -  filter with <select> fields (used Select2-ajax plugin)  for user's preferencies/product's lovers with links to adding/remowing user's likes or links to adding/removing product's lovers
  
  
  --------------------------------




Initial task: 


Na podstawie diagramu EER (plik schema.mwb), należy wykonać aplikację z funkcjami:

Część 1 - moduł “user”

•	zarządzanie danymi użytkowników,

•	lista użytkowników z filtrami,

•	edycja użytkownika,

•	nowy użytkownik,


Część 2 - moduł “product”

•	zarządzanie danymi produktów,

•	lista produktów z filtrami,

• edycja produktu,

•	nowy produkt,


Część 3 - moduł “like”

•	zarządzanie danymi „użytkownik lubi produkt”,

•	lista „użytkownik lubi produkt” (z 2ma polami filtrów),

•	edycja „użytkownik lubi produkt” (uwaga na duplicate key!),

•	nowy „użytkownik lubi produkt”,


Część 4 - problem dużej liczby danych

Jak rozwiążesz problem wyboru użytkownika i produktu w module „like” gdy baza zawiera 100k użytkowników lub/i 100k produktów. Taka liczba opcji w polach typu select jest niedopuszczalna! Zaproponuj inne rozwiązanie.


Wymagania

•	użytkownicy posiadają 3 stany: 1=aktywny, 2=banned, 3=usunięty,

•	usunięcie użytkownika nie powoduje usunięcia jego wpisu w bazie danych, a jedynie ustawienie jego statusu na 3=usunięty,

•	usunięcie produktu fizycznie usuwa produkt z bazy danych oraz jego powiązania w innych tabelach,

•	wszystkie dane mogą być edytowane,

•	żadna operacja nie powinna doprowadzić do błędu aplikacji,

•	na formularzu użytkownika i produktu nie powinno być możliwości wybrania „użytkownik lubi produkt” – ta funkcja powinna być dostępna na formularzu w module „like”.


Wymagania techniczne

•	aplikacja powinna być oparta na Symfony 4 lub 5,

•	NIE można korzystać z bundli generatorów np. SonataAdmin, EasyAdmin, itp.

•	należy użyć ORM Doctrine,

•	aplikacja powinna nazywać się „backend”,

•	aplikacja powinna posiadać dokładnie 3 moduły: person, like, product.

•	proszę nie zmieniać diagramu bazy danych,

•	diagram EER jest zapisany w formacie MySQL Workbench – program do pobrania ze strony: https://dev.mysql.com/downloads/workbench/,

•	do edycji pola „public_date” w modelu „Product” należy użyć widgeta z kalendarzem,

•	pole „state” w modelu Person w formularzu i filtrze powinno wykorzystywać selectbox z nazwami stanów,

•	pole “state” na liście Użytkowników powinno występować w pełnej nazwie,

•	stany “state” powinny być zdefiniowane jako stałe, zaproponuj gdzie,

•	interfejs graficzny powinien zawierać linki do poszczególnych modułów,

•	wymagana jest podstawowa dokumentacja kodu.
