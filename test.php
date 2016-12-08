<?php
// $rs = file_get_contents("20161202_sns_record.log");

// var_dump(str_replace("\n", "<br>", $rs));

$a = ["d"=>["a","c"]];;

$b = ["b"=>["a","c"]];

$c = array_merge($a,$b);

var_dump($c);