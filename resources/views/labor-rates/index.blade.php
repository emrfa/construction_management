<x-app-layout>
    <x-slot name="header">
        {{-- Use flexbox for header layout --}}
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Master Labor Rates') }} 🛠️
            </h2>
            {{-- Styled "Add New" button with icon --}}
            <a href="{{ route('labor-rates.create') }}" class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700 active:bg-blue-800 focus:outline-none focus:border-blue-800 focus:ring ring-blue-300 disabled:opacity-25 transition ease-in-out duration-150">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" />
                </svg>
                {{ __('Add New Labor Type') }}
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                {{-- Added space-y-6 --}}
                <div class="p-6 text-gray-900 space-y-6">

                    {{-- Removed the separate "Add New" button div here --}}

                    {{-- Table container with border and overflow --}}
                    <div class="overflow-x-auto border rounded-md">
                        <table class="min-w-full divide-y divide-gray-200">
                            {{-- Styled table header --}}
                            <thead class="bg-gray-100">
                                <tr>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Labor Type</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Unit</th>
                                    <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Rate (Rp)</th>
                                    <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                                </tr>
                            </thead>
                            {{-- Styled table body --}}
                            <tbody class="bg-white divide-y divide-gray-200">
                                @forelse ($laborRates as $rate)
                                    {{-- Row with hover effect --}}
                                    <tr class="hover:bg-gray-50 transition duration-150">
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">{{ $rate->labor_type }}</td> {{-- Adjusted styling --}}
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">{{ $rate->unit }}</td> {{-- Adjusted styling --}}
                                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-semibold text-gray-800">{{ number_format($rate->rate, 0, ',', '.') }}</td> {{-- Adjusted styling --}}
                                        {{-- Actions with icons --}}
                                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium space-x-2">
                                            {{-- Edit icon link --}}
                                            <a href="{{ route('labor-rates.edit', $rate) }}" class="text-indigo-600 hover:text-indigo-800" title="Edit">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 inline" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" /></svg>
                                            </a>
                                            {{-- Delete form with icon button --}}
                                            <form class="inline-block" action="{{ route('labor-rates.destroy', $rate) }}" method="POST" onsubmit="return confirm('Are you sure?');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="text-red-600 hover:text-red-800" title="Delete">
                                                     <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 inline" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
                                                </button>
                                            </form>
                                        </td>
                                    </tr>
                                @empty
                                    {{-- Empty state row --}}
                                    <tr>
                                        <td colspan="4" class="px-6 py-4 whitespace-nowrap text-center text-gray-500"> {{-- Adjusted colspan --}}
                                            No labor rates found.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    {{-- Add Pagination Links if using pagination in controller --}}
                     @if ($laborRates instanceof \Illuminate\Pagination\LengthAwarePaginator)
                     <div class="mt-4">
                         {{ $laborRates->links() }}
                     </div>
                     @endif

                </div>
            </div>
        </div>
    </div>
</x-app-layout>