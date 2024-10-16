<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="text-3xl font-bold text-gray-900 leading-tight">
                {{ __('Change Subscription Plan') }}
            </h2>
            <div class="text-right">
                <p id="datePlaceholder" class="text-gray-500"></p>
                <h1 id="timePlaceholder" class="text-2xl font-bold text-gray-700"></h1>
            </div>
        </div>
    </x-slot>

    <div class="mt-16 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center">
            <h2 class="text-3xl font-extrabold text-gray-900 sm:text-4xl">
                Choose Your New Plan
            </h2>
            <p class="mt-4 text-xl text-gray-600">
                Unlock powerful features to boost your productivity
            </p>
        </div>
    
        <div class="mt-12 space-y-4 sm:mt-16 sm:space-y-0 sm:grid sm:grid-cols-2 sm:gap-6 lg:max-w-4xl lg:mx-auto xl:max-w-none xl:grid-cols-2">
            @foreach ($plans as $plan)
                <div class="relative bg-white border border-gray-200 rounded-lg shadow-sm divide-y divide-gray-200">
                    
                    @if ($currentSubscription === $plan['name'])
                        <span class="absolute top-3 right-3 inline-block px-2 py-1 text-xs font-semibold text-green-800 bg-green-200 rounded-full">Active</span>
                    @endif
                    
                    <div class="p-6">
                        <h3 class="text-lg leading-6 font-medium text-gray-900">{{ $plan['name'] }}</h3>
                        <p class="mt-4 text-sm text-gray-500">{{ $plan['description'] }}</p>
                        <p class="mt-8">
                            <span class="text-4xl font-extrabold text-gray-900">₱{{ $plan['monthly_price'] }}</span>
                            <span class="text-base font-medium text-gray-500">/mo</span>
                        </p>
        
                        <a href="{{ $plan['route'] }}" class="mt-8 block w-full bg-indigo-600 border border-transparent rounded-md py-2 text-sm font-semibold text-white text-center hover:bg-indigo-700">Select {{ $plan['name'] }} Plan</a>
                    </div>
                    <div class="pt-6 pb-8 px-6">
                        <h4 class="text-sm font-medium text-gray-900 tracking-wide uppercase">What's included</h4>
                        <ul class="mt-6 space-y-4">
                            @foreach ($plan['features'] as $feature)
                                <li class="flex space-x-3">
                                    <svg class="flex-shrink-0 h-5 w-5 text-green-500" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                        <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                                    </svg>
                                    <span class="text-sm text-gray-500">{{ $feature }}</span>
                                </li>
                            @endforeach
                        </ul>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</x-app-layout>
