<template>
    <div class="font-sans antialiased min-h-screen bg-white lg:bg-gray-100">
        <div class="lg:min-h-screen bg flex flex-wrap lg:flex-nowrap">
            <!-- Static Desktop Sidebar -->
            <div class="order-last lg:order-first lg:w-92 py-10 lg:pt-24 px-6 bg-white lg:shadow-lg" id="sideBar">
                <div class="max-w-md" v-if="$page.props.appLogo" v-html="$page.props.appLogo">
                </div>

                <h1 class="text-3xl font-bold text-gray-900" v-else>
                    {{ $page.props.appName }}
                </h1>

                <h2 class="mt-1 text-lg font-semibold text-gray-700">
                    {{ __('Billing Management') }}
                </h2>

                <div class="flex items-center mt-12 text-gray-600">
                    <div>
                        {{ __('Signed in as') }}
                    </div>

                    <img :src="$page.props.userAvatar" class="ml-2 h-6 w-6 rounded-full" v-if="$page.props.userAvatar" />

                    <div :class="{'ml-1': ! $page.props.userAvatar, 'ml-2': $page.props.userAvatar}">
                        {{ $page.props.userName }}.
                    </div>
                </div>

                <div class="mt-3 text-sm text-gray-600" v-if="$page.props.billableName">
                    {{ __('Managing billing for :billableName', {billableName: $page.props.billableName}) }}.
                </div>

                <div class="mt-12 text-gray-600">
                    {{ __('Our billing management portal allows you to conveniently manage your subscription plan, payment method, and download your recent invoices.') }}
                </div>

                <div class="mt-12" id="sideBarTermsLink" v-if="$page.props.termsUrl">
                    <a :href="$page.props.termsUrl" class="text-gray-600 underline" target="_blank">
                        {{ __('Terms of Service') }}
                    </a>
                </div>

                <div class="mt-12" id="sideBarReturnLink">
                    <a :href="$page.props.dashboardUrl" class="flex items-center">
                        <svg viewBox="0 0 20 20" fill="currentColor" class="arrow-left w-5 h-5 text-gray-400">
                            <path fill-rule="evenodd" d="M9.707 16.707a1 1 0 01-1.414 0l-6-6a1 1 0 010-1.414l6-6a1 1 0 011.414 1.414L5.414 9H17a1 1 0 110 2H5.414l4.293 4.293a1 1 0 010 1.414z" clip-rule="evenodd"></path>
                        </svg>

                        <div class="ml-2 text-gray-600 underline">
                            {{ __('Return to :appName', {appName: $page.props.appName}) }}
                        </div>
                    </a>
                </div>
            </div>

            <div class="w-full lg:flex-1 bg-gray-100">
                <!-- Mobile Top Nav -->
                <a :href="$page.props.dashboardUrl" class="lg:hidden flex items-center w-full px-4 py-4 bg-white shadow-lg" id="topNavReturnLink">
                    <svg viewBox="0 0 20 20" fill="currentColor" class="arrow-left w-4 h-4 text-gray-400">
                        <path fill-rule="evenodd" d="M9.707 16.707a1 1 0 01-1.414 0l-6-6a1 1 0 010-1.414l6-6a1 1 0 011.414 1.414L5.414 9H17a1 1 0 110 2H5.414l4.293 4.293a1 1 0 010 1.414z" clip-rule="evenodd"></path>
                    </svg>

                    <div class="ml-2 text-gray-600 underline">
                        {{ __('Return to :appName', {appName: $page.props.appName}) }}
                    </div>
                </a>

                <!-- Main Content -->
                <div class="pb-10 pt-6 lg:pt-24 lg:pb-24 lg:max-w-4xl lg:mx-auto">
                    <!-- Custom Message -->
                    <div class="mb-10 sm:px-8" v-if="$page.props.message">
                        <div class="px-6 py-4 text-sm text-gray-600 bg-blue-100 border border-blue-200 sm:rounded-lg shadow-sm mb-6">
                            {{ $page.props.message }}
                        </div>
                    </div>

                    <!-- Error Messages -->
                    <error-messages class="mb-10 sm:px-8" :errors="errors" v-if="errors.length > 0" />

                    <!-- Pending -->
                    <div v-if="$page.props.state == 'pending' || $page.props.state == 'hasHighRiskPayment'">
                        <div class="flex items-center px-4 sm:px-8">
                            <section-heading>
                                {{ __('Subscription Pending') }}
                            </section-heading>

                            <svg xmlns="http://www.w3.org/2000/svg" class="ml-2 text-gray-300 h-6 w-6 animate-spin" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round">
                                <path stroke="none" d="M0 0h24v24H0z"/>
                                <path d="M4.05 11a8 8 0 1 1 .5 4m-.5 5v-5h5" />
                            </svg>
                        </div>

                        <div class="mt-3 sm:px-8">
                            <div class="px-6 py-4 bg-white sm:rounded-lg shadow-sm">
                                <div class="max-w-2xl text-sm text-gray-600">
                                    {{ __('We are processing your subscription. Once the subscription has successfully processed, this page will update automatically. Typically, this process should only take a few seconds.') }}
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Subscribe -->
                    <div v-if="$page.props.state == 'none'">
                        <section-heading class="px-4 sm:px-8">
                            {{ __('Subscribe') }}
                        </section-heading>

                        <div class="mt-6 sm:px-8">
                            <info-messages class="mb-10" v-if="! $page.props.genericTrialEndsAt">
                                {{ __('It looks like you do not have an active subscription. You may choose one of the subscription plans below to get started. Subscription plans may be changed or cancelled at your convenience.') }}
                            </info-messages>

                            <info-messages class="mb-10" v-else>
                                {{ __('You are currently within your free trial period. Your trial will expire on :date. Starting a new subscription will end your trial.', {'date': $page.props.genericTrialEndsAt}) }}
                            </info-messages>
                        </div>

                        <!-- Interval Selector -->
                        <interval-selector class="mt-6 px-4 sm:px-8"
                                           :showing-default-interval-plans="showingPlansOfInterval == $page.props.defaultInterval"
                                           @toggled="toggleDisplayedPlanIntervals"
                                           v-if="monthlyPlans.length > 0 && yearlyPlans.length > 0"/>

                        <transition name="component-fade" mode="out-in">
                            <!-- Monthly Plans -->
                            <plan-list class="mt-6 sm:px-8" key="subscribe-monthly-plans"
                                            :plans="monthlyPlans"
                                            interval="monthly"
                                            :seat-name="seatName"
                                            :current-plan="plan"
                                            @plan-selected="subscribeToPlan($event)"
                                            v-if="monthlyPlans.length > 0 && showingPlansOfInterval == 'monthly'" />

                            <!-- Yearly Plans -->
                            <plan-list class="mt-6 sm:px-8" key="subscribe-yearly-plans"
                                            :plans="yearlyPlans"
                                            interval="yearly"
                                            :seat-name="seatName"
                                            :current-plan="plan"
                                            @plan-selected="subscribeToPlan($event)"
                                            v-if="yearlyPlans.length > 0 && showingPlansOfInterval == 'yearly'" />
                        </transition>
                    </div>

                    <!-- Active Subscription -->
                    <div v-if="$page.props.state == 'active'">
                        <!-- Change Subscription Plan -->
                        <section-heading class="px-4 sm:px-8" v-if="! selectingNewPlan">
                            {{ __('Current Subscription Plan') }}
                        </section-heading>

                        <section-heading class="px-4 sm:px-8" v-else>
                            {{ __('Change Subscription Plan') }}
                        </section-heading>

                        <div class="mt-3 sm:px-8">
                            <div class="bg-white sm:rounded-lg shadow-sm" v-if="! selectingNewPlan">
                                <plan :plan="plan" :seat-name="seatName" :hide-incentive="true" />

                                <div class="px-6 pb-4">
                                    <spark-button class="mt-4" v-if="monthlyPlans.length + yearlyPlans.length > 1" @click.native="selectingNewPlan = true">
                                        {{ __('Change Subscription Plan') }}
                                    </spark-button>
                                </div>
                            </div>
                        </div>

                        <div v-if="selectingNewPlan">
                            <!-- Interval Selector -->
                            <interval-selector class="mt-6 px-4 sm:px-8"
                                       :showing-default-interval-plans="showingPlansOfInterval == $page.props.defaultInterval"
                                       @toggled="toggleDisplayedPlanIntervals"
                                       v-if="monthlyPlans.length > 0 && yearlyPlans.length > 0"/>

                            <transition name="component-fade" mode="out-in">
                                <!-- Monthly Plans -->
                                <plan-list class="mt-6 sm:px-8" key="change-monthly-plans"
                                                :plans="monthlyPlans"
                                                interval="monthly"
                                                :seat-name="seatName"
                                                :current-plan="plan"
                                                @plan-selected="open(switchToPlan, __('Are you sure you would like to switch billing plans?'), [$event])"
                                                v-if="monthlyPlans.length > 0 && showingPlansOfInterval == 'monthly'" />

                                <!-- Yearly Plans -->
                                <plan-list class="mt-6 sm:px-8" key="change-yearly-plans"
                                                :plans="yearlyPlans"
                                                interval="yearly"
                                                :current-plan="plan"
                                                :seat-name="seatName"
                                                @plan-selected="open(switchToPlan, __('Are you sure you would like to switch billing plans?'), [$event])"
                                                v-if="yearlyPlans.length > 0 && showingPlansOfInterval == 'yearly'" />
                            </transition>

                            <!-- Nevermind, Keep Old Plan -->
                            <button class="flex items-center mt-4 px-4 sm:px-8" @click="selectingNewPlan = false">
                                <svg viewBox="0 0 20 20" fill="currentColor" class="text-gray-400 w-4 h-4"><path fill-rule="evenodd" d="M9.707 16.707a1 1 0 01-1.414 0l-6-6a1 1 0 010-1.414l6-6a1 1 0 011.414 1.414L5.414 9H17a1 1 0 110 2H5.414l4.293 4.293a1 1 0 010 1.414z" clip-rule="evenodd"></path></svg>

                                <div class="ml-2 text-sm text-gray-600 underline">
                                    {{ __('Nevermind, I\'ll keep my old plan') }}
                                </div>
                            </button>
                        </div>

                        <!-- Update Payment Method -->
                        <section-heading class="mt-10 px-4 sm:px-8">
                            {{ __('Payment Method') }}
                        </section-heading>

                        <div class="mt-3 sm:px-8">
                            <div class="px-6 py-4 bg-white sm:rounded-lg shadow-sm">
                                <div class="max-w-2xl text-sm text-gray-600" v-if="$page.props.paymentMethod == 'card'"
                                    v-html="__('Your current payment method is a credit card ending in :lastFour that expires on :expiration.', {
                                        lastFour: '<span class=\'font-semibold\'>'+$page.props.cardLastFour+'</span>',
                                        expiration: '<span class=\'font-semibold\'>'+$page.props.cardExpirationDate+'</span>'
                                    })">
                                </div>

                                <div class="max-w-2xl text-sm text-gray-600" v-if="$page.props.paymentMethod == 'paypal'"
                                     v-html="__('Your current payment method is :paypal.', {
                                         paypal: '<span class=\'font-semibold\'>PayPal</span>'
                                     })">
                                </div>

                                <spark-button class="mt-4" @click.native="updatePaymentMethod">
                                    {{ __('Update Payment Method') }}
                                </spark-button>
                            </div>
                        </div>
                    </div>

                    <!-- On Grace Period / Subscription Paused -->
                    <div v-if="$page.props.state == 'onGracePeriod'">
                        <!-- Resume Subscription -->
                        <section-heading class="px-4 sm:px-8">
                            {{ __('Resume Subscription') }}
                        </section-heading>

                        <div class="mt-3 sm:px-8">
                            <div class="px-6 py-4 bg-white sm:rounded-lg shadow-sm">
                                <div class="max-w-2xl text-sm text-gray-600">
                                    {{ __('Having second thoughts about cancelling your subscription? You can instantly reactive your subscription at any time until the end of your current billing cycle. After your current billing cycle ends, you may choose an entirely new subscription plan.') }}
                                </div>

                                <div class="mt-4">
                                    <spark-button @click.native="open(resumeSubscription, __('Are you sure you want to resume your subscription?'))">
                                        {{ __('Resume Subscription') }}
                                    </spark-button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Next Payment -->
                    <div class="mt-10" v-if="$page.props.nextPayment">
                        <section-heading class="px-4 sm:px-8">
                            {{ __('Next Payment') }}
                        </section-heading>

                        <div class="mt-3 sm:px-8">
                            <div class="px-6 py-4 bg-white sm:rounded-lg shadow-sm">
                                <div class="max-w-2xl text-sm text-gray-600">
                                    {{ __('Your next payment of :amount will be processed on :date.', {
                                        amount: $page.props.nextPayment.amount,
                                        date: $page.props.nextPayment.date
                                    }) }}
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Cancel Subscription -->
                    <div v-if="$page.props.state == 'active'">
                        <section-heading class="mt-10 px-4 sm:px-8">
                            {{ __('Cancel Subscription') }}
                        </section-heading>

                        <div class="mt-3 sm:px-8">
                            <div class="px-6 py-4 bg-white sm:rounded-lg shadow-sm">
                                <div class="max-w-2xl text-sm text-gray-600">
                                    {{ __('You may cancel your subscription at any time. Once your subscription has been cancelled, you will have the option to resume the subscription until the end of your current billing cycle.') }}
                                </div>

                                <div class="mt-4">
                                    <spark-secondary-button @click.native="open(cancelSubscription, __('Are you sure you want to cancel your subscription?'))">
                                        {{ __('Cancel Subscription') }}
                                    </spark-secondary-button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Receipts -->
                    <div class="mt-10" v-if="$page.props.state != 'pending' && $page.props.receipts.length > 0">
                        <section-heading class="px-4 sm:px-8">
                            {{ __('Receipts') }}
                        </section-heading>

                        <receipt-list class="mt-3 sm:px-8" :receipts="$page.props.receipts" />
                    </div>

                    <div class="text-center mt-10 lg:hidden" id="footerTermsLink" v-if="$page.props.termsUrl">
                        <a :href="$page.props.termsUrl" class="text-gray-600 underline">
                            {{ __('Terms of Service') }}
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <modal v-if="showModal" ref="modal" :title="__('Confirm Billing Action')" :max-width="800" @closed="showModal = false">
            <template #default>
                {{ confirmText }}
            </template>

            <template #footer>
                <div class="flex justify-end mt-4">
                    <spark-secondary-button @click.native="close">{{ __('Nevermind') }}</spark-secondary-button>
                    <spark-button class="ml-2" @click.native="confirm">{{ __('Confirm') }}</spark-button>
                </div>
            </template>
        </modal>
    </div>

    <!-- bg-gray-50 bg-gray-100 bg-gray-200 bg-gray-300 bg-gray-400 bg-gray-500 bg-gray-600 bg-gray-700 bg-gray-800 bg-gray-900 -->
    <!-- bg-red-50 bg-red-100 bg-red-200 bg-red-300 bg-red-400 bg-red-500 bg-red-600 bg-red-700 bg-red-800 bg-red-900 -->
    <!-- bg-yellow-50 bg-yellow-100 bg-yellow-200 bg-yellow-300 bg-yellow-400 bg-yellow-500 bg-yellow-600 bg-yellow-700 bg-yellow-800 bg-yellow-900 -->
    <!-- bg-green-50 bg-green-100 bg-green-200 bg-green-300 bg-green-400 bg-green-500 bg-green-600 bg-green-700 bg-green-800 bg-green-900 -->
    <!-- bg-blue-50 bg-blue-100 bg-blue-200 bg-blue-300 bg-blue-400 bg-blue-500 bg-blue-600 bg-blue-700 bg-blue-800 bg-blue-900 -->
    <!-- bg-indigo-50 bg-indigo-100 bg-indigo-200 bg-indigo-300 bg-indigo-400 bg-indigo-500 bg-indigo-600 bg-indigo-700 bg-indigo-800 bg-indigo-900 -->
    <!-- bg-purple-50 bg-purple-100 bg-purple-200 bg-purple-300 bg-purple-400 bg-purple-500 bg-purple-600 bg-purple-700 bg-purple-800 bg-indigo-900 -->
    <!-- bg-pink-50 bg-pink-100 bg-pink-200 bg-pink-300 bg-pink-400 bg-pink-500 bg-pink-600 bg-pink-700 bg-pink-800 bg-indigo-900 -->
