# SQL Manager 2005 for MySQL 3.7.0.1
# ---------------------------------------
# Host     : localhost
# Port     : 3306
# Database : minigis


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES cp1251 */;

SET FOREIGN_KEY_CHECKS=0;

DROP DATABASE IF EXISTS `inmypocket`;

CREATE DATABASE `inmypocket`
    CHARACTER SET 'utf8'
    COLLATE 'utf8_general_ci';

USE `inmypocket`;

#
# Structure for the `audit` table : 
#

DROP TABLE IF EXISTS `audit`;

CREATE TABLE `audit` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user` int(11) DEFAULT NULL,
  `text` tinytext,
  `ts` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `object` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=0 DEFAULT CHARSET=utf8;

#
# Structure for the `comments` table : 
#

DROP TABLE IF EXISTS `comments`;

CREATE TABLE `comments` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `location_id` int(11) DEFAULT NULL,
  `auth_name` tinytext COMMENT '��� ������ (�������)',
  `contact_info` tinytext COMMENT '���������� ���������� �� ������ �����������',
  `text` text,
  `date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `status` enum('N','A','D','I') DEFAULT NULL,
  `ip` int(14) DEFAULT NULL,
  `uid` text,
  `hash` varchar(32) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `location_id` (`location_id`)
) ENGINE=MyISAM AUTO_INCREMENT=0 DEFAULT CHARSET=utf8;

#
# Structure for the `images` table : 
#

DROP TABLE IF EXISTS `images`;

CREATE TABLE `images` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `filename` text,
  `owner_id` text,
  `location_id` int(11) DEFAULT '0',
  `order` int(11) DEFAULT '0',
  `orig_filename` text,
  `active` tinyint(1) DEFAULT '1',
  `full` tinytext,
  `mid` tinytext,
  `small` tinytext,
  `comment` text,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=0 DEFAULT CHARSET=utf8 PACK_KEYS=0 ROW_FORMAT=COMPACT;

#
# Structure for the `locations` table : 
#

DROP TABLE IF EXISTS `locations`;

CREATE TABLE `locations` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `location_name` text,
  `owner` text,
  `type` int(11) DEFAULT '7',
  `style_override` text,
  `contact_info` text COMMENT '�������� ����������� ����',
  `address` text COMMENT '�������� ����� �������',
  `coord_y` longtext COMMENT '���������� � ������� ������-�����',
  `coord_obj` longtext,
  `coord_array` longtext,
  `date` date DEFAULT NULL COMMENT '���� �����������',
  `parent` int(11) DEFAULT '0' COMMENT '������ �� ������������ ���������� ��� ��������� � �������. 0 - �������� ���������� ��� ���� ��� ����',
  `active` tinyint(1) NOT NULL DEFAULT '1',
  `comments` tinyint(1) DEFAULT '0' COMMENT '��������/��������� �����������',
  `cache` longtext,
  `cache_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `loc_hash` tinytext,
  PRIMARY KEY (`id`),
  KEY `type` (`type`),
  KEY `owner` (`owner`(1))
) ENGINE=MyISAM AUTO_INCREMENT=0 DEFAULT CHARSET=utf8 PACK_KEYS=0 ROW_FORMAT=DYNAMIC;

#
# Structure for the `locations_types` table : 
#

DROP TABLE IF EXISTS `locations_types`;

CREATE TABLE `locations_types` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `has_child` tinyint(1) DEFAULT '1' COMMENT '������� ����������� ����������:\r\n������ -> ����� -> ���� � ������ - > �����-�����',
  `name` text COMMENT '�������� ���� ����������',
  `attributes` text COMMENT '������ ��������� (���� ���� ��� �����)',
  `object_group` int(11) DEFAULT NULL COMMENT '������ �������� �����',
  `pl_num` int(11) DEFAULT NULL COMMENT '������, �������� ������������� ������ ������ � ������� �������. ����������� ��� ������, � ���������� ����� �������� ��. ����������� �� ����������',
  `pr_type` int(11) DEFAULT '1' COMMENT 'presentation type: 1 - point; 2 - route; 3 - area;',
  PRIMARY KEY (`id`),
  KEY `pl_num` (`pl_num`),
  KEY `has_child` (`has_child`),
  KEY `object_group` (`object_group`)
) ENGINE=MyISAM AUTO_INCREMENT=0 DEFAULT CHARSET=utf8;

#
# Structure for the `map_content` table : 
#

DROP TABLE IF EXISTS `map_content`;

CREATE TABLE `map_content` (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT '������ �������� �������� ������.',
  `a_layers` varchar(120) DEFAULT NULL COMMENT '������� ���� (object groups) � �����������, ���������� �������',
  `a_types` varchar(120) DEFAULT NULL COMMENT '������� ����� � �������� �����������.',
  `b_layers` varchar(120) DEFAULT NULL COMMENT '������� ���� (object groups) � �����������, ���������� �������',
  `b_types` varchar(360) DEFAULT NULL,
  `objects` varchar(360) DEFAULT NULL COMMENT '������� �������� � �������� �����������',
  `name` text COMMENT '��� ������ ��������. ������������� ��� �������',
  `hash` text COMMENT '��� ����� ��� ��������',
  `active` tinyint(1) DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `a_layers` (`a_layers`),
  KEY `a_types` (`a_types`),
  KEY `b_layers` (`b_layers`),
  KEY `b_types` (`b_types`)
) ENGINE=MyISAM AUTO_INCREMENT=0 DEFAULT CHARSET=utf8 PACK_KEYS=0 ROW_FORMAT=DYNAMIC;

#
# Structure for the `modx_sessions` table : 
#

DROP TABLE IF EXISTS `modx_sessions`;

CREATE TABLE `modx_sessions` (
  `session_id` varchar(40) NOT NULL DEFAULT '0',
  `ip_address` varchar(16) NOT NULL DEFAULT '0',
  `user_agent` text NOT NULL,
  `last_activity` int(10) unsigned NOT NULL DEFAULT '0',
  `user_data` text,
  PRIMARY KEY (`session_id`),
  KEY `last_activity` (`last_activity`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

#
# Structure for the `objects_groups` table : 
#

DROP TABLE IF EXISTS `objects_groups`;

CREATE TABLE `objects_groups` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` text,
  `active` tinyint(1) DEFAULT '0',
  `array` text,
  `function` tinytext,
  `adm_array` text,
  `icon` varchar(250) DEFAULT NULL,
  `refcoord` varchar(40) DEFAULT NULL,
  `refzoom` int(2) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=0 DEFAULT CHARSET=utf8 PACK_KEYS=0 ROW_FORMAT=DYNAMIC;

