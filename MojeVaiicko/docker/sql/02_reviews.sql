-- Hodnotenia receptov (vzťah 1:N – jeden recept môže mať viac hodnotení)
SET NAMES utf8mb4;

CREATE TABLE IF NOT EXISTS `reviews` (
                                         `id`         INT AUTO_INCREMENT PRIMARY KEY,
                                         `recipe_id`  INT          NOT NULL,
                                         `author`     VARCHAR(100) NOT NULL,
    `rating`     TINYINT      NOT NULL,
    `comment`    TEXT         NULL,
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT `fk_reviews_recipe`
    FOREIGN KEY (`recipe_id`) REFERENCES `recipes` (`id`)
    ON DELETE CASCADE
    ON UPDATE CASCADE,
    CONSTRAINT `chk_reviews_rating` CHECK (`rating` BETWEEN 1 AND 5)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE INDEX `idx_reviews_recipe_id` ON `reviews` (`recipe_id`);

INSERT INTO `reviews` (`recipe_id`, `author`, `rating`, `comment`) VALUES
                                                                       (1, 'Jana', 5, 'Rýchle, chutné a z jednej panvice. Robím každý týždeň.'),
                                                                       (1, 'Peter', 5, 'Pridal som trochu čili, výborné.'),
                                                                       (1, 'Lucia', 5, 'Mozzarella na konci je skvelý nápad.'),
                                                                       (1, 'Tomáš', 4, 'Dobré, nabudúce dám viac špenátu.'),
                                                                       (2, 'Eva', 5, 'Konečne kaša, ktorá nie je mdlá.'),
                                                                       (2, 'Michal', 4, 'S jogurtom je to super kombinácia.'),
                                                                       (3, 'Zuzana', 5, 'Losos bol šťavnatý presne podľa času v recepte.'),
                                                                       (3, 'Jozef', 5, 'Zemiaky na plechu spolu s rybou – geniálne a rýchle.'),
                                                                       (4, 'Martin', 4, 'Svieže a sýte, ideálne do krabičky.'),
                                                                       (5, 'Katarína', 5, 'Najlepšia tekvicová polievka, akú som varila.'),
                                                                       (5, 'Juraj', 5, 'Opražené semienka navrch sú bodka.'),
                                                                       (6, 'Lenka', 4, 'Jednoduché, ale chutí ako z kaviarne.'),
                                                                       (7, 'Peter', 4, 'Pridal som viac parmezánu, odporúčam.'),
                                                                       (8, 'Eva', 5, 'Ako od babky!'),
                                                                       (8, 'Tomáš', 3, 'Halušky dobré, bryndze mohlo byť viac.'),
                                                                       (11, 'Zuzana', 5, 'Lávové jadro sa podarilo na prvý pokus.'),
                                                                       (12, 'Jana', 5, 'Celý byt voňal Vianocami.'),
                                                                       (13, 'Michal', 4, 'Hummus lepší ako kupovaný.');
