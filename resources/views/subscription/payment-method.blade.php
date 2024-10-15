<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="text-3xl font-bold text-gray-900 leading-tight">
                {{ __('Manage Payment') }}
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
                    <h3 class="text-2xl font-bold text-gray-900 mb-8">Payment Methods</h3>

                    <!-- Current default payment method -->
                    <div class="mb-10 p-6 bg-indigo-50 rounded-lg">
                        <h4 class="text-xl font-semibold text-indigo-900 mb-4">Default Payment Method</h4>
                        @if ($defaultPaymentMethod)
                            <div class="flex items-center space-x-4">
                                <div class="flex-shrink-0">
                                    <svg class="h-8 w-8 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"></path></svg>
                                </div>
                                <div>
                                    <p class="text-lg font-medium text-gray-900">
                                        {{ ucfirst($defaultPaymentMethod->card->brand) }} ending in {{ $defaultPaymentMethod->card->last4 }}
                                    </p>
                                    <p class="text-sm text-gray-600">
                                        Expires {{ $defaultPaymentMethod->card->exp_month }}/{{ $defaultPaymentMethod->card->exp_year }}
                                    </p>
                                </div>
                            </div>
                        @else
                            <p class="text-gray-700">No default payment method set.</p>
                        @endif
                    </div>

                    <!-- All payment methods -->
                    <div class="mb-10">
                        <h4 class="text-xl font-semibold text-gray-900 mb-6">Saved Payment Methods</h4>
                        @if ($paymentMethods->isNotEmpty())
                            <ul class="space-y-6">
                                @foreach ($paymentMethods as $paymentMethod)
                                    <li class="bg-white border border-gray-200 rounded-lg p-6 shadow-sm hover:shadow-md transition duration-300">
                                        <div class="flex justify-between items-center">
                                            <div class="flex items-center space-x-4">
                                                <div class="flex-shrink-0">
                                                    <svg class="h-8 w-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"></path></svg>
                                                </div>
                                                <div>
                                                    <p class="text-lg font-medium text-gray-900">
                                                        {{ ucfirst($paymentMethod->card->brand) }} ending in {{ $paymentMethod->card->last4 }}
                                                    </p>
                                                    <p class="text-sm text-gray-600">
                                                        Expires {{ $paymentMethod->card->exp_month }}/{{ $paymentMethod->card->exp_year }}
                                                    </p>
                                                </div>
                                            </div>
                                            @if ($defaultPaymentMethod && $defaultPaymentMethod->id === $paymentMethod->id)
                                            <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-green-100 text-green-800">
                                                Default
                                            </span>
                                        @else
                                            @if (!$defaultPaymentMethod) <!-- Check if there is no default payment method -->
                                                <form action="{{ route('subscription.payment-method.set-default', $paymentMethod->id) }}" method="POST">
                                                    @csrf
                                                    <button type="submit" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-indigo-700 bg-indigo-100 hover:bg-indigo-200 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                                        Set as Default
                                                    </button>
                                                </form>
                                            @else
                                                <form action="{{ route('subscription.payment-method.set-default', $paymentMethod->id) }}" method="POST">
                                                    @csrf
                                                    <button type="submit" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-indigo-700 bg-indigo-100 hover:bg-indigo-200 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                                        Set as Default
                                                    </button>
                                                </form>
                                            @endif
                                        @endif
                                        
                                        </div>
                                    </li>
                                @endforeach
                            </ul>
                        @else
                            <p class="text-gray-700">No additional saved payment methods.</p>
                        @endif
                    </div>

                    <!-- Form to add a new payment method -->
                    <div class="bg-gray-50 rounded-lg p-6">
                        <h4 class="text-xl font-semibold text-gray-900 mb-6">Add New Payment Method</h4>
                        <form action="{{ route('subscription.payment-method.add') }}" method="POST" id="add-payment-form" class="space-y-6">
                            @csrf
                            <div class="space-y-2">
                                <label for="card-element" class="block text-sm font-medium text-gray-700">
                                    Credit or debit card
                                </label>
                                <div id="card-element" class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm p-4 bg-white"></div>
                            </div>

                            <div class="flex items-center">
                                <input type="checkbox" id="set-default" name="set_default" value="1" class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded">
                                <label for="set-default" class="ml-2 block text-sm text-gray-700">Set as default payment method</label>
                            </div>

                            <div id="card-errors" role="alert" class="mt-2 text-sm text-red-600"></div>
                            
                            <div class="mt-6">
                                <button type="submit" class="w-full inline-flex justify-center items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                    Add Payment Method
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>

<script src="https://js.stripe.com/v3/"></script>
<script>
    const stripe = Stripe('{{ config('cashier.key') }}');
    const elements = stripe.elements();
    const cardElement = elements.create('card', {
        style: {
            base: {
                fontSize: '16px',
                color: '#32325d',
                '::placeholder': {
                    color: '#aab7c4'
                },
            },
        }
    });
    cardElement.mount('#card-element');

    const form = document.getElementById('add-payment-form');
    const cardErrors = document.getElementById('card-errors');

    form.addEventListener('submit', async (event) => {
        event.preventDefault();
        const submitButton = form.querySelector('button[type="submit"]');
        submitButton.disabled = true;
        submitButton.innerHTML = '<svg class="animate-spin -ml-1 mr-3 h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg> Processing...';

        const { setupIntent, error } = await stripe.confirmCardSetup(
            '{{ $intent->client_secret }}',
            {
                payment_method: {
                    card: cardElement,
                }
            }
        );

        if (error) {
            cardErrors.textContent = error.message;
            submitButton.disabled = false;
            submitButton.innerHTML = 'Add Payment Method';
        } else {
            const hiddenInput = document.createElement('input');
            hiddenInput.setAttribute('type', 'hidden');
            hiddenInput.setAttribute('name', 'payment_method');
            hiddenInput.setAttribute('value', setupIntent.payment_method);
            form.appendChild(hiddenInput);
            form.submit();
        }
    });
</script>