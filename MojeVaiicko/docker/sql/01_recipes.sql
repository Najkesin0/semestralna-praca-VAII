-- SmartRecipe Hub – recepty, ingrediencie a postup prípravy
SET NAMES utf8mb4;

CREATE TABLE IF NOT EXISTS `recipes` (
                                         `id`              INT AUTO_INCREMENT PRIMARY KEY,
                                         `user_id`         INT          NOT NULL,
                                         `title`           VARCHAR(255) NOT NULL,
                                         `summary`         VARCHAR(160) NOT NULL,
                                         `description`     TEXT         NULL,
                                         `category`        VARCHAR(50)  NOT NULL,
                                         `image`           VARCHAR(255) NULL,
                                         `prep_time`       INT          NOT NULL DEFAULT 0,
                                         `cook_time`       INT          NOT NULL DEFAULT 0,
                                         `difficulty`      VARCHAR(20)  NOT NULL DEFAULT 'Jednoduché',
                                         `servings`        TINYINT      NOT NULL DEFAULT 2,
                                         `calories`        INT          NOT NULL,
                                         `protein`         INT          NOT NULL DEFAULT 0,
                                         `carbs`           INT          NOT NULL DEFAULT 0,
                                         `fat`             INT          NOT NULL DEFAULT 0,
                                         `is_vegetarian`   TINYINT(1)   NOT NULL DEFAULT 0,
                                         `is_vegan`        TINYINT(1)   NOT NULL DEFAULT 0,
                                         `is_gluten_free`  TINYINT(1)   NOT NULL DEFAULT 0,
                                         `is_lactose_free` TINYINT(1)   NOT NULL DEFAULT 0,
                                         `tip`             TEXT         NULL,
                                         `created_at`      DATETIME DEFAULT CURRENT_TIMESTAMP,
                                         CONSTRAINT `fk_recipes_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`)
                                             ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE INDEX `idx_recipes_category` ON `recipes` (`category`);

