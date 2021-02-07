<?php

require '../app/code/helper/app.php';

$count_osmolateur = 0;
$date = new DateTime();

$day_name = date('D');

$periode = '-1 day';

$date = new DateTime();
$today = $date->format('Y-m-d H:i:s');
$date->modify($periode);
$yesterday = $date->format('Y-m-d H:i:s');



