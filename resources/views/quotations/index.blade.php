<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Quotations') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    
                    <div class="mb-4">
                        <a href="{{ route('quotations.create') }}" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                            + Add New Quotation
                        </a>
                    </div>

                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Quote #</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Project Name</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Client</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Total</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @forelse ($quotations as $quotation)
                                <tr>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <a href="{{ route('quotations.show', $quotation) }}" class="text-indigo-600 hover:text-indigo-900 font-medium">
                                            {{ $quotation->quotation_no }}
                                        </a>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">{{ $quotation->project_name }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap">{{ $quotation->client->name }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap">{{ \Carbon\Carbon::parse($quotation->date)->format('m/d/Y') }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        {{ number_format($quotation->total_estimate, 0, ',', '.') }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                            @switch($quotation->status)
                                                @case('draft') bg-gray-200 text-gray-800 @break
                                                @case('sent') bg-blue-200 text-blue-800 @break
                                                @case('approved') bg-green-200 text-green-800 @break
                                                @case('rejected') bg-red-200 text-red-800 @break
                                            @endswitch">
                                            {{ ucfirst($quotation->status) }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                        <a href="{{ route('quotations.edit', $quotation) }}" class="text-indigo-600 hover:text-indigo-900">Edit</a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="px-6 py-4 whitespace-nowrap text-center text-gray-500">
                                        No quotations found.
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