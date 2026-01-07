# Laravel Telegram Bot (Hello Bot)

## Overview

This guide shows how to build a **simple Telegram bot using Laravel 12** with the **defstudio/telegraph** package.

**Bot behavior**

* Receives webhook updates from Telegram
* Replies `hello` when a user sends `hi`

**Local development**

* Uses **ngrok** to expose your local server over HTTPS

---

## Prerequisites

* PHP 8.2+
* Laravel 12
* Composer
* Telegram account
* ngrok

---

## 1. Create a Telegram Bot

1. Open Telegram.
2. Search for **@BotFather**.
3. Send:

```
/newbot
```

4. Complete the setup.
5. Save the bot token provided by BotFather.

---

## 2. Install Telegraph

Install the package:

```bash
composer require defstudio/telegraph
```

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

## 3. Register the Bot in Telegraph

Run:

```bash
php artisan telegraph:new-bot
```

* Enter the bot token.
* Optionally give the bot a name.
* Skip chat and webhook setup for now.

Note the **bot ID** shown at the end.

---

## 4. Run Laravel and ngrok

Start Laravel:

```bash
php artisan serve --port=8000
```

Start ngrok in another terminal:

```bash
ngrok http 8000
```

Copy the HTTPS forwarding URL.

---

## 5. Configure Environment Variables

Update `.env`:

```
APP_URL=https://your-ngrok-url.ngrok.io
TELEGRAM_BOT_TOKEN=your_bot_token
```

Clear caches:

```bash
php artisan config:clear
php artisan route:clear
```

---

## 6. Exclude Webhook From CSRF Protection

Telegram webhook requests do not include CSRF tokens.

Update **`config/app.php`** to exclude the Telegraph webhook route from CSRF validation.

This prevents 419 errors when Telegram sends updates.

---

## 7. Register the Webhook

Register the webhook for your bot:

```bash
php artisan telegraph:set-webhook {bot_id}
```

Replace `{bot_id}` with your actual bot ID.

---

## 8. Webhook Logic

* Telegram sends messages to the webhook URL.
* Laravel receives the payload.
* If the message text is `hi`, the bot replies with `hello`.
* All requests are logged and acknowledged.

---

## 9. Test the Bot

1. Open your bot in Telegram.
2. Send:

```
hi
```

3. The bot replies:

```
hello
```

---

## Debugging

### Check Webhook Status

```
https://api.telegram.org/bot<YOUR_TOKEN>/getWebhookInfo
```

Confirm the webhook URL is set and no errors are shown.

---

## Notes

* Restarting ngrok changes the HTTPS URL.
* After restarting ngrok, re-run:

```bash
php artisan telegraph:set-webhook {bot_id}
```

* You can inspect incoming webhook requests using **ngrok inspect**:

  * Open `http://127.0.0.1:4040` in your browser.
  * View request payloads, headers, and response status.
  * Useful for debugging webhook issues and verifying Telegram calls.

* The default Telegraph webhook route is:

```
POST /telegraph/{token}/webhook
```

* Use Telegraph command handlers
* Add multiple keyword responses