#
# Structure for the `properties_assigned` table : 
#

DROP TABLE IF EXISTS `properties_assigned`;

CREATE TABLE `properties_assigned` (
  `location_id` int(11) DEFAULT NULL COMMENT '������ �������',
  `property_id` int(11) DEFAULT NULL COMMENT '������ ������������ ��������',
  `value` text COMMENT '�������� ������������ ��������',
  KEY `value` (`value`(1)),
  KEY `property_id` (`property_id`),
  KEY `location_id` (`location_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 PACK_KEYS=0 ROW_FORMAT=COMPACT;

#
# Structure for the `properties_list` table : 
#

DROP TABLE IF EXISTS `properties_list`;

CREATE TABLE `properties_list` (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT '����������� ������������� ��������',
  `row` int(11) DEFAULT NULL COMMENT '��� � ������� ��������� ���������',
  `element` int(11) DEFAULT '1' COMMENT '��������� ��������� � ������ � ������� ��������� ',
  `label` text COMMENT '��� ���� (��������������� ��������)',
  `algoritm` varchar(5) NOT NULL DEFAULT 'u' COMMENT '�������� ��������� ����',
  `selfname` text COMMENT '��� ���� (�������)',
  `page` int(11) DEFAULT NULL COMMENT '�������� ��������� ��� ����������� �������',
  `property_group` varchar(20) DEFAULT NULL COMMENT '������ �������',
  `fieldtype` enum('text','select','textarea','checkbox','radio') DEFAULT NULL COMMENT '��� ���� ��� ���������',
  `cat` varchar(20) DEFAULT NULL COMMENT '������� ���������',
  `style` varchar(20) DEFAULT NULL COMMENT '����������� �����',
  `object_group` int(11) DEFAULT '1' COMMENT '����������� �������� ������ �������� (����������� ������������� �������� �� �����)',
  `parameters` text COMMENT '�������������� ��������� ������������� ����',
  `active` tinyint(1) DEFAULT '0',
  `searchable` tinyint(1) DEFAULT '1',
  `multiplier` int(11) DEFAULT '1',
  `divider` int(11) DEFAULT '1',
  `coef` tinytext,
  `linked` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `cat` (`cat`),
  KEY `page` (`page`),
  KEY `object_group` (`object_group`)
) ENGINE=MyISAM AUTO_INCREMENT=0 DEFAULT CHARSET=utf8;

#
# Structure for the `sheets` table : 
#

DROP TABLE IF EXISTS `sheets`;

CREATE TABLE `sheets` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `root` int(11) DEFAULT NULL,
  `parent` int(11) DEFAULT NULL,
  `header` text,
  `text` text,
  `owner` char(32) DEFAULT NULL,
  `date` date DEFAULT NULL,
  `ts` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `active` tinyint(1) DEFAULT '0',
  `pageorder` int(11) DEFAULT '0',
  `redirect` text,
  `comment` text,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=0 DEFAULT CHARSET=utf8;

#
# Structure for the `timers` table : 
#

DROP TABLE IF EXISTS `timers`;

CREATE TABLE `timers` (
  `start_point` datetime DEFAULT NULL,
  `end_point` datetime DEFAULT NULL,
  `price` int(11) DEFAULT NULL,
  `type` enum('price','zone','order','request') DEFAULT NULL,
  `location_id` int(11) DEFAULT NULL,
  `order` int(11) DEFAULT NULL,
  KEY `price` (`price`),
  KEY `start_point` (`start_point`),
  KEY `end_point` (`end_point`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

#
# Structure for the `usermaps` table : 
#

DROP TABLE IF EXISTS `usermaps`;

CREATE TABLE `usermaps` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `center_lon` tinytext,
  `center_lat` tinytext,
  `hash_a` tinytext,
  `hash_e` tinytext,
  `zoom` int(3) DEFAULT NULL,
  `maptype` tinytext,
  `name` text,
  `author` tinytext,
  `public` tinyint(1) DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `hash_a` (`hash_a`(4)),
  KEY `hash_e` (`hash_e`(4))
) ENGINE=MyISAM AUTO_INCREMENT=0 DEFAULT CHARSET=utf8;

#
# Structure for the `userobjects` table : 
#

DROP TABLE IF EXISTS `userobjects`;

CREATE TABLE `userobjects` (
  `map_id` tinytext,
  `hash` tinytext,
  `name` tinytext,
  `description` longtext,
  `coord` longtext,
  `attributes` tinytext,
  `address` text,
  `type` int(11) DEFAULT NULL,
  `timestamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `ip` int(11) DEFAULT NULL,
  `uagent` text,
  `link` text,
  `frame` int(11) DEFAULT '0',
  KEY `map_id` (`map_id`(1)),
  KEY `type` (`type`),
  KEY `hash` (`hash`(1))
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

#
# Structure for the `userobjects_heap` table : 
#

DROP TABLE IF EXISTS `userobjects_heap`;

CREATE TABLE `userobjects_heap` (
  `map_id` tinytext,
  `hash` tinytext,
  `name` tinytext,
  `description` longtext,
  `coord` longtext,
  `attributes` tinytext,
  `address` text,
  `type` int(11) DEFAULT NULL,
  `timestamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `ip` int(11) DEFAULT NULL,
  `uagent` text,
  KEY `map_id` (`map_id`(1)),
  KEY `type` (`type`),
  KEY `hash` (`hash`(1))
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

#
# Structure for the `users_admins` table : 
#

DROP TABLE IF EXISTS `users_admins`;

CREATE TABLE `users_admins` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `class_id` int(10) NOT NULL,
  `nick` varchar(255) DEFAULT NULL,
  `passw` varchar(255) DEFAULT NULL,
  `registration_date` date DEFAULT NULL,
  `name_f` tinytext,
  `name_i` tinytext,
  `name_o` tinytext,
  `info` text,
  `uid` text,
  `active` tinyint(1) NOT NULL DEFAULT '0',
  `rating` int(11) DEFAULT '0',
  `valid` tinyint(1) NOT NULL DEFAULT '0',
  `validcode` text NOT NULL,
  `email` text,
  `map_center` varchar(50) NOT NULL DEFAULT '40.521172,64.55342',
  `map_zoom` int(11) DEFAULT '11',
  `map_type` int(11) DEFAULT '2',
  `lang` varchar(3) DEFAULT NULL,
  `access` varchar(120) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `uid` (`uid`(1))
) ENGINE=MyISAM AUTO_INCREMENT=0 DEFAULT CHARSET=utf8 PACK_KEYS=0 ROW_FORMAT=COMPACT;

#
# Structure for the `users_searches` table : 
#

DROP TABLE IF EXISTS `users_searches`;

CREATE TABLE `users_searches` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `userid` tinytext COMMENT '������ �� ������� ������ ������',
  `string` text COMMENT '������ �������������, ���������� ����������',
  `ts` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `userid` (`userid`(1))
) ENGINE=MyISAM AUTO_INCREMENT=0 DEFAULT CHARSET=utf8;



/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
