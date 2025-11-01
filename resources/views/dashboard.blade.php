<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Dashboard') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 space-y-8">
                    
                    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                        <div class="bg-gray-50 p-4 rounded-lg border">
                            <dt class="text-sm font-medium text-gray-500 truncate">Active Projects</dt>
                            <dd class="mt-1 text-3xl font-semibold text-indigo-600">{{ $activeProjectsCount }}</dd>
                        </div>
                        <div class="bg-gray-50 p-4 rounded-lg border">
                            <dt class="text-sm font-medium text-gray-500 truncate">Total Clients</dt>
                            <dd class="mt-1 text-3xl font-semibold text-indigo-600">{{ $clientCount }}</dd>
                        </div>
                        <div class="bg-gray-50 p-4 rounded-lg border">
                            <dt class="text-sm font-medium text-gray-500 truncate">Pending Billings</dt>
                            <dd class="mt-1 text-3xl font-semibold text-yellow-600">{{ $pendingBillingsCount }}</dd>
                        </div>
                        <div class="bg-gray-50 p-4 rounded-lg border">
                            <dt class="text-sm font-medium text-gray-500 truncate">Pending Material Requests</dt>
                            <dd class="mt-1 text-3xl font-semibold text-red-600">{{ $pendingRequestsCount }}</dd>
                        </div>
                    </div>

                    <div>
                        <h3 class="text-lg font-medium text-gray-900">Quick Access</h3>
                        <div class="mt-4 flex flex-wrap gap-2">
                            <a href="{{ route('quotations.create') }}" class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700">
                                New Quotation
                            </a>
                            <a href="{{ route('clients.create') }}" class="inline-flex items-center px-4 py-2 bg-gray-700 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-800">
                                New Client
                            </a>
                            <a href="{{ route('suppliers.create') }}" class="inline-flex items-center px-4 py-2 bg-gray-700 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-800">
                                New Supplier
                            </a>
                            <a href="{{ route('purchase-orders.create') }}" class="inline-flex items-center px-4 py-2 bg-gray-700 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-800">
                                New Purchase Order
                            </a>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <h3 class="text-lg font-medium text-gray-900">Pending Material Requests</h3>
                            <div class="mt-4 border rounded-lg overflow-hidden">
                                <ul class="divide-y divide-gray-200">
                                    @forelse($pendingRequests as $request)
                                        <li class="p-3 hover:bg-gray-50">
                                            <a href="{{ route('material-requests.show', $request) }}" class="block">
                                                <div class="flex justify-between">
                                                    <span class="font-semibold text-indigo-600">{{ $request->request_code }}</span>
                                                    <span class="text-sm text-gray-500">{{ $request->request_date->format('d-M-Y') }}</span>
                                                </div>
                                                <p class="text-sm text-gray-700">{{ $request->project->quotation->project_name }}</p>
                                                <p class="text-xs text-gray-500">Requested by {{ $request->requester->name }}</p>
                                            </a>
                                        </li>
                                    @empty
                                        <li class="p-4 text-sm text-gray-500 text-center">No pending material requests.</li>
                                    @endforelse
                                </ul>
                            </div>
                        </div>

                        <div>
                            <h3 class="text-lg font-medium text-gray-900">Pending Billings</h3>
                            <div class="mt-4 border rounded-lg overflow-hidden">
                                <ul class="divide-y divide-gray-200">
                                    @forelse($pendingBillings as $billing)
                                        <li class="p-3 hover:bg-gray-50">
                                            <a href="{{ route('billings.show', $billing) }}" class="block">
                                                <div class="flex justify-between">
                                                    <span class="font-semibold text-indigo-600">{{ $billing->billing_no }}</span>
                                                    <span class="font-bold text-gray-800">Rp {{ number_format($billing->amount, 0) }}</span>
                                                </div>
                                                <p class="text-sm text-gray-700">{{ $billing->project->quotation->project_name }}</p>
                                                <p class="text-xs text-gray-500">Date: {{ $billing->billing_date->format('d-M-Y') }}</p>
                                            </a>
                                        </li>
                                    @empty
                                        <li class="p-4 text-sm text-gray-500 text-center">No pending billings.</li>
                                    @endforelse
                                </ul>
                            </div>
                        </div>
                    </div>

                    <div>
                        <h3 class="text-lg font-medium text-gray-900">Active Projects</h3>
                        <div class="mt-4 border rounded-lg overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Project Name</th>
                                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Client</th>
                                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                                        <th class="px-4 py-2 text-right text-xs font-medium text-gray-500 uppercase">Actions</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    @forelse($activeProjects as $project)
                                        <tr>
                                            <td class="px-4 py-3 whitespace-nowrap text-sm font-medium text-gray-900">{{ $project->quotation->project_name }}</td>
                                            <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-500">{{ $project->client->name }}</td>
                                            <td class="px-4 py-3 whitespace-nowrap text-sm">
                                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                                    {{ $project->status == 'in_progress' ? 'bg-green-100 text-green-800' : 'bg-blue-100 text-blue-800' }}">
                                                    {{ str_replace('_', ' ', $project->status) }}
                                                </span>
                                            </td>
                                            <td class="px-4 py-3 whitespace-nowrap text-right text-sm">
                                                <a href="{{ route('projects.show', $project) }}" class="text-indigo-600 hover:text-indigo-900">View</a>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="4" class="px-4 py-4 text-center text-sm text-gray-500">No active projects found.</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>
</x-app-layout>