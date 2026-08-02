<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Http\Requests\SnoozeNotificationDeliveryRequest;
use App\Models\NotificationDelivery;
use App\Models\Project;
use Illuminate\Http\RedirectResponse;

class NotificationDeliveryController extends Controller
{
    /**
     * Acknowledge a notification delivery.
     */
    public function acknowledge(Project $project, NotificationDelivery $delivery): RedirectResponse
    {
        $this->authorize('update', $delivery->project);

        $delivery->acknowledge(auth()->user());

        return redirect()->back()->with('status', 'Alert acknowledged.');
    }

    /**
     * Snooze a notification delivery until a given time.
     */
    public function snooze(SnoozeNotificationDeliveryRequest $request, Project $project, NotificationDelivery $delivery): RedirectResponse
    {
        $until = $request->filled('until') ? $request->date('until') : now()->addHours(2);

        $delivery->snooze($until);

        return redirect()->back()->with('status', 'Alert snoozed.');
    }
}
