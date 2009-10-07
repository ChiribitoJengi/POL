<?php 
include('inc-login.php');
$adsense_exclude = true;

function polform($action, $pol_form, $submit='Enviar', $submit_disable=false) {
	global $pol, $link;

	$f .= '<div class="pol_form">
<form action="/accion.php?a=' . $action . '" method="post">
<input type="hidden" name="user_ID" value="' . $pol['user_ID'] . '"  />
<ol>
';

	if ($pol_form) {
		foreach($pol_form as $v) {
			if (!$v['size']) { $v['size'] = '30'; }
			if (!$v['maxlenght']) { $v['maxlength'] = '255'; }

			switch ($v['type']) {
								
				case 'hidden':
					$f .= '<input type="hidden" name="' . $v['name'] . '" value="' . $v['value'] . '"  />' . "\n";
					
					break;

				case 'textrico':
					$f .= '<li><b>' . $v['nombre'] . ':</b><br />';
					include('inc-functions-accion.php');
					$f .= editor_enriquecido($v['name']) . '</li>' . "\n";
					
					break;


				case 'text':
					$f .= '<li><b>' . $v['nombre'] . ':</b> ' . $v['desc'] . '<br /><input type="' . $v['type'] . '" name="' . $v['name'] . '" size="' . $v['size'] . '" maxlength="' . $v['maxlegth'] . '" /></li>' . "\n";
					break;

				case 'select_partidos':
					$f .= '<li><b>Partido Pol&iacute;tico:</b> Elige las siglas de tu partido o <em>ninguno</em>.<br /><select name="partido"><option value="0">Ninguno</option>';
					
					$result = mysql_query("SELECT siglas, ID FROM ".SQL."partidos WHERE estado = 'ok' ORDER BY siglas ASC", $link);
					while($row = mysql_fetch_array($result)){
						if ($v['partido'] == strtolower($row['siglas'])) { $selected = ' selected="selected"'; } else { $selected = '';  }
						$f .= '<option value="' . $row['ID'] . '"' . $selected . '>' . $row['siglas'] . '</option>';
					}

					$f .= '</select></li>' . "\n";
					break;

				case 'select_nivel':
					$f .= '<li><b>Nivel de acceso:</b> Selecciona el nivel minimo necesario para editar el documento.<br />' . form_select_nivel() . '</li>' . "\n";
					break;

				case 'select_cat':
					$f .= '<li><b>Categor&iacute;a:</b><br />' . form_select_cat('docs') . '</li>' . "\n";
					break;


				case 'selectexpire':
					$f .= '<li><b>Duraci&oacute;n:</b> tiempo de expiraci&oacute;n de la expulsi&oacute;n.<br />
<select name="expire">
<option value="60">1 minuto</option>
<option value="120">2 minutos</option>
<option value="300">5 minutos</option>
<option value="600">10 minutos</option>
<option value="900">15 minutos</option>
<option value="1800">30 minutos</option>
<option value="3600">1 hora</option>
<option value="18000">5 horas</option>
<option value="86400">1 d&iacute;a</option>
<option value="259200">3 d&iacute;as</option>
<option value="518400">6 d&iacute;as</option>
<option value="777600">9 d&iacute;as</option>
</select></li>' . "\n";
					break;

			}
		}
	}
	if ($submit_disable == true) { $submit_disable = ' disabled="disabled"'; }
	$f .= '<li><input type="submit" value="' . $submit . '"' . $submit_disable . ' /></li></ol></form></div>';

	return $f;
}








switch ($_GET['a']) {

case 'crear-documento':

	$txt .= '<p>Formulario para crear un nuevo documento en '.PAIS.'.</p>';

	$pol_form = array(
	array('type'=>'select_nivel'),
	array('type'=>'select_cat'),
	array('type'=>'text', 'name'=>'title', 'size'=>'60', 'maxlegth'=>'200', 'nombre'=>'T&iacute;tulo', 'desc'=>'Frase &uacute;nica a modo de titular del documento.'),
	array('type'=>'textrico', 'name'=>'text', 'size'=>'10', 'nombre'=>'Documento'),
	);
	$txt .= polform($_GET['a'], $pol_form, 'Crear documento');


	break;

case 'solicitar-ciudadania':
	header('Location: '.REGISTRAR); exit;
	break;


case 'afiliarse':

	$txt .= '<p>Hola <b>' . $pol['nick'] . '</b>!</p> <p>Puedes afiliarte a cualquier partido o a ninguno, es una muestra tu fidelidad de forma simbolica.</p>';

	$pol_form = array(
	array('type'=>'select_partidos', 'partido'=>$_GET['b']),
	);
	if ($pol['config']['elecciones_estado'] == 'elecciones') { $submit_disable = true; } else { $submit_disable = false; }
	$txt .= polform($_GET['a'], $pol_form, 'Afiliarse', $submit_disable);


	break;

case 'crear-partido':

	$txt .= '<p>Formulario para crear un nuevo partido pol&iacute;tico en '.PAIS.'.</p>

<ul>
<li>Afirmas que el partido creado es legal en '.PAIS.'.</li>
</ul>
';
	$pol_form = array(
	array('type'=>'text', 'name'=>'siglas', 'value'=>'', 'size'=>'6', 'maxlegth'=>'10', 'nombre'=>'Siglas del partido', 'desc'=>'Escribe entre 2 y 10 letras may&uacute;sculas, guion permitido.'),
	array('type'=>'text', 'name'=>'nombre', 'value'=>'', 'size'=>'', 'maxlegth'=>'40', 'nombre'=>'Nombre del partido', 'desc'=>'Frase a modo de nombre que concuerda con las siglas anteriormente dadas.'),
	array('type'=>'textrico', 'name'=>'descripcion', 'size'=>'10', 'nombre'=>'Introducci&oacute;n'),
	);
	$txt .= polform($_GET['a'], $pol_form, 'Crear partido');


	break;




default: header('Location: http://'.HOST.'/');
}






//THEME
if (!$txt_title) { $txt_title = 'Formulario'; }
include('theme.php');
?>
