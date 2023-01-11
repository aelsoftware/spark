<?php

namespace Spark\Http\Controllers;

use Illuminate\Validation\ValidationException;

class CancelSubscriptionController
{
    use RetrievesBillableModels;

    /**
     * Pause the billable's current subscription.
     *
     * @return void
     */
    public function __invoke()
    {
        $billable = $this->billable();

        $subscription = $billable->subscription('default');

        if (! $subscription) {
            throw ValidationException::withMessages([
                '*' => __('This account does not have an active subscription.'),
            ]);
        }

        $billable->subscription('default')->pause();
    }
}
