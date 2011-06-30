# SQL-Front 5.1  (Build 4.16)

/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE */;
/*!40101 SET SQL_MODE='' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES */;
/*!40103 SET SQL_NOTES='ON' */;


# Host: teoriza-static.com:4321    Database: teoriza_virtualpol
# ------------------------------------------------------
# Server version 5.0.77-log

#
# Source for table 15mbcn_partidos
#

CREATE TABLE `15mbcn_partidos` (
  `ID` smallint(5) NOT NULL auto_increment,
  `ID_presidente` mediumint(7) NOT NULL default '0',
  `fecha_creacion` datetime NOT NULL default '0000-00-00 00:00:00',
  `siglas` varchar(12) NOT NULL default '',
  `nombre` varchar(50) NOT NULL,
  `descripcion` text NOT NULL,
  `estado` enum('ok') NOT NULL default 'ok',
  PRIMARY KEY  (`ID`),
  UNIQUE KEY `siglas` (`siglas`),
  UNIQUE KEY `ID_presidente` (`ID_presidente`),
  KEY `estado` (`estado`)
) ENGINE=MyISAM AUTO_INCREMENT=5 DEFAULT CHARSET=latin1;

#
# Source for table chats
#

CREATE TABLE `chats` (
  `chat_ID` smallint(5) unsigned NOT NULL auto_increment,
  `estado` enum('activo','bloqueado','en_proceso','expirado','borrado') NOT NULL default 'en_proceso',
  `pais` enum('POL','15M','15MBCN','15MMAD','Hispania','Atlantis','VP') NOT NULL default 'POL',
  `url` varchar(90) NOT NULL,
  `titulo` varchar(90) NOT NULL,
  `user_ID` mediumint(8) unsigned NOT NULL,
  `acceso_leer` varchar(30) character set utf8 NOT NULL default 'anonimos',
  `acceso_escribir` varchar(30) default 'ciudadanos',
  `acceso_cfg_leer` varchar(500) default NULL,
  `acceso_cfg_escribir` varchar(500) default NULL,
  `fecha_creacion` datetime NOT NULL,
  `fecha_last` datetime NOT NULL,
  `dias_expira` smallint(5) unsigned default NULL,
  `url_externa` varchar(500) default NULL,
  `stats_visitas` int(12) unsigned NOT NULL default '0',
  `stats_msgs` int(12) unsigned NOT NULL default '0',
  `GMT` tinyint(2) NOT NULL default '1',
  PRIMARY KEY  (`chat_ID`),
  UNIQUE KEY `url` (`url`),
  KEY `estado` (`estado`),
  KEY `pais` (`pais`)
) ENGINE=MyISAM AUTO_INCREMENT=292 DEFAULT CHARSET=latin1;

#
# Source for table chats_msg
#

CREATE TABLE `chats_msg` (
  `msg_ID` int(12) unsigned NOT NULL auto_increment,
  `chat_ID` smallint(5) unsigned NOT NULL,
  `nick` varchar(32) character set utf8 NOT NULL,
  `msg` varchar(900) character set utf8 NOT NULL,
  `time` timestamp NOT NULL default CURRENT_TIMESTAMP,
  `cargo` tinyint(3) unsigned NOT NULL default '0',
  `user_ID` mediumint(6) unsigned NOT NULL default '0',
  `tipo` enum('m','p','e','c') character set utf8 NOT NULL default 'm',
  `IP` bigint(12) default NULL,
  PRIMARY KEY  (`msg_ID`),
  KEY `chat_ID` (`chat_ID`),
  KEY `nick` (`nick`),
  KEY `time` (`time`),
  KEY `cargo` (`cargo`),
  KEY `user_ID` (`user_ID`),
  KEY `tipo` (`tipo`)
) ENGINE=MyISAM AUTO_INCREMENT=907860 DEFAULT CHARSET=latin1;

#
# Source for table docs
#