-- Ingrediencie (1:N k receptu). amount = NULL znamená „podľa chuti“.
-- shop_category určuje sekciu v nákupnom zozname: zelenina, chladene, ovocie, spajza, ine
CREATE TABLE IF NOT EXISTS `ingredients` (
                                             `id`            INT AUTO_INCREMENT PRIMARY KEY,
                                             `recipe_id`     INT          NOT NULL,
                                             `position`      INT          NOT NULL DEFAULT 0,
                                             `amount`        DECIMAL(8,2) NULL,
                                             `unit`          VARCHAR(20)  NOT NULL DEFAULT '',
                                             `name`          VARCHAR(120) NOT NULL,
                                             `shop_category` VARCHAR(20)  NOT NULL DEFAULT 'ine',
                                             CONSTRAINT `fk_ingredients_recipe` FOREIGN KEY (`recipe_id`) REFERENCES `recipes` (`id`)
                                                 ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Kroky postupu (1:N k receptu)
CREATE TABLE IF NOT EXISTS `steps` (
                                       `id`          INT AUTO_INCREMENT PRIMARY KEY,
                                       `recipe_id`   INT          NOT NULL,
                                       `position`    INT          NOT NULL DEFAULT 0,
                                       `title`       VARCHAR(120) NOT NULL,
                                       `description` TEXT         NOT NULL,
                                       CONSTRAINT `fk_steps_recipe` FOREIGN KEY (`recipe_id`) REFERENCES `recipes` (`id`)
                                           ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO `recipes`
(`id`, `user_id`, `title`, `summary`, `description`, `category`, `prep_time`, `cook_time`, `difficulty`, `servings`,
 `calories`, `protein`, `carbs`, `fat`, `is_vegetarian`, `is_vegan`, `is_gluten_free`, `is_lactose_free`, `tip`, `created_at`)
VALUES
    (1, 1, 'Cestoviny s paradajkami a špenátom', 'Ľahký obed plný farieb, hotový za pár minút.',
     'Šťavnaté paradajky, jemná mozzarella a čerstvý špenát. Rýchly obed z jednej panvice, ku ktorému sa budeš rád vracať.',
     'Obed', 10, 15, 'Jednoduché', 2, 485, 21, 62, 17, 1, 0, 0, 0,
     'Zvyšný špenát pridaj zajtra do šalátu. Cestoviny uchovaj v uzavretej nádobe v chladničke a spotrebuj do 2 dní.',
     NOW() - INTERVAL 1 DAY),
    (2, 1, 'Ovsená kaša s čučoriedkami', 'Krémový štart dňa s ovocím a jogurtom.',
     'Jemné ovsené vločky uvarené v mlieku, čučoriedky a lyžica gréckeho jogurtu. Raňajky, ktoré zasýtia až do obeda.',
     'Raňajky', 5, 5, 'Jednoduché', 2, 340, 13, 52, 9, 1, 0, 0, 0,
     'Prezretý banán nevyhadzuj – roztlač ho priamo do kaše a ušetríš med.',
     NOW() - INTERVAL 2 DAY),
    (3, 1, 'Pečený losos s citrónom', 'Šťavnatý losos s voňavými bylinkami.',
     'Filet z lososa pečený na plechu s citrónom, cesnakom a bylinkami, podávaný s chrumkavými pečenými zemiakmi.',
     'Večera', 10, 20, 'Jednoduché', 2, 520, 34, 28, 29, 0, 0, 1, 1,
     'Zvyšok lososa rozdrv vidličkou do šalátu alebo na toast – v chladničke vydrží do druhého dňa.',
     NOW() - INTERVAL 3 DAY),
    (4, 1, 'Cícerový šalát s fetou', 'Svieža miska, ktorá príjemne zasýti.',
     'Cícer, chrumkavá uhorka, cherry paradajky a slaná feta s citrónovým dresingom. Hotové bez varenia.',
     'Večera', 15, 0, 'Jednoduché', 2, 390, 17, 38, 19, 1, 0, 1, 0,
     'Šalát vydrží v chladničke do druhého dňa – fetu pridaj až tesne pred jedlom.',
     NOW() - INTERVAL 4 DAY),
    (5, 1, 'Krémová tekvicová polievka', 'Hrejivá klasika z pár jednoduchých surovín.',
     'Zamatová polievka z tekvice hokkaido so smotanou a štipkou muškátového orieška. Presne to pravé na jesenné dni.',
     'Obed', 10, 25, 'Jednoduché', 2, 280, 6, 30, 15, 1, 0, 1, 0,
     'Semienka z tekvice nevyhadzuj – opraž ich nasucho na panvici a posyp nimi polievku.',
     NOW() - INTERVAL 5 DAY),
    (6, 1, 'Avokádový toast', 'Chrumkavý chlieb a dokonale zrelé avokádo.',
     'Kváskový chlieb, roztlačené avokádo s citrónom a čili vločkami, navrch vajíčko namäkko.',
     'Raňajky', 5, 5, 'Jednoduché', 2, 360, 14, 32, 20, 1, 0, 0, 1,
     'Ak ti zostane polovica avokáda, nechaj v nej kôstku a zabaľ ju – nezhnedne tak rýchlo.',
     NOW() - INTERVAL 6 DAY),
    (7, 1, 'Krémové hubové rizoto', 'Poctivé rizoto so šampiňónmi a parmezánom.',
     'Krémové rizoto z ryže arborio so šampiňónmi, maslom a parmezánom. Trochu trpezlivosti pri miešaní sa oplatí.',
     'Obed', 10, 25, 'Stredné', 2, 450, 14, 66, 14, 1, 0, 1, 0,
     'Zvyšné rizoto vyformuj na placky a opeč – vzniknú chrumkavé arancini bez námahy.',
     NOW() - INTERVAL 8 DAY),
    (8, 1, 'Tradičné bryndzové halušky', 'Slovenská klasika s bryndzou a slaninkou.',
     'Mäkké zemiakové halušky premiešané s bryndzou, navrch chrumkavá slaninka a čerstvá pažítka.',
     'Obed', 20, 20, 'Stredné', 2, 680, 24, 78, 30, 0, 0, 0, 0,
     'Zvyšnú bryndzu zmiešaj s maslom a cibuľkou – máš hotovú nátierku na raňajky.',
     NOW() - INTERVAL 10 DAY),
    (9, 1, 'Grécky šalát s fetou a olivami', 'Ľahká večera plná stredomorských chutí.',
     'Paradajky, uhorka, paprika, červená cibuľa, olivy a feta s oreganom a olivovým olejom.',
     'Večera', 15, 0, 'Jednoduché', 2, 290, 10, 14, 22, 1, 0, 1, 0,
     'Šťavu, ktorá zostane na dne misky, vytri kúskom chleba – je najlepšia.',
     NOW() - INTERVAL 12 DAY),
    (10, 1, 'Lievance s javorovým sirupom', 'Nadýchané lievance na víkendové ráno.',
     'Husté cesto z mlieka a vajec, lievance opečené na masle a podávané s javorovým sirupom a čučoriedkami.',
     'Raňajky', 10, 10, 'Jednoduché', 2, 360, 11, 52, 12, 1, 0, 0, 0,
     'Nadbytočné lievance zamraz – v hriankovači ich za minútku ohreješ.',
     NOW() - INTERVAL 14 DAY),
    (11, 1, 'Čokoládové lávové koláčiky', 'Tekuté čokoládové jadro, ktoré poteší.',
     'Malé koláčiky z horkej čokolády s tekutým stredom. Ideálne s guľkou vanilkovej zmrzliny.',
     'Dezert', 15, 12, 'Stredné', 2, 390, 7, 34, 26, 1, 0, 0, 0,
     'Cesto môžeš pripraviť deň vopred a nechať ho vo formičkách v chladničke.',
     NOW() - INTERVAL 16 DAY),
    (12, 2, 'Pečené jablká so škoricou', 'Voňavý jesenný dezert, ktorý prevonia celý byt.',
     'Celé jablká plnené orechmi, medom a škoricou, pečené do mäkka. Recept od mojej babky, ktorý robím každú jeseň.',
     'Dezert', 10, 25, 'Jednoduché', 2, 220, 3, 38, 8, 1, 0, 1, 1,
     'Na pečenie sú ideálne aj mierne zvráskavené jablká, ktoré by inak skončili v koši.',
     NOW() - INTERVAL 7 DAY),
    (13, 2, 'Domáci hummus so zeleninou', 'Krémový dip na rýchle zahryznutie.',
     'Hummus z cíceru, tahini a citróna, podávaný s chrumkavými zeleninovými tyčinkami.',
     'Občerstvenie', 10, 0, 'Jednoduché', 4, 240, 9, 22, 13, 1, 1, 1, 1,
     'Nálev z cíceru (aquafaba) nevylievaj – dá sa vyšľahať ako náhrada bielkov.',
     NOW() - INTERVAL 9 DAY),
    (14, 4, 'Jogurtová miska s granolou', 'Chrumkavá desiata do práce či školy.',
     'Grécky jogurt s domácou granolou, kúskami jablka a medom. Pripravená za päť minút.',
     'Občerstvenie', 5, 0, 'Jednoduché', 1, 310, 15, 36, 11, 1, 0, 0, 0,
     'Granolu si priprav do zásoby – v uzavretej dóze vydrží aj dva týždne.',
     NOW() - INTERVAL 11 DAY);

INSERT INTO `ingredients` (`recipe_id`, `position`, `amount`, `unit`, `name`, `shop_category`) VALUES
                                                                                                   (1, 1, 200, 'g', 'cestoviny rigatoni', 'spajza'),
                                                                                                   (1, 2, 250, 'g', 'cherry paradajky', 'zelenina'),
                                                                                                   (1, 3, 100, 'g', 'baby špenát', 'zelenina'),
                                                                                                   (1, 4, 125, 'g', 'mozzarella', 'chladene'),
                                                                                                   (1, 5, 2, 'strúčiky', 'cesnak', 'zelenina'),
                                                                                                   (1, 6, 1, 'PL', 'olivový olej', 'spajza'),
                                                                                                   (1, 7, NULL, 'podľa chuti', 'soľ a čierne korenie', 'spajza'),

                                                                                                   (2, 1, 80, 'g', 'ovsené vločky', 'spajza'),
                                                                                                   (2, 2, 400, 'ml', 'mlieko', 'chladene'),
                                                                                                   (2, 3, 125, 'g', 'čučoriedky', 'ovocie'),
                                                                                                   (2, 4, 1, 'ks', 'banán', 'ovocie'),
                                                                                                   (2, 5, 150, 'g', 'grécky jogurt', 'chladene'),
                                                                                                   (2, 6, 1, 'PL', 'med', 'spajza'),

                                                                                                   (3, 1, 300, 'g', 'filet z lososa', 'chladene'),
                                                                                                   (3, 2, 400, 'g', 'zemiaky', 'zelenina'),
                                                                                                   (3, 3, 1, 'ks', 'citrón', 'zelenina'),
                                                                                                   (3, 4, 2, 'strúčiky', 'cesnak', 'zelenina'),
                                                                                                   (3, 5, 1, 'zväzok', 'čerstvé bylinky', 'zelenina'),
                                                                                                   (3, 6, 2, 'PL', 'olivový olej', 'spajza'),
                                                                                                   (3, 7, NULL, 'podľa chuti', 'soľ a čierne korenie', 'spajza'),

                                                                                                   (4, 1, 400, 'g', 'cícer (sterilizovaný)', 'spajza'),
                                                                                                   (4, 2, 1, 'ks', 'uhorka', 'zelenina'),
                                                                                                   (4, 3, 200, 'g', 'cherry paradajky', 'zelenina'),
                                                                                                   (4, 4, 100, 'g', 'feta', 'chladene'),
                                                                                                   (4, 5, 0.5, 'ks', 'citrón', 'zelenina'),
                                                                                                   (4, 6, 2, 'PL', 'olivový olej', 'spajza'),
                                                                                                   (4, 7, 0.5, 'zväzok', 'čerstvé bylinky', 'zelenina'),

                                                                                                   (5, 1, 600, 'g', 'tekvica hokkaido', 'zelenina'),
                                                                                                   (5, 2, 1, 'ks', 'cibuľa', 'zelenina'),
                                                                                                   (5, 3, 1, 'strúčik', 'cesnak', 'zelenina'),
                                                                                                   (5, 4, 500, 'ml', 'zeleninový vývar', 'spajza'),
                                                                                                   (5, 5, 100, 'ml', 'smotana na varenie', 'chladene'),
                                                                                                   (5, 6, 1, 'PL', 'maslo', 'chladene'),
                                                                                                   (5, 7, NULL, 'podľa chuti', 'soľ a muškátový oriešok', 'spajza'),

                                                                                                   (6, 1, 4, 'plátky', 'kváskový chlieb', 'spajza'),
                                                                                                   (6, 2, 2, 'ks', 'avokádo', 'zelenina'),
                                                                                                   (6, 3, 2, 'ks', 'vajcia', 'chladene'),
                                                                                                   (6, 4, 0.5, 'ks', 'citrón', 'zelenina'),
                                                                                                   (6, 5, 1, 'štipka', 'čili vločky', 'spajza'),

                                                                                                   (7, 1, 160, 'g', 'ryža arborio', 'spajza'),
                                                                                                   (7, 2, 250, 'g', 'šampiňóny', 'zelenina'),
                                                                                                   (7, 3, 1, 'ks', 'cibuľa', 'zelenina'),
                                                                                                   (7, 4, 700, 'ml', 'zeleninový vývar', 'spajza'),
                                                                                                   (7, 5, 40, 'g', 'parmezán', 'chladene'),
                                                                                                   (7, 6, 2, 'PL', 'maslo', 'chladene'),

                                                                                                   (8, 1, 500, 'g', 'zemiaky', 'zelenina'),
                                                                                                   (8, 2, 200, 'g', 'hladká múka', 'spajza'),
                                                                                                   (8, 3, 1, 'ks', 'vajcia', 'chladene'),
                                                                                                   (8, 4, 200, 'g', 'bryndza', 'chladene'),
                                                                                                   (8, 5, 100, 'g', 'slanina', 'chladene'),
                                                                                                   (8, 6, 1, 'zväzok', 'pažítka', 'zelenina'),

                                                                                                   (9, 1, 3, 'ks', 'paradajky', 'zelenina'),
                                                                                                   (9, 2, 1, 'ks', 'uhorka', 'zelenina'),
                                                                                                   (9, 3, 1, 'ks', 'paprika', 'zelenina'),
                                                                                                   (9, 4, 0.5, 'ks', 'červená cibuľa', 'zelenina'),
                                                                                                   (9, 5, 60, 'g', 'olivy kalamata', 'spajza'),
                                                                                                   (9, 6, 150, 'g', 'feta', 'chladene'),
                                                                                                   (9, 7, 2, 'PL', 'olivový olej', 'spajza'),
                                                                                                   (9, 8, 1, 'ČL', 'oregano', 'spajza'),

                                                                                                   (10, 1, 150, 'g', 'hladká múka', 'spajza'),
                                                                                                   (10, 2, 200, 'ml', 'mlieko', 'chladene'),
                                                                                                   (10, 3, 1, 'ks', 'vajcia', 'chladene'),
                                                                                                   (10, 4, 1, 'ČL', 'prášok do pečiva', 'spajza'),
                                                                                                   (10, 5, 1, 'PL', 'maslo', 'chladene'),
                                                                                                   (10, 6, 3, 'PL', 'javorový sirup', 'spajza'),
                                                                                                   (10, 7, 100, 'g', 'čučoriedky', 'ovocie'),

                                                                                                   (11, 1, 100, 'g', 'horká čokoláda', 'spajza'),
                                                                                                   (11, 2, 60, 'g', 'maslo', 'chladene'),
                                                                                                   (11, 3, 2, 'ks', 'vajcia', 'chladene'),
                                                                                                   (11, 4, 40, 'g', 'cukor', 'spajza'),
                                                                                                   (11, 5, 20, 'g', 'hladká múka', 'spajza'),

                                                                                                   (12, 1, 4, 'ks', 'jablká', 'ovocie'),
                                                                                                   (12, 2, 40, 'g', 'vlašské orechy', 'spajza'),
                                                                                                   (12, 3, 2, 'PL', 'med', 'spajza'),
                                                                                                   (12, 4, 1, 'ČL', 'škorica', 'spajza'),

                                                                                                   (13, 1, 400, 'g', 'cícer (sterilizovaný)', 'spajza'),
                                                                                                   (13, 2, 2, 'PL', 'tahini', 'spajza'),
                                                                                                   (13, 3, 1, 'ks', 'citrón', 'zelenina'),
                                                                                                   (13, 4, 1, 'strúčik', 'cesnak', 'zelenina'),
                                                                                                   (13, 5, 3, 'PL', 'olivový olej', 'spajza'),
                                                                                                   (13, 6, 2, 'ks', 'mrkva', 'zelenina'),
                                                                                                   (13, 7, 1, 'ks', 'paprika', 'zelenina'),

                                                                                                   (14, 1, 150, 'g', 'grécky jogurt', 'chladene'),
                                                                                                   (14, 2, 40, 'g', 'granola', 'spajza'),
                                                                                                   (14, 3, 0.5, 'ks', 'jablká', 'ovocie'),
                                                                                                   (14, 4, 1, 'ČL', 'med', 'spajza');

INSERT INTO `steps` (`recipe_id`, `position`, `title`, `description`) VALUES
                                                                          (1, 1, 'Uvar cestoviny', 'Vo veľkom hrnci priveď osolenú vodu do varu. Pridaj cestoviny a var ich podľa návodu na obale, aby zostali al dente. Pred scedením si odlož 100 ml vody z varenia.'),
                                                                          (1, 2, 'Opeč paradajky a cesnak', 'Na panvici rozohrej olivový olej. Pridaj nasekaný cesnak a krátko ho opeč. Prisyp prekrojené cherry paradajky a restuj 5–7 minút, kým zmäknú a pustia šťavu.'),
                                                                          (1, 3, 'Pridaj špenát a všetko spoj', 'Do panvice vlož umytý baby špenát a nechaj ho zvädnúť. Pridaj cestoviny a trochu odloženej vody. Premiešaj, osoľ a okoreň podľa chuti.'),
                                                                          (1, 4, 'Dokonči a podávaj', 'Panvicu odstav a vmiešaj mozzarellu natrhanú na malé kúsky. Rozdeľ na dva taniere a podávaj ešte teplé. Dobrú chuť!'),

                                                                          (2, 1, 'Uvar vločky', 'V hrnci zohrej mlieko, pridaj ovsené vločky a za stáleho miešania var 4–5 minút, kým kaša nezhustne.'),
                                                                          (2, 2, 'Priprav ovocie', 'Banán nakrájaj na kolieska a čučoriedky opláchni studenou vodou.'),
                                                                          (2, 3, 'Dokonči misku', 'Kašu rozdeľ do misiek, navrch pridaj grécky jogurt, ovocie a pokvapkaj medom.'),

                                                                          (3, 1, 'Priprav zemiaky', 'Rúru predhrej na 200 °C. Zemiaky nakrájaj na mesiačiky, premiešaj s polovicou oleja a soľou a peč 8 minút.'),
                                                                          (3, 2, 'Ochuť lososa', 'Filet osoľ, okoreň, potri prelisovaným cesnakom a zvyšným olejom. Oblož ho plátkami citróna.'),
                                                                          (3, 3, 'Peč spolu', 'Lososa polož na plech k zemiakom a peč ďalších 12 minút, kým sa mäso nezačne lúpať.'),
                                                                          (3, 4, 'Podávaj', 'Posyp nasekanými bylinkami a pokvapkaj šťavou z pečeného citróna.'),

                                                                          (4, 1, 'Priprav cícer', 'Cícer sceď, prepláchni studenou vodou a nechaj odkvapkať.'),
                                                                          (4, 2, 'Nakrájaj zeleninu', 'Uhorku nakrájaj na kocky a cherry paradajky rozpoľ.'),
                                                                          (4, 3, 'Zmiešaj dresing', 'Olivový olej vymiešaj so šťavou z citróna, soľou a čiernym korením.'),
                                                                          (4, 4, 'Spoj a podávaj', 'Všetko premiešaj s dresingom, navrch rozdrob fetu a posyp nasekanými bylinkami.'),

                                                                          (5, 1, 'Opraž základ', 'Na masle opraž nadrobno nakrájanú cibuľu a cesnak, kým nezmäknú.'),
                                                                          (5, 2, 'Pridaj tekvicu', 'Tekvicu nakrájaj na kocky (hokkaido netreba lúpať), pridaj k cibuľke a zalej vývarom.'),
                                                                          (5, 3, 'Var a rozmixuj', 'Var asi 20 minút do mäkka, potom polievku rozmixuj ponorným mixérom dohladka.'),
                                                                          (5, 4, 'Dochuť', 'Vmiešaj smotanu, osoľ a dochuť štipkou muškátového orieška.'),

                                                                          (6, 1, 'Opeč chlieb', 'Plátky chleba opeč na suchej panvici alebo v hriankovači dochrumkava.'),
                                                                          (6, 2, 'Priprav avokádo', 'Avokádo roztlač vidličkou so šťavou z citróna a štipkou soli.'),
                                                                          (6, 3, 'Uvar vajíčka', 'Vajíčka var 6 minút, potom ich schlaď studenou vodou a ošúp.'),
                                                                          (6, 4, 'Poskladaj toast', 'Avokádo natri na chlieb, pridaj rozpolené vajíčko a posyp čili vločkami.'),

                                                                          (7, 1, 'Opraž huby', 'Šampiňóny nakrájaj a opraž na polovici masla dozlatista. Odlož ich bokom.'),
                                                                          (7, 2, 'Orestuj ryžu', 'Na zvyšnom masle speň nadrobno nakrájanú cibuľu, pridaj ryžu a minútu ju restuj.'),
                                                                          (7, 3, 'Postupne podlievaj', 'Prilievaj horúci vývar po naberačkách a miešaj, kým ryža nezmäkne – asi 18 minút.'),
                                                                          (7, 4, 'Dokonči', 'Vmiešaj huby a strúhaný parmezán a nechaj rizoto pod pokrievkou 2 minúty odpočinúť.'),

                                                                          (8, 1, 'Priprav cesto', 'Zemiaky ošúp a nastrúhaj najemno. Pridaj vajce, múku a soľ a vymiešaj husté cesto.'),
                                                                          (8, 2, 'Uvar halušky', 'Cesto pretláčaj cez haluškár do vriacej osolenej vody a var, kým halušky nevyplávu.'),
                                                                          (8, 3, 'Opeč slaninu', 'Slaninu nakrájaj na kocky a na panvici vypraž dochrumkava.'),
                                                                          (8, 4, 'Spoj s bryndzou', 'Scedené halušky premiešaj s bryndzou, navrch daj slaninu s výpekom a pažítku.'),

                                                                          (9, 1, 'Nakrájaj zeleninu', 'Paradajky, uhorku a papriku nakrájaj na väčšie kúsky, cibuľu na tenké krúžky.'),
                                                                          (9, 2, 'Pridaj olivy', 'Zeleninu premiešaj v miske s olivami a jemne osoľ.'),
                                                                          (9, 3, 'Dochuť a podávaj', 'Navrch polož plátok fety, pokvapkaj olivovým olejom a posyp oreganom.'),

                                                                          (10, 1, 'Vymiešaj cesto', 'Múku zmiešaj s práškom do pečiva, pridaj mlieko a vajce a vymiešaj husté hladké cesto.'),
                                                                          (10, 2, 'Opeč lievance', 'Na rozpálenej panvici s troškou masla opekaj malé lievance z oboch strán dozlatista.'),
                                                                          (10, 3, 'Podávaj', 'Lievance polej javorovým sirupom a ozdob čučoriedkami.'),

                                                                          (11, 1, 'Roztop čokoládu', 'Čokoládu s maslom roztop nad parou a nechaj mierne vychladnúť.'),
                                                                          (11, 2, 'Vyšľahaj vajcia', 'Vajcia vyšľahaj s cukrom do peny, vmiešaj čokoládu a nakoniec preosiatu múku.'),
                                                                          (11, 3, 'Upeč', 'Cesto rozdeľ do vymastených formičiek a peč pri 200 °C 10–12 minút. Stred musí zostať mäkký.'),
                                                                          (11, 4, 'Vyklop a podávaj', 'Koláčiky nechaj minútu postáť, vyklop na tanierik a podávaj hneď.'),

                                                                          (12, 1, 'Vykroj jadrovníky', 'Jablká umy a vykrajovačom alebo nožíkom z nich vyber jadrovníky.'),
                                                                          (12, 2, 'Naplň jablká', 'Nasekané orechy zmiešaj s medom a škoricou a naplň nimi jablká.'),
                                                                          (12, 3, 'Upeč', 'Jablká polož do zapekacej misy a peč pri 180 °C asi 25 minút do mäkka.'),

                                                                          (13, 1, 'Rozmixuj základ', 'Scedený cícer rozmixuj s tahini, cesnakom a šťavou z citróna.'),
                                                                          (13, 2, 'Dochuť', 'Za stáleho mixovania prilievaj olivový olej a trochu studenej vody, kým nie je hummus krémový. Osoľ.'),
                                                                          (13, 3, 'Priprav zeleninu', 'Mrkvu a papriku nakrájaj na tyčinky a podávaj s hummusom.'),

                                                                          (14, 1, 'Nakrájaj jablko', 'Jablko nakrájaj na malé kocky.'),
                                                                          (14, 2, 'Poskladaj misku', 'Do misky daj jogurt, navrch granolu a jablko a pokvapkaj medom.');
