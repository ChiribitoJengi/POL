<?php 
include('inc-login.php');

$votaciones_tipo = array('sondeo', 'referendum', 'parlamento', 'cargo');


// FINALIZAR VOTACIONES
$result = mysql_query("SELECT ID, tipo, num, pregunta, ejecutar, privacidad, acceso_ver FROM votacion 
WHERE estado = 'ok' AND pais = '".PAIS."' AND (time_expire <= '".$date."' OR ((votos_expire != 0) AND (num >= votos_expire)))", $link);
while($r = mysql_fetch_array($result)){
	
	// Finaliza la votación
	mysql_query("UPDATE votacion SET estado = 'end', time_expire = '".$date."' WHERE ID = '".$r['ID']."' LIMIT 1", $link);

	include_once('inc-functions-accion.php');

	if ($r['ejecutar'] != '') { 
		// EJECUTAR ACCIONES

		$validez_voto['true'] = 0; $validez_voto['false'] = 0; $voto[0] = 0; $voto[1] = 0; $voto[2] = 0;
		$result2 = mysql_query("SELECT validez, voto FROM votacion_votos WHERE ref_ID = ".$r['ID']."", $link);
		while($r2 = mysql_fetch_array($result2)) {
			$validez_voto[$r2['validez']]++;
			$voto[$r2['voto']]++;
		}

		// Determinar validez: mayoria simple = votacion nula
		if ($validez_voto['false'] < $validez_voto['true']) { 
			// OK: es válida
			if ($r['tipo'] == 'cargo') {
				if ($voto[1] > $voto[2]) {
					cargo_add(explodear('|', $r['ejecutar'], 0), explodear('|', $r['ejecutar'], 1), true, true);
				} else {
					cargo_del(explodear('|', $r['ejecutar'], 0), explodear('|', $r['ejecutar'], 1), true, true);
				}
			}
		}
	}

	// _______ A continuación se rompe la relación Usuario-Voto irreversiblemente ________
	
	if ($r['privacidad'] == 'true') {
		// Rompe la relación Usuario-Voto. Solo en votaciones con secreto de voto.
		barajar_votos($r['ID']); // Esta funcion está documentada en /source/inc-functions-accion.php
	}

	// Actualiza contador de votaciones activas
	$result2 = mysql_query("SELECT COUNT(ID) AS num FROM votacion WHERE estado = 'ok' AND pais = '".PAIS."' AND acceso_ver = 'anonimos'", $link);
	while($r2 = mysql_fetch_array($result2)) {
		mysql_query("UPDATE config SET valor = '".$r2['num']."' WHERE pais = '".PAIS."' AND dato = 'info_consultas' LIMIT 1", $link);
	}

	if ($r['acceso_ver'] == 'anonimos') {
		evento_chat('<b>['.strtoupper($r['tipo']).']</b> Finalizado, resultados: <a href="/votacion/'.$r['ID'].'"><b>'.$r['pregunta'].'</b></a> <span style="color:grey;">(votos: <b>'.$r['num'].'</b>)</span>');
	}
}
// FIN DE FINALIZAR VOTACIONES




// EMPIEZA PRESENTACION

if (($_GET['a'] == 'verificacion') AND ($_GET['b']) AND (isset($pol['user_ID']))) {
	$comprobante_full = $_GET['b'];
	$ref_ID = explodear('-', $comprobante_full, 0);
	$comprobante = explodear('-', $comprobante_full, 1);
	redirect('/votacion/'.$ref_ID.'/verificacion#'.$comprobante);

} elseif ($_GET['a'] == 'crear') {
	$txt_title = 'Borrador de votación';
	$txt_nav = array('/votacion'=>'Votaciones', '/votacion/borradores'=>'Borradores', 'Crear borrador');
	$txt_tab = array('/votacion/borradores'=>'Ver borradores', '/votacion/'.$_GET['b']=>'Previsualizar', '/votacion/crear/'.$_GET['b']=>'Editar borrador');

	// EDITAR
	if (is_numeric($_GET['b'])) {
		$result = mysql_query("SELECT * FROM votacion WHERE estado = 'borrador' AND ID = '".$_GET['b']."' LIMIT 1", $link);
		$edit = mysql_fetch_array($result);
	}


	// Pre-selectores
	if (!isset($edit['ID'])) { $edit['tipo'] = 'sondeo'; $edit['acceso_votar'] = 'ciudadanos'; $edit['acceso_ver'] = 'anonimos'; }
	
	$sel['tipo_voto'][$edit['tipo_voto']] = ' selected="selected"';
	$sel['privacidad'][$edit['privacidad']] = ' selected="selected"';
	
	$sel['tipo'][$edit['tipo']] = ' checked="checked"';
	
	$sel['acceso_votar'][$edit['acceso_votar']] = ' selected="selected"';
	$sel['acceso_ver'][$edit['acceso_ver']] = ' selected="selected"';

	$txt .= '<form action="http://'.strtolower(PAIS).'.'.DOMAIN.'/accion.php?a=votacion&b=crear" method="post">

'.(isset($edit['ID'])?'<input type="hidden" name="ref_ID" value="'.$_GET['b'].'" />':'').'

<table border="0"><tr><td valign="top">
<p class="azul" style="text-align:left;"><b>Tipo de votación</b>:<br />
<span id="tipo_select">';

	$tipo_extra = array(
'sondeo'=>'<span style="float:right;">(informativo, no vinculante)</span>', 
'referendum'=>'<span style="float:right;">(vinculante)</span>',
'parlamento'=>'<span style="float:right;">(vinculante)</span>',
'cargo'=>'<span style="float:right;" title="Se ejecuta una acción automática tras su finalización.">(ejecutiva)</span>',
);

	if (ASAMBLEA) { unset($votaciones_tipo[2]); } // Quitar tipo de votacion de parlamento.

	foreach ($votaciones_tipo AS $tipo) {
		$txt .= '<span style="font-size:18px;"><input type="radio" name="tipo" value="'.$tipo.'" onclick="cambiar_tipo_votacion(\''.$tipo.'\');"'.$sel['tipo'][$tipo].' />'.$tipo_extra[$tipo].ucfirst($tipo).'</span><br >';
	}

	$txt .= '</span><br />

<span id="time_expire">
<b>Duración</b>:

<input type="text" name="time_expire" value="'.(isset($edit['ID'])?round($edit['duracion']/3600):'24').'" style="text-align:right;width:50px;" />

<select name="time_expire_tipo">
<option value="3600" selected="selected">horas</option>
<option value="86400">días</option>
</select></span>


<span id="cargo_form" style="display:none;">
<b>Cargo</b>: 
<select name="cargo">';

	$sel['cargo'][explodear('|', $edit['ejecutar'], 0)] = ' selected="selected"';
	$result = mysql_query("SELECT cargo_ID, nombre FROM cargos ORDER BY nivel DESC", $link);
	while($r = mysql_fetch_array($result)) { $txt .= '<option value="'.$r['cargo_ID'].'"'.$sel['cargo'][$r['cargo_ID']].'>'.$r['nombre'].'</option>'; }

	$txt .= '
</select><br />
Ciudadano: <input type="text" name="nick" value="" size="10" /></span>


<br /><span id="votos_expire">
<b>Finalizar con</b>: <input type="text" name="votos_expire" value="'.($edit['votos_expire']?$edit['votos_expire']:'').'" size="1" maxlength="5" style="text-align:right;" /> votos</span><br />

<span id="tipo_voto">
<b>Tipo de voto</b>: 
<select name="tipo_voto">
<option value="estandar"'.$sel['tipo_voto']['estandar'].'>Una elección (estándar)</option>
<option value="multiple"'.$sel['tipo_voto']['multiple'].'>Múltiple</option>

<optgroup label="Preferencial">
<option value="3puntos"'.$sel['tipo_voto']['3puntos'].'>3 votos (6 puntos)</option>
<option value="5puntos"'.$sel['tipo_voto']['5puntos'].'>5 votos (15 puntos)</option>
<option value="8puntos"'.$sel['tipo_voto']['8puntos'].'>8 votos (36 puntos)</option>
</optgroup>


</select></span>
<br />
<span id="privacidad">
<b>Voto</b>: 
<select name="privacidad">
<option value="true"'.$sel['privacidad']['true'].'>Secreto (estándar)</option>
<option value="false"'.$sel['privacidad']['false'].'>Público</option>
</select>

<br />

<b>Orden de opciones:</b> <input type="checkbox" name="aleatorio" value="true"'.($edit['aleatorio']=='true'?' checked="checked"':'').' /> Aleatorio.
</span>
</p>


</td><td valign="top" align="right">
		
<p id="acceso_votar" class="azul"><b>Acceso para votar:</b><br />
<select name="acceso_votar">';


	$tipos_array = nucleo_acceso('print');
	unset($tipos_array['anonimos']);
	foreach ($tipos_array AS $at => $at_var) {
		$txt .= '<option value="'.$at.'"'.$sel['acceso_votar'][$at].' />'.ucfirst(str_replace("_", " ", $at)).'</option>';
	}

	$txt .= '</select><br />
<input type="text" name="acceso_cfg_votar" size="18" maxlength="500" id="acceso_cfg_votar_var" value="'.$edit['acceso_cfg_votar'].'" /></p>
		
<p id="acceso_ver" class="azul"><b>Acceso ver votación:</b><br />
<select name="acceso_ver">';


	$tipos_array = nucleo_acceso('print');
	foreach ($tipos_array AS $at => $at_var) {
		$txt .= '<option value="'.$at.'"'.$sel['acceso_ver'][$at].' />'.ucfirst(str_replace("_", " ", $at)).'</opcion>';
	}

	$txt .= '</select><br />
<input type="text" name="acceso_cfg_ver" size="18" maxlength="500" id="acceso_cfg_ver_var" value="'.$edit['acceso_cfg_ver'].'" /></p>

</td></tr></table>

<div class="votar_form">
<p><b>Pregunta</b>: 
<input type="text" name="pregunta" size="57" maxlength="70" value="'.$edit['pregunta'].'" /></p>
</div>

<p><b>Descripción</b>:<br />
<textarea name="descripcion" style="color: green; font-weight: bold; width: 570px; height: 250px;">
'.strip_tags($edit['descripcion']).'
</textarea></p>

<p><b>URL de debate</b>: (opcional, debe empezar por http://...)<br />
<input type="text" name="debate_url" size="57" maxlength="300" value="'.$edit['debate_url'].'" /></p>

<div class="votar_form">
<p><b>Opciones de voto</b>:
<ul style="margin-bottom:-16px;">
<li><input type="text" name="respuesta0" size="22" value="En Blanco" readonly="readonly" style="color:grey;" /> &nbsp; <a href="#" id="a_opciones" onclick="opcion_nueva();return false;">Añadir opción</a></li>
</ul>
<ol id="li_opciones" style="margin-top:10px;">';

	if (!isset($edit['ID'])) {
		$edit['respuestas'] = 'SI|NO|';
		$edit['respuestas_desc'] = '][][';
	}

	$respuestas = explode("|", $edit['respuestas']);
	$respuestas_desc = explode("][", $edit['respuestas_desc']);
	if ($respuestas[0] == 'En Blanco') { unset($respuestas[0]); }

	foreach ($respuestas AS $ID => $respuesta) {
		if ($respuesta != '') {
			$respuestas_num++;
			// &nbsp; Descripción: <input type="text" name="respuesta_desc'.$respuestas_num.'" size="28" maxlength="500" value="'.$respuestas_desc[$ID].'" /> (opcional)
			$txt .= '<li><input type="text" name="respuesta'.$respuestas_num.'" size="80" maxlength="160" value="'.$respuesta.'" /></li>';
		}
	}

	$txt .= '
</ol>
</p>
</div>
<p><input type="submit" value="Guardar borrador"'.(nucleo_acceso($vp['acceso']['votacion_borrador'])?'':' disabled="disabled"').' style="font-size:18px;" /></p>';

	$txt_header .= '<script type="text/javascript">
campos_num = '.($respuestas_num+1).';
campos_max = 30;

function cambiar_tipo_votacion(tipo) {
	$("#acceso_ver, #acceso_votar, #time_expire, .votar_form, #votos_expire, #tipo_voto, #privacidad").show();
	$("#cargo_form").hide();
	switch (tipo) {
		case "parlamento": $("#acceso_votar, #votos_expire, #privacidad, #acceso_ver").hide(); break;
		case "cargo": $("'.(ASAMBLEA?'':'#acceso_ver, #acceso_votar, ').'#time_expire, .votar_form, #votos_expire, #tipo_voto, #privacidad").hide(); $("#cargo_form").show(); break;
	}
}

function opcion_nueva() {
	$("#li_opciones").append(\'<li><input type="text" name="respuesta\' + campos_num + \'" size="80" maxlength="160" /></li>\');
	if (campos_num >= campos_max) { $("#a_opciones").hide(); }
	campos_num++;
	return false;
}

</script>';

} elseif ($_GET['a'] == 'borradores') { // VER BORRADORES

	$txt_title = 'Borradores de votaciones';
	$txt_nav = array('/votacion'=>'Votaciones', '/votacion/borradores'=>'Borradores de votación');
	$txt_tab = array('/votacion/crear'=>'Crear votación');
	
	$txt .= '<table border="0" cellpadding="1" cellspacing="0" class="pol_table">';

	$result = mysql_query("SELECT ID, duracion, tipo_voto, pregunta, time, time, time_expire, user_ID, estado, num, tipo, acceso_votar, acceso_cfg_votar, acceso_ver, acceso_cfg_ver,
(SELECT nick FROM users WHERE ID = votacion.user_ID LIMIT 1) AS nick
FROM votacion
WHERE estado = 'borrador' AND pais = '".PAIS."'
ORDER BY time DESC
LIMIT 500", $link);
	while($r = mysql_fetch_array($result)) {

		if (nucleo_acceso($vp['acceso'][$r['tipo']])) {
			$boton_borrar = boton('X', '/accion.php?a=votacion&b=eliminar&ID='.$r['ID'], '¿Estás seguro de querer ELIMINAR este borrador de votación?', 'small');
			$boton_iniciar = boton('Iniciar', '/accion.php?a=votacion&b=iniciar&ref_ID='.$r['ID'], '¿Estás seguro de querer INICIAR esta votación?', 'small');
		} else {
			$boton_borrar = boton('X', false, false, 'small');
			$boton_iniciar = boton('Iniciar', false, false, 'small');
		}
		
		$txt .= '<tr>
<td valign="top" align="right" nowrap="nowrap"><b>'.ucfirst($r['tipo']).'</b><br />'.$boton_borrar.' '.$boton_iniciar.'<br />'.boton('Previsualizar', '/votacion/'.$r['ID'], false, 'small').'</td>
<td><a href="/votacion/crear/'.$r['ID'].'"><b style="font-size:18px;">'.$r['pregunta'].'</b></a><br />
Creado hace <b><span class="timer" value="'.strtotime($r['time']).'"></span></b> por '.crear_link($r['nick']).', editado hace <span class="timer" value="'.strtotime($r['time_expire']).'"></span>
<br />
Ver: <em title="'.$r['acceso_cfg_ver'].'">'.$r['acceso_ver'].'</em>, votar: <em title="'.$r['acceso_cfg_votar'].'">'.$r['acceso_votar'].'</em>, tipo voto: <em>'.$r['tipo_voto'].'</em>, duración: <em>'.duracion($r['duracion']).'</em></td>
</tr>';
	}
	$txt .= '</table>';



} elseif ($_GET['a']) { // VER VOTACION

	$result = mysql_query("SELECT *,
(SELECT nick FROM users WHERE ID = votacion.user_ID LIMIT 1) AS nick, 
(SELECT ID FROM votacion_votos WHERE ref_ID = votacion.ID AND user_ID = '".$pol['user_ID']."' LIMIT 1) AS ha_votado,
(SELECT voto FROM votacion_votos WHERE ref_ID = votacion.ID AND user_ID = '".$pol['user_ID']."' LIMIT 1) AS que_ha_votado,
(SELECT validez FROM votacion_votos WHERE ref_ID = votacion.ID AND user_ID = '".$pol['user_ID']."' LIMIT 1) AS que_ha_votado_validez,
(SELECT mensaje FROM votacion_votos WHERE ref_ID = votacion.ID AND user_ID = '".$pol['user_ID']."' LIMIT 1) AS que_ha_mensaje,
(SELECT comprobante FROM votacion_votos WHERE ref_ID = votacion.ID AND user_ID = '".$pol['user_ID']."' LIMIT 1) AS comprobante
FROM votacion
WHERE ID = '".$_GET['a']."' AND pais = '".PAIS."'
LIMIT 1", $link);
	while($r = mysql_fetch_array($result)) {

		if ((!nucleo_acceso($r['acceso_ver'], $r['acceso_cfg_ver'])) AND ($r['estado'] != 'borrador')) { 
			$txt .= '<p style="color:red;">Esta votación es privada. No tienes acceso para ver su contenido o resultado.</p>'; 
			break; 
		}

		$votos_total = $r['num'];

		$time_expire = strtotime($r['time_expire']);
		$time_creacion = strtotime($r['time']);
		$duracion = duracion($time_expire - $time_creacion);
		$respuestas = explode("|", $r['respuestas']);
		$respuestas_desc = explode("][", $r['respuestas_desc']);
		$respuestas_num = count($respuestas) - 1;
		
		$txt_title = 'Votacion: ' . strtoupper($r['tipo']) . ' | ' . $r['pregunta'];
		$txt_nav = array('/votacion'=>'Votaciones', '/votacion/'.$r['ID']=>strtoupper($r['tipo']));

		if ($r['estado'] == 'ok') { 
			$txt_nav['/votacion/'.$r['ID']] = 'En curso: '.num($votos_total).' votos';
			$txt_tab = array('/votacion'=>'Ver otros resultados');

			$tiempo_queda =  '<span style="color:blue;">Quedan '.timer($time_expire, true).'.</span>'; 
		} elseif ($r['estado'] == 'borrador') {
			$txt_nav[] = 'Borrador';
			$txt_tab = array('/votacion/borradores'=>'Ver borradores', '/votacion/'.$r['ID']=>'Previsualizar', '/votacion/crear/'.$r['ID']=>'Editar borrador');

			$tiempo_queda =  '<span style="color:red;">Borrador <span style="font-weight:normal;">(Previsualización de votación)</span></span> ';
		} else { 
			$txt_nav['/votacion/'.$r['ID']] = 'Finalizado: '.num($votos_total).' votos';
			$txt_tab = array('/votacion/'.$r['ID']=>'Resultado', '/votacion/'.$r['ID'].'/info'=>'Más información');
			if (isset($pol['user_ID'])) { $txt_tab['/votacion/'.$r['ID'].'/verificacion'] = 'Verificación'; }
			$tiempo_queda =  '<span style="color:grey;">Finalizado</span>'; 
		}


		if ($_GET['b'] == 'info') {
			
			$txt .= '<table border="0" width="100%"><tr><td valign="top">';
			
			
			$result2 = mysql_query("SELECT COUNT(*) AS num FROM votacion_votos WHERE ref_ID = '".$r['ID']."' AND mensaje != ''", $link);
			while($r2 = mysql_fetch_array($result2)) { $comentarios_num = $r2['num']; }

			$txt .= '<h2 style="margin-top:18px;">Comentarios anónimos ('.($r['estado']=='end'?$comentarios_num.' comentarios, '.num(($comentarios_num*100)/$votos_total, 1).'%':'*').')</h2>';
			if (nucleo_acceso('ciudadanos_global')) {
				if ($r['estado'] == 'end') { 
					$result2 = mysql_query("SELECT mensaje FROM votacion_votos WHERE ref_ID = '".$r['ID']."' AND mensaje != ''", $link);
					while($r2 = mysql_fetch_array($result2)) { $txt .= '<p>'.$r2['mensaje'].'</p>'; }
				} else { $txt .= '<p>Los comentarios estarán visibles al finalizar la votación.</p>'; }
			} else { $txt .= '<p>Para ver los comentarios debes ser ciudadano.</p>'; }


			if ((($r['privacidad'] == 'false') AND ($r['estado'] == 'end')) OR ($r['estado'] != 'end')) {
				$txt .= '<h2 style="margin-top:18px;">Registro de votos</h2>

<table border="0" cellpadding="3">
<tr>
<th>Quien</th>
<th>Voto</th>
<th>Autentificado</th>
</tr>';
				$orden = 0;
				$result2 = mysql_query("SELECT user_ID, voto, validez, autentificado, (SELECT nick FROM users WHERE ID = user_ID LIMIT 1) AS nick FROM votacion_votos WHERE ref_ID = '".$r['ID']."' ORDER BY RAND()", $link);
				while($r2 = mysql_fetch_array($result2)) {
					$orden++;

					$txt .= '<tr>
<td>'.($r2['user_ID']==0?'*':crear_link($r2['nick'])).'</td>
<td nowrap="nowrap"><b>'.($r['privacidad']=='false'&&$r['estado']=='end'?($r['tipo_voto']=='estandar'?$respuestas[$r2['voto']]:$r2['voto']):'*').'</b></td>
<td>'.($r2['autentificado']=='true'?'<span style="color:blue;"><b>SI</b></span>':'<span style="color:grey;">NO</span>').'</td>
</tr>';
				}
				$txt .= '<tr><td colspan="4" nowrap="nowrap">Votos computados: <b>'.$orden.'</b> (Contador: '.$r['num'].')</td></tr></table>';
			}

	
			$txt .= '
</td>
<td valign="top" width="350">

<h2>Propiedades de la votación:</h2>
<ul>';

			if ($r['privacidad'] == 'true') { // Privacidad SI, voto secreto.
				$txt .= '
<li><b title="Accuracy: el computo de los votos es exacto.">Precisión:</b> Si, el computo de los votos es exacto.</b>

<li><b title="Democracy: solo pueden votar personas autorizadas y una sola vez.">Democracia:</b> Autentificación solida mediante DNIe (y otros certificados) opcional y avanzado sistema de vigilancia del censo de eficacia elevada.</li>

<li><b title="Privacy: el sentido del voto es secreto.">Privacidad:</b> Si, siempre que el servidor no se comprometa mientras la votación está activa.</li>

<li><b title="Veriability: capacidad publica de comprobar el recuento de votos.">Verificación:</b> Muy alta. Se permite verificar el sentido del propio voto mientras la votación está activa, se hace publico CUANDO vota QUIEN y hay un sistema de comprobantes que permite verificar el sentido del voto.</li>

<li><b title="Posibilidad de modificar el sentido del voto propio en una votación activa.">Rectificación</b> Si.</li>';
			} else { // Privacidad NO, voto publico, verificabilidad
				$txt .= '
<li><b title="Accuracy: el computo de los votos es exacto.">Precisión:</b> Si, el computo de los votos es exacto.</b>

<li><b title="Democracy: solo pueden votar personas autorizadas y una sola vez.">Democracia:</b> Autentificación solida mediante DNIe (y otros certificados) opcional y avanzado sistema de vigilancia del censo de eficacia elevada.</li>

<li><b title="Privacy: el sentido del voto es secreto.">Privacidad:</b> No, el voto es público.</li>

<li><b title="Veriability: capacidad publica de comprobar el recuento de votos.">Verificación:</b> Si, verificabilidad universal.</li>

<li><b title="Posibilidad de modificar el sentido del voto propio en una votación activa.">Rectificación</b> Si.</li>';
			}

			$txt .= '</ul></td></tr></table>';

		} elseif ($_GET['b'] == 'verificacion') {

			$txt .= '<h2>Verificación de votación</h2>

<p>La información presentada a continuación es la tabla de comprobantes que muestra el escrutinio completo y la relación Voto-Comprobante de esta votación. Esto permite a cualquier votante comprobar el sentido de su voto ejercido más allá de toda duda. Todo ello sin romper el secreto de voto.</p>

'.($r['tipo_voto']!='estandar'?'<p><em>* El tipo de voto de esta votación es múltiple o preferencial. Por razones tecnicas -provisionalmente- se muestra el campo "voto" en bruto.'.($r['tipo_voto']=='multiple'?' 0=En Blanco, 1=SI y 2=NO.':'').'</em></p>':'').'

<style>
#tabla_comprobantes td { padding:0 4px; }
#tabla_comprobantes .tcb { color:blue; }
#tabla_comprobantes .tcr { color:red; }
</style>

<table border="0" style="font-family:\'Courier New\',Courier,monospace;" id="tabla_comprobantes">
<tr>
<th title="Conteo de los diferentes sentidos de votos">Contador</th>
<th title="Sentido del voto emitido">Sentido de voto</th>
<th title="Voto de validez/nulidad, es una votación binaria paralela a la votación para determinar la validez de la misma.">Validez</th>
<th title="Código aleatorio relacionado a cada voto">Comprobante</th>
<th title="Comentario emitido junto al voto, anónimo y opcional">Comentario</th>
</tr>';
			if ((!nucleo_acceso('ciudadanos')) AND ($r['estado'] == 'end')) {
				$txt .= '<tr><td colspan="3" style="color:red;"><hr /><b>Tienes que ser ciudadano para ver la tabla de comprobantes.</b></td></tr>';
			} else if (($r['estado'] == 'end') AND (nucleo_acceso($r['acceso_ver'], $r['acceso_cfg_ver']))) {
				$contador_votos = 0;
				$result2 = mysql_query("SELECT voto, validez, comprobante, mensaje FROM votacion_votos WHERE ref_ID = '".$r['ID']."' AND comprobante IS NOT NULL".($r['tipo_voto']=='estandar'?" ORDER BY voto ASC":""), $link);
				while($r2 = mysql_fetch_array($result2)) { 
					$contador_votos++; 
					$txt .= '<tr id="'.$r2['comprobante'].'">
<td align="right">'.($r['tipo_voto']=='estandar'?++$contador[$r2['voto']]:++$contador).'.</td>
<td nowrap>'.($r['tipo_voto']=='estandar'?'<b>'.$respuestas[$r2['voto']].'</b>':$r2['voto']).'</td>
<td'.($r2['validez']=='true'?' class="tcb">Válida':' class="tcr">Nula').'</td>
<td nowrap>'.$r['ID'].'-'.$r2['comprobante'].'</td>
'.($r2['mensaje']?'<td title="'.$r2['mensaje'].'">Comentario</td>':'').'
</tr>'."\n"; }
				if ($contador_votos == 0) { $txt .= '<tr><td colspan="3" style="color:red;"><hr /><b>Esta votación es anterior al sistema de comprobantes, por lo tanto esta comprobación no es posible.</b></td></tr>'; }
			} else {
				$txt .= '<tr><td colspan="3" style="color:red;"><hr /><b>Esta votación aún no ha finalizado. Cuando finalice se mostrará aquí la tabla de votos-comprobantes.</b></td></tr>';
			}

			$txt .= '</table>';

		} else {


			$txt_description = 'VirtualPol, la primera red social democrática | '.ucfirst($r['tipo']).' de '.PAIS.': '.$r['pregunta'].'.';
			$txt .= '<div class="amarillo" style="margin-top:5px;">
<h1>'.$r['pregunta'].'</h1>
<div class="rich'.($r['estado']=='end'?' votacion_desc_min':'').'">
'.$r['descripcion'].'
'.(substr($r['debate_url'], 0, 4)=='http'?'<hr /><p><b>Debate sobre esta votación: <a href="'.$r['debate_url'].'">aquí</a>.</b></p>':'').'
</div>
</div>

'.($r['acceso_ver']=='anonimos'&&((!isset($pol['user_ID'])) || ($r['ha_votado']) || ($r['estado']=='end'))?'<table border="0" style="margin:5px 0 15px 0;">
<tr>
'.(!isset($pol['user_ID'])?'<td>'.boton('¡Crea tu ciudadano para votar!', REGISTRAR.'?p='.PAIS, false, 'large blue').'</td>':'').'
<td width="20"></td>
<td nowrap="nowrap"><b style="font-size:20px;color:#777;">¡Difúnde esta votación!</b> &nbsp;</td>

<td width="140" height="35">
<a href="https://twitter.com/share" class="twitter-share-button" data-url="http://'.HOST.'/votacion/'.$r['ID'].'" data-text="'.($r['estado']=='ok'?'VOTACIÓN':'RESULTADO').': '.substr($r['pregunta'], 0, 83).'" data-lang="es" data-size="large" data-related="AsambleaVirtuaI" data-hashtags="15M">Twittear</a>
<script>!function(d,s,id){var js,fjs=d.getElementsByTagName(s)[0];if(!d.getElementById(id)){js=d.createElement(s);js.id=id;js.src="//platform.twitter.com/widgets.js";fjs.parentNode.insertBefore(js,fjs);}}(document,"script","twitter-wjs");</script>
</td>


<td width="50"><g:plusone annotation="none" href="http://'.HOST.'/votacion/'.$r['ID'].'"></g:plusone></td>

<td>'.boton('Donar', 'https://virtualpol.com/donaciones', false, 'pill orange').'</td>

<td><div id="fb-root"></div>
<script>(function(d, s, id) {
  var js, fjs = d.getElementsByTagName(s)[0];
  if (d.getElementById(id)) return;
  js = d.createElement(s); js.id = id;
  js.src = "//connect.facebook.net/es_LA/all.js#xfbml=1";
  fjs.parentNode.insertBefore(js, fjs);
}(document, \'script\', \'facebook-jssdk\'));</script>
<div style="display:inline;" class="fb-like" data-href=http://'.HOST.'/votacion/'.$r['ID'].'" data-send="true" data-layout="button_count" data-width="300" data-show-faces="false" data-action="recommend" data-font="verdana"></div></td>
</tr></table>':'').'
';




			// Muestra información de votación (a la derecha)
			$txt .= '<span style="float:right;text-align:right;">
Creador ' . crear_link($r['nick']) . '. Duración <b>'.$duracion.'</b>.<br />
Acceso de voto: <acronym title="'.$r['acceso_cfg_votar'].'">'.ucfirst(str_replace('_', ' ', $r['acceso_votar'])).'</acronym>'.($r['acceso_ver']!='anonimos'?' (privada)':'').'.<br /> 
Inicio: <em>' . $r['time'] . '</em><br /> 
Fin: <em>' . $r['time_expire'] . '</em><br />
'.($r['votos_expire']!=0?'Finaliza tras  <b>'.$r['votos_expire'].'</b> votos.<br />':'').'
'.($r['tipo_voto']!='estandar'?($r['tipo_voto']=='multiple'?'<b>Votación múltiple</b>':'<b>Votación preferencial</b> ('.$r['tipo_voto'].').').'<br />':'').'
<a href="/votacion/'.$r['ID'].'/info/#ver_info">Más información</a>.
</span>';



			if ($r['estado'] == 'end') {  // VOTACION FINALIZADA: Mostrar escrutinio. 

				// Conteo/Proceso de votos (ESCRUTINIO)
				$escrutinio['votos'] = array(0,0,0,0,0,0,0,0,0,0,0,0);
				$escrutinio['votos_autentificados'] = 0;
				$escrutinio['votos_total'] = 0;
				$escrutinio['validez']['true'] = 0; $escrutinio['validez']['false'] = 0;
				$puntos_total = ($r['tipo_voto']=='estandar'?$votos_total:0);

				$result2 = mysql_query("SELECT voto, validez, autentificado, mensaje FROM votacion_votos WHERE ref_ID = '".$r['ID']."'", $link);
				while($r2 = mysql_fetch_array($result2)) {
					
					switch ($r['tipo_voto']) {

						case 'estandar': $escrutinio['votos'][$r2['voto']]++; break;

						case '3puntos': case '5puntos': case '8puntos': 
							$voto_array = explode(' ', $r2['voto']); $puntos = 1;
							foreach ($voto_array AS $elvoto) {
								if (isset($respuestas[$elvoto])) {
									$escrutinio['votos'][$elvoto] += $puntos;
									$puntos_total += $puntos;
									$puntos++;
								}
							}
							break;

						case 'multiple':
							$voto_array = explode(' ', $r2['voto']);
							foreach ($voto_array AS $voto_ID => $elvoto) {
								if (isset($respuestas[$voto_ID])) { 
									$escrutinio['votos'][$voto_ID] += ($elvoto==2?-1:$elvoto);
									$escrutinio['votos_full'][$voto_ID][$elvoto]++;
								}
							}
							break;
					}

					$escrutinio['validez'][$r2['validez']]++;
					if ($r2['autentificado'] == 'true') { $escrutinio['votos_autentificados']++; }
				}

				// Ordena escrutinio multiple por porcentaje de SI.
				if ($r['tipo_voto'] == 'multiple') { 
					foreach ($escrutinio['votos_full'] AS $voto_ID => $voto_array) {
						$escrutinio['votos'][$voto_ID] = round(($voto_array[1]>0?($voto_array[1]*100)/($voto_array[1] + $voto_array[2])*100:0));
					}
				}

				// Determina validez (por mayoria simple)
				$nulo_limite = ceil(($votos_total)/2);
				if ($escrutinio['validez']['false'] < $escrutinio['validez']['true']) { $validez = true; } else { $validez = false; }

				// Opciones del escrutinio en orden descendente.
				arsort($escrutinio['votos']);

				// Imprime escrutinio en texto.
				$txt .= '<table border="0" cellpadding="0" cellspacing="0"><tr><td valign="top">';

				// Imprime escrutinio en grafico.
				if ($validez == true) { // Solo si el resultado es válido (menos de 50% de votos nulos).
					foreach ($escrutinio['votos'] AS $voto => $num) {
						if ($respuestas[$voto] != 'En Blanco') {
							$grafico_array_votos[] = $num;
							$grafico_array_respuestas[] = (strlen($respuestas[$voto])>=13?trim(substr($respuestas[$voto], 0, 13)).'..':$respuestas[$voto]);
						}
					}

					if ((count($respuestas) <= 8) AND ($r['tipo_voto'] != 'multiple')) { 
						$txt .= '<img src="http://chart.apis.google.com/chart?cht=p&chds=a&chp=4.71&chd=t:'.implode(',', $grafico_array_votos).'&chs=350x175&chl='.implode('|', $grafico_array_respuestas).'&chf=bg,s,ffffff01|c,s,ffffff01&chco=FF9900|FFBE5E|FFD08A|FFDBA6" alt="Escrutinio" width="350" height="175" /><br />'; 
					}
				}

				if ($validez == true) {

					if ($r['tipo_voto'] == 'multiple') {
						$txt .= '<table border="0" cellpadding="1" cellspacing="0" class="pol_table"><tr><th>Escrutinio &nbsp; </th><th>SI</th><th>NO</th><th></th></tr>';
						
						$puntos_total_sin_en_blanco = $puntos_total - $escrutinio['votos'][$en_blanco_ID];

						foreach ($escrutinio['votos'] AS $voto => $num) { 
							if ($respuestas[$voto]) {
								if ($respuestas[$voto] != 'En Blanco') {
									$voto_si = ($escrutinio['votos_full'][$voto][1]?$escrutinio['votos_full'][$voto][1]:0);
									$voto_no = ($escrutinio['votos_full'][$voto][2]?$escrutinio['votos_full'][$voto][2]:0);
									$voto_en_blanco = ($escrutinio['votos_full'][$voto][0]?$escrutinio['votos_full'][$voto][0]:0);

									$txt .= '<tr>
<td'.($respuestas_desc[$voto]?' title="'.$respuestas_desc[$voto].'" class="punteado"':'').'>'.$respuestas[$voto].'</td>
<td align="right"><b>'.$voto_si.'</b></td>
<td align="right">'.$voto_no.'</td>
<td align="right"><b title="Votos computables: '.num($voto_si+$voto_no).', En Blanco: '.$voto_en_blanco.'">'.num(($voto_si>0?($voto_si*100)/($voto_si + $voto_no):0),1).'%</b></td>
</tr>';

								} else { $votos_en_blanco = $num; }
							} else { unset($escrutinio['votos'][$voto]);  }
						}
						$txt .= '</table>';

					} else {
						$txt .= '<table border="0" cellpadding="1" cellspacing="0" class="pol_table"><tr><th>Escrutinio</th><th>'.($r['tipo_voto']=='estandar'?'Votos':'Puntos').'</th><th></th></tr>';
						
						// Obtener ID del voto "En Blanco"
						foreach ($escrutinio['votos'] AS $voto => $num) { if ($respuestas[$voto] == 'En Blanco') { $en_blanco_ID = $voto; } }
						
						$puntos_total_sin_en_blanco = $puntos_total - $escrutinio['votos'][$en_blanco_ID];

						foreach ($escrutinio['votos'] AS $voto => $num) { 
							if ($respuestas[$voto]) {
								if ($respuestas[$voto] != 'En Blanco') {
									$txt .= '<tr><td nowrap="nowrap"'.($respuestas_desc[$voto]?' title="'.$respuestas_desc[$voto].'" class="punteado"':'').'>'.$respuestas[$voto].'</td><td align="right" title="'.num(($num*100)/$puntos_total, 1).'%"><b>'.num($num).'</b></td><td align="right">'.num(($num*100)/$puntos_total_sin_en_blanco, 1).'%</td></tr>';
								} else { $votos_en_blanco = $num; }
							} else { unset($escrutinio['votos'][$voto]);  }
						}
						$txt .= '<tr><td nowrap="nowrap" title="Voto no computable. Equivale a: No sabe/No contesta."><em>En Blanco</em></td><td align="right" title="'.num(($votos_en_blanco*100)/$puntos_total, 1).'%"><b>'.num($votos_en_blanco).'</b></td><td></td></tr></table>';
					}
				}
				

				// Imprime datos de legitimidad y validez
				$txt .= '</td>
<td valign="top" style="color:#888;"><br />
Legitimidad: <span style="color:#555;"><b>'.num($votos_total).'</b>&nbsp;votos</span>, <b>'.$escrutinio['votos_autentificados'].'</b>&nbsp;autentificados.<br />
Validez de esta votación: '.($validez?'<span style="color:#2E64FE;"><b>OK</b>&nbsp;'.num(($escrutinio['validez']['true'] * 100) / $votos_total, 1).'%</span>':'<span style="color:#FF0000;"><b>NULO</b>&nbsp;'.$porcentaje_validez.'%</span>').'<br />
<img width="230" height="130" title="Votos de validez: OK: '.num($escrutinio['validez']['true']).', NULO: '.$escrutinio['validez']['false'].'" src="http://chart.apis.google.com/chart?cht=p&chp=4.71&chd=t:'.$escrutinio['validez']['true'].','.$escrutinio['validez']['false'].'&chs=230x130&chds=a&chl=OK|NULO&chf=bg,s,ffffff01|c,s,ffffff01&chco=2E64FE,FF0000,2E64FE,FF0000" alt="Validez" /></td>
</tr></table>';


			} else { // VOTACION EN CURSO: VOTAR.

				$tiene_acceso_votar = nucleo_acceso($r['acceso_votar'],$r['acceso_cfg_votar']);


				$txt .= '<form action="http://'.strtolower($pol['pais']).'.'.DOMAIN.'/accion.php?a=votacion&b=votar" method="post">
<input type="hidden" name="ref_ID" value="'.$r['ID'].'"  /><p>';


				if ($r['tipo_voto'] == 'estandar') {

					if (($r['privacidad'] == 'false') AND (!isset($r['ha_votado']))) { $txt .= '<p style="color:red;">El voto es público en esta votación, por lo tanto NO será secreto.</p>'; }

					for ($i=0;$i<$respuestas_num;$i++) { if ($respuestas[$i]) { 
							$votos_array[] = '<option value="'.$i.'"'.($i==$r['que_ha_votado']?' selected="selected"':'').'>'.$respuestas[$i].'</option>'; 
					} }

					if ($r['aleatorio'] == 'true') { shuffle($votos_array); }

					$txt .= '<select name="voto" style="font-size:20px;white-space:normal;max-width:400px;">'.implode('', $votos_array).'</select>';

				} elseif (($r['tipo_voto'] == '3puntos') OR ($r['tipo_voto'] == '5puntos') OR ($r['tipo_voto'] == '8puntos')) {

					if ($r['ha_votado']) { $txt .= 'Tu voto preferencial ha sido recogido <b>correctamente</b>.<br /><br />'; }

					$txt .= '<span style="color:red;">Debes repartir <b>los puntos más altos a tus opciones preferidas</b>. Puntos no acumulables.</span>
<table border="0">
<tr>
<th colspan="'.substr($r['tipo_voto'], 0, 1).'" align="center">Puntos</th>
<th></th>
</tr>
<tr>
<th align="center">1</th>
<th align="center">2</th>
<th align="center">3</th>
'.($r['tipo_voto']=='5puntos'?'<th align="center">4</th><th align="center">5</th>':'').'
'.($r['tipo_voto']=='8puntos'?'<th align="center">4</th><th align="center">5</th><th align="center">6</th><th align="center">7</th><th align="center">8</th>':'').'
<th>Opciones</th>
</tr>';				if ($r['ha_votado']) { $ha_votado_array = explode(' ', $r['que_ha_votado']); }
					else { $ha_votado_array = array(0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0); }
					for ($i=0;$i<$respuestas_num;$i++) { if ($respuestas[$i]) { 
							$votos_array[] = '<tr>
<td valign="top"><input type="radio" name="voto_1" value="'.$i.'"'.($ha_votado_array[0]==$i?' checked="checked"':'').' /></td>
<td valign="top"><input type="radio" name="voto_2" value="'.$i.'"'.($ha_votado_array[1]==$i?' checked="checked"':'').' /></td>
<td valign="top"><input type="radio" name="voto_3" value="'.$i.'"'.($ha_votado_array[2]==$i?' checked="checked"':'').' /></td>
'.($r['tipo_voto']=='5puntos'?'
<td valign="top"><input type="radio" name="voto_4" value="'.$i.'"'.($ha_votado_array[3]==$i?' checked="checked"':'').' /></td>
<td valign="top"><input type="radio" name="voto_5" value="'.$i.'"'.($ha_votado_array[4]==$i?' checked="checked"':'').' /></td>
':'').'
'.($r['tipo_voto']=='8puntos'?'
<td valign="top"><input type="radio" name="voto_4" value="'.$i.'"'.($ha_votado_array[3]==$i?' checked="checked"':'').' /></td>
<td valign="top"><input type="radio" name="voto_5" value="'.$i.'"'.($ha_votado_array[4]==$i?' checked="checked"':'').' /></td>
<td valign="top"><input type="radio" name="voto_6" value="'.$i.'"'.($ha_votado_array[5]==$i?' checked="checked"':'').' /></td>
<td valign="top"><input type="radio" name="voto_7" value="'.$i.'"'.($ha_votado_array[6]==$i?' checked="checked"':'').' /></td>
<td valign="top"><input type="radio" name="voto_8" value="'.$i.'"'.($ha_votado_array[7]==$i?' checked="checked"':'').' /></td>
':'').'
<td nowrap="nowrap"'.($respuestas_desc[$i]?' title="'.$respuestas_desc[$i].'" class="punteado"':'').'>'.($respuestas[$i]==='En Blanco'?'<em title="Equivale a No sabe/No contesta. No computable.">En Blanco</em>':$respuestas[$i]).'</td>
</tr>';
					} }
					if ($r['aleatorio'] == 'true') { shuffle($votos_array); }
					$txt .= implode('', $votos_array).'
<tr>
<th align="center">1</th>
<th align="center">2</th>
<th align="center">3</th>
'.($r['tipo_voto']=='5puntos'?'<th align="center">4</th><th align="center">5</th>':'').'
'.($r['tipo_voto']=='8puntos'?'<th align="center">4</th><th align="center">5</th><th align="center">6</th><th align="center">7</th><th align="center">8</th>':'').'
<th></th>
</tr>
</table>';
				} elseif ($r['tipo_voto'] == 'multiple') { // VOTAR MULTIPLE

					if ($r['ha_votado']) { $txt .= 'Tus votos múltiples han sido recogidos <b>correctamente</b>. '; }

					$txt .= '<table border="0">
<tr>
<th>SI</th>
<th>NO</th>
<th nowrap="nowrap"><em>En Blanco</em></th>
<th></th>
</tr>';				if ($r['ha_votado']) { $ha_votado_array = explode(' ', $r['que_ha_votado']); }
					else { $ha_votado_array = array(0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0); }

					for ($i=0;$i<$respuestas_num;$i++) { if (($respuestas[$i]) AND ($respuestas[$i] != 'En Blanco')) { 
							$votos_array[] = '<tr>
<td valign="top" align="center"><input type="radio" name="voto_'.$i.'" value="1"'.($ha_votado_array[$i]==1?' checked="checked"':'').' /></td>
<td valign="top" align="center"><input type="radio" name="voto_'.$i.'" value="2"'.($ha_votado_array[$i]==2?' checked="checked"':'').' /></td>
<td valign="top" align="center"><input type="radio" name="voto_'.$i.'" value="0"'.($ha_votado_array[$i]==0||!$ha_votado_array[$i]?' checked="checked"':'').' /></td>
<td'.($respuestas_desc[$i]?' title="'.$respuestas_desc[$i].'" class="punteado"':'').'>'.$respuestas[$i].'</td>
</tr>';
					} }
					if ($r['aleatorio'] == 'true') { shuffle($votos_array); }
					$txt .= implode('', $votos_array).'<tr>
<th>SI</th>
<th>NO</th>
<th nowrap="nowrap"><em>En Blanco</em></th>
<th></th>
</tr>
</table>';


				}


				// Imprime boton para votar, aviso de tiempo y votacion correcta/nula.
				$txt .= ' '.boton(($r['ha_votado']?'Modificar voto':'Votar'), ($r['estado']!='borrador'&&$tiene_acceso_votar?'submit':false), false, 'large blue').' <span style="white-space:nowrap;">'.($tiene_acceso_votar?($r['ha_votado']?'<span style="color:#2E64FE;">Puedes modificar tu voto durante <span class="timer" value="'.$time_expire.'"></span>.</span>':'<span style="color:#2E64FE;">Tienes <span class="timer" value="'.$time_expire.'"></span> para votar.</span>'):'<span style="color:red;white-space:nowrap;">'.(!$pol['user_ID']?'<b>Para votar debes <a href="'.REGISTRAR.'?p='.PAIS.'">crear tu ciudadano</a>.</b>':'No tienes acceso para votar.').'</span>').'</span></p>

<p>
<input type="radio" name="validez" value="true"'.($r['que_ha_votado_validez']!='false'?' checked="checked"':'').' /> Votación válida.<br />
<input type="radio" name="validez" value="false"'.($r['que_ha_votado_validez']=='false'?' checked="checked"':'').' /> Votación nula (inválida, inapropiada o tendenciosa).
</p>

<p>Comentario (opcional, secreto y público al finalizar la votación).<br />
<input type="text" name="mensaje" value="'.$r['que_ha_mensaje'].'" size="60" maxlength="160" /></p>
</form>

'.($r['ha_votado']?'<p style="margin-top:30px;">Comprobante de voto:<br />
<input type="text" value="'.$r['ID'].'-'.$r['comprobante'].'" size="60" readonly="readonly" style="color:#AAA;" /> '.boton('Enviar al email', '/accion.php?a=votacion&b=enviar_comprobante&comprobante='.$r['ID'].'-'.$r['comprobante'], false, 'pill').'</p>':'');

			}

			// Añade tabla de escrutinio publico si es votacion tipo parlamento.
			if ($r['tipo'] == 'parlamento') {
				$txt .= '<table border="0" cellpadding="0" cellspacing="3" class="pol_table"><tr><th>Diputado</th><th></th><th colspan="2">Voto</th><th>Mensaje</th></tr>';
				$result2 = mysql_query("SELECT user_ID,
(SELECT nick FROM users WHERE ID = cargos_users.user_ID LIMIT 1) AS nick,
(SELECT (SELECT siglas FROM ".SQL."partidos WHERE ID = users.partido_afiliado LIMIT 1) AS las_siglas FROM users WHERE ID = cargos_users.user_ID LIMIT 1) AS siglas,
(SELECT voto FROM votacion_votos WHERE ref_ID = '".$r['ID']."' AND user_ID = cargos_users.user_ID LIMIT 1) AS ha_votado,
(SELECT mensaje FROM votacion_votos WHERE ref_ID = '".$r['ID']."' AND user_ID = cargos_users.user_ID LIMIT 1) AS ha_mensaje
FROM cargos_users
WHERE cargo = 'true' AND cargo_ID = '6'
ORDER BY siglas ASC", $link);
				while($r2 = mysql_fetch_array($result2)) {
					if ($r2['ha_votado'] != null) { $ha_votado = ' style="background:blue;"';
					} else { $ha_votado = ' style="background:red;"'; }
					$txt .= '<tr><td><img src="'.IMG.'cargos/6.gif" /> <b>' . crear_link($r2['nick']) . '</b></td><td><b>' . crear_link($r2['siglas'], 'partido') . '</b></td><td' . $ha_votado . '></td><td><b>' . $respuestas[$r2['ha_votado']]  . '</b></td><td style="color:#555;font-size:12px;" class="rich">'.$r2['ha_mensaje'].'</td></tr>';
				}
				$txt .= '</table>';
			}
		}
	}

} else {


	// Calcular votos por hora
	$result = mysql_query("SELECT COUNT(*) AS num FROM votacion_votos WHERE time >= '".date('Y-m-d H:i:s', time() - 60*60*2)."'", $link);
	while($r = mysql_fetch_array($result)) { $votos_por_hora = num($r['num']/2); }

	$result = mysql_query("SELECT COUNT(*) AS num FROM votacion WHERE estado = 'borrador' AND pais = '".PAIS."'", $link);
	while($r = mysql_fetch_array($result)) { $borradores_num = $r['num']; }

	$txt_title = 'Votaciones';
	$txt_nav = array('/votacion'=>'Votaciones');
	$txt_tab = array('/votacion/borradores'=>'Borradores ('.$borradores_num.')', '/votacion/crear'=>'Crear votación');
	
	$txt .= '
<span style="float:right;text-align:right;">
<b title="Promedio global de las ultimas 2 horas">'.$votos_por_hora.'</b> votos/hora</span>

<span style="color:#888;"><br /><b>Votaciones en curso</b>:</span>
<table border="0" cellpadding="1" cellspacing="0">
<tr>
<th></th>
<th>Votos</th>
<th></th>
<th>Finaliza en...</th>
</tr>';
	$mostrar_separacion = true;
	
	$result = mysql_query("SELECT ID, pregunta, time, time_expire, user_ID, estado, num, tipo, acceso_votar, acceso_cfg_votar, acceso_ver, acceso_cfg_ver,
(SELECT ID FROM votacion_votos WHERE ref_ID = votacion.ID AND user_ID = '" . $pol['user_ID'] . "' LIMIT 1) AS ha_votado
FROM votacion
WHERE estado = 'ok' AND pais = '".PAIS."'
ORDER BY time_expire DESC
LIMIT 500", $link);
	while($r = mysql_fetch_array($result)) {
		$time_expire = strtotime($r['time_expire']);

		if ((!isset($pol['user_ID'])) OR ((!$r['ha_votado']) AND ($r['estado'] == 'ok') AND (nucleo_acceso($r['acceso_votar'],$r['acceso_cfg_votar'])))) { 
			$votar = boton('Votar', (isset($pol['user_ID'])?'/votacion/'.$r['ID']:REGISTRAR.'?p='.PAIS), false, 'small blue').' ';
		} else { $votar = ''; }

		$boton = '';
		if ($r['user_ID'] == $pol['user_ID']) {
			if ($r['estado'] == 'ok') {
				if ($r['tipo'] != 'cargo') { $boton .= boton('Finalizar', '/accion.php?a=votacion&b=concluir&ID='.$r['ID'], '¿Seguro que quieres FINALIZAR esta votacion?', 'small orange'); }
				$boton .= boton('X', '/accion.php?a=votacion&b=eliminar&ID='.$r['ID'], '¿Seguro que quieres ELIMINAR esta votacion?', 'small red');
			}
		}
		
		if (($r['acceso_ver'] == 'anonimos') OR (nucleo_acceso($r['acceso_ver'], $r['acceso_cfg_ver']))) {
			$txt .= '<tr>
<td width="100"'.($r['tipo']=='referendum'?' style="font-weight:bold;"':'').'>'.ucfirst($r['tipo']).'</td>
<td align="right"><b>'.num($r['num']).'</b></td>
<td>'.$votar.'<a href="/votacion/'.$r['ID'].'" style="'.($r['tipo']=='referendum'?'font-weight:bold;':'').($r['acceso_ver']!='anonimos'?'color:red;" title="Votación privada':'').'">'.$r['pregunta'].'</a></td>
<td nowrap="nowrap" class="gris" align="right">'.timer($time_expire, true).'</td>
<td nowrap="nowrap">'.$boton.'</td>
<td></td>
</tr>';
		}
	}
	$txt .= '</table>';



	$txt_header .= '<script type="text/javascript">

function ver_votacion(tipo) {
	var estado = $("#c_" + tipo).is(":checked");
	if (estado) {
		$(".v_" + tipo).show();
	} else {
		$(".v_" + tipo).hide();
	}
}

</script>';
	

$txt .= '<span style="color:#888;"><br /><b>Finalizadas</b>:</span> &nbsp; &nbsp; 

<span style="color:#666;padding:3px 4px;border:1px solid #999;border-bottom:none;" class="redondeado"><b>
<input type="checkbox" onclick="ver_votacion(\'referendum\');" id="c_referendum" checked="checked" /> Referendums &nbsp; 
'.(ASAMBLEA?'':'<input type="checkbox" onclick="ver_votacion(\'parlamento\');" id="c_parlamento" checked="checked" /> Parlamento &nbsp; ').' 
<input type="checkbox" onclick="ver_votacion(\'sondeo\');" id="c_sondeo" checked="checked" /> Sondeos</b> &nbsp; 
<input type="checkbox" onclick="ver_votacion(\'cargo\');" id="c_cargo" /> Cargos &nbsp; 
<input type="checkbox" onclick="ver_votacion(\'privadas\');" id="c_privadas" /> <span style="color:red;">Privadas</span> &nbsp; 
</span>

<hr />
<table border="0" cellpadding="1" cellspacing="0" class="pol_table">
';
	$mostrar_separacion = true;
	$result = mysql_query("SELECT ID, pregunta, time, time_expire, user_ID, estado, num, tipo, acceso_votar, acceso_cfg_votar, acceso_ver, acceso_cfg_ver
FROM votacion
WHERE estado = 'end' AND pais = '".PAIS."'
ORDER BY time_expire DESC
LIMIT 500", $link);
	while($r = mysql_fetch_array($result)) {
		$time_expire = strtotime($r['time_expire']);
		
		if (($r['acceso_ver'] == 'anonimos') OR (nucleo_acceso($r['acceso_ver'], $r['acceso_cfg_ver']))) {
			$txt .= '<tr class="v_'.$r['tipo'].($r['acceso_ver']!='anonimos'?' v_privadas':'').'"'.(in_array($r['tipo'], array('referendum', 'parlamento', 'sondeo'))&&$r['acceso_ver']=='anonimos'?'':' style="display:none;"').'>
<td width="100"'.($r['tipo']=='referendum'?' style="font-weight:bold;"':'').'>'.ucfirst($r['tipo']).'</td>
<td align="right"><b>'.num($r['num']).'</b></td>
<td><a href="/votacion/'.$r['ID'].'" style="'.($r['tipo']=='referendum'?'font-weight:bold;':'').($r['acceso_ver']!='anonimos'?'color:red;" title="Votación privada':'').'">'.$r['pregunta'].'</a></td>
<td nowrap="nowrap" align="right" class="gris">'.timer($time_expire, true).'</td>
<td></td>
</tr>';
		}
	}
	$txt .= '</table>';



}



//THEME
$txt_menu = 'demo';
include('theme.php');
?>