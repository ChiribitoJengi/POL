<?php # POL.VirtualPol.com — Copyright (c) 2008 Javier González González <gonzo@virtualpol.com> — MIT License 


$pre_login = true;
if ($pol['user_ID']) {
    sql("UPDATE users SET lang = ".($_POST['lang']?"'".$_POST['lang']."'":"NULL")." WHERE ID = '".$pol['user_ID']."' LIMIT 1");
}
redirect('/registrar/login/panel');