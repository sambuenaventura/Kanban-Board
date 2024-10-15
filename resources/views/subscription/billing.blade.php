<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="text-3xl font-bold text-gray-900 leading-tight">
                {{ __('Select Billing Period for') }} {{ $selectedPlan['name'] }}
            </h2>
            <div class="text-right">
                <p id="datePlaceholder" class="text-gray-500"></p>
                <h1 id="timePlaceholder" class="text-2xl font-bold text-gray-700"></h1>
            </div>
        </div>
    </x-slot>

    <div class="py-12 bg-gray-100">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="bg-white shadow-lg rounded-lg overflow-hidden">
                <div class="p-6 sm:p-8">
                    <h3 class="text-2xl font-bold text-gray-900 mb-6">Choose Your Billing Period</h3>

                    <form action="{{ route('subscription.change-plan', ['plan' => $plan]) }}" method="POST">
                        @csrf
                    
                        <div class="space-y-6">
                            <div>
                                <label for="billing_period" class="block text-sm font-medium text-gray-700 mb-2">Select the duration:</label>
                                <select name="billing_period" id="billing_period" class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm rounded-md">
                                    <option value="" disabled selected>Select a billing period</option>
                                    <option value="monthly">Monthly</option>
                                    <option value="yearly">Annually (Save 20%)</option>
                                </select>
                            </div>
                    
                            <div class="bg-gray-50 rounded-lg p-6">
                                <h4 class="text-lg font-semibold text-gray-900 mb-4">Plan Summary</h4>
                                <dl class="divide-y divide-gray-200">
                                    <div class="py-4 sm:py-5 sm:grid sm:grid-cols-3 sm:gap-4">
                                        <dt class="text-sm font-medium text-gray-500">Selected Plan</dt>
                                        <dd class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2">{{ $selectedPlan['name'] }}</dd>
                                    </div>
                                    <div class="py-4 sm:py-5 sm:grid sm:grid-cols-3 sm:gap-4">
                                        <dt class="text-sm font-medium text-gray-500">Billing Period</dt>
                                        <dd class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2" id="billing_period_display">-</dd>
                                    </div>
                                    <div class="py-4 sm:py-5 sm:grid sm:grid-cols-3 sm:gap-4">
                                        <dt class="text-sm font-medium text-gray-500">Cost</dt>
                                        <dd class="mt-1 text-sm font-semibold text-indigo-600 sm:mt-0 sm:col-span-2" id="price_display">-</dd>
                                    </div>
                                </dl>
                            </div>
                    
                            <div class="mt-6 flex justify-end space-x-4">
                                <a href="{{ route('subscription.change-plan.show') }}" class="inline-flex items-center px-4 py-2 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                    Back to Plans
                                </a>
                                <button type="submit" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                    Continue to Change Plan
                                </button>
                            </div>
                        </div>
                    </form>
                    
                </div>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const billingPeriodSelect = document.getElementById('billing_period');
            const priceDisplay = document.getElementById('price_display');
            const billingPeriodDisplay = document.getElementById('billing_period_display');

            billingPeriodSelect.addEventListener('change', function() {
                const selectedPeriod = this.value;
                let price, periodDisplay;

                const monthlyPrice = '{{ $selectedPlan["monthly_price"] }}';
                const yearlyPrice = '{{ $selectedPlan["yearly_price"] }}';

                if (selectedPeriod === 'monthly') {
                    price = monthlyPrice;
                    periodDisplay = 'Monthly';
                } else {
                    price = yearlyPrice;
                    periodDisplay = 'Annually';
                }

                priceDisplay.textContent = `₱${price}`;
                billingPeriodDisplay.textContent = periodDisplay;
            });
        });
    </script>
</x-app-layout>
