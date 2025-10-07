```md
# FAL Laravel Integration Library

This Laravel package provides integration with [FAL AI](https://fal.ai) via queue-based endpoint execution, webhook handling, error tracking, and request management.

---

## 🚀 Installation

```bash
composer require aifnet-com/fal-laravel
```

---

## ⚙️ Environment Setup

Add the following environment variables to your `.env` file:

```env
FAL_KEY=your-fal-api-key
NGROK_URL_FOR_FAL_WEBHOOK_LOCALHOST=https://your-ngrok-url.io
```

- `FAL_KEY`: Your API key from [https://fal.ai](https://fal.ai)
- `NGROK_URL_FOR_FAL_WEBHOOK_LOCALHOST`: Used in `local` environment to map the webhook route via ngrok

---

## 🧱 Migrations

This package auto-loads its migrations.

Run:

```bash
php artisan migrate
```

Or publish them into your project:

```bash
php artisan vendor:publish --tag=fal-migrations
```

This will create the following tables:
- `fal_requests`
- `fal_endpoints`
- `fal_data`
- `fal_errors`

---

## 📡 Webhook Handling

The package registers this route automatically:

```http
POST /fal/webhook
```

You don't need to manually define this. The controller handles incoming FAL webhook updates, finds the matching request, updates its status, and logs errors if applicable.

---

## 🧠 Submitting Requests

To submit a request to a FAL endpoint:

```php
use Aifnet\Fal\Models\FalRequest;

FalRequest::submit('your-endpoint-name', [
    'input_key' => 'value',
], [
    'user_id' => auth()->id(),
], FalRequest::TYPE_AUDIO);
```

---

## 🕒 Scheduled Command

This package includes a command to fail stale requests:

```
php artisan fal:check-for-stuck-requests
```

It will:
- Fail requests older than 10 minutes
- Mark them as `STATUS_FAILED`
- Trigger a `FalWebhookArrived` event

### Add it to your schedule:

In `App\Console\Kernel.php`:

```php
protected function schedule(Schedule $schedule)
{
    $schedule->command('fal:check-for-stuck-requests')->everyFiveMinutes();
}
```

## 🛠 Laravel Compatibility

- Laravel 8, 9, 10, 11
- PHP >= 8.0
- Auto-discovered Service Provider

---

## 📄 License

[MIT](LICENSE)