CREATE TABLE `docs` (
  `ID` smallint(5) NOT NULL auto_increment,
  `pais` enum('VP','15M','15MBCN','15MMAD') NOT NULL default 'VP',
  `url` varchar(255) NOT NULL default '',
  `title` varchar(255) NOT NULL default '',
  `text` longtext NOT NULL,
  `time` datetime NOT NULL default '0000-00-00 00:00:00',
  `time_last` datetime NOT NULL default '0000-00-00 00:00:00',
  `estado` enum('ok','del','borrador') NOT NULL default 'ok',
  `cat_ID` tinyint(3) NOT NULL default '0',
  `acceso_leer` varchar(30) character set utf8 NOT NULL default 'anonimos',
  `acceso_escribir` varchar(30) character set utf8 NOT NULL default 'privado',
  `acceso_cfg_leer` varchar(800) character set utf8 NOT NULL default '',
  `acceso_cfg_escribir` varchar(800) character set utf8 NOT NULL default '',
  PRIMARY KEY  (`ID`),
  UNIQUE KEY `url` (`url`),
  KEY `estado` (`estado`),
  KEY `cat_ID` (`cat_ID`)
) ENGINE=MyISAM AUTO_INCREMENT=240 DEFAULT CHARSET=latin1;

#
# Source for table empresa_acciones
#

