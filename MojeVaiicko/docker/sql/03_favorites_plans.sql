-- Obľúbené recepty (M:N medzi users a recipes) a týždenný plán jedál
SET NAMES utf8mb4;

CREATE TABLE IF NOT EXISTS `favorites` (
    `id`         INT AUTO_INCREMENT PRIMARY KEY,
    `user_id`    INT NOT NULL,
    `recipe_id`  INT NOT NULL,
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT `uq_favorites_user_recipe` UNIQUE (`user_id`, `recipe_id`),
    CONSTRAINT `fk_favorites_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`)
        ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT `fk_favorites_recipe` FOREIGN KEY (`recipe_id`) REFERENCES `recipes` (`id`)
        ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Jeden záznam = jeden recept v konkrétny deň a jedlo (raňajky / obed / večera)
CREATE TABLE IF NOT EXISTS `meal_plans` (
    `id`         INT AUTO_INCREMENT PRIMARY KEY,
    `user_id`    INT         NOT NULL,
    `recipe_id`  INT         NOT NULL,
    `plan_date`  DATE        NOT NULL,
    `meal_type`  VARCHAR(10) NOT NULL,
    `servings`   TINYINT     NOT NULL DEFAULT 2,
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT `uq_meal_plans_slot` UNIQUE (`user_id`, `plan_date`, `meal_type`),
    CONSTRAINT `chk_meal_plans_type` CHECK (`meal_type` IN ('ranajky', 'obed', 'vecera')),
    CONSTRAINT `fk_meal_plans_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`)
        ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT `fk_meal_plans_recipe` FOREIGN KEY (`recipe_id`) REFERENCES `recipes` (`id`)
        ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO `favorites` (`user_id`, `recipe_id`) VALUES
(2, 1), (2, 3), (2, 5), (2, 9), (2, 11), (4, 2), (4, 8);

-- Ukážkový plán pre Martinu na aktuálny týždeň (pondelok = CURDATE() - WEEKDAY(CURDATE()))
SET @monday = DATE_SUB(CURDATE(), INTERVAL WEEKDAY(CURDATE()) DAY);

INSERT INTO `meal_plans` (`user_id`, `recipe_id`, `plan_date`, `meal_type`, `servings`) VALUES
(2, 2, @monday, 'ranajky', 2),
(2, 5, @monday, 'obed', 2),
(2, 4, @monday, 'vecera', 2),
(2, 6, DATE_ADD(@monday, INTERVAL 1 DAY), 'ranajky', 2),
(2, 1, DATE_ADD(@monday, INTERVAL 1 DAY), 'obed', 2),
(2, 3, DATE_ADD(@monday, INTERVAL 1 DAY), 'vecera', 2),
(2, 2, DATE_ADD(@monday, INTERVAL 2 DAY), 'ranajky', 2),
(2, 1, DATE_ADD(@monday, INTERVAL 3 DAY), 'obed', 2),
(2, 2, DATE_ADD(@monday, INTERVAL 4 DAY), 'ranajky', 2);
