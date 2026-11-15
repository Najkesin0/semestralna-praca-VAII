-- Hodnotenia receptov (vzťah 1:N – jeden recept môže mať viac hodnotení)
CREATE TABLE IF NOT EXISTS `reviews` (
                                         `id` INT AUTO_INCREMENT PRIMARY KEY,
                                         `recipe_id` INT NOT NULL,
                                         `author` VARCHAR(100) NOT NULL,
    `rating` TINYINT NOT NULL,
    `comment` TEXT NULL,
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT `fk_reviews_recipe`
    FOREIGN KEY (`recipe_id`) REFERENCES `recipes` (`id`)
    ON DELETE CASCADE
    ON UPDATE CASCADE,
    CONSTRAINT `chk_reviews_rating` CHECK (`rating` BETWEEN 1 AND 5)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE INDEX `idx_reviews_recipe_id` ON `reviews` (`recipe_id`);

INSERT INTO `reviews` (`recipe_id`, `author`, `rating`, `comment`) VALUES
                                                                       (1, 'Jana', 5, 'Výborné rizoto, krémové presne ako má byť.'),
                                                                       (1, 'Peter', 4, 'Pridal som viac parmezánu, odporúčam.'),
                                                                       (2, 'Lucia', 5, 'Rýchle a zdravé raňajky.'),
                                                                       (3, 'Martin', 4, 'Koláč bol šťavnatý, nabudúce menej cukru.'),
                                                                       (6, 'Eva', 5, 'Ako od babky!'),
                                                                       (6, 'Tomáš', 3, 'Halušky dobré, bryndze mohlo byť viac.'),
                                                                       (8, 'Zuzana', 5, 'Lávové jadro sa podarilo na prvý pokus.'),
                                                                       (12, 'Michal', 4, 'Klasika, ktorá nesklame.'),
                                                                       (14, 'Jozef', 5, 'Najlepší guláš, aký som robil.');
