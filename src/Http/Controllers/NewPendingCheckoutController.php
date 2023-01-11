<?php

namespace Spark\Http\Controllers;

use Illuminate\Http\Request;
use Laravel\Paddle\Cashier;

class NewPendingCheckoutController
{
    use RetrievesBillableModels;

    /**
     * Mark the billable as having a pending checkout.
     *
     * @param  \Illuminate\Http\Request
     * @return void
     */
    public function __invoke(Request $request)
    {
        $billable = $this->billable();

        Cashier::$customerModel::updateOrCreate([
            'billable_id' => $billable->getKey(),
            'billable_type' => $billable->getMorphClass(),
        ], array_filter([
            'pending_checkout_id' => $request->checkout_id,
        ]));
    }
}
