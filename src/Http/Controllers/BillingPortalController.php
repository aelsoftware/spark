<?php

namespace Spark\Http\Controllers;

use Illuminate\Support\Facades\View;
use Inertia\Inertia;
use Spark\FrontendState;
use Spark\GuessesBillableTypes;
use Spark\Spark;

class BillingPortalController
{
    use GuessesBillableTypes;
    use RetrievesBillableModels;

    /**
     * Show the billing portal.
     *
     * @param  string  $type
     * @param  mixed  $id
     * @return \Inertia\Response
     */
    public function __invoke($type = null, $id = null)
    {
        $type = $type ?: $this->guessBillableType();

        $billable = $this->billable($type, $id);

        Inertia::setRootView('spark::app');

        View::share([
            'cssPath' => __DIR__.'/../../../public/css/app.css',
            'jsPath' => __DIR__.'/../../../public/js/app.js',
            'translations' => static::getTranslations(),
        ]);

        Inertia::share(app(FrontendState::class)->current($type, $billable));

        return Inertia::render('BillingPortal', [
            'subscribingTo' => request('subscribe') ? $this->planToSubscribeTo($type) : null,
        ]);
    }

    /**
     * Get the Spark translations from the appropriate language file.
     *
     * @return string
     */
    private static function getTranslations()
    {
        if (! is_readable($file = lang_path('spark/'.app()->getLocale().'.json'))) {
            $file = lang_path('spark/'.app('translator')->getFallback().'.json');
        }

        return is_readable($file) ? file_get_contents($file) : '{}';
    }

    /**
     * Get the plan the user is subscribing to.
     *
     * @param  string  $type
     * @return \Spark\Plan
     */
    private function planToSubscribeTo($type)
    {
        return Spark::plans($type)->first(function ($plan) {
            return $plan->id == request('subscribe');
        });
    }
}
