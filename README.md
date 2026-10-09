# SmartRecipe Hub

Nicolas Stroška

5ZYI33

## Stručný opis projektu
Pripravovaná webová aplikácia SmartRecipe Hub rieši problém neefektívneho plánovania stravovania, vyhadzovania potravín a zdĺhavého vymýšľania denného jedálnička. Aplikácia umožňuje používateľom jednoducho spravovať recepty, organizovať si týždenný jedálniček a automaticky generovať nákupné zoznamy na základe vybraných jedál.

## Role v projekte
Neregistrovaný používateľ (Guest):
Má prístup k verejnej časti aplikácie. Môže si prezerať katalóg receptov, vyžadovať detaily jedál a filtrovať recepty podľa kategórií či ingrediencií. Nemá právo upravovať obsah ani plánovať jedlá.

Registrovaný používateľ (User):
Zodpovedá za svoj vlastný profil a ním vytvorený obsah. Má právo vytvárať, upravovať a mazať vlastné recepty (vrátane nahrávania fotografií), plánovať si osobný týždenný jedálniček a generovať nákupný zoznam.

Správca (Admin):
Dohliada na chod celého systému a nezávadnosť obsahu. Má neobmedzené práva na správu všetkých receptov, kategórií, ingrediencií a používateľských účtov.

## Prípady použitia podľa rolí
Guest:
- Návštevník vyhľadá recept podľa názvu alebo ingrediencií
- Návštevník si zobrazí detail receptu (s ingredienciami, postupom a nutričnými hodnotami)
- Návštevník sa zaregistruje alebo prihlási do systému

User:
- Používateľ si vytvorí nový recept a nahrá k nemu sprievodnú fotografiu
- Používateľ si upraví alebo vymaže svoj existujúci recept
- Používateľ si pridá recept do svojho týždenného plánovača na konkrétny deň a chod (raňajky, obed, večera)
- Používateľ si odoberie jedlo z plánovača
- Používateľ si vygeneruje nákupný zoznam na základe naplánovaných jedál

Admin:
- Správca vytvorí, upraví alebo vymaže kategóriu receptov (napr. Raňajky, Dezert, Fit)
- Správca spravuje globálny zoznam ingrediencií a ich merateľných jednotiek
- Správca upraví alebo odstráni nevhodný recept ktoréhokoľvek používateľa
- Správca spravuje používateľské účty a ich roly

## Plánované entity
Používateľ (users):
- Účel: Reprezentuje registrované osoby v systéme.
- Atribúty: id, username, email, password, role, created_at

Recept (recipes):
- Účel: Ukladá základné informácie o konkrétnom recepte.
- Atribúty: id, user_id, category_id, title, description, instructions,prep_time_min, image_path, created_at

Ingrediencia (ingredients):
- Účel: Katalóg jednotlivých potravín a surovín.
- Atribúty: id, name, unit (g, ml, ks), calories_per_unit

Ingrediencia receptu (recipe_ingredients):
- Účel: Spájacia entita určujúca, koľko z danej ingrediencie ide do konkrétneho receptu.
- Atribúty: recipe_id, ingredient_id, amount

Kategória (categories):
- Účel: Zaraďovanie receptov do prehľadných skupín.
- Atribúty: id, name

Plánovač jedál (meal_plans):
- Účel: Ukladá naplánované recepty pre daného používateľa v konkrétny deň a čas
- Atribúty: id, user_id, recipe_id, day_of_week, meal_type (raňajky, obed, večera)

## Vzťahy medzi entitami
Používateľ – Recept (1:N): Jeden používateľ môže vytvoriť viacero receptov, každý recept patrí práve jednému autorovi

Kategória – Recept (1:N): Jedna kategória obsahuje viacero receptov, každý recept patrí do jednej kategórie

Recept – Ingrediencia (M:N): Recept obsahuje viacero ingrediencií a ingrediencia sa môže nachádzať vo viacerých receptoch (dekomponované pomocou asociačnej tabuľky recipe_ingredients)

Používateľ – Plánovač jedál (1:N): Používateľ má priradených viacero záznamov v týždennom plánovači

Recept – Plánovač jedál (1:N): Jeden recept môže byť v plánovači zaradený viackrát (v rôznych dňoch či u rôznych používateľov)

## Hlavné stránky aplikácie
Domovská stránka a katalóg receptov (/recipes):
Prehľadný zoznam/mriežka receptov s vy vyhľadávaním, dynamickým filtrovaním podľa kategórií a časového limitu.

Detail receptu (/recipe/detail):
Zobrazenie kompletnej fotografie receptu, zoznamu ingrediencií s vypočítanými kalóriami, postupu varenia a tlačidla na rýchle pridanie do týždenného plánu.

Moje recepty a Pridanie/Úprava receptu (/recipe/create, /recipe/edit):
Formulár na vkladanie a úpravu receptu s dynamickým pridávaním ingrediencií (AJAX) a uploadom obrázka.

Týždenný plánovač jedálnička (/meal-planner):
Interaktívna tabuľka/mriežka dní v týždni (Pondelok až Nedeľa) rozdelená na raňajky, obedy a večere.

Nákupný zoznam (/shopping-list):
Stránka s vygenerovaným zhrnutím všetkých potrebných surovín na základe zvolených jedál v týždennom plánovači.

Administračná zóna (/admin/categories):
Správa kategórií a globálnych ingrediencií určená výhradne pre administrátora.

## Rozdelenie funkcionality
Základná funkcionalita:
- Registrácia a prihlasovanie používateľov s overovaním rolí (ADMIN, USER)
- Plný CRUD nad entitou Recepty (vytvorenie, zobrazenie, úprava, zmazanie)
- Nahratie a správa obsluhového obrázka k receptu na servery
- Dynamické pridávanie/odoberanie ingrediencií vo formulári
- Týždenný plánovač jedál pre prihláseného používateľa
- Responzívny dizajn pre mobilné aj desktopové zariadenia

Rozširujúca funkcionalita:
- Automatické vygenerovanie a export nákupného zoznamu (napr. do PDF alebo TXT)
- Napojenie na externé API pre automatické doťahovanie nutričných hodnôt surovín
- Úprava množstva ingrediencií priamo v tabuľke bez opätovného načítania stránky
- Pokročilá klientska validácia formulárov využívajúca JavaScript framework