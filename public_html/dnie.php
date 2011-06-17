<?php
// Conecta con la base de datos, define constantes, carga el sistema de usuarios de VP, hace un par de gestiones rutinarias.
include('inc-login.php');

$txt_title = 'Autentificaci&oacute;n DNIe'; // Define el titulo de la pagina finalmente formada.

// Obtiene si el usuario ya está autentificado o no.
$dnie_autentificado = false;
$result = mysql_query("SELECT ID FROM users WHERE ID = '".$pol['user_ID']."' AND dnie = 'true'", $link);
while ($r = mysql_fetch_array($result)) { $dnie_autentificado = true; }

if ((isset($pol['user_ID'])) AND ($dnie_autentificado == false)) {
	// Es un usuario y no está autentificado con DNIe 
	
	// Plugin de Tractis para PHP. Software Libre. Fuente: https://github.com/tractis/tractis_identity_verifications_for_php
	require('img/tractis_identity/tractis_identity.php');

	// POR ARREGLAR: lo ideal es que la URL de VirtualPol a la que tractis envia la información sea mediante HTTPS seguro. Estudiar y arreglar cuanto antes.
	$tractis_identity = new tractis_identity(CLAVE_API_TRACTIS, 'http://www.virtualpol.com/dnie.php', 'false', 'http://www.virtualpol.com/img/tractis_identity/images/trac_but_bg_lrg_b_es.png');

	// Trata la redireccion desde Tractis tras una autentificacion correcta.
	if ($data = $tractis_identity->check_auth()) { 

/* LA SIGUIENTE LINEA ES EL QUID DE LA CUESTION

Consiste en una miniaturización irreversible de la información extraida del DNIe (proveeida por la pasarela de Tractis), junto con otra información estática para evitar ataques por diccionario y hacer inéditos los hash. Se hace mediante el algoritmo de hash sha256.

EJEMPLO: 

hash('sha256', '.VirtualPol.clave_del_sistema.72135000A.JAVIER RUBALCABA RAJOY.')

Se convierte en:

da39a3ee5e6b4b0d3255bfef95606b4b0d3255bfef95601890afdd80709

Esta linea final no supone ninguna información en claro.
*/
		$dnie_check = hash('sha256', '.VirtualPol.'.CLAVE_DNIE.'.'.strtoupper($data['tractis:attribute:dni']).'.'.str_replace('+', ' ', strtoupper($data['tractis:attribute:name'])).'.');
		
		// Elimina todos los datos obtenidos del DNIe y desaparecen definitivamente a partir de aquí.
		unset($data);

		// Busca checks coincidentes (para garantizar que cada DNIe se inscribe una vez.
		$dnie_clon = false;
		$result = mysql_query("SELECT ID FROM users WHERE dnie_check = '".$dnie_check."' AND dnie = 'true' LIMIT 1", $link);
		while ($r = mysql_fetch_array($result)) { $dnie_clon = true; }

		if ($dnie_clon == true) { 
			// Persona ya identificada con otro usuario. No realiza la autentificacion. 
			$txt .= 'Ya estas autentificado con otro usuario. Envia un email a desarrollo@virtualpol.com explicando la situacion. Gracias.';

		} else {
			// Autentificacion correcta. DNIe inedito. Procede a guardar el HASH de la info extraida del DNIe (miniaturización irreversible) en base de datos.
			mysql_query("UPDATE users SET dnie = 'true', dnie_check = '".$dnie_check."' WHERE ID = '".$pol['user_ID']."' LIMIT 1", $link);

			// Carga funciones extra para ejecutar evento_chat() que envia un mensaje en el chat principal.
			include('source/inc-functions-accion.php'); // functions extra
			evento_chat('<b>[#] '.crear_link($pol['nick']).' se ha <a href="http://www.virtualpol.com/dnie.php">autentificado</a> correctamente.</b>');

			header('Location: http://www.virtualpol.com/dnie.php');
			mysql_close($link);
			exit;
		}

	} else { 
		// Autentificar.
		$txt .= 'Usuario sin autentificar.<br />'.$tractis_identity->show_form(); // Muestra el boton de autentificación de Tractis.
	}

} elseif (isset($pol['user_ID'])) {
	$txt .= 'Est&aacute;s autentificado correctamente.';
} else {
	$txt .= 'No est&aacute;s registrado, debes crear un usuario.';
}

// Presentacion, diseño y nada más.
$txt = '
<style type="text/css">.content { text-align:center; width:500px; margin: 0 auto; padding: 2px 12px 30px 12px; }</style>

<h1>Autentificaci&oacute;n DNIe</h1>

<p>La autentificaci&oacute;n mediante DNIe (y otros certificados) es el futuro, pues permite identificar a una persona fisica con seguridad.</p>

<p>Estado: <b>'.$txt.'</b></p>

<p>&nbsp;</p>

<p><em>Seguridad:</em> no se almacenar&aacute; ningun dato proporcionado por el DNIe u otro certificado en ninguna parte del sistema. Tan solo se almacena una miniaturizaci&oacute;n irreversible de esta informaci&oacute;n. De esta forma incluso ante el peor ataque posible (acceso a contrase&ntilde;as, claves y base de datos) no se podr&iacute;a obtener informaci&oacute;n alguna. La pasarela de autentificaci&oacute;n se conf&iacute;a totalmente a una empresa importante del sector llamada <a href="http://www.tractis.com/">Tractis</a>.</p>

<p>Puedes ver el codigo fuente de <a href="http://code.google.com/p/virtualpol/source/browse/trunk/public_html/dnie.php">esta aplicaci&oacute;n</a>.</p>';

// Carga el dise&ntilde;o completo de VirtualPol. Mucho HTML, CSS y poco m&aacute;s.
include('theme.php');
?>