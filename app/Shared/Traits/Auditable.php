<?php

declare(strict_types=1);

namespace App\Shared\Traits;

use Illuminate\Support\Facades\Context;
use Illuminate\Support\Str;
use Spatie\Activitylog\Contracts\Activity;
use Spatie\Activitylog\Models\Concerns\LogsActivity;
use Spatie\Activitylog\Support\LogOptions;

trait Auditable
{
    use LogsActivity;

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->useLogName(Str::snake(class_basename($this)))
            ->logFillable()
            ->logOnlyDirty()
            ->dontLogEmptyChanges();
    }

    public function beforeActivityLogged(Activity $activity, string $eventName): void
    {
        $activity->event = $eventName;
        $activity->properties = ($activity->properties ?? collect())
            ->merge([
                'branch_id' => Context::get('branch_id'),
                'context_user_id' => Context::get('user_id'),
            ]);
    }
}
