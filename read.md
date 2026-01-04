Here’s the **full `README.md`** you can use for your Laravel 12 + Telegraph Telegram bot project, with **clear steps**, including:

* creating a bot with `telegraph:new-bot`
* setting the webhook with `telegraph:set-webhook`
* adding `.env` config (APP_URL + TELEGRAM_BOT_TOKEN)
* CSRF setup specific to Laravel 12 (`withMiddleware`)
* ngrok local HTTPS testing
* simple reply logic (hi → hello)
* optional chat creation

This is a **ready-to-use file** you can copy into your project.

---

```md
# Laravel Telegram Bot (Simple Hello Bot)

This project shows how to build a **Telegram bot in Laravel 12** using the **defstudio/telegraph** package.

The bot will:
  * receive webhook updates from Telegram
  * reply **hello** when a user sends **hi**

For local testing you’ll use **ngrok** to expose your local server over HTTPS.

---

## Quickstart

### 1) Creating a new Telegram Bot

1. Open the Telegram app.
2. Search for **@BotFather**.
3. Send the command:
```

/newbot

```
4. BotFather will ask for a bot token. Paste your token when prompted.
5. BotFather will show you a token like:
```

123456789:ABCdefGhiJkl_MNOPqrstUVwxyZ

````
Save this — you’ll use it in your project.

---

## 2) Add the bot to Telegraph (Laravel project)

Install the Telegraph package:

```bash
composer require defstudio/telegraph
````

Publish migrations and migrate:

```bash
php artisan vendor:publish --tag="telegraph-migrations"
php artisan migrate
```

Publish config:

```bash
php artisan vendor:publish --tag="telegraph-config"
```

---

## 3) Registering a Bot with Telegraph

Telegraph comes with an artisan wizard to create a bot record.

Run:

```bash
php artisan telegraph:new-bot
```

You will be prompted like this:

```
Please, enter the bot token:
> 123456789:ABCdefGhiJkl_MNOPqrstUVwxyZ

Enter the bot name (optional):
> TeleDemoChat

Do you want to add a chat to this bot? (yes/no) [no]:
> no

Do you want to setup a webhook for this bot? (yes/no) [no]:
> no

New bot TeleDemoChat has been created
```

At the end you’ll see a **bot ID** (e.g., `1`).
This is stored in your database (`telegraph_bots` table). You’ll use it later.

---

## 4) Local HTTPS (ngrok) for webhook

Telegram requires a secure HTTPS URL for webhooks.

In one terminal start your Laravel app:

```bash
php artisan serve --port=8000
```

In another terminal start ngrok:

```bash
ngrok http 8000
```

You’ll see something like:

```
Forwarding  https://abcd1234.ngrok.io -> http://localhost:8000
```

Copy the **HTTPS** URL. You’ll use it in `.env` and to register the webhook.

---

## 5) Update `.env`: App URL and Telegram Bot Token

Open your project’s `.env` and add or update:

```
APP_URL=https://abcd1234.ngrok.io
TELEGRAM_BOT_TOKEN=123456789:ABCdefGhiJkl_MNOPqrstUVwxyZ
```

Replace:

* `https://abcd1234.ngrok.io` with **your ngrok HTTPS URL**
* `123456789:ABCdef…` with **your real bot token**

Then clear Laravel config:

```bash
php artisan config:clear
php artisan route:clear
```

---

## 6) CSRF Setup (Laravel 12)

Laravel protects POST routes with CSRF by default. Telegram webhook POSTs do **not** include a CSRF token, so you must exclude the webhook route.

Update **`config/app.php`** with:

```php
use Illuminate\Routing\Middleware\ValidatePostSize;

return [

    // …

    'withMiddleware' => function ($middleware) {
        $middleware->validateCsrfTokens(
            except: [
                'telegraph/*/webhook', // exclude webhook from CSRF
            ],
        );
    },

    // …
];
```

This ensures Laravel does not block Telegram’s webhook calls with a 419 error.

---

## 7) Setting a Webhook

Now register the webhook for your bot:

```bash
php artisan telegraph:set-webhook {bot_id}
```

Replace `{bot_id}` with the ID shown when you created the bot (e.g., `1`).

This command tells Telegram to send updates to:

```
https://abcd1234.ngrok.io/telegraph/123456789:ABCdefGhiJkl_MNOPqrstUVwxyZ/webhook
```

The `{token}` part is your bot token (Telegraph’s default webhook route).

---

## 8) Adding a Chat (optional)

If you want to pre-add a chat to your bot (for record keeping), run:

```bash
php artisan telegraph:new-chat {bot_id}
```

Follow the prompts to choose a chat.

---

## 9) Sending a Message (Webhook Logic)

Add this to **`routes/web.php`**:

```php
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

Route::post('/telegraph/{token}/webhook', function (Request $request, $token) {

    Log::info('Telegram webhook request', $request->all());

    $text   = strtolower($request->input('message.text', ''));
    $chatId = $request->input('message.chat.id');

    if ($chatId && $text === 'hi') {
        $bot = resolve('telegraph.bot');

        $bot->sendMessage([
            'chat_id' => $chatId,
            'text'    => 'hello',
        ]);
    }

    return response()->json(['ok' => true]);
});
```

This route:

* logs the incoming webhook payload
* replies **hello** when user sends **hi**
* always returns success to Telegram

---

## 10) Testing

1. Open your bot in Telegram (use username from BotFather).
2. Send:

   ```
   hi
   ```
3. Bot should reply:

   ```
   hello
   ```

---

## Debugging

### Check webhook registration

Visit:

```
https://api.telegram.org/bot<YOUR_TOKEN>/getWebhookInfo
```

Example:

```
https://api.telegram.org/bot123456789:ABCdefGhiJkl_MNOPqrstUVwxyZ/getWebhookInfo
```

Look for:

* `url` set to your ngrok URL + `/telegraph/<token>/webhook`
* `last_error_message` empty → good

---

### View logs

Tail Laravel logs for webhook hits:

```
tail -f storage/logs/laravel.log
```

You should see entries like:

```
Telegram webhook request
```

with the JSON payload.

---

## Notes

* If you restart ngrok, your HTTPS URL changes — re-run:

  ```
  php artisan telegraph:set-webhook {bot_id}
  ```
* CSRF must be excluded for webhook POSTs.
* The webhook route used by Telegraph is:

  ```
  POST /telegraph/{token}/webhook
  ```

---
