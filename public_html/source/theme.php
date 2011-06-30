<?php 
if ((!$txt) OR ($_SERVER['HTTP_HOST'] == 'ninguno.virtualpol.com')) { header('HTTP/1.1 301 Moved Permanently'); header('Location: http://www.virtualpol.com/'); exit; }
$kw = '';

if ($txt_title) { 
	$txt_title .= ' | '.PAIS.' | VirtualPol'; 
} else { 	//home
	$txt_title = ($pol['config']['pais_des']?$pol['config']['pais_des'].' de '.PAIS.' '.$kw.'| Comunidad VirtualPol':PAIS.' '.$kw.'| Comunidad VirtualPol');
}
if (!$txt_description) { $txt_description = $txt_title.' - '.$kw.PAIS.' | VirtualPol'; }

if ($pol['config']['bg']) { $body_bg = COLOR_BG.' url(\''.IMG.'bg/'.$pol['config']['bg'].'\') repeat fixed top left'; } else { $body_bg = COLOR_BG; }
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<title><?=$txt_title?></title>
<meta http-equiv="content-type" content="text/html; charset=UTF-8" />
<meta name="language" content="es_ES" />
<meta name="description" content="<?=$txt_description?>" />
<link href="<?=IMG?>style.css" rel="stylesheet" type="text/css" />
<link rel="stylesheet" type="text/css" media="screen" href="<?=IMG?>superfish.css" /> 
<script type="text/javascript" src="http://ajax.googleapis.com/ajax/libs/jquery/1.4.2/jquery.min.js"></script>
<script type="text/javascript" src="<?=IMG?>superfish.js"></script> 

<script type="text/javascript">
$(document).ready(function(){ 
	$("ul.sfn-menu").superfish(); 
}); 
defcon = <?=$pol['config']['defcon']?>;
IMG = "<?=IMG?>";
window.google_analytics_uacct = "UA-59186-46";
</script>

<style type="text/css">
body { background: <?=$body_bg?>; }
div#footer, div.column, div.content, div#header {
border: 1px solid #000;
}
</style>

<?=$txt_header?>
<link rel="shortcut icon" href="/favicon.ico" /> 
</head>

<body class="fullwidth">
<div id="container">
<div id="header">
<div id="header-in">

<?php

// ARREGLAR: este pedazo de trozo de codigo es un lamentable pero histórico trozo primienio. De las primeras lineas en construirse allá por el 2008. Hay que hacerlo de nuevo cuanto antes.

