<?php
use App\Http\Controllers\RecordsController;
use App\Spending;
use App\Http\Controllers\SpendingController;

$botman = resolve('botman');

$botman->hears('(\d*[\.\,]?\d+?) (.*)', SpendingController::class . '@saveSpending');
$botman->hears('records', RecordsController::class . '@show');
$botman->hears('/start', SpendingController::class . '@welcome');