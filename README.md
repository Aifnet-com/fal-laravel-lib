### Installation

#### 1. Add routes
In `routes/web.php`, include the Fal routes:

```php
include_once base_path('app/Lib/Fal/routes.php');
```

#### 2. Schedule the command
```php
$schedule->command('fal:check-for-stuck-requests')->everyFiveMinutes()->withoutOverlapping();
```

