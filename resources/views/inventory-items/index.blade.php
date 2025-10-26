<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Item Master (Materials)') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">

                    <div class="mb-4">
                        <a href="{{ route('inventory-items.create') }}" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                            + Add New Item
                        </a>
                    </div>

                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Item Code</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Item Name</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Category</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">UoM</th>
                                <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Qty on Hand</th>
                                <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Reorder Level</th>
                                <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @forelse ($items as $item)
                                <tr>
                                    <td class="px-6 py-4 whitespace-nowrap font-medium">{{ $item->item_code }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap">{{ $item->item_name }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap">{{ $item->category }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap">{{ $item->uom }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-right font-semibold">{{ number_format($item->quantity_on_hand, 2, ',', '.') }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-right">{{ number_format($item->reorder_level, 2, ',', '.') }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                        <a href="{{ route('inventory-items.edit', $item) }}" class="text-indigo-600 hover:text-indigo-900">Edit</a>
                                        </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="px-6 py-4 whitespace-nowrap text-center text-gray-500">
                                        No items found. Add your first material to get started.
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