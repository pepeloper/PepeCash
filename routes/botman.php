<?php

use App\Http\Controllers\RecordsController;
use App\Http\Controllers\SpendingController;

$botman = resolve('botman');

$botman->hears('(\d*[\.\,]?\d+?) (.*)', SpendingController::class . '@saveSpending');
$botman->hears('records', RecordsController::class . '@show');
$botman->hears('records ((?i)[a-z]+)', RecordsController::class . '@showByCategory');
$botman->hears('/start', SpendingController::class . '@welcome');