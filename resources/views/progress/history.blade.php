<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            <a href="{{ route('projects.show', $project) }}" class="text-indigo-600 hover:text-indigo-900">
                &larr; {{ $project->project_code }}
            </a>
            <span class="text-gray-500">/</span>
            <span>Task Progress Log</span>
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-5xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">

                    <h3 class="text-lg font-semibold">{{ $quotation_item->description }}</h3>
                    <p class="text-sm text-gray-600 mb-4">
                        Current Progress: 
                        <span class="font-bold text-blue-600">{{ $quotation_item->latest_progress }}%</span>
                    </p>

                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Updated By</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">New % Complete</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Notes</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @forelse ($quotation_item->progressUpdates as $update)
                                <tr>
                                    <td class="px-6 py-4 whitespace-nowrap">{{ \Carbon\Carbon::parse($update->date)->format('F j, Y') }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap">{{ $update->user->name }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap">{{ $update->percent_complete }}%</td>
                                    <td class="px-6 py-4 whitespace-nowrap">{{ $update->notes }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="px-6 py-4 whitespace-nowrap text-center text-gray-500">
                                        No progress updates found for this task.
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