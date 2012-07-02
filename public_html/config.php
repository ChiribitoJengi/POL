<?php
/* The source code packaged with this file is Free Software, Copyright (C) 2008 by
** Javier González González <desarrollo AT virtualpol.com> <gonzomail AT gmail.com>
** It's licensed under the GNU GENERAL PUBLIC LICENSE v3 unless stated otherwise.
** You can get copies of the licenses here: http://www.gnu.org/licenses/gpl.html
** The source: http://www.virtualpol.com/codigo - TOS: http://www.virtualpol.com/TOS
** VirtualPol, The first Democratic Social Network - http://www.virtualpol.com
*/


// INICIALIZACION
define('RAIZ', dirname(__FILE__).'/');
include(RAIZ.'config-pwd.php');						// Passwords y claves
include(RAIZ.'source/inc-functions.php');			// Funciones basicas
$link = conectar();									// Conecta MySQL
$pais = explodear('.', $_SERVER['HTTP_HOST'], 0);	// Obtiene "PAIS" de "PAIS.dominio.com"


// LOAD CONFIG
$result = mysql_unbuffered_query("SELECT dato, valor FROM config WHERE pais = '".escape($pais)."' AND autoload = 'si'");
while ($r = r($result)) { 
	switch ($r['dato']) {
		case 'PAIS': define('PAIS', $r['valor']); break;
		case 'ASAMBLEA': case 'ECONOMIA':  define($r['dato'], ($r['valor']=='true'?true:false)); break;

		case 'acceso': 
			foreach(explode('|', $r['valor']) AS $item) {
				$elem = explode(';', $item);
				$elem1 = explode(':', $elem[1]);
				$vp['acceso'][$elem[0]] = array($elem1[0], $elem1[1]);
			}
			break;

		default: $pol['config'][$r['dato']] = $r['valor'];
	} 
}

// TIMEZONE
date_default_timezone_set((isset($pol['config']['timezone'])?$pol['config']['timezone']:'Europe/Madrid'));

// LENGUAJES ACTIVADOS
$vp['langs'] = array(
'es_ES'=>'Español (100%)',
'en_US'=>'English (70%)',
'fr'=>'Français (70%)',
'ca_ES'=>'Català (70%)',
'gl_ES'=>'Galego (70%)',
'eo'=>'Esperanto (40%)',
'eu'=>'Euskera (20%)',
'de_DE'=>'Deutsch (30%)',
'ja'=>'Japanese (10%)',
'pt'=>'Português (10%)',
);

// CONFIG PLATAFORMAS (pendiente de desmantelar)
$vp['paises'] = array('15M', 'Hispania', 'MIC', 'JRO', 'MCxVBC', 'ETSIIT', 'Occupy', 'PS', 'PCP');


if ($pais == 'www') {
	define('PAIS', 'Ninguno');
} elseif (!defined('PAIS')) {
	redirect('http://www.virtualpol.com');
}


// CONFIG
define('DOMAIN', 'virtualpol.com');
define('SQL', strtolower(PAIS).'_');
define('CONTACTO_EMAIL', 'desarrollo@virtualpol.com');
define('USERCOOKIE', '.'.DOMAIN);
define('HOST', $_SERVER['HTTP_HOST']);
define('VOTO_CONFIANZA_MAX', 50); // Máximo de votos de confianza emitibles
define('MP_MAX', 25); // Máximo de MP (mensajes privados) que puede enviar un ciudadano
$datos_perfil = array('Blog', 'Twitter', 'Facebook', 'Google+', '', 'Menéame');
$columnas = 14; $filas = 14; // Dimensiones mapa


// URLS (SSL, IMG, REGISTRAR)
define('REGISTRAR', 'https://www.'.DOMAIN.'/registrar/');
define('SSL_URL', 'https://www.'.DOMAIN.'/'); // SSL_URL | http://www.virtualpol.com/ = https://www.virtualpol.com/

if ($_SERVER['HTTPS']) {
	define('IMG', 'https://www.'.DOMAIN.'/img/');
} else {
	define('IMG', 'http://www.'.DOMAIN.'/img/');;
}

define('MONEDA', '<img src="'.IMG.'varios/m.gif" />');
?>