unset($txt_header);
if ($pol['msg'] > 0) { $num_msg = '<b style="font-size:18px;">' . $pol['msg'] . '</b>'; } else { $num_msg = $pol['msg']; }
if ($pol['estado'] == 'ciudadano') { // ciudadano
	$nick_lower = strtolower($pol['nick']);
	
	$elecciones = '';
	if ($pol['config']['elecciones_estado'] == 'normal') {  
		$elecciones_quedan = duracion(strtotime($pol['config']['elecciones_inicio']) - time());
		$elecciones = ' <a href="/elecciones/">Elecciones en <b style="font-size:18px;">' . $elecciones_quedan . '</b></a> |'; 
	} elseif ($pol['config']['elecciones_estado'] == 'elecciones') {  
		$elecciones_quedan = (strtotime($pol['config']['elecciones_inicio']) + $pol['config']['elecciones_duracion']) - time();
		switch ($pol['config']['elecciones']) {
			case 'pres1': $elecciones = ' <a href="/elecciones/" style="color:red;"><b>1&ordf; Vuelta en curso</b>, queda <b style="font-size:18px;">' .  duracion($elecciones_quedan - 86400) . '</b></a> |';  break;
			case 'pres2': $elecciones = ' <a href="/elecciones/" style="color:red;"><b>2&ordf; Vuelta en curso</b>, queda <b style="font-size:18px;">' .  duracion($elecciones_quedan) . '</b></a> |'; break;
			case 'parl': $elecciones = ' <a href="/elecciones/" style="color:blue;"><b>Elecciones en curso</b>, queda <b style="font-size:18px;">' .  duracion($elecciones_quedan) . '</b></a> |';  break;
		}
	}
	if ($pol['cargo']) { $cargo_icono = ' <img src="'.IMG.'cargos/' . $pol['cargo'] . '.gif" border="0" />'; } else { $cargo_icono = ''; }
	$txt_perfil = '<a href="/perfil/' . $pol['nick'] . '/">' . $pol['nick'] . ' ' . $cargo_icono . '</a>'.(ECONOMIA?' | <a href="/pols/"><b>' . pols($pol['pols']) . '</b> ' . MONEDA . '</a>':'').' | <a href="/msg/" title="Mensajes">(' . $num_msg . ') <img src="'.IMG.'email.gif" alt="Mensajes" border="0" style="margin-bottom:-5px;" /></a> | <a href="/foro/mis-respuestas/" title="Respuestas a tus mensajes en el foro">Resp</a> |' . $elecciones . ' <a href="/accion.php?a=logout">Salir</a>';} elseif ($pol['estado'] == 'extranjero') { // extranjero
	$txt_perfil = '<a href="http://'.strtolower($pol['pais']).'.virtualpol.com/perfil/'.$pol['nick'].'/">'.$pol['nick'].'</a> <img src="'.IMG.'cargos/99.gif" style="margin-bottom:-2px;" border="0" /> (<b class="extranjero">Extranjero</b>) |  <a href="http://'.strtolower($pol['pais']).'.virtualpol.com/msg/" title="Mensajes">(' . $num_msg . ') <img src="'.IMG.'email.gif" alt="Mensajes" border="0" style="margin-bottom:-5px;" /></a> | <a href="/accion.php?a=logout">Salir</a>';
} elseif ($pol['estado'] == 'turista') { // TURISTA
	$txt_perfil = $pol['nick'] . ' (<b class="turista">Turista</b>) ' . $pol['tiempo_ciudadanizacion'] . ' | ' . boton('Solicitar Ciudadania', REGISTRAR) . ' | <a href="/accion.php?a=logout">Salir</a>';
} elseif ($pol['estado'] == 'kickeado') { // KICKEADO
	$txt_perfil = $pol['nick'] . ' (<b class="expulsado">Kickeado</b>) | <a href="/control/kick/"><b>Ver Kicks</b></a> | <a href="http://'.strtolower($pol['pais']).'.virtualpol.com/msg/" title="Mensajes">(' . $num_msg . ') <img src="'.IMG.'email.gif" alt="Mensajes" border="0" style="margin-bottom:-5px;" /></a>';
} elseif ($pol['estado'] == 'expulsado') { // EXPULSADO
	$txt_perfil = $pol['nick'] . ' (<b class="expulsado">Expulsado</b>)';
} elseif ($pol['nick']) { // sin identificar, login OK
	$txt_perfil = '<b>' . $pol['nick'] . '</b> (<span class="infog"><b>Turista</b></span>) <span class="azul">' . boton('Solicitar Ciudadania', REGISTRAR) . '</span> | <a href="/accion.php?a=logout">Salir</a>';
} else { // sin identificar, sin login
	$txt_perfil = '
<script type="text/javascript" src="'.IMG.'md5.js"></script> 
<script type="text/javascript">
function vlgn (objeto) { if ((objeto.value == "Usuario") || (objeto.value == "123")) { objeto.value = ""; } }
</script>
<span style="float:right;margin-top:-3px;">
<form action="'.REGISTRAR.'login.php?a=login" method="post">
<input name="url" value="' . base64_encode('http://'.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI']) . '" type="hidden" />
<input name="user" value="Usuario" size="8" maxlength="20" onfocus="vlgn(this)" type="text" />
<input id="login_pass" name="pass" value="123" size="10" maxlength="200" onfocus="vlgn(this)" type="password" />
<input type="submit" value="Entrar" onclick="$(\'#login_pass\').val(hex_md5($(\'#login_pass\').val()));$(\'#login_pass\').attr(\'name\', \'pass_md5\');" /></form>
</span>
<span class="azul"><a href="'.REGISTRAR.'"><b>Crear Ciudadano!</b></a></span> &nbsp; <a href="/info/recuperar-login/"><acronym title="Recuperar contrase&ntilde;a">?</acronym></a> &nbsp;';

}
?>
<div style="margin:10px 0 2px 0;">
<table border="0" cellpadding="0" cellspacing="0" width="100%">
<tr>


<td nowrap="nowrap">
<ul class="sfn-menu">
	<li class="current">
		<a href="http://www.virtualpol.com/" title="VirtualPol">&#9660; <img src="<?=IMG?>virtualpol-logo-cuadrado-40.gif" border="0" alt="VirtualPol" style="margin:-15px 0;" /></a>
			<ul>
<?php foreach ($vp['paises'] AS $pais) { if ($pais != PAIS) { echo '<li><a href="http://'.strtolower($pais).'.virtualpol.com/">Plataforma '.$pais.'</a></li>'; } } ?>
				<li><a href="http://desarrollo.virtualpol.com/">Blog Desarrollo</a></li>
				<li><a href="http://code.google.com/p/virtualpol/">C&oacute;digo fuente</a></li>
				<li><a href="https://www.ohloh.net/p/virtualpol/contributors">Desarrollo</a></li>
				<li><a href="http://www.virtualpol.com/manual">Manual de ayuda</a></li>
			</ul>
	</li>
