<?php

namespace Spark\Http\Controllers;

use Laravel\Paddle\Cashier;
use Laravel\Paddle\Events\SubscriptionCancelled;
use Laravel\Paddle\Http\Controllers\WebhookController as PaddleWebhookController;
use Laravel\Paddle\Subscription;

class WebhookController extends PaddleWebhookController
{
    /**
     * {@inheritDoc}
     */
    protected function handleSubscriptionCreated(array $payload)
    {
        parent::handleSubscriptionCreated($payload);

        $passthrough = json_decode($payload['passthrough'], true);

        $customer = Cashier::$customerModel::where([
            'billable_id' => $passthrough['billable_id'],
            'billable_type' => $passthrough['billable_type'],
        ])->firstOrFail();

        $customer->update([
            'pending_checkout_id' => null,
            'trial_ends_at' => null,
        ]);

        $customer->billable->subscriptions()
                    ->paused()
                    ->each(function ($subscription) {
                        $subscription->cancelNow();
                    });
    }

    /**
     * {@inheritDoc}
     */
    protected function handleSubscriptionCancelled(array $payload)
    {
        if (! $subscription = $this->findSubscription($payload['subscription_id'])) {
            return;
        }

        $subscription->forceFill([
            'paddle_status' => Subscription::STATUS_DELETED,
            'ends_at' => now(),
            'paused_from' => null,
        ])->save();

        SubscriptionCancelled::dispatch($subscription, $payload);
    }

    /**
     * Handle high-risk transaction creation webhook.
     *
     * @param  array  $payload
     * @return void
     */
    protected function handleHighRiskTransactionCreated(array $payload)
    {
        $passthrough = json_decode($payload['passthrough'], true);

        Cashier::$customerModel::updateOrCreate([
            'billable_id' => $passthrough['billable_id'],
            'billable_type' => $passthrough['billable_type'],
        ], [
            'has_high_risk_payment' => true,
        ]);
    }

    /**
     * Handle the high-risk transaction updated webhook.
     *
     * @param  array  $payload
     * @return void
     */
    protected function handleHighRiskTransactionUpdated(array $payload)
    {
        $passthrough = json_decode($payload['passthrough'], true);

        Cashier::$customerModel::updateOrCreate([
            'billable_id' => $passthrough['billable_id'],
            'billable_type' => $passthrough['billable_type'],
        ], [
            'pending_checkout_id' => null,
            'has_high_risk_payment' => false,
        ]);
    }
}
