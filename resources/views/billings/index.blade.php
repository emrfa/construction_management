<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Billing Requests') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">

                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Billing #</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Project</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Billing Date</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Amount (Rp)</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @forelse ($billings as $billing)
                                <tr>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <a href="{{ route('billings.show', $billing) }}" class="text-indigo-600 hover:text-indigo-900 font-medium">
                                            {{ $billing->billing_no }}
                                        </a>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">{{ $billing->project->quotation->project_name }}                                        
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">{{ \Carbon\Carbon::parse($billing->billing_date)->format('m/d/Y') }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap">{{ number_format($billing->amount, 0, ',', '.') }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full
                                            @switch($billing->status)
                                                @case('pending') bg-yellow-200 text-yellow-800 @break
                                                @case('approved') bg-blue-200 text-blue-800 @break
                                                @case('invoiced') bg-green-200 text-green-800 @break
                                                @case('rejected') bg-red-200 text-red-800 @break
                                            @endswitch">
                                            {{ ucfirst($billing->status) }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                        <a href="{{ route('billings.show', $billing) }}" class="text-indigo-600 hover:text-indigo-900">View</a>
                                        </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="px-6 py-4 whitespace-nowrap text-center text-gray-500">
                                        No billing requests found.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>

                </div>
            </div>
        </div>
    </div>
</x-app-layout>