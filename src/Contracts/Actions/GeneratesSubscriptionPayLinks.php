<?php

namespace Spark\Contracts\Actions;

interface GeneratesSubscriptionPayLinks
{
    /**
     * Generate a pay link for a new subscription.
     *
     * @param  \Laravel\Paddle\Billable  $billable
     * @param  string  $plan
     * @param  array  $options
     * @return mixed
     */
    public function generatePayLink($billable, $plan, array $options = []);
}