CREATE TABLE `empresa_acciones` (
  `id` int(11) NOT NULL auto_increment,
  `ID_empresa` mediumint(9) unsigned NOT NULL default '0',
  `nick` varchar(300) character set utf8 collate utf8_spanish_ci NOT NULL,
  `pais` varchar(300) character set utf8 collate utf8_spanish_ci NOT NULL,
  `num_acciones` int(11) NOT NULL default '100',
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

#
# Source for table expulsiones
#

CREATE TABLE `expulsiones` (
  `ID` smallint(5) NOT NULL auto_increment,
  `user_ID` mediumint(8) NOT NULL default '0',
  `autor` mediumint(8) NOT NULL default '0',
  `expire` datetime NOT NULL default '0000-00-00 00:00:00',
  `razon` varchar(150) NOT NULL,
  `estado` enum('activo','inactivo','expulsado','cancelado') NOT NULL default 'activo',
  `tiempo` varchar(20) NOT NULL default '0',
  `IP` varchar(12) NOT NULL default '0',
  `cargo` tinyint(3) unsigned NOT NULL default '12',
  `motivo` text NOT NULL,
  PRIMARY KEY  (`ID`),
  KEY `user_ID` (`user_ID`),
  KEY `estado` (`estado`),
  KEY `IP` (`IP`)
) ENGINE=MyISAM AUTO_INCREMENT=797 DEFAULT CHARSET=latin1;

#
# Source for table hechos
#

CREATE TABLE `hechos` (
  `ID` mediumint(8) unsigned NOT NULL auto_increment,
  `time` date NOT NULL,
  `nick` varchar(14) character set utf8 NOT NULL default 'GONZO',
  `texto` varchar(2000) character set utf8 NOT NULL,
  `estado` enum('ok','del') character set utf8 NOT NULL default 'ok',
  `time2` datetime NOT NULL,
  `pais` enum('VirtualPol','POL','15M','15MBCN','15MMAD','VULCAN','Hispania','Atlantis','VP','Desarrollo') NOT NULL default 'VirtualPol',
  PRIMARY KEY  (`ID`),
  KEY `time` (`time`,`estado`)
) ENGINE=MyISAM AUTO_INCREMENT=338 DEFAULT CHARSET=latin1;

#
# Source for table mensajes
#

CREATE TABLE `mensajes` (
  `ID` int(10) unsigned NOT NULL auto_increment,
  `envia_ID` mediumint(8) unsigned NOT NULL default '0',
  `recibe_ID` mediumint(8) unsigned NOT NULL default '0',
  `time` datetime NOT NULL default '0000-00-00 00:00:00',
  `text` text NOT NULL,
  `leido` enum('0','1') NOT NULL default '0',
  `cargo` smallint(5) NOT NULL default '0',
  `recibe_masivo` varchar(10) NOT NULL default '',
  PRIMARY KEY  (`ID`),
  KEY `envia_ID` (`envia_ID`),
  KEY `recibe_ID` (`recibe_ID`),
  KEY `leido` (`leido`),
  KEY `time` (`time`)
) ENGINE=MyISAM AUTO_INCREMENT=121142 DEFAULT CHARSET=latin1;

#
# Source for table referencias
#

CREATE TABLE `referencias` (
  `ID` mediumint(8) NOT NULL auto_increment,
  `user_ID` mediumint(8) NOT NULL default '0',
  `IP` varchar(10) NOT NULL default '',
  `time` datetime NOT NULL default '0000-00-00 00:00:00',
  `referer` varchar(255) NOT NULL default '',
  `pagado` enum('0','1') NOT NULL default '0',
  `new_user_ID` mediumint(8) NOT NULL default '0',
  PRIMARY KEY  (`ID`),
  UNIQUE KEY `IP` (`IP`),
  KEY `user_ID` (`user_ID`),
  KEY `pagado` (`pagado`)
) ENGINE=MyISAM AUTO_INCREMENT=8908 DEFAULT CHARSET=latin1;

#
# Source for table stats
#

CREATE TABLE `stats` (
  `stats_ID` smallint(5) unsigned NOT NULL auto_increment,
  `pais` enum('POL','15M','15MBCN','15MMAD','VULCAN','Hispania','Atlantis','VP') NOT NULL default 'POL',
  `time` datetime NOT NULL default '0000-00-00 00:00:00',
  `ciudadanos` smallint(5) unsigned NOT NULL default '0',
  `nuevos` smallint(5) unsigned NOT NULL default '0',
  `pols` int(10) NOT NULL default '0',
  `pols_cuentas` int(10) NOT NULL default '0',
  `transacciones` smallint(5) unsigned NOT NULL default '0',
  `hilos_msg` smallint(5) unsigned NOT NULL default '0',
  `pols_gobierno` int(10) NOT NULL default '0',
  `partidos` tinyint(3) unsigned NOT NULL default '0',
  `frase` smallint(5) unsigned NOT NULL default '0',
  `empresas` smallint(5) unsigned NOT NULL default '0',
  `eliminados` smallint(5) unsigned NOT NULL default '0',
  `mapa` tinyint(3) unsigned NOT NULL default '0',
  `mapa_vende` tinyint(3) unsigned NOT NULL default '0',
  `24h` smallint(5) unsigned NOT NULL default '0',
  `confianza` smallint(5) NOT NULL,
  `autentificados` mediumint(9) default NULL,
  PRIMARY KEY  (`stats_ID`),
  KEY `time` (`time`)
) ENGINE=MyISAM AUTO_INCREMENT=2152 DEFAULT CHARSET=latin1;

#
# Source for table users
#

CREATE TABLE `users` (
  `ID` mediumint(8) unsigned NOT NULL auto_increment,
  `nick` varchar(18) NOT NULL default '',
  `pais` enum('ninguno','VP','15M','15MBCN','15MMAD') NOT NULL default 'ninguno',
  `pols` int(10) NOT NULL default '0',
  `fecha_registro` datetime NOT NULL default '0000-00-00 00:00:00',
  `fecha_last` datetime NOT NULL default '0000-00-00 00:00:00',
  `partido_afiliado` smallint(6) NOT NULL default '0',
  `estado` enum('turista','ciudadano','expulsado','desarrollador','validar') NOT NULL default 'validar',
  `nivel` tinyint(3) unsigned NOT NULL default '1',
  `email` varchar(255) NOT NULL default '',
  `pass` varchar(64) NOT NULL,
  `num_elec` tinyint(3) unsigned NOT NULL default '0',
  `online` int(10) unsigned NOT NULL default '0',
  `fecha_init` datetime NOT NULL default '0000-00-00 00:00:00',
  `ref` varchar(25) NOT NULL default '',
  `ref_num` mediumint(8) unsigned NOT NULL default '0',
  `api_pass` varchar(16) NOT NULL default '0',
  `api_num` smallint(5) NOT NULL default '0',
  `IP` varchar(12) NOT NULL default '0',
  `nota` decimal(3,1) NOT NULL default '0.0',
  `avatar` enum('true','false') NOT NULL default 'false',
  `text` varchar(2300) NOT NULL default '',
  `cargo` tinyint(3) unsigned NOT NULL default '0',
  `visitas` mediumint(8) unsigned NOT NULL default '0',
  `paginas` int(10) unsigned NOT NULL default '0',
  `nav` varchar(500) NOT NULL,
  `voto_confianza` smallint(5) NOT NULL default '0',
  `rechazo_last` datetime NOT NULL default '0000-00-00 00:00:00',
  `avatar_localdir` varchar(100) NOT NULL,
  `host` varchar(150) NOT NULL,
  `IP_proxy` varchar(150) NOT NULL,
  `geo` varchar(200) NOT NULL,
  `dnie` enum('true','false') default 'false',
  `dnie_check` varchar(400) default NULL,
  `bando` varchar(255) default NULL,
  `nota_SC` varchar(500) NOT NULL default '',
  `fecha_legal` datetime NOT NULL default '0000-00-00 00:00:00',
  `traza` varchar(600) NOT NULL default '',
  PRIMARY KEY  (`ID`),
  UNIQUE KEY `nick` (`nick`),
  UNIQUE KEY `api_pass` (`api_pass`),
  UNIQUE KEY `dnie_check` (`dnie_check`),
  KEY `estado` (`estado`),
  KEY `partido_afiliado` (`partido_afiliado`),
  KEY `nivel` (`nivel`),
  KEY `pols` (`pols`),
  KEY `cargo` (`cargo`),
  KEY `voto_confianza` (`voto_confianza`),
  KEY `pais` (`pais`),
  KEY `ref_num` (`ref_num`)
) ENGINE=MyISAM AUTO_INCREMENT=203222 DEFAULT CHARSET=latin1;

#
# Source for table votacion
#

CREATE TABLE `votacion` (
  `ID` smallint(5) NOT NULL auto_increment,
  `pais` enum('VP','15M','15MBCN','15MMAD','POL','Hispania','VULCAN','Atlantis') NOT NULL default 'VP',
  `pregunta` varchar(255) NOT NULL default '',
  `descripcion` text NOT NULL,
  `respuestas` text NOT NULL,
  `time` datetime NOT NULL default '0000-00-00 00:00:00',
  `time_expire` datetime NOT NULL default '0000-00-00 00:00:00',
  `user_ID` mediumint(8) NOT NULL default '0',
  `estado` enum('ok','end') NOT NULL default 'ok',
  `num` smallint(5) NOT NULL default '0',
  `tipo` enum('sondeo','referendum','parlamento','destituir','otorgar') NOT NULL default 'sondeo',
  `acceso_votar` varchar(30) NOT NULL default 'ciudadanos_pais',
  `acceso_cfg_votar` varchar(800) NOT NULL default '',
  `ejecutar` varchar(255) NOT NULL default '',
  PRIMARY KEY  (`ID`),
  KEY `tipo` (`tipo`)
) ENGINE=MyISAM AUTO_INCREMENT=580 DEFAULT CHARSET=latin1;

#
# Source for table votacion_votos
#

CREATE TABLE `votacion_votos` (
  `ID` mediumint(8) NOT NULL auto_increment,
  `user_ID` mediumint(8) NOT NULL default '0',
  `ref_ID` smallint(5) NOT NULL default '0',
  `voto` tinyint(3) NOT NULL default '0',
  `validez` enum('true','false') NOT NULL default 'true',
  PRIMARY KEY  (`ID`),
  KEY `ref_ID` (`ref_ID`)
) ENGINE=MyISAM AUTO_INCREMENT=13597 DEFAULT CHARSET=latin1;

#
# Source for table votos
#

CREATE TABLE `votos` (
  `ID` mediumint(8) unsigned NOT NULL auto_increment,
  `user_ID` mediumint(8) unsigned NOT NULL,
  `uservoto_ID` mediumint(8) unsigned NOT NULL,
  `voto` tinyint(3) NOT NULL,
  `time` datetime NOT NULL,
  `estado` enum('confianza') NOT NULL,
  PRIMARY KEY  (`ID`),
  KEY `estado` (`estado`),
  KEY `uservoto_ID` (`uservoto_ID`),
  KEY `user_ID` (`user_ID`)
) ENGINE=MyISAM AUTO_INCREMENT=1451 DEFAULT CHARSET=latin1;

#
# Source for table vp_ban
#

CREATE TABLE `vp_ban` (
  `ID` smallint(5) NOT NULL auto_increment,
  `user_ID` mediumint(8) NOT NULL default '0',
  `autor` mediumint(8) NOT NULL default '0',
  `expire` datetime NOT NULL default '0000-00-00 00:00:00',
  `razon` varchar(150) NOT NULL,
  `estado` enum('activo','inactivo','expulsado','cancelado') NOT NULL default 'activo',
  `tiempo` varchar(20) NOT NULL default '0',
  `IP` varchar(12) NOT NULL default '0',
  `cargo` tinyint(3) unsigned NOT NULL default '12',
  `motivo` text NOT NULL,
  PRIMARY KEY  (`ID`),
  KEY `user_ID` (`user_ID`),
  KEY `estado` (`estado`),
  KEY `IP` (`IP`)
) ENGINE=MyISAM AUTO_INCREMENT=346 DEFAULT CHARSET=latin1;

#
# Source for table vp_cat
#

CREATE TABLE `vp_cat` (
  `ID` tinyint(3) NOT NULL auto_increment,
  `url` varchar(80) NOT NULL default '',
  `nombre` varchar(80) NOT NULL default '',
  `num` smallint(5) NOT NULL default '0',
  `nivel` tinyint(3) NOT NULL default '0',
  `tipo` enum('empresas','docs','cargos') NOT NULL default 'empresas',
  `orden` tinyint(3) NOT NULL default '0',
  PRIMARY KEY  (`ID`),
  KEY `url` (`url`,`tipo`),
  KEY `tipo` (`tipo`),
  KEY `orden` (`orden`)
) ENGINE=MyISAM AUTO_INCREMENT=32 DEFAULT CHARSET=latin1;

#
# Source for table vp_config
#

CREATE TABLE `vp_config` (
  `ID` tinyint(3) NOT NULL auto_increment,
  `dato` varchar(30) NOT NULL default '',
  `valor` text NOT NULL,
  `autoload` enum('si','no') NOT NULL default 'no',
  PRIMARY KEY  (`ID`),
  UNIQUE KEY `dato` (`dato`),
  KEY `autoload` (`autoload`)
) ENGINE=MyISAM AUTO_INCREMENT=48 DEFAULT CHARSET=latin1;

#
# Source for table vp_cuentas
#

CREATE TABLE `vp_cuentas` (
  `ID` mediumint(8) NOT NULL auto_increment,
  `nombre` varchar(25) NOT NULL,
  `user_ID` mediumint(8) NOT NULL default '0',
  `pols` int(10) NOT NULL default '0',
  `nivel` tinyint(3) NOT NULL default '0',
  `time` datetime NOT NULL default '0000-00-00 00:00:00',
  `exenta_impuestos` tinyint(1) NOT NULL default '0',
  PRIMARY KEY  (`ID`),
  UNIQUE KEY `nombre` (`nombre`),
  KEY `user_ID` (`user_ID`),
  KEY `nivel` (`nivel`)
) ENGINE=MyISAM AUTO_INCREMENT=695 DEFAULT CHARSET=latin1;

#
# Source for table vp_diputados
#

CREATE TABLE `vp_diputados` (
  `ID` smallint(5) NOT NULL auto_increment,
  `ID_partido` smallint(5) NOT NULL default '0',
  `user_ID` mediumint(8) NOT NULL default '0',
  PRIMARY KEY  (`ID`),
  UNIQUE KEY `user_ID` (`user_ID`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

#
# Source for table vp_elec
#

CREATE TABLE `vp_elec` (
  `ID` smallint(5) NOT NULL auto_increment,
  `time` datetime NOT NULL default '0000-00-00 00:00:00',
  `tipo` enum('pres','parl') NOT NULL default 'pres',
  `num_votantes` mediumint(8) NOT NULL default '0',
  `escrutinio` text NOT NULL,
  `num_votos` smallint(5) NOT NULL default '0',
  `pols_init` mediumint(8) NOT NULL default '0',
  PRIMARY KEY  (`ID`),
  KEY `time` (`time`,`tipo`)
) ENGINE=MyISAM AUTO_INCREMENT=11 DEFAULT CHARSET=latin1;

#
# Source for table vp_elecciones
#

CREATE TABLE `vp_elecciones` (
  `ID` mediumint(8) NOT NULL auto_increment,
  `ID_partido` varchar(800) NOT NULL default '0',
  `user_ID` mediumint(8) NOT NULL default '0',
  `nav` varchar(255) NOT NULL default '',
  `IP` varchar(30) NOT NULL default '',
  `time` datetime NOT NULL default '0000-00-00 00:00:00',
  PRIMARY KEY  (`ID`),
  UNIQUE KEY `user_ID` (`user_ID`)
) ENGINE=MyISAM AUTO_INCREMENT=883 DEFAULT CHARSET=latin1;

#
# Source for table vp_empresas
#

CREATE TABLE `vp_empresas` (
  `ID` smallint(5) NOT NULL auto_increment,
  `url` varchar(40) NOT NULL default '',
  `nombre` varchar(40) NOT NULL default '',
  `user_ID` mediumint(8) NOT NULL default '0',
  `descripcion` text NOT NULL,
  `web` varchar(200) NOT NULL default '',
  `cat_ID` tinyint(3) NOT NULL default '0',
  `time` datetime NOT NULL default '0000-00-00 00:00:00',
  `pv` int(10) unsigned NOT NULL default '0',
  PRIMARY KEY  (`ID`),
  UNIQUE KEY `url` (`url`,`cat_ID`),
  KEY `cat_ID` (`cat_ID`)
) ENGINE=MyISAM AUTO_INCREMENT=1144 DEFAULT CHARSET=latin1;

#
# Source for table vp_estudios
#

CREATE TABLE `vp_estudios` (
  `ID` tinyint(3) unsigned NOT NULL auto_increment,
  `nombre` varchar(30) NOT NULL default '',
  `tiempo` mediumint(9) NOT NULL default '86400',
  `nivel` tinyint(3) NOT NULL default '1',
  `num_cargo` smallint(5) NOT NULL default '1',
  `asigna` smallint(5) NOT NULL default '7',
  `salario` smallint(5) NOT NULL default '0',
  `ico` enum('true','false','') NOT NULL default 'false',
  PRIMARY KEY  (`ID`),
  KEY `nivel` (`nivel`),
  KEY `nombre` (`nombre`),
  KEY `asigna` (`asigna`)
) ENGINE=MyISAM AUTO_INCREMENT=61 DEFAULT CHARSET=latin1;

#
# Source for table vp_estudios_users
#

CREATE TABLE `vp_estudios_users` (
  `ID` bigint(20) NOT NULL auto_increment,
  `ID_estudio` smallint(5) NOT NULL default '0',
  `user_ID` mediumint(8) NOT NULL default '0',
  `time` datetime NOT NULL default '0000-00-00 00:00:00',
  `estado` enum('ok','estudiando','examen') NOT NULL default 'ok',
  `cargo` enum('0','1') NOT NULL default '0',
  `nota` decimal(3,1) unsigned default NULL,
  PRIMARY KEY  (`ID`),
  KEY `cargo` (`cargo`),
  KEY `estado` (`estado`),
  KEY `ID_estudio` (`ID_estudio`),
  KEY `user_ID` (`user_ID`)
) ENGINE=MyISAM AUTO_INCREMENT=5445 DEFAULT CHARSET=latin1;

#
# Source for table vp_examenes
#

CREATE TABLE `vp_examenes` (
  `ID` smallint(5) NOT NULL auto_increment,
  `titulo` varchar(100) NOT NULL default '',
  `descripcion` text NOT NULL,
  `user_ID` mediumint(8) NOT NULL default '0',
  `time` datetime NOT NULL default '0000-00-00 00:00:00',
  `cargo_ID` smallint(5) NOT NULL default '0',
  `nota` varchar(5) NOT NULL default '5.0',
  `num_preguntas` smallint(5) NOT NULL default '10',
  PRIMARY KEY  (`ID`),
  UNIQUE KEY `titulo` (`titulo`),
  KEY `nota` (`nota`)
) ENGINE=MyISAM AUTO_INCREMENT=46 DEFAULT CHARSET=latin1 COMMENT='(ID, titulo, descripcion, user_ID, time, cargo_ID, nota, num';

#
# Source for table vp_examenes_preg
#

CREATE TABLE `vp_examenes_preg` (
  `ID` mediumint(8) NOT NULL auto_increment,
  `examen_ID` smallint(5) NOT NULL default '0',
  `user_ID` mediumint(8) NOT NULL default '0',
  `time` datetime NOT NULL default '0000-00-00 00:00:00',
  `pregunta` text NOT NULL,
  `respuestas` text NOT NULL,
  `tiempo` varchar(6) NOT NULL default '',
  PRIMARY KEY  (`ID`),
  KEY `examen_ID` (`examen_ID`)
) ENGINE=MyISAM AUTO_INCREMENT=499 DEFAULT CHARSET=latin1 COMMENT='(ID, examen_ID, user_ID, time, pregunta, respuestas, tiempo)';

#
# Source for table vp_foros
#

CREATE TABLE `vp_foros` (
  `ID` smallint(5) NOT NULL auto_increment,
  `url` varchar(50) NOT NULL default '',
  `title` varchar(50) NOT NULL default '',
  `descripcion` varchar(255) NOT NULL default '',
  `acceso` tinyint(3) unsigned NOT NULL default '1',
  `time` smallint(3) NOT NULL default '1',
  `estado` enum('ok','eliminado') NOT NULL default 'ok',
  `acceso_msg` tinyint(3) unsigned NOT NULL default '1',
  PRIMARY KEY  (`ID`),
  UNIQUE KEY `url` (`url`),
  KEY `estado` (`estado`)
) ENGINE=MyISAM AUTO_INCREMENT=26 DEFAULT CHARSET=latin1;

#
# Source for table vp_foros_hilos
#

CREATE TABLE `vp_foros_hilos` (
  `ID` mediumint(8) NOT NULL auto_increment,
  `sub_ID` smallint(5) NOT NULL default '0',
  `url` varchar(80) NOT NULL default '',
  `user_ID` mediumint(8) NOT NULL default '0',
  `title` varchar(80) NOT NULL default '',
  `time` datetime NOT NULL default '0000-00-00 00:00:00',
  `time_last` datetime NOT NULL default '0000-00-00 00:00:00',
  `text` text NOT NULL,
  `cargo` tinyint(3) NOT NULL default '0',
  `num` smallint(5) NOT NULL default '0',
  `estado` enum('ok','borrado') NOT NULL default 'ok',
  PRIMARY KEY  (`ID`),
  UNIQUE KEY `url` (`url`),
  KEY `sub_ID` (`sub_ID`),
  KEY `time_last` (`time_last`),
  KEY `estado` (`estado`)
) ENGINE=MyISAM AUTO_INCREMENT=6640 DEFAULT CHARSET=latin1;

#
# Source for table vp_foros_msg
#

CREATE TABLE `vp_foros_msg` (
  `ID` int(10) unsigned NOT NULL auto_increment,
  `hilo_ID` mediumint(8) NOT NULL default '0',
  `user_ID` mediumint(8) unsigned NOT NULL default '0',
  `time` datetime NOT NULL default '0000-00-00 00:00:00',
  `text` text NOT NULL,
  `cargo` tinyint(3) unsigned NOT NULL default '1',
  `estado` enum('ok','borrado') NOT NULL default 'ok',
  `time2` datetime NOT NULL default '0000-00-00 00:00:00',
  PRIMARY KEY  (`ID`),
  KEY `foro_ID` (`hilo_ID`),
  KEY `time` (`time`),
  KEY `estado` (`estado`)
) ENGINE=MyISAM AUTO_INCREMENT=67659 DEFAULT CHARSET=latin1;

#
# Source for table vp_log
#

CREATE TABLE `vp_log` (
  `ID` bigint(12) unsigned NOT NULL auto_increment,
  `time` datetime NOT NULL default '0000-00-00 00:00:00',
  `user_ID` mediumint(8) unsigned NOT NULL default '0',
  `user_ID2` mediumint(8) unsigned NOT NULL default '0',
  `accion` tinyint(3) unsigned NOT NULL default '0',
  `dato` mediumint(8) NOT NULL default '0',
  PRIMARY KEY  (`ID`),
  KEY `time` (`time`)
) ENGINE=MyISAM AUTO_INCREMENT=5049 DEFAULT CHARSET=latin1;

#
# Source for table vp_mapa
#

CREATE TABLE `vp_mapa` (
  `ID` smallint(5) unsigned NOT NULL auto_increment,
  `pos_x` tinyint(2) NOT NULL default '1',
  `pos_y` tinyint(2) NOT NULL default '1',
  `size_x` tinyint(2) NOT NULL default '1',
  `size_y` tinyint(2) NOT NULL default '1',
  `user_ID` mediumint(8) NOT NULL default '1',
  `nick` varchar(20) NOT NULL default '',
  `link` varchar(90) NOT NULL default '',
  `text` varchar(90) NOT NULL default '',
  `time` datetime NOT NULL default '0000-00-00 00:00:00',
  `pols` mediumint(8) NOT NULL default '0',
  `color` char(3) NOT NULL default '',
  `estado` enum('p','v','e') NOT NULL default 'p',
  `superficie` smallint(4) NOT NULL default '0',
  PRIMARY KEY  (`ID`),
  KEY `pos_x` (`pos_x`),
  KEY `pos_y` (`pos_y`),
  KEY `estado` (`estado`),
  KEY `user_ID` (`user_ID`)
) ENGINE=MyISAM AUTO_INCREMENT=1529 DEFAULT CHARSET=latin1;

#
# Source for table vp_mercado
#

CREATE TABLE `vp_mercado` (
  `ID` mediumint(8) NOT NULL auto_increment,
  `user_ID` mediumint(8) NOT NULL default '0',
  `title` varchar(90) NOT NULL default '',
  `descripcion` text NOT NULL,
  `pols` mediumint(8) NOT NULL default '0',
  `tipo` enum('subasta','venta','compra') NOT NULL default 'subasta',
  `time` datetime NOT NULL default '0000-00-00 00:00:00',
  `estado` enum('ok','old') NOT NULL default 'ok',
  PRIMARY KEY  (`ID`),
  KEY `tipo` (`tipo`,`estado`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

#
# Source for table vp_partidos
#

CREATE TABLE `vp_partidos` (
  `ID` smallint(5) NOT NULL auto_increment,
  `ID_presidente` mediumint(7) NOT NULL default '0',
  `fecha_creacion` datetime NOT NULL default '0000-00-00 00:00:00',
  `siglas` varchar(12) NOT NULL default '',
  `nombre` varchar(50) NOT NULL,
  `descripcion` text NOT NULL,
  `estado` enum('ok') NOT NULL default 'ok',
  PRIMARY KEY  (`ID`),
  UNIQUE KEY `siglas` (`siglas`),
  UNIQUE KEY `ID_presidente` (`ID_presidente`),
  KEY `estado` (`estado`)
) ENGINE=MyISAM AUTO_INCREMENT=1185 DEFAULT CHARSET=latin1;

#
# Source for table vp_partidos_listas
#

CREATE TABLE `vp_partidos_listas` (
  `ID` smallint(5) NOT NULL auto_increment,
  `ID_partido` smallint(5) NOT NULL default '0',
  `user_ID` mediumint(8) NOT NULL default '0',
  `orden` tinyint(3) NOT NULL default '0',
  PRIMARY KEY  (`ID`),
  UNIQUE KEY `user_ID` (`user_ID`)
) ENGINE=MyISAM AUTO_INCREMENT=48 DEFAULT CHARSET=latin1;

#
# Source for table vp_pujas
#

CREATE TABLE `vp_pujas` (
  `ID` mediumint(8) unsigned NOT NULL auto_increment,
  `mercado_ID` tinyint(3) unsigned NOT NULL default '0',
  `user_ID` mediumint(8) unsigned NOT NULL default '0',
  `pols` mediumint(8) unsigned NOT NULL default '0',
  `time` datetime NOT NULL default '0000-00-00 00:00:00',
  PRIMARY KEY  (`ID`),
  KEY `mercado_ID` (`mercado_ID`),
  KEY `time` (`time`)
) ENGINE=MyISAM AUTO_INCREMENT=2109 DEFAULT CHARSET=latin1;

#
# Source for table vp_ref
#

CREATE TABLE `vp_ref` (
  `ID` smallint(5) NOT NULL auto_increment,
  `pregunta` varchar(255) NOT NULL default '',
  `descripcion` text NOT NULL,
  `respuestas` text NOT NULL,
  `time` datetime NOT NULL default '0000-00-00 00:00:00',
  `time_expire` datetime NOT NULL default '0000-00-00 00:00:00',
  `user_ID` mediumint(8) NOT NULL default '0',
  `estado` enum('ok','end') NOT NULL default 'ok',
  `num` smallint(5) NOT NULL default '0',
  `tipo` enum('sondeo','referendum','parlamento') NOT NULL default 'sondeo',
  PRIMARY KEY  (`ID`),
  KEY `tipo` (`tipo`)
) ENGINE=MyISAM AUTO_INCREMENT=460 DEFAULT CHARSET=latin1;

#
# Source for table vp_ref_votos
#

CREATE TABLE `vp_ref_votos` (
  `ID` mediumint(8) NOT NULL auto_increment,
  `user_ID` mediumint(8) NOT NULL default '0',
  `ref_ID` smallint(5) NOT NULL default '0',
  `voto` tinyint(3) NOT NULL default '0',
  PRIMARY KEY  (`ID`),
  KEY `ref_ID` (`ref_ID`)
) ENGINE=MyISAM AUTO_INCREMENT=11773 DEFAULT CHARSET=latin1;

#
# Source for table vp_transacciones
#

CREATE TABLE `vp_transacciones` (
  `ID` int(12) unsigned NOT NULL auto_increment,
  `pols` int(10) NOT NULL default '0',
  `emisor_ID` mediumint(8) NOT NULL default '0',
  `receptor_ID` mediumint(8) NOT NULL default '0',
  `concepto` varchar(90) NOT NULL default '',
  `time` datetime NOT NULL default '0000-00-00 00:00:00',
  PRIMARY KEY  (`ID`),
  KEY `emisor_ID` (`emisor_ID`),
  KEY `receptor_ID` (`receptor_ID`),
  KEY `time` (`time`)
) ENGINE=MyISAM AUTO_INCREMENT=21200 DEFAULT CHARSET=latin1;

/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;
/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
