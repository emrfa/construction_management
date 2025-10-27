<x-app-layout>
    <x-slot name="header">
        {{-- Use flexbox for layout --}}
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Equipment Master') }} 🏗️
            </h2>
            <a href="{{ route('equipment.create') }}" class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700 active:bg-blue-800 focus:outline-none focus:border-blue-800 focus:ring ring-blue-300 disabled:opacity-25 transition ease-in-out duration-150">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" />
                </svg>
                {{ __('Add New Equipment') }}
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 space-y-6"> {{-- Added space-y-6 --}}

                    {{-- Table container with border and overflow --}}
                    <div class="overflow-x-auto border rounded-md">
                        <table class="min-w-full divide-y divide-gray-200">
                            {{-- Styled table header --}}
                            <thead class="bg-gray-100">
                                <tr>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Identifier</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Name</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Type</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Supplier (if Rented)</th>
                                    <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                                </tr>
                            </thead>
                            {{-- Styled table body --}}
                            <tbody class="bg-white divide-y divide-gray-200">
                                @forelse ($equipmentItems as $item)
                                    {{-- Row with hover effect --}}
                                    <tr class="hover:bg-gray-50 transition duration-150">
                                        <td class="px-6 py-4 whitespace-nowrap font-mono text-sm text-gray-700">{{ $item->identifier ?? 'N/A' }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">{{ $item->name }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">{{ $item->type }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm">
                                            {{-- Status Badge --}}
                                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full
                                                @switch($item->status)
                                                    @case('owned') bg-green-200 text-green-800 @break
                                                    @case('rented') bg-blue-200 text-blue-800 @break
                                                    @case('maintenance') bg-yellow-200 text-yellow-800 @break
                                                    @case('disposed') bg-gray-300 text-gray-700 @break
                                                    @default bg-gray-200 text-gray-600 @break
                                                @endswitch">
                                                {{ ucfirst($item->status) }}
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">{{ $item->supplier->name ?? ($item->status === 'rented' ? '-' : 'N/A') }}</td> {{-- Simplified logic --}}
                                        {{-- Actions with icons --}}
                                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium space-x-2">
                                            {{-- Optional View Icon Link (like AHS) --}}
                                            {{-- <a href="{{ route('equipment.show', $item) }}" class="text-blue-600 hover:text-blue-800" title="View Details">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 inline" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" /><path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" /></svg>
                                            </a> --}}
                                            <a href="{{ route('equipment.edit', $item) }}" class="text-indigo-600 hover:text-indigo-800" title="Edit">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 inline" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" /></svg>
                                            </a>
                                            {{-- Delete form with icon button --}}
                                            <form class="inline-block" action="{{ route('equipment.destroy', $item) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete this equipment?');">
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
                                        <td colspan="6" class="px-6 py-4 whitespace-nowrap text-center text-gray-500"> {{-- Adjusted colspan --}}
                                            No equipment found. Start by adding one.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    {{-- Pagination Links --}}
                    <div class="mt-4">
                        {{ $equipmentItems->links() }}
                    </div>

                </div>
            </div>
        </div>
    </div>
</x-app-layout>