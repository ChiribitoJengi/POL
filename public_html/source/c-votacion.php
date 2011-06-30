<?php 
include('inc-login.php');

$votaciones_tipo = array('sondeo', 'referendum', 'parlamento', 'destituir', 'otorgar');


// ¿FINALIZAR VOTACIONES?
$result = mysql_query("SELECT ID, tipo, num, pregunta, ejecutar FROM votacion WHERE time_expire < '".$date."' AND estado = 'ok' AND pais = '".PAIS."'", $link);
while($r = mysql_fetch_array($result)){
	
	mysql_query("UPDATE votacion SET estado = 'end' WHERE ID = '".$r['ID']."' LIMIT 1", $link);

	@include_once('inc-functions-accion.php');

	evento_chat('<b>['.strtoupper($r['tipo']).']</b> Finalizado, resultados: <a href="/votacion/'.$r['ID'].'/"><b>'.$r['pregunta'].'</b></a> <span style="color:grey;">(votos: <b>'.$r['num'].'</b>)</span>');

	if ($r['ejecutar'] != '') { 
		// EJECUTAR ACCIONES

		$validez_voto['true'] = 0; $validez_voto['false'] = 0; $voto[0] = 0; $voto[1] = 0;
		$result2 = mysql_query("SELECT validez, voto FROM votacion_votos WHERE ref_ID = '".$r['ID']."'", $link);
		while($r2 = mysql_fetch_array($result2)) {
			$validez_voto[$r2['validez']]++;
			$voto[$r2['voto']]++;
		}

		// Determinar validez: mayoria simple = votacion nula.
		$nulo_limite = ceil(($validez_voto['true']+$validez_voto['false'])/2);
		if ($validez_voto['false'] >= $nulo_limite) { $validez = false; } else { $validez = true; }

		if (($voto[0] > $voto[1]) AND ($validez)) {
			if ($r['tipo'] == 'destituir') {
				cargo_del(explodear('|', $r['ejecutar'], 0), explodear('|', $r['ejecutar'], 1), true, '');
			} else if ($r['tipo'] == 'otorgar') {
				cargo_add(explodear('|', $r['ejecutar'], 0), explodear('|', $r['ejecutar'], 1), true, '');
			}
		}
	}
	


	// actualizar info en theme
	$result2 = mysql_query("SELECT COUNT(ID) AS num FROM votacion WHERE estado = 'ok' AND pais = '".PAIS."'", $link);
	while($r2 = mysql_fetch_array($result2)) {
		mysql_query("UPDATE ".SQL."config SET valor = '".$r2['num']."' WHERE dato = 'info_consultas' LIMIT 1", $link);
	}
}



// load user cargos
$pol['cargos'] = cargos();


