<?php

namespace App\Http\Controllers;

use DefStudio\Telegraph\Models\TelegraphBot;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class TelegramWebhookController extends Controller
{
  public function handle(Request $request)
  {
    Log::info('=== WEBHOOK RECEIVED ===');
    Log::info('Request data:', $request->all());

    $update = $request->all();

    $bot = TelegraphBot::first();

    if (!$bot) {
      Log::error('No bot found!');
      return response()->json(['ok' => true]);
    }

    if (isset($update['message'])) {
      $this->handleMessage($update['message'], $bot);
    }

    return response()->json(['ok' => true]);
  }

  protected function handleMessage($message, $bot)
  {
    $chatId = $message['chat']['id'];
    $text = $message['text'] ?? '';

    Log::info("Message from {$chatId}: {$text}");

    $chat = $bot->chats()->where('chat_id', $chatId)->first();

    if (!$chat) {
      $chat = $bot->chats()->create([
        'chat_id' => $chatId,
        'name' => $message['chat']['first_name'] ?? 'User'
      ]);
    }

    if ($text === '/start') {
      $chat->html("<strong>Hello!</strong>\n\nI'm here!")->send();
    } else {
      $chat->message("You said: {$text}")->send();
    }
  }
}
