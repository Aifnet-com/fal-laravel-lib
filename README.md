### Installation

#### 1. Add routes
In `routes/web.php`, include the Fal routes:

```php
include_once base_path('app/Lib/Fal/routes.php');
```

#### 2. Schedule the command
Add to your scheduler (Laravel 10 or below: `app/Console/Kernel.php`; Laravel 11+: `bootstrap/app.php`):

```php
$schedule->command('fal:check-for-stuck-requests')
    ->everyFiveMinutes()
    ->withoutOverlapping();
```

#### 3. Listen for `FalWebhookArrived` event
You can attach listeners to the `FalWebhookArrived` event to handle webhook processing.

In `bootstrap/app.php`:
```php
->withEvents(discover: [
    __DIR__ . '/../app/Lib/YOUR_COMPONENT/Listeners',
])
```

Your listener might look like this:

```php
namespace App\Lib\YOUR_COMPONENT\Listeners;

use App\Lib\Fal\Events\FalWebhookArrived;

class AudioGenerationCompleted
{
    public function handle(FalWebhookArrived $event): void
    {
        if (empty($event->data['falRequestId'])) {
            return Log::error("[AudioGenerationCompleted] Missing FAL Request ID");
        }

        $falRequest = FalRequest::findByRequestId($event->data['falRequestId']);

        if ($falRequest->type != FalRequest::TYPE_AUDIO) {
            return false;
        }

        // Your logic here
    }
}
```
