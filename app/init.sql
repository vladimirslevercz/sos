-- Adminer 4.2.0 MySQL dump

SET NAMES utf8mb4;
SET time_zone = '+00:00';
SET foreign_key_checks = 0;
SET sql_mode = 'NO_AUTO_VALUE_ON_ZERO';

DROP TABLE IF EXISTS `article`;
CREATE TABLE `article` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `name` varchar(255) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `annotation` text,
  `text` text NOT NULL,
  PRIMARY KEY (`id`),
  KEY `author_id` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

INSERT INTO `article` (`id`, `created_at`, `name`, `user_id`, `annotation`, `text`) VALUES
(1,	'2015-04-06 22:11:21',	'První článek z editoru',	1,	'<p>Jek&eacute; je vlastně heslo do administrace.</p>',	'<p>Asi bych to sem nměl ps&aacute;t, ale heslo je \"sosprohudbu\" ale pst ;-)</p>\n<p>&nbsp;</p>\n<p>&nbsp;</p>'),
(3,	'2015-07-19 11:13:42',	'O projektu',	3,	'<h2>O projektu Hudební S.O.S</h2>\n<ul>\n<li>o čem to má být</li>\n<li>jak to vzniklo</li>\n<li>co to vše zahrnuje</li>\n</ul>',	'<h1>Zakladatelka (1)</h1>\n<h2>Sponzoři (2)</h2>\n<h3>Spolupracujeme (3)</h3>\n<p>další informace a odkazy na weby spolupracovníků Hudebního S.O.S.</p>\n<p>povídání o Majce vzdělání atd.</p>\n<p>publikační činnost... náhledy obálek publikací a popis</p>\n<p>youtube fotoprezenatce &sbquo;z mého života&lsquo; (vložená nebo odkaz) nebo pár fotek</p>\n<p>Soukromé pedagogické nakladateství dělá bla bla atd... jak sponzoruje Hudební S.O.S.</p>\n<p>kde můžete koupit publikace apod.</p>\n<p>odkaz na web</p>\n<p>další informace a odkazy na weby sponzorů Hudebního S.O.S.</p>\n<p>Ficae voluptaepro dolupta distius andanda con parcia doluptiae. Et dus duntiorum nullab il eum sum, sam sequi quasped magnim nia por am essequa ernatur?</p>\n<p>Metur, sollam natis audis alibusdam ea dolore nos ab ium fugitis eosae. Nam aut laut maxim quo quamento mi, sandici ducimporum quidi culluptatur, sus res ipitatur? Quibus esequo que perum experchit occuptaquae porepro bea dolumqui te delenet laborehentia volupitat magnat velecepta eos ditatur re mint evelitatem et am quiae ium ea prat aliciet anis id mos ipsunt am aborehe ndelentet quaerfe rnatemporro eatempore ma comniscium aut experem rent, quaes dolor saest aspicipic te con pelecto cum hitem rem liquatem quatent ioribus ex est explab ipic tem rendis ut et qui vent vellore rcillandaes entotasim quame pori utempor eseritat quam, volendamus cumquas pellento is dolor magnat.</p>');

DROP TABLE IF EXISTS `file`;
CREATE TABLE `file` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `path` varchar(255) NOT NULL COMMENT 'Path to file',
  `type` enum('picture','document','other') DEFAULT 'other' COMMENT 'picture, document, other',
  `user_id` int(11) DEFAULT NULL COMMENT 'Owner',
  `nice_name` varchar(255) DEFAULT NULL COMMENT 'Name as title',
  `keywords` varchar(255) DEFAULT NULL COMMENT 'Keywords',
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  CONSTRAINT `file_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `user` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Paths to pictures, documents and other files.';

INSERT INTO `file` (`id`, `path`, `type`, `user_id`, `nice_name`, `keywords`) VALUES
(1,	'author/vml.jpg',	'picture',	NULL,	'vml',	'author profile picture, vml');

DROP TABLE IF EXISTS `menu`;
CREATE TABLE `menu` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `url` text,
  `article_id` int(11) DEFAULT NULL,
  `menu_id` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `article_id` (`article_id`),
  KEY `menu_id` (`menu_id`),
  CONSTRAINT `menu_ibfk_1` FOREIGN KEY (`article_id`) REFERENCES `article` (`id`) ON DELETE SET NULL,
  CONSTRAINT `menu_ibfk_2` FOREIGN KEY (`menu_id`) REFERENCES `menu` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

INSERT INTO `menu` (`id`, `name`, `url`, `article_id`, `menu_id`) VALUES
(1,	'První menu',	'',	1,	NULL),
(2,	'První podřazené',	'',	3,	1),
(3,	'O projektu',	'',	3,	NULL),
(4,	'Seznam',	'http://seznam.cz',	NULL,	NULL);

DROP TABLE IF EXISTS `user`;
CREATE TABLE `user` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `email` varchar(255) NOT NULL COMMENT 'email and login',
  `password` varchar(255) NOT NULL COMMENT 'hashed password',
  `role` varchar(255) DEFAULT 'guest' COMMENT 'user_role',
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

INSERT INTO `user` (`id`, `email`, `password`, `role`) VALUES
(3,	'liskovamaj@gmail.com',	'$2y$10$FIrpbNKoJLM7RHvfLwVwgOP6d/ct..o8xHzwXQA.q/plDMvbXMV8m',	'guest'),
(4,	'mlazovla@gmail.com',	'$2y$10$7bq17HuG2Rk266U/0VGTYOrFYFATNuwNUxm5WqC6fKLkqw7EZRbIC',	'guest');

-- 2015-07-19 14:06:49