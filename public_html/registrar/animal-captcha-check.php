<?php 
/*
### ANIMAL CAPTCHA 1.4
Author: GONZO (Javier Gonzalez Gonzalez) gonzomail@gmail.com
url: http://gonzo.teoriza.com/animal-captcha
Blogs Teoriza (www.Teoriza.com)
2009/11/20
###
*/

function animal_captcha_check($try) {
	if (!isset($_SESSION)) { session_start(); }
	$try = trim(strip_tags($try));
	$try = ereg_replace("[��������]", "a", $try);
	$try = ereg_replace("[������]", "e", $try);
	$try = ereg_replace("[������]", "i", $try);
	$try = ereg_replace("[��������]", "o", $try);
	$try = ereg_replace("[������]", "u", $try);
	$try = ereg_replace("[��]", "c", $try);
	$try = ereg_replace("[��]", "n", $try);
	$delete = array('�', '�', '�', '�', '�', '�', '�', '"', "'", '.', ',', '_', ':',';','.', '�','!','�','?','[',']','{','}','(',')','/','%','&','$','@');
	$try = str_replace($delete, "", $try);
	$try = utf8_encode(strtolower($try));
	$trys = explode(" ", $try);
	$animals = explode('|', $_SESSION['animalcaptcha']);
	unset($_SESSION['animalcaptcha']);
	$result = true;
	$e = 0;
	foreach($animals AS $one_animal) {	
		$animals_resp = explode('-', $one_animal);
		if (!$trys[$e]) { $trys[$e] = $trys[0]; }
		if (!in_array($trys[$e], $animals_resp)) { $result = false; }
		$e++;
	}
	return $result;
}
?>