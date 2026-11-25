-- SmartRecipe Hub – používatelia
-- Skripty v tomto priečinku sa spúšťajú v abecednom poradí (00_, 01_, 02_, 03_).
-- Pri ručnom spustení najprv zmažeme tabuľky v opačnom poradí kvôli cudzím kľúčom.
SET NAMES utf8mb4;

DROP TABLE IF EXISTS `meal_plans`;
DROP TABLE IF EXISTS `favorites`;
DROP TABLE IF EXISTS `reviews`;
DROP TABLE IF EXISTS `steps`;
DROP TABLE IF EXISTS `ingredients`;
DROP TABLE IF EXISTS `recipes`;
DROP TABLE IF EXISTS `users`;

CREATE TABLE `users` (
    `id`            INT AUTO_INCREMENT PRIMARY KEY,
    `username`      VARCHAR(50)  NOT NULL,
    `name`          VARCHAR(100) NOT NULL,
    `password_hash` VARCHAR(255) NOT NULL,
    `plan_servings` TINYINT      NOT NULL DEFAULT 2,
    `created_at`    DATETIME DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT `uq_users_username` UNIQUE (`username`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Heslá: smartrecipe / smartrecipe-team, martina / martina123, admin / admin, jakub / jakub123
INSERT INTO `users` (`id`, `username`, `name`, `password_hash`, `plan_servings`) VALUES
    (1, 'smartrecipe', 'Tím SmartRecipe', '$2y$10$5CbfEZojifggUScD4/wp9ODGYy2FfVQmkR3Fk1FjZasFgM28cJWpy', 2),
    (2, 'martina', 'Martina Kováčová', '$2y$10$lGF5vTOK5vj0dqXyRg2w2uf/wVKCozR6RUA7LxV4DfPfbJ3VOh.CW', 2),
    (3, 'admin', 'Admin', '$2y$10$34Dlv3AHyskYP30trRgOjOdnS.OUKbv931m66fi4kT3qFjND2yBf.', 2),
    (4, 'jakub', 'Jakub Horváth', '$2y$10$aEVaAG.v4REPmpD8lDGir.THSHFLsfniWhr.I8q8NsbRRBhi9bkeu', 2);
