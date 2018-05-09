<?php

namespace App\Http\Conversations;

use BotMan\BotMan\Messages\Incoming\Answer;
use BotMan\BotMan\Messages\Outgoing\Question;
use BotMan\BotMan\Messages\Outgoing\Actions\Button;
use BotMan\BotMan\Messages\Conversations\Conversation;
use App\Spending;
use App\Category;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Carbon;

class AddSpending extends Conversation
{

    protected $spending;

    public function __construct($concept, $amount)
    {
        $this->spending = new Spending();
        $this->spending->concept = $concept;
        $this->spending->amount = $amount * 100;
        $this->spending->telegram_id = $this->bot->getUser()->getId();
    }
    /**
     * Start the conversation.
     *
     * @return mixed
     */
    public function run()
    {
        $buttons = [];
        Category::all()->each(function($item) use (&$buttons) {
            $buttons[] = Button::create($item->name)->value($item->id);
        });

        $question = Question::create("A que categoría quieres añadirlo? 🗒️")
            ->fallback('Unable to ask question')
            ->callbackId('ask_reason')
            ->addButtons($buttons);

        return $this->ask($question, function (Answer $answer) {
            if ($answer->isInteractiveMessageReply()) {
                $this->spending->category()->associate(Category::find($answer->getValue()));

                // Set here the created_at timestamp
                $now = Carbon::now()->format('d/m/Y');
                $question = Question::create("Cuándo ha sido el gasto? 📅 Puedes escribir la fecha ({$now})")
                    ->fallback('Unable to ask question')
                    ->callbackId('ask_reason')
                    ->addButtons([
                        Button::create('Now')->value('now'),
                        Button::create('Tomorrow')->value('tomorrow'),
                        Button::create('Yerterday')->value('yesterday'),
                    ]);

                $this->ask($question, function (Answer $answer) {
                    if ($answer->isInteractiveMessageReply()) {
                        switch($answer->getValue()) {
                            case 'now':
                                $date = Carbon::now();
                            case 'yesterday':
                                $date = Carbon::yesterday();
                            case 'tomorrow':
                                $date = Carbon::tomorrow();
                        }
                    }
                    else {
                        $date = Carbon::parse($answer->getValue());
                    }
                    $this->spending->created_at = $date;
                    $this->spending->save();
                    $this->say('Gasto guardado');
                });
            }
        });
    }
}
