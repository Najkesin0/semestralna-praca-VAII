CREATE TABLE IF NOT EXISTS `recipes` (`id` INT AUTO_INCREMENT PRIMARY KEY,
    `title` VARCHAR(255) NOT NULL,
    `category` VARCHAR(50) NOT NULL,
    `prep_time` INT NOT NULL,
    `calories` INT NOT NULL,
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

TRUNCATE TABLE `recipes`;

INSERT INTO `recipes` (`title`, `category`, `prep_time`, `calories`) VALUES
                    ('Krémové hubové rizoto', 'Obed', 30, 450),
                    ('Avokádový toast s vajíčkom', 'Raňajky', 15, 320),
                    ('Domáci čučoriedkový koláč', 'Dezert', 45, 280),
                    ('Grilovaný losos so špargľou', 'Večera', 25, 510),
                    ('Ovocná smoothie miska', 'Raňajky', 10, 210),
                    ('Tradičné bryndzové halušky', 'Obed', 40, 680),
                    ('Grécky šalát s fetou a olivami', 'Večera', 15, 290),
                    ('Čokoládové lávové koláčiky', 'Dezert', 25, 390),
                    ('Ovsená kaša s jablkami a škoricou', 'Raňajky', 12, 250),
                    ('Kuracie prsia na medovo-horčicovej omáčke', 'Obed', 35, 480),
                    ('Tuniakový šalát s vajíčkom', 'Večera', 15, 310),
                    ('Pravé talianske Tiramisu', 'Dezert', 30, 420),
                    ('Lievance s javorovým sirupom', 'Raňajky', 20, 360),
                    ('Hovädzí guláš so žemľovou knedľou', 'Obed', 120, 750),
                    ('Zeleninové krémové rizoto', 'Večera', 25, 380),
                    ('Jablkový závin s orechmi', 'Dezert', 50, 310),
                    ('Míchané vajíčka na cibuľke', 'Raňajky', 10, 270),
                    ('Pečená krkovička s kyslou kapustou', 'Obed', 90, 820),
                    ('Caesar šalát s grilovaným kuracím mäsom', 'Večera', 20, 430);