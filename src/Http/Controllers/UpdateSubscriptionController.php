<?php

namespace Spark\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Laravel\Paddle\Exceptions\PaddleException;
use Spark\Spark;
use Spark\ValidPlan;

class UpdateSubscriptionController
{
    use RetrievesBillableModels;

    /**
     * Update the plan that the billable is currently subscribed to.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return void
     */
    public function __invoke(Request $request)
    {
        $billable = $this->billable();

        $subscription = $billable->subscription('default');

        if (! $subscription) {
            throw ValidationException::withMessages([
                '*' => __('This account does not have an active subscription.'),
            ]);
        }

        $request->validate([
            'plan' => ['required', new ValidPlan($request->billableType)],
        ]);

        Spark::ensurePlanEligibility(
            $billable,
            Spark::plans($billable->sparkConfiguration('type'))
                    ->where('id', $request->plan)
                    ->first()
        );

        try {
            $subscription
                ->setProration(config('spark.prorates'))
                ->swapAndInvoice($request->plan);
        } catch (PaddleException $e) {
            report($e);

            throw ValidationException::withMessages([
                '*' => __('We are unable to process your payment. Please contact customer support.'),
            ]);
        }
    }
}
