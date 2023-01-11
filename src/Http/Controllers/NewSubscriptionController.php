<?php

namespace Spark\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Spark\Contracts\Actions\GeneratesSubscriptionPayLinks;
use Spark\Spark;
use Spark\ValidPlan;

class NewSubscriptionController
{
    use RetrievesBillableModels;

    /**
     * Generate a Paddle pay link for creating a new subscription.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function __invoke(Request $request)
    {
        $billable = $this->billable();

        $request->validate([
            'plan' => ['required', new ValidPlan($request->billableType)],
        ]);

        $subscription = $billable->subscription();

        if ($subscription && $subscription->active()) {
            throw ValidationException::withMessages([
                'plan' => __('You are already subscribed.'),
            ]);
        }

        Spark::ensurePlanEligibility(
            $billable,
            Spark::plans($billable->sparkConfiguration('type'))
                    ->where('id', $request->plan)
                    ->first()
        );

        return response()->json([
            'link' => app(GeneratesSubscriptionPayLinks::class)->generatePayLink(
                $billable,
                $request->plan
            ),
        ]);
    }
}
