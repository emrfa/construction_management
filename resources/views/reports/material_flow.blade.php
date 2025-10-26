<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{-- Clearly identify the project --}}
            <a href="{{ route('projects.show', $project) }}" class="text-indigo-600 hover:text-indigo-900">
                {{ $project->project_code }}
            </a>
            <span class="text-gray-500">/</span>
            <span>Material Flow Report (Request vs PO vs Usage)</span>
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">

                    <h3 class="text-lg font-semibold mb-2">Project: {{ $project->quotation->project_name }}</h3>
                    <p class="text-sm text-gray-600 mb-4">Report generated on: {{ now()->format('Y-m-d H:i') }}</p>

                    <div class="overflow-x-auto border rounded">
                        <table class="min-w-full divide-y divide-gray-200 text-sm">
                            <thead class="bg-gray-100">
                                <tr>
                                    <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase">Code</th>
                                    <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase">Material</th>
                                    <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase">UOM</th>
                                    <th class="px-3 py-2 text-right text-xs font-medium text-gray-500 uppercase">Requested</th>
                                    <th class="px-3 py-2 text-right text-xs font-medium text-gray-500 uppercase">Ordered</th>
                                    <th class="px-3 py-2 text-right text-xs font-medium text-gray-500 uppercase">Received</th>
                                    <th class="px-3 py-2 text-right text-xs font-medium text-gray-500 uppercase">Used</th>
                                    {{-- Optional Balance Columns --}}
                                    {{-- <th class="px-3 py-2 text-right text-xs font-medium text-gray-500 uppercase">Bal. Req</th> --}}
                                    {{-- <th class="px-3 py-2 text-right text-xs font-medium text-gray-500 uppercase">Bal. PO</th> --}}
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                {{-- We will loop through $reportData here later --}}
                                @forelse ($reportData as $item)
                                    <tr>
                                        <td class="px-3 py-2 whitespace-nowrap font-mono">{{ $item['item_code'] ?? 'N/A' }}</td>
                                        <td class="px-3 py-2 whitespace-nowrap">{{ $item['item_name'] ?? 'N/A' }}</td>
                                        <td class="px-3 py-2 whitespace-nowrap">{{ $item['uom'] ?? 'N/A' }}</td>
                                        <td class="px-3 py-2 whitespace-nowrap text-right">{{ number_format($item['requested_qty'] ?? 0, 2, ',', '.') }}</td>
                                        <td class="px-3 py-2 whitespace-nowrap text-right">{{ number_format($item['ordered_qty'] ?? 0, 2, ',', '.') }}</td>
                                        <td class="px-3 py-2 whitespace-nowrap text-right">{{ number_format($item['received_qty'] ?? 0, 2, ',', '.') }}</td>
                                        <td class="px-3 py-2 whitespace-nowrap text-right">{{ number_format($item['used_qty'] ?? 0, 2, ',', '.') }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="7" class="px-3 py-4 text-center text-gray-500">
                                            No material flow data available for this project yet. Ensure requests, POs, receipts, and usage are recorded.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                </div>
            </div>
        </div>
    </div>
</x-app-layout>