<?php

namespace Spark;

use Laravel\Paddle\Billable as CashierBillable;

trait Billable
{
    use CashierBillable;

    /**
     * Boot the billable model.
     *
     * @return void
     */
    public static function bootBillable()
    {
        static::created(function ($model) {
            $trialDays = $model->sparkConfiguration('trial_days');

            $model->createAsCustomer([
                'trial_ends_at' => $trialDays ? now()->addDays($trialDays) : null,
            ]);
        });
    }

    /**
     * Get the Spark plan that corresponds with the billable's current subscription.
     *
     * @return \Spark\Plan|null
     */
    public function sparkPlan()
    {
        $subscription = $this->subscription();

        $plans = Spark::plans($this->sparkConfiguration('type'));

        if ($subscription && $subscription->valid()) {
            return $plans->first(function ($plan) use ($subscription) {
                return $plan->id == $subscription->paddle_plan;
            });
        }
    }

    /**
     * Get the Spark configuration or a configuration item for the billable model.
     *
     * @param  string|null  $key
     * @return mixed
     */
    public function sparkConfiguration($key = null)
    {
        $config = collect(config('spark.billables'))->map(function ($config, $type) {
            $config['type'] = $type;

            return $config;
        })->first(function ($billable, $type) {
            return $billable['model'] == get_class($this);
        });

        if ($key) {
            return $config[$key] ?? null;
        }

        return $config;
    }

    /**
     * Determine if the user can add seats to their subscription.
     *
     * @return bool
     */
    public function canUpdateSeats()
    {
        if ($this->customer && $this->customer->onGenericTrial()) {
            return true;
        }

        $subscription = $this->subscription();

        return $subscription &&
            $subscription->recurring() &&
            ! $subscription->pastDue();
    }

    /**
     * Add seats to the current subscription.
     *
     * @param  int  $count
     * @return void
     */
    public function addSeat($count = 1)
    {
        if (! $subscription = $this->subscription()) {
            return;
        }

        $subscription
            ->setProration(config('spark.prorates'))
            ->incrementAndInvoice($count);
    }

    /**
     * Remove seats from the current subscription.
     *
     * @param  int  $count
     * @return void
     */
    public function removeSeat($count = 1)
    {
        if (! $subscription = $this->subscription()) {
            return;
        }

        $subscription
            ->setProration(config('spark.prorates'))
            ->decrementQuantity($count);
    }

    /**
     * Update the number of seats in the current subscription.
     *
     * @param  int  $count
     * @return void
     */
    public function updateSeats($count)
    {
        if (! $subscription = $this->subscription()) {
            return;
        }

        $subscription
            ->setProration(config('spark.prorates'))
            ->updateQuantity($count);
    }

    /**
     * Get the billable model's email address to associate with Paddle.
     *
     * @return string|null
     */
    public function paddleEmail()
    {
        return $this->email;
    }
}
