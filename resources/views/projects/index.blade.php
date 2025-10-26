<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Projects') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">

                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Project Code</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Project Name</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Client</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Budget (Rp)</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Start Date</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @forelse ($projects as $project)
                                <tr>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <a href="{{ route('projects.show', $project) }}" class="text-indigo-600 hover:text-indigo-900 font-medium">
                                            {{ $project->project_code }}
                                        </a>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">{{ $project->quotation->project_name }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap">{{ $project->client->name }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap">{{ number_format($project->total_budget, 0, ',', '.') }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap">{{ $project->start_date ? \Carbon\Carbon::parse($project->start_date)->format('m/d/Y') : 'Not set' }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                            @switch($project->status)
                                                @case('initiated') bg-blue-200 text-blue-800 @break
                                                @case('in_progress') bg-yellow-200 text-yellow-800 @break
                                                @case('completed') bg-green-200 text-green-800 @break
                                                @case('closed') bg-gray-200 text-gray-800 @break
                                            @endswitch">
                                            {{ ucfirst(str_replace('_', ' ', $project->status)) }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                        <a href="{{ route('projects.show', $project) }}" class="text-indigo-600 hover:text-indigo-900">View</a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="px-6 py-4 whitespace-nowrap text-center text-gray-500">
                                        No projects found.
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