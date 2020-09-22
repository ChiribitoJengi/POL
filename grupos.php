<?php # POL.VirtualPol.com — Copyright (c) 2008 Javier González González <gonzo@virtualpol.com> — MIT License 



$txt_title = _('Grupos');

switch ($_GET[1]) {

case 'crear':
	$txt_title = _('Crear grupo');
	$txt_nav = array('/grupos'=>_('Grupos'), _('Crear grupo'));
	$txt_tab = array('/grupos'=>_('Ver grupos'), '/grupos/crear'=>_('Crear grupo'));

	echo '<form action="/accion/grupos/crear" method="POST">

<p>'._('Nombre').': <input type="text" name="nombre" size="20" maxlength="40" /> <input type="submit" value="'._('Crear grupo').'"'.(nucleo_acceso($vp['acceso']['control_grupos'])?'':' disabled="disabled"').' /> ('._('Pueden crear grupos').': '.verbalizar_acceso($vp['acceso']['control_grupos']).')

</form>
';	
	break;

default:
	$txt_nav = array('/grupos'=>_('Grupos'));
	$txt_tab = array('/grupos/crear'=>_('Crear grupo'));

	echo '<p>'._('Afiliandote a grupos podrás acceder a sus foros, documentos, chats y votaciones. Puedes afiliarte a múltiples grupos').'.</p>

<form action="/accion/grupos/afiliarse" method="POST">

<fieldset><legend>'._('Grupos').'</legend>

<table border="0">
<tr>
<th></th>
<th></th>
<th>'._('Afiliados').'</th>
<th>'._('Foros asociados').'</th>
<th align="right">ID</th>
</tr>
';

	$result = mysql_query_old("SELECT ID, nick, grupos FROM users WHERE pais = '".PAIS."' AND estado = 'ciudadano' AND grupos != ''", $link);
	while($r = mysqli_fetch_array($result)) {
		foreach (explode(' ', $r['grupos']) AS $grupo_ID) { $users_array[$grupo_ID][] = $r['nick']; }
	}

	$result = mysql_query_old("SELECT url, title, acceso_cfg_leer FROM ".SQL."foros WHERE estado = 'ok' AND acceso_leer = 'grupos'", $link);
	while($r = mysqli_fetch_array($result)) {
		foreach (explode(' ', $r['acceso_cfg_leer']) AS $grupo_ID) { $foros_array[$grupo_ID][] = '<a href="/foro/'.$r['url'].'"><b>'.$r['title'].'</b></a>'; }
	}

	$result = mysql_query_old("SELECT * FROM grupos WHERE pais = '".PAIS."' ORDER BY grupo_ID ASC", $link);
	while($r = mysqli_fetch_array($result)) {
		echo '<tr>
<td align="right"><input type="checkbox" name="grupo_'.$r['grupo_ID'].'" id="grupo_'.$r['grupo_ID'].'" value="true"'.(nucleo_acceso('grupos', $r['grupo_ID'])?' checked="checked"':'').' /></td>
<td><b><label for="grupo_'.$r['grupo_ID'].'" class="inline">'.$r['nombre'].'</label></b></td>
<td align="right" style="font-size:18px;color:#777;" title="AFILIADOS: '.(is_array($users_array[$r['grupo_ID']])?implode(' ', $users_array[$r['grupo_ID']]):'').'"><b>'.$r['num'].'</b> (<span class="punteado">'._('Ver').'</span>)</td>
<td>'.(is_array($foros_array[$r['grupo_ID']])?implode(' ', $foros_array[$r['grupo_ID']]):'').'</td>
<td width="100" align="right" style="color:#888;">'.$r['grupo_ID'].'</td>
<td>'.(nucleo_acceso($vp['acceso']['control_grupos'])?boton(_('Eliminar'), '/accion/grupos/eliminar?grupo_ID='.$r['grupo_ID'], false, 'red'):'').'</td>
</tr>';
	}

	echo '</table></fieldset>

<p>'.boton(_('Guardar afiliación'), 'submit', false, 'blue').'</p>
	
</form>';
}



$txt_menu = 'demo';

?>
