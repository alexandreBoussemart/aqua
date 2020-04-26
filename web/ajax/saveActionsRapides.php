<?php

require '../../cron/helper/functions.php';

// form actions rapides
setStatus($link, $_POST['on_off_osmolateur'], 'on_off_osmolateur');
setStatus($link, $_POST['on_off_ecumeur'], 'on_off_ecumeur');

