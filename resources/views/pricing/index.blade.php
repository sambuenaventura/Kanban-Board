<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <div>
                <p id="datePlaceholder" class="text-gray-500"></p>
                <h1 id="timePlaceholder" class="text-2xl font-bold text-gray-700"></h1>
            </div>
        </div>
    </x-slot>

    <div class="flex flex-col pb-6">
        <div class="flex-grow overflow-hidden sm:px-6 lg:px-8">
            <div class="h-full bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 sm:p-8">
                    <div class="flex flex-col sm:flex-row justify-between items-center mb-8 pb-6 border-b border-gray-200">
                        <h1 class="text-3xl font-bold text-gray-900 mb-4 sm:mb-0">Pricing</h1>
                        <p class="text-lg text-gray-600">Choose the plan that's right for you</p>
                    </div>

                    @if($user->hasPremiumAccess())
                        <div class="max-w-3xl mx-auto bg-blue-50 rounded-lg p-6 text-center mb-12">
                            <h3 class="text-xl font-semibold text-blue-800 mb-4">Your Current Subscription</h3>
                            <p class="text-lg text-blue-700 mb-4">
                                Plan: {{ $user->hasLifetimeAccess() ? 'Lifetime Access' : ucfirst($currentPlan) }}
                            </p>
                            @if(!$user->hasLifetimeAccess())
                                <form action="{{ route('subscription.cancel') }}" method="POST">
                                    @csrf
                                    <button type="submit" class="bg-red-500 text-white px-6 py-2 rounded-md hover:bg-red-600 transition duration-300">
                                        Cancel Subscription
                                    </button>
                                </form>
                            @endif
                        </div>
                    @endif

                    <div class="grid gap-6 md:grid-cols-2 lg:grid-cols-4">
                        @foreach($plans as $plan)
                            <x-pricing-card :plan="$plan" :user="$user" :currentPlan="$currentPlan" />
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