</ul>

<a href="/" title="Bandera de <?=PAIS?>"><img src="<?=IMG?>banderas/<?=PAIS?>_60.gif" border="0" alt="Bandera de <?=PAIS?>" style="margin:-6px 0 2px 0;" /></a>
</td>

<td align="right" nowrap="nowrap"><?=$txt_perfil?></td>

<td align="right" width="180"><form action="/buscar/" id="cse-search-box">
<input type="hidden" name="cx" value="000141954329957006250:h-_yuvq_rwk" />
<input type="hidden" name="cof" value="FORID:9" />
<input type="hidden" name="ie" value="UTF-8" />
<input type="text" name="q" size="3" />
<input type="submit" name="sa" value="Buscar" />
</form></td>

</tr>
</table>
</div>


</div>
</div>
<div id="content-wrap" class="clear lcol">
<div class="column">
<div class="column-in">


<dl id="menu">
<ul class="sf-menu sf-vertical">
			<li class="current">
				<a href="/">Comunicaci&oacute;n</a>
				<ul>
					<li><a href="/chats/">Chats</a></li>
					<li><a href="/foro/">Foro</a></li>
					<li><a href="/notas/">Notas</a></li>
				</ul>
			</li>
			<li>
				<a href="#">Informaci&oacute;n</a>
					<ul>
						<li><a href="/info/censo/">Censo <span class="md">(<?=$pol['config']['info_censo']?>)</span></a></li>
						<li><a href="/doc/">Documentos <span class="md">(<?=$pol['config']['info_documentos']?>)</span></a></li>
						<li><a href="/estadisticas/">Estad&iacute;sticas</a></li>
						<?=(ECONOMIA?'<li><a href="/geolocalizacion/">GeoLocalizaci&oacute;n</a></li>':'')?>
						<li><a href="/log-eventos/">Log de eventos</a></li>
						<li><a href="/historia/">Historia</a></li>
					</ul>
			</li>
			<li>
				<a href="#">Democracia</a>
				<ul>
<?php if (ECONOMIA) { ?>
					<li><a href="/control/"><span style="float:right;">&#9658;</span><b>Control</b></a>
						<ul>
							<li><a href="/control/gobierno/">Despacho Oval</a></li>
							<li><a href="/control/kick/">Kicks</a></li>
							<li><a href="/control/expulsiones/">Expulsiones</a></li>
							<li><a href="/control/judicial/">Judicial</a></li>
						</ul>
					</li>
					<li><a href="/elecciones/"><b>Elecciones</b></a></li>
					<li><a href="/votacion/">Votaciones <span class="md">(<?=$pol['config']['info_consultas']?>)</span></a></li>
					<li><a href="/cargos/">Cargos</a></li>
					<li><a href="/partidos/"><?=NOM_PARTIDOS?> <span class="md">(<?=$pol['config']['info_partidos']?>)</span></a></li>
					<li><a href="/poderes/">Poderes</a></li>
<?php } else { ?>
					<li><a href="/control/"><span style="float:right;">&#9658;</span><b>Control</b></a>
						<ul>
							<li><a href="/control/gobierno/">Control</a></li>
							<li><a href="/control/kick/">Kicks</a></li>
							<li><a href="/control/expulsiones/">Expulsiones</a></li>
						</ul>
					</li>
					<li><a href="/elecciones/"><b>Elecciones</b></a></li>
					<li><a href="/votacion/">Votaciones <span class="md">(<?=$pol['config']['info_consultas']?>)</span></a></li>
					<li><a href="/cargos/">Cargos</a></li>
					<li><a href="/partidos/"><?=NOM_PARTIDOS?> <span class="md">(<?=$pol['config']['info_partidos']?>)</span></a></li>
<?php } ?>
				</ul>
			</li>

