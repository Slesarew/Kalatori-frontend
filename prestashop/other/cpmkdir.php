#!/usr/bin/php

<?php

if($argc <= 1) die(
    "USE:\n"
    ."cpmkdir.php .path1/path2/path3/path4\n"
    ."cpmkdir.php ./path/file.from ./path1/path2/path3/path4/file.to\n"
);

if($argc == 2) {
    $dir=$argv[1];
    $file=false;
    echo "Create path: [$dir]\n";
} else {
    $file=$argv[1];
    $dir=$argv[2];
    echo "Copy file [$file] to created path [$dir]\n";
}

echo "\n Create dir: $dir\n";

$d=explode('/',$dir); $x=''; for($i=0;$i<sizeof($d);$i++) { $l=$d[$i];
    $x.=($x==''?'':'/').$l;
    echo $x."\n";
    if( $x=='.' || is_dir($x) || ( $i==(sizeof($d)-1) && $file ) ) continue;
    mkdir($x); chmod($x,0777);
}

if($file) { copy($file,$dir); chmod($x,0666); }

?>