</template>

<script>
    import ErrorMessages from './../Components/ErrorMessages';
    import InfoMessages from './../Components/InfoMessages';
    import IntervalSelector from './../Components/IntervalSelector';
    import Modal from './../Components/Modal';
    import Plan from './../Components/Plan';
    import PlanList from './../Components/PlanList';
    import PlanSectionHeading from './../Components/PlanSectionHeading';
    import ReceiptList from './../Components/ReceiptList';
    import SectionHeading from './../Components/SectionHeading';
    import SparkButton from './../Components/Button';
    import SparkSecondaryButton from './../Components/SecondaryButton';
    import { Inertia } from '@inertiajs/inertia'

    export default {
        components: {
            ErrorMessages,
            InfoMessages,
            IntervalSelector,
            Modal,
            Plan,
            PlanList,
            PlanSectionHeading,
            ReceiptList,
            SectionHeading,
            SparkButton,
            SparkSecondaryButton,
        },

        props: [
            'billableId',
            'billableType',
            'paddleVendorId',
            'plan',
            'seatName',
            'monthlyPlans',
            'yearlyPlans',
        ],

        data() {
            return {
                errors: [],
                showingPlansOfInterval: 'monthly',
                selectingNewPlan: false,
                showingSideMenu: false,

                confirmAction: null,
                confirmArguments: [],
                confirmText: '',
                showModal: false,
            };
        },

        watch: {
            /**
             * Watch the "$page.props.state" variable to reload data during "pending" state.
             */
            '$page.props.state': {
                immediate: true,
                handler: function (newState, oldState) {
                    if (newState == 'pending') {
                        this.startReloadingData();
                    }
                }
            }
        },

        /**
         * Initialize the component.
         */
        mounted() {
            Paddle.Setup({
                vendor: this.paddleVendorId
            });

            if (this.$page.props.sandbox) {
                Paddle.Environment.set('sandbox');
            }

            Inertia.on('invalid', (event) => {
                event.preventDefault();

                if (event.detail.response.request.responseURL) {
                    window.location.href = event.detail.response.request.responseURL;
                } else {
                    console.error(event);
                }
            });

            if (this.monthlyPlans.length == 0 &&
                this.yearlyPlans.length > 0) {
                this.showingPlansOfInterval = 'yearly';
            } else {
                this.showingPlansOfInterval = this.$page.props.defaultInterval;
            }

            if (this.$page.props.state == 'none' && this.$page.props.subscribingTo) {
                this.subscribeToPlan(this.$page.props.subscribingTo);
            }
        },

        methods: {
            /**
             * Toggle the plan intervals that are being displayed.
             */
            toggleDisplayedPlanIntervals() {
                if (this.showingPlansOfInterval == 'monthly') {
                    this.showingPlansOfInterval = 'yearly';
                } else {
                    this.showingPlansOfInterval = 'monthly';
                }
            },

            /**
             * Subscribe to the given plan.
             */
            subscribeToPlan(plan) {
                Paddle.Spinner.show();

                window.history.pushState({}, document.title, window.location.pathname+'?subscribe='+plan.id);

                this.request('POST', '/spark/subscription', {
                    plan: plan.id
                }).then(response => {
                    Paddle.Checkout.open({
                        override: response.data.link,
                        disableLogout: true,
                        successCallback: response => {
                            this.$page.props.state = 'pending';

                            window.history.pushState({}, document.title, window.location.pathname);

                            this.request('POST', '/spark/pending-checkout', {
                                checkout_id: response.checkout.id
                            });
                        },
                        closeCallback: () => {
                            window.history.pushState({}, document.title, window.location.pathname);
                        }
                    });
                });
            },

            /**
             * Switch to the given plan.
             */
            switchToPlan(plan) {
                Paddle.Spinner.show();

                this.request('PUT', '/spark/subscription', {
                    plan: plan.id,
                }).then(response => {
                    this.reloadData();
                });
            },

            /**
             * Update the customer's payment method.
             */
            updatePaymentMethod() {
                Paddle.Spinner.show();

                this.request('PUT', '/spark/subscription/payment-method').then(response => {
                    Paddle.Checkout.open({
                        override: response.data.link,
                        successCallback: response => {
                            this.reloadData();
                        }
                    })
                });
            },

            /**
             * Cancel the customer's subscription.
             */
            cancelSubscription() {
                Paddle.Spinner.show();

                this.request('PUT', '/spark/subscription/cancel').then(response => {
                    this.reloadData();
                });
            },

            resumeSubscription() {
                Paddle.Spinner.show();

                this.request('PUT', '/spark/subscription/resume', {}).then(response => {
                    this.reloadData();
                });
            },

            /**
             * Start periodically reloading the page's data.
             */
            startReloadingData() {
                setTimeout(() => {
                    this.reloadData();
                }, 2000)
            },

            /**
             * Reload the page's data, while maintaining any current state.
             */
            reloadData() {
                return this.$inertia.reload({
                    onSuccess: () => {
                        if (this.$page.props.state == 'pending') {
                            this.startReloadingData();
                        }

                        if (this.selectingNewPlan) {
                            this.selectingNewPlan = false;
                        }

                        Paddle.Spinner.hide();
                    }
                });
            },

            /**
             * Make an outgoing request to the Laravel application.
             */
            request(method, url, data = {}) {
                this.errors = [];

                data.billableType = this.billableType;
                data.billableId = this.billableId;

                return axios.request({
                    url: url,
                    method: method,
                    data: data,
                }).then(response => {
                    return response;
                }).catch(error => {
                    Paddle.Spinner.hide();

                    if (error.response.status == 422) {
                        this.errors = _.flatMap(error.response.data.errors)
                    } else {
                        this.errors = [this.__('An unexpected error occurred and we have notified our support team. Please try again later.')]
                    }
                });
            },

            confirm() {
                this.$refs.modal?.close();

                this.confirmAction(...this.confirmArguments);

                this.confirmAction = null;
                this.confirmArguments = [];
                this.confirmText = '';
            },

            open(confirmAction, confirmText, confirmArguments = []) {
                this.confirmAction = confirmAction;
                this.confirmArguments = confirmArguments;
                this.confirmText = confirmText;
                this.showModal = true;
            },

            close() {
                this.$refs.modal?.close();

                this.confirmAction = null;
                this.confirmArguments = [];
                this.confirmText = '';
            },
        }
    }
</script>
