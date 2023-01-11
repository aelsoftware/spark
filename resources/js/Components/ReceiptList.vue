<template>
    <div>
        <div class="bg-white sm:rounded-lg shadow-sm divide-y divide-gray-100">
            <div class="flex items-center px-6 py-4" v-for="receipt in receipts" :key="receipt.id">
                <div class="text-sm w-full">
                    {{ receipt.paid_at }}
                    <span class="px-1">-</span>
                    <span class="text-sm" v-html="receipt.amount"></span>
                </div>

                <div class="text-right w-full">
                    <a class="underline text-sm text-gray-500" :href="receipt.receipt_url" target="_blank">
                       {{ __('Download Receipt') }}
                    </a>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
    export default {
        props: ['receipts'],
    }
</script>
