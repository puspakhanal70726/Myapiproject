<?php
require './vendor/autoload.php';
$app = (new puspa\apiClient\App())->get();
$app->run();
