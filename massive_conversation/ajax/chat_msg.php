<?php


for ($i=0;$i<10;$i++) {
    echo mt_rand(0,100).'<br />';
}

echo '<br />';

foreach (sql("SELECT * FROM msg ORDER BY msg_id ASC") AS $r) {
    echo date('H:m:s', strtotime($r['date_creation'])).'&nbsp; '.$r['text'].'<br />';
}
