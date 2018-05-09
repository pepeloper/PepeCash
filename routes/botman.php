<?php
use App\Http\Controllers\BotManController;
use App\Spending;

$botman = resolve('botman');

$botman->hears('(\d*[\.\,]?\d+?) (.*)', function($bot, $amount, $concept) {
    $bot->startConversation(new App\Http\Conversations\AddSpending($concept, $amount));
});

$botman->hears('records', function($bot) {
    $spendings = Spending::where('telegram_id', $bot->getUser()->getId());

    $c = App\Category::all()
        ->load(['spendings' => function($query) use ($bot) {
            $query->where('telegram_id', $bot->getUser()->getId());
        }])
        ->mapWithKeys(function ($item) {
            $value = $item->spendings->reduce(function ($carry, $item) {
                return $carry + $item->amountFormatted;
            }, 0);

            return [$item->name => $value];
        });

    $c->each(function($item, $key) use ($bot){
        switch ($key) {
            case 'Tech': $icon = '💻'; break;
            case 'Viaje': $icon = '✈️'; break;
            case 'Comida': $icon = '🍴'; break;
        }
        $bot->reply("{$icon} {$key}: {$item}€");
    });
});