<?php if (ECONOMIA) { ?>
			<li>
				<a href="#">Econom&iacute;a</a>
				<ul>
					<li><a href="/empresas/"><b>Empresas</b></a></li>
					<li><a href="/pols/cuentas/">Cuentas</a></li>
					<li><a href="/pols/cuentas/1/">Cuenta <em>Gobierno</em></a></li>
					<li><a href="/subasta/">Subastas</a></li>
					<li><a href="/mapa/">Mapa</a></li>
				</ul>
			</li>
			<?php } if ($pol['estado'] == 'ciudadano') { ?>
			<li>
				<a href="#"><?=$pol['nick']?></a>
				<ul>
					<li><a href="/perfil/<?=$pol['nick']?>/">Perfil</a></li>
					<li><a href="/examenes/">Ex&aacute;menes</a></li>
					<li><a href="/foro/mis-respuestas/">Respuestas</a></li>
					<?=(ECONOMIA?'<li><a href="/pols/">Dinero</a></li><li><a href="/mapa/propiedades/">Parcelas</a></li><li><a href="http://aziroet.com/blog/aziroet-quiere-ser-la-plataforma-web-de-tu-blog.php">Crear Blog</a></li>':'')?>
				</ul>
			</li>			<?php } ?><?php if ($pol['config']['info_consultas'] > 0) { ?>
			<li>
				<a href="/votacion/">Votaciones (<?=$pol['config']['info_consultas']?>)</a>
			</li><?php } ?>
			
		</ul></dd></dl>


<?php
echo '<hr style="margin:5px 20px -5px -5px;color:#FF6;" />

<div id="palabras">';

foreach(explode(";", $pol['config']['palabras']) as $t) {
	$t = explode(":", $t);
	if ($t[0] == $pol['user_ID']) { $edit = ' <a href="/subasta/editar/" class="gris">#</a>'; } else { $edit = ''; }
	if ($t[1]) { echo '<a href="http://'.$t[1].'"><b>'.$t[2].'</b></a>'.$edit."<br />\n"; } 
	else { echo $t[2].$edit."<br />\n"; }
}

if (ECONOMIA) {
	echo '<a href="/mapa/" class="gris" style="float:right;margin:0 11px 0 0;">Mapa</a><a href="/subasta/" class="gris" style="margin:0 0 0 -3px;">Subasta</a>';
	if (!isset($cuadrado_size)) {
		$cuadrado_size = 10;
		include('inc-mapa.php');
		echo '<div style="margin:0 0 0 -4px;">'.$txt_mapa.'</div>';
	}
}
echo '</div>';
?>






</div>
</div>
<div class="content">
<div class="content-in">


<?=$txt.$kw?>

</div>
</div>
</div>
<div class="clear"></div>
<div id="footer">
<div id="footer-in">



<div>
<span style="float:right;font-size:14px;">
<?php
unset($txt);
if (isset($pol['user_ID'])) {
	$mtime = explode(' ', microtime()); 
	$tiempofinal = $mtime[1] + $mtime[0]; 
	$tiempototal = round(($tiempofinal-$tiempoinicial)*1000); 
	echo ($pol['user_ID']==1?$tiempototal.'ms ':'');
} else {
	// Enlaces de GONZO, solo lo ven los no-registrados, no quitar por favor :))))
	echo '
<a href="http://www.teoriza.com/">Teoriza</a> &middot; 
<a href="http://www.eventuis.com/">eventos</a>
';
}
?> | 
<a href="http://www.virtualpol.com/legal" title="Condiciones del Servicio">TOS</a> | 
<a href="http://www.virtualpol.com/manual"><b>Ayuda</b></a> &nbsp; 
&nbsp; 2008-2011 <b><a href="http://www.virtualpol.com/" style="font-size:16px;">VirtualPol</a></b></span>
<b><?=PAIS?></b> <span style="font-size:11px;">DEFCON <b><?=$pol['config']['defcon']?></b></span>

<?php
if (ECONOMIA) {
	echo '<span class="amarillo" id="pols_frase"><b>'.$pol['config']['pols_frase'].'</b>';
	if ($pol['config']['pols_fraseedit'] == $pol['user_ID']) { echo ' <a href="/subasta/editar/" class="gris">#</a>'; }
}
?>
</span>

</div>
</div>
</div>
</div>

<div id="pnick" class="azul" style="display:none;opacity:0.9;"></div>

<script type="text/javascript" src="<?=IMG?>scripts2.js"></script>
<script type="text/javascript">
var _gaq = _gaq || [];
_gaq.push(['_setAccount', 'UA-59186-46']);
_gaq.push(['_setDomainName', '.virtualpol.com']);
_gaq.push(['_trackPageview']);
(function() {
var ga = document.createElement('script'); ga.type = 'text/javascript'; ga.async = true;
ga.src = ('https:' == document.location.protocol ? 'https://ssl' : 'http://www') + '.google-analytics.com/ga.js';
var s = document.getElementsByTagName('script')[0]; s.parentNode.insertBefore(ga, s);
})();
</script>


</body>
</html>
<?php if ($link) { mysql_close($link); } ?>