if ($_GET['a'] == 'crear') {
	$txt_title = 'Crear votacion';

	foreach ($votaciones_tipo AS $tipo) {
		$a = explode('|', $vp['acceso'][$tipo]);
		if (!nucleo_acceso($a[0], $a[1])) { $disabled[$tipo] = ' disabled="disabled"'; }
	}

	// SI el usuario es SC puee hacer sondeos tambien.
	$sc = get_supervisores_del_censo();
	if (isset($sc[$pol['user_ID']])) { $disabled['sondeo'] = ''; }

	$result = mysql_query("SELECT ID FROM votacion WHERE (tipo = 'destituir' OR tipo = 'otorgar') AND estado = 'ok' LIMIT 1", $link);
	while($r = mysql_fetch_array($result)) { 
		$disabled['destituir'] = ' disabled="disabled"'; 
		$disabled['otorgar'] = ' disabled="disabled"';
	}


	$txt_header .= '<script type="text/javascript">

function cambiar_tipo_votacion(tipo) {
	$("#acceso_votar, #time_expire, #votar_form").show();
	$("#cargo_form").hide();
	switch (tipo) {
		case "parlamento": $("#acceso_votar").hide(); break;
		case "destituir": case "otorgar": $("#acceso_votar, #time_expire, #votar_form").hide(); $("#cargo_form").show(); break;
	}

}

</script>';


	$txt .= '<h1><a href="/votacion/">Votaciones</a>: Crear votaci&oacute;n</h1>
<form action="/accion.php?a=votacion&b=crear" method="post">
<table width="570"><tr><td valign="top">
<p class="azul"><b>Tipo de votaci&oacute;n</b>:<br />
<span id="tipo_select">';

	foreach ($votaciones_tipo AS $tipo) {
		$disabled['sondeo'] .= ' checked="checked"';
		$txt .= '<span style="font-size:18px;"><input type="radio" name="tipo" value="'.$tipo.'"'.$disabled[$tipo].' onclick="cambiar_tipo_votacion(\''.$tipo.'\');" />'.ucfirst($tipo).'</span><br >';
	}

$txt .= '</span><br />

<span id="time_expire">
<b>Duraci&oacute;n</b>: 
<select name="time_expire">
<option value="300">5 minutos</option>
<option value="600">10 minutos</option>
<option value="1800">30 minutos</option>
<option value="3600">1 hora</option>
<option value="86400" selected="selected">24 horas</option>
<option value="172800">2 d&iacute;as</option>
<option value="259200">3 d&iacute;as</option>
<option value="345600">4 d&iacute;as</option>
</select></span>


<span id="cargo_form" style="display:none;">
<b>Cargo</b>: 
<select name="cargo">';

$result = mysql_query("SELECT ID, nombre FROM ".SQL."estudios ORDER BY nivel DESC", $link);
while($r = mysql_fetch_array($result)) { $txt .= '<option value="'.$r['ID'].'">'.$r['nombre'].'</option>'; }

$txt .= '
</select> &nbsp; Ciudadano: <input type="text" name="nick" value="" size="10" /></span>

</p>
';


		$r['acceso_votar'] = 'ciudadanos';
		$tipos_array = nucleo_acceso('print');
		unset($tipos_array['anonimos']);
		foreach ($tipos_array AS $at => $at_var) {
			$txt_li['votar'] .= '<input type="radio" name="acceso_votar" value="'.$at.'"'.($at==$r['acceso_votar']?' checked="checked"':'').' onclick="$(\'#acceso_cfg_votar_var\').val(\''.$at_var.'\');" />'.ucfirst(str_replace("_", " ", $at)).'<br />';
		}


		$txt .= '</td><td valign="top" align="right">
		
<p id="acceso_votar" class="azul"><b>Acceso para votar:</b><br />
'.$txt_li['votar'].'
<input type="text" name="acceso_cfg_votar" size="18" maxlength="500" id="acceso_cfg_votar_var" value="'.$r['acceso_cfg_votar'].'" /></p>

</td></tr></table>

<div id="votar_form">
<p><b>Pregunta</b>: 
<input type="text" name="pregunta" size="57" maxlength="70" /></p>

<p><b>Descripci&oacute;n</b>: (siempre visible)<br />
<textarea name="descripcion" style="color: green; font-weight: bold; width: 570px; height: 250px;"></textarea></p>

<p><b>Respuestas</b>:
<ol>
<li><input type="text" name="respuesta0" size="22" maxlength="30" value="SI" /></li>
<li><input type="text" name="respuesta1" size="22" maxlength="30" value="NO" /></li>
<li><input type="text" name="respuesta2" size="22" maxlength="30" /></li>
<li><input type="text" name="respuesta3" size="22" maxlength="30" /></li>
<li><input type="text" name="respuesta4" size="22" maxlength="30" /></li>
<li><input type="text" name="respuesta5" size="22" maxlength="30" /></li>
<li><input type="text" name="respuesta6" size="22" maxlength="30" /></li>
<li><input type="text" name="respuesta7" size="22" maxlength="30" /></li>
<li><input type="text" name="respuesta8" size="22" maxlength="30" /></li>
<li><input type="text" name="respuesta9" size="22" maxlength="30" /></li>
</ol>
<ul style="margin-top:-16px;">
<li><input type="text" name="respuesta10" size="22" value="En Blanco" readonly="readonly" style="color:grey;" /></li>
</ul></p>
</div>
<p><input type="submit" value="Iniciar votaci&oacute;n" style="font-size:18px;" /> &nbsp; <a href="/votacion/"><b>Ver votaciones</b></a></p>';




} elseif ($_GET['a']) {


	$result = mysql_query("SELECT *,
(SELECT nick FROM users WHERE ID = votacion.user_ID LIMIT 1) AS nick, 
(SELECT ID FROM votacion_votos WHERE ref_ID = votacion.ID AND user_ID = '" . $pol['user_ID'] . "' LIMIT 1) AS ha_votado
FROM votacion
WHERE ID = '".$_GET['a']."' AND pais = '".PAIS."'
LIMIT 1", $link);
	while($r = mysql_fetch_array($result)) {

		if ($r['tipo'] == 'parlamento') {
			$result2 = mysql_unbuffered_query("SELECT ID FROM ".SQL."estudios_users WHERE user_ID = '" . $pol['user_ID'] . "' AND cargo = '1' AND ID_estudio = '6' LIMIT 1", $link);
			while($r2 = mysql_fetch_array($result2)){ $es_diputado = true; }
		}

		$time_expire = strtotime($r['time_expire']);
		$time_creacion = strtotime($r['time']);
		$duracion = duracion($time_expire - $time_creacion);
		$respuestas = explode("|", $r['respuestas']);
		$respuestas_num = count($respuestas) - 1;
		$txt_title = 'Votacion: ' . strtoupper($r['tipo']) . ' | ' . $r['pregunta'];

		if ($r['estado'] == 'ok') { 
			$tiempo_queda =  ' | <span style="color:blue;">Queda ' . duracion($time_expire - time()) . '</span>'; 
		} else { $tiempo_queda =  ' | <span style="color:grey;">Finalizado</span>'; }


		$txt .= '<h1><a href="/votacion/">Votaciones</a>: ' . strtoupper($r['tipo']) . ' | ' . $r['pregunta'] . $tiempo_queda . '</h1>

<div class="amarillo" style="margin:15px 0 15px 0;"><p>' . $r['descripcion'] . '</p></div>

<span style="float:right;">Acceso: <acronym title="'.$r['acceso_cfg_votar'].'"><b>'.ucfirst(str_replace('_', ' ', $r['acceso_votar'])).'</b></acronym>. Creador <b>' . crear_link($r['nick']) . '</b>, a fecha <em>' . $r['time'] . '</em>, duraci&oacute;n <b title="'.$r['time_expire'].'">'.$duracion.'</b>.</span>';

		if ($time_expire < time()) { // VOTACION TERMINADA, IMPRIMIR RESULTADOS 

			$txt_escrutinio = '';
			$chart_dato = array();
			$chart_nom = array();
			$result2 = mysql_query("SELECT COUNT(user_ID) as num, voto, validez
FROM votacion_votos
WHERE ref_ID = '" . $r['ID'] . "'
GROUP BY voto", $link);
			while($r2 = mysql_fetch_array($result2)) {
				$txt_escrutinio .= '<tr><td>' . $respuestas[$r2['voto']] . '</td><td align="right"><b>' . $r2['num'] . '</b></td><td align="right">' . round(($r2['num'] * 100) / $r['num']) . '%</td></tr>';

				$escanos_total = $escanos_total + $r2['num'];
				$chart_dato[] = $r2['num'];
				$chart_nom[] = $respuestas[$r2['voto']];
			}

			$validez_voto['true'] = 0; $validez_voto['false'] = 0;
			$result2 = mysql_query("SELECT validez, COUNT(user_ID) AS num FROM votacion_votos WHERE ref_ID = '".$r['ID']."' GROUP BY validez", $link);
			while($r2 = mysql_fetch_array($result2)) {
				$validez_voto[$r2['validez']] = $r2['num'];
			}

			// Determinar validez: mayoria simple = votacion nula.
			$nulo_limite = ceil(($validez_voto['true']+$validez_voto['false'])/2);
			if ($validez_voto['false'] >= $nulo_limite) { $validez = false; } else { $validez = true; }
			

			$txt .= '
<table border="0" cellpadding="0" cellspacing="0"><tr><td valign="top">
'.($validez==true?'<table border="0" cellpadding="1" cellspacing="0" class="pol_table">
<tr>
<th>Respuestas &nbsp;</th>
<th>Votos</th>
<th></th>
</tr>'.$txt_escrutinio.'</table>':'').'</td><td valign="top">';

if ($validez==true) {
	if ($r['tipo']=='parlamento') {
		$txt .= '<img src="http://chart.apis.google.com/chart?cht=p&chds=a&chd=t:' . $escanos_total . ',' . implode(',', $chart_dato) . '&chs=450x300&chl=|' . implode('|', $chart_nom) . '&chco=ffffff01,FF8000&chf=bg,s,ffffff01|c,s,ffffff01" alt="Escrutinio" />';
	} else {
		$txt .= '<img src="http://chart.apis.google.com/chart?cht=p&chd=t:' . implode(',', $chart_dato) . '&chs=440x200&chds=a&chl=' . implode('|', $chart_nom) . '&chf=bg,s,ffffff01|c,s,ffffff01" alt="Escrutinio" />';
	}
}

$txt .= '
</td>
<td valign="top" style="color:#888;">Validez: '.($validez?'<b style="color:#2E64FE;">OK</b>':'<b style="color:#FF0000;">NULO</b>').'<br />
<img title="Votos de validez: '.$validez_voto['true'].' OK, '.$validez_voto['false'].' NULO" src="http://chart.apis.google.com/chart?cht=p&chd=t:'.$validez_voto['true'].','.$validez_voto['false'].'&chs=210x130&chds=a&chl=OK|NULO&chf=bg,s,ffffff01|c,s,ffffff01&chco=2E64FE,FF0000,2E64FE,FF0000" alt="Validez" /><br />
M&iacute;nimo para nulidad: <b>'.$nulo_limite.'</b> (50%).</td>

</tr></table>';


		} else {
			if ((!$r['ha_votado']) AND (nucleo_acceso($r['acceso_votar'],$r['acceso_cfg_votar']))) {
				for ($i=0;$i<$respuestas_num;$i++) { 
					if ($respuestas[$i]) { 
						$votos .= '<option value="'.$i.'"'.($respuestas[$i]=='En Blanco'?' selected="selected"':'').'>' . $respuestas[$i] . '</option>'; 
					} 
				}
				$txt .= '<form action="http://'.strtolower($pol['pais']).'.virtualpol.com/accion.php?a=votacion&b=votar" method="post">
<input type="hidden" name="ref_ID" value="' . $r['ID'] . '"  />
<p><select name="voto" style="font-size:22px;">
'.$votos.'
</select>
<input type="submit" value="Votar" style="font-size:22px;" /></p>

<p>
<input type="radio" name="validez" value="true" checked="checked" /> Votaci&oacute;n correcta.<br />
<input type="radio" name="validez" value="false" /> Votaci&oacute;n nula (inv&aacute;lida, inapropiada o tendenciosa).<br />
</p>


</form>';
			} elseif ($r['ha_votado']) {
				$txt .= 'Tu voto ha sido recogido correctamente.';
			} else {
				$txt .= '<b style="color:red;">No tienes acceso para votar.</b>';
			}
		}

		if ($r['tipo'] == 'parlamento') {
			$txt .= '
<table border="0" cellpadding="0" cellspacing="3" class="pol_table">
<tr>
<th>Diputado</th>
<th></th>
<th colspan="2">Voto</th>
</tr>';

			$result2 = mysql_query("SELECT user_ID,
(SELECT nick FROM users WHERE ID = ".SQL."estudios_users.user_ID LIMIT 1) AS nick,
(SELECT (SELECT siglas FROM ".SQL."partidos WHERE ID = users.partido_afiliado LIMIT 1) AS las_siglas FROM users WHERE ID = ".SQL."estudios_users.user_ID LIMIT 1) AS siglas,
(SELECT voto FROM votacion_votos WHERE ref_ID = '" . $r['ID'] . "' AND user_ID = ".SQL."estudios_users.user_ID LIMIT 1) AS ha_votado
FROM ".SQL."estudios_users
WHERE cargo = '1' AND ID_estudio = '6'
ORDER BY siglas ASC", $link);
			$txt .= mysql_error($link);
			while($r2 = mysql_fetch_array($result2)) {
				if ($r2['ha_votado'] != null) { $ha_votado = ' style="background:blue;"';
				} else { $ha_votado = ' style="background:red;"'; }
				$txt .= '<tr><td><img src="'.IMG.'cargos/6.gif" /> <b>' . crear_link($r2['nick']) . '</b></td><td><b>' . crear_link($r2['siglas'], 'partido') . '</b></td><td' . $ha_votado . '></td><td><b>' . $respuestas[$r2['ha_votado']]  . '</b></td></tr>';
			}
			$txt .= '</table>';

		}


	}

} else {

	$result = mysql_query("SELECT ID FROM ".SQL."estudios_users WHERE user_ID = '" . $pol['user_ID'] . "' AND cargo = '1' AND ID_estudio = '6' LIMIT 1", $link);
	while($r = mysql_fetch_array($result)){ $es_diputado = true; }

	$txt_title = 'Votaciones: sondeos, referendums, parlamento';
	$txt .= '<h1>Votaciones: (Referendum, sondeos, parlamento, etc)</h1>

<p>'.boton('Crear votacion', '/votacion/crear/').'</p>

<table border="0" cellpadding="1" cellspacing="0" class="pol_table">
<tr>
<th>Tipo</th>
<th>Votos</th>
<th>Pregunta</th>
<th>Autor</th>
<th>Estado</th>
<th></th>
</tr>';
	$result = mysql_query("SELECT ID, pregunta, time, time_expire, user_ID, estado, num, tipo, acceso_votar, acceso_cfg_votar,
(SELECT nick FROM users WHERE ID = votacion.user_ID LIMIT 1) AS nick,
(SELECT ID FROM votacion_votos WHERE ref_ID = votacion.ID AND user_ID = '" . $pol['user_ID'] . "' LIMIT 1) AS ha_votado
FROM votacion
WHERE pais = '".PAIS."' 
ORDER BY estado ASC, time_expire DESC", $link);
	while($r = mysql_fetch_array($result)) {
		if ($r['estado'] == 'ok') { 
			$time_expire = strtotime($r['time_expire']);
			$estado =  '<span style="color:blue;">' . duracion($time_expire - time()) . '</span>'; 
		} else { $estado = '<span style="color:grey;">Finalizado</span>'; }

		if ((!$r['ha_votado']) AND ($r['estado'] == 'ok') AND (nucleo_acceso($r['acceso_votar'],$r['acceso_cfg_votar']))) { 
			$votar = boton('Votar', '/votacion/' . $r['ID'] . '/');
		} else { $votar = ''; }

		$boton = '';
		if ($r['user_ID'] == $pol['user_ID']) {
			if ($r['estado'] == 'ok') {
				if (($r['tipo'] != 'destituir') AND ($r['tipo'] != 'otorgar')) { $boton .= boton('Finalizar', '/accion.php?a=votacion&b=concluir&ID='.$r['ID'], '&iquest;Seguro que quieres FINALIZAR esta votacion?').' '; }
				$boton .= boton('X', '/accion.php?a=votacion&b=eliminar&ID=' . $r['ID'], '&iquest;Seguro que quieres ELIMINAR esta votacion?');
			}
		}

		$txt .= '<tr>
<td style="'.($r['tipo']=='referendum'?'font-weight:bold;':'').'">' . ucfirst($r['tipo']) . '</td>
<td align="right"><b>' . $r['num'] . '</b></td>
<td><a href="/votacion/' . $r['ID'] . '/"><b>' . $r['pregunta'] . '</b></a></td>
<td>' . crear_link($r['nick']) . '</td>
<td><b>' . $estado . '</b></td>
<td nowrap="nowrap">'.$votar.$boton.'</td>
<td></td>
</tr>';
	}
	$txt .= '</table>';


}



//THEME
include('theme.php');
?>