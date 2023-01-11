<?php

use Illuminate\Support\Facades\Route;
use Spark\Http\Middleware\HandleInertiaRequests;

Route::group([
    'namespace' => 'Spark\Http\Controllers',
    'prefix' => 'spark',
], function () {
    // Paddle Webhook Controller...
    Route::post('webhook', 'WebhookController');

    Route::group(['middleware' => config('spark.middleware', ['web', 'auth'])], function () {
        // Subscription...
        Route::post('/subscription', 'NewSubscriptionController');
        Route::put('/subscription', 'UpdateSubscriptionController');
        Route::put('/subscription/cancel', 'CancelSubscriptionController');
        Route::put('/subscription/resume', 'ResumeSubscriptionController');

        // Payment Method...
        Route::put('/subscription/payment-method', 'UpdatePaymentMethodController');

        // Pending Checkouts...
        Route::post('/pending-checkout', 'NewPendingCheckoutController');
    });
});

Route::group([
    'middleware' => array_merge(config('spark.middleware', ['web', 'auth']), [HandleInertiaRequests::class]),
    'namespace' => 'Spark\Http\Controllers',
    'prefix' => config('spark.path'),
], function () {
    Route::get('/{type?}/{id?}', 'BillingPortalController')->name('spark.portal');
});
