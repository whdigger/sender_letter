<?php

if ($item === '' && !file_exists($item)) {
    exit('');
}
$linesz = filesize($item) + 1;
$fp = fopen($item, 'r');
if ($fp === false) {
    $item = basename($item);
    echo "Не возможно открыть файл $item";
} else {
    echo fread($fp, $linesz);
    /*
     *  $line = fread($fp, $linesz);
     *  echo nl2br($line);
    */
}

fclose($fp);
?>  