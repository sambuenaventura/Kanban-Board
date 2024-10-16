<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="text-3xl font-bold text-gray-900 leading-tight">
                {{ __('Your Subscription') }}
            </h2>
            <div class="text-right">
                <p id="datePlaceholder" class="text-gray-500"></p>
                <h1 id="timePlaceholder" class="text-2xl font-bold text-gray-700"></h1>
            </div>
        </div>
    </x-slot>

    <div class="py-12 bg-gray-100">
        <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="bg-white shadow-lg rounded-lg overflow-hidden">
                <div class="p-8">
                    @if (isset($subscription) && $subscription)
                        <div class="space-y-8">
                            <div class="flex items-center justify-between">
                                <span class="text-2xl font-bold text-gray-900">{{ $planName ?? 'Unknown Plan' }}</span>
                                <span class="px-4 py-2 text-sm font-semibold {{ $subscription->onGracePeriod() ? 'text-yellow-800 bg-yellow-100' : 'text-green-800 bg-green-100' }} rounded-full">
                                    {{ $subscription->onGracePeriod() ? 'Grace Period' : 'Active' }}
                                </span>
                            </div>

                            @if ($subscription->onGracePeriod())
                                <div class="bg-yellow-50 border-l-4 border-yellow-400 p-6 rounded-lg">
                                    <div class="flex">
                                        <div class="flex-shrink-0">
                                            <svg class="h-6 w-6 text-yellow-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                                <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                                            </svg>
                                        </div>
                                        <div class="ml-3">
                                            <p class="text-base text-yellow-700">
                                                Your subscription is canceled and will end on <strong>{{ $subscription->ends_at->format('F j, Y') }}</strong>.
                                            </p>
                                        </div>
                                    </div>
                                </div>
                            @endif

                            <div class="bg-gray-50 rounded-lg p-6">
                                <h4 class="text-xl font-semibold text-gray-900 mb-6">Subscription Details</h4>
                                <dl class="space-y-6">
                                    <div class="flex justify-between">
                                        <dt class="text-sm font-medium text-gray-500">Billing cycle</dt>
                                        <dd class="text-sm font-semibold text-gray-900">
                                            @if (isset($billingFrequency)) 
                                                {{ $billingFrequency }}
                                            @else
                                                N/A
                                            @endif
                                        </dd>
                                    </div>
                                    <div class="flex justify-between">
                                        <dt class="text-sm font-medium text-gray-500">Next billing date</dt>
                                        <dd class="text-sm font-semibold text-gray-900">
                                            @if ($subscription->onGracePeriod())
                                                N/A
                                            @else
                                                {{ $nextBillingDate }}
                                            @endif
                                        </dd>
                                    </div>
                                    <div class="flex justify-between items-center mb-4">
                                        <dt class="text-sm font-medium text-gray-500 mr-4">Payment method</dt>
                                        <dd class="text-sm font-semibold text-gray-900 flex items-center">
                                            <span class="mr-2">
                                                @if($defaultPaymentMethod && $defaultPaymentMethod->card)
                                                    •••• •••• •••• {{ $defaultPaymentMethod->card->last4 }}
                                                @else
                                                    No default payment method set.
                                                @endif
                                            </span>
                                            <a href="{{ route('subscription.payment-method.edit') }}" class="ml-4 text-indigo-600 hover:text-indigo-500 text-sm font-medium">
                                                Update
                                            </a>
                                        </dd>
                                    </div>
                                </dl>
                            </div>
                            
                            

                            <div class="flex flex-col sm:flex-row justify-between items-center space-y-4 sm:space-y-0 sm:space-x-4">
                                <a href="{{ route('subscription.invoices') }}" class="w-full sm:w-auto flex items-center justify-center px-4 py-2 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                    <svg class="mr-2 h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01" />
                                    </svg>
                                    View Invoices
                                </a>
                                <div class="flex space-x-4">
                                    @if ($subscription->onGracePeriod())
                                        <form action="{{ route('subscription.resume') }}" method="POST">
                                            @csrf
                                            <button type="submit" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                                Resume Subscription
                                            </button>
                                        </form>
                                    @else
                                        <a href="{{ route('subscription.change-plan.show') }}" class="inline-flex items-center px-4 py-2 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                            Change Plan
                                        </a>
                                        <form action="{{ route('subscription.cancel') }}" method="POST">
                                            @csrf
                                            <button type="submit" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-red-600 hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500">
                                                Cancel Subscription
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </div>
                        </div>
                    @else
                        <div class="text-center py-12">
                            <svg class="mx-auto h-16 w-16 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            <h3 class="mt-4 text-xl font-medium text-gray-900">No active subscription</h3>
                            <p class="mt-2 text-base text-gray-500">Get started by choosing a plan that fits your needs.</p>
                            <div class="mt-8">
                                <a href="{{ route('pricing.index') }}" class="inline-flex items-center px-6 py-3 border border-transparent text-base font-medium rounded-md shadow-sm text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                    Choose a Plan
                                </a>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-app-layout>