<?php
require "vendor/autoload.php";
use Blankalmasry\Glicko2\Glicko2;

var_dump(Glicko2::match([new Blankalmasry\Glicko2\Rating\Rating()], [new Blankalmasry\Glicko2\Rating\Rating()], 1, 0));

