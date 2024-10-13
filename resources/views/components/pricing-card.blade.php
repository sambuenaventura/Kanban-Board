<div class="flex flex-col bg-white border {{ $plan['popular'] ?? false ? 'border-2 border-blue-500' : 'border-gray-200' }} rounded-lg shadow-sm overflow-hidden">
    <div class="p-6 flex-grow">
        @if($plan['popular'] ?? false)
            <div class="inline-block bg-blue-100 rounded-full px-3 py-1 text-xs font-semibold text-blue-800 mb-4">
                MOST POPULAR
            </div>
        @endif
        <h3 class="text-lg font-semibold text-gray-900 mb-4">{{ $plan['name'] }}</h3>
        <p class="mb-6">
            <span class="text-3xl font-bold text-gray-900">{{ $plan['price'] }}</span>
            <span class="text-base font-medium text-gray-500">{{ $plan['period'] }}</span>
        </p>
        <ul class="space-y-4 mb-6">
            @foreach($plan['features'] as $feature)
                <li class="flex items-start">
                    <svg class="h-6 w-6 text-green-500 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                    </svg>
                    <span class="text-gray-600">{{ $feature }}</span>
                </li>
            @endforeach
        </ul>
    </div>
    <div class="p-6 bg-gray-50 mt-auto">
        @if($user->hasLifetimeAccess() && $plan['name'] === 'Lifetime')
            <div class="w-full bg-green-500 text-white text-center px-4 py-2 rounded-md font-semibold">
                Current Plan
            </div>
        @elseif($currentPlan === $plan['action'] && !$user->hasLifetimeAccess())
            <div class="w-full bg-green-500 text-white text-center px-4 py-2 rounded-md font-semibold">
                Current Plan
            </div>
        @else
            @if($plan['disabled'])
                <div class="w-full bg-gray-300 text-gray-500 text-center px-4 py-2 rounded-md font-semibold">
                    Disabled
                </div>
            @elseif($plan['name'] === 'Lifetime')
                <a href="{{ route('checkout', ['plan' => $plan['action']]) }}" class="block w-full bg-blue-600 text-white text-center px-4 py-2 rounded-md font-semibold hover:bg-blue-700 transition duration-300">
                    {{ $plan['button_text'] }}
                </a>
            @else
                <form action="{{ route('subscription.change') }}" method="POST">
                    @csrf
                    <input type="hidden" name="plan" value="{{ $plan['action'] }}">
                    <button type="submit" class="w-full bg-blue-600 text-white text-center px-4 py-2 rounded-md font-semibold hover:bg-blue-700 transition duration-300">
                        {{ $plan['button_text'] }}
                    </button>
                </form>
            @endif
        @endif
    </div>
</div>
