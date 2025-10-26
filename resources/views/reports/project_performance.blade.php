<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{-- Clearly identify the project --}}
            <a href="{{ route('projects.show', $project) }}" class="text-indigo-600 hover:text-indigo-900">
                {{ $project->project_code }}
            </a>
            <span class="text-gray-500">/</span>
            <span>Project Performance Report</span>
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8"> {{-- Adjusted max-width --}}
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">

                    <h3 class="text-lg font-semibold mb-2">Project: {{ $project->quotation->project_name }}</h3>
                    <p class="text-sm text-gray-600 mb-4">Report generated on: {{ now()->format('Y-m-d H:i') }}</p>

                    <div class="overflow-x-auto border rounded">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-100">
                                <tr>
                                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider w-2/5">Metric</th>
                                    <th class="px-4 py-2 text-right text-xs font-medium text-gray-500 uppercase tracking-wider w-2/5">Value (Rp)</th>
                                    <th class="px-4 py-2 text-right text-xs font-medium text-gray-500 uppercase tracking-wider w-1/5">%</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200 text-sm">
                                <tr>
                                    <td class="px-4 py-2 font-medium">Budget (RAB)</td>
                                    <td class="px-4 py-2 text-right">{{ number_format($reportData['budget_total'], 0, ',', '.') }}</td>
                                    <td class="px-4 py-2 text-right">100.0%</td>
                                </tr>
                                <tr class="bg-blue-50">
                                    <td class="px-4 py-2 font-medium">Earned Value (Physical Progress)</td>
                                    <td class="px-4 py-2 text-right">{{ number_format($reportData['earned_value'], 0, ',', '.') }}</td>
                                    <td class="px-4 py-2 text-right font-semibold text-blue-700">{{ number_format($reportData['physical_progress_percent'], 1, ',', '.') }}%</td>
                                </tr>
                                <tr class="bg-orange-50">
                                    <td class="px-4 py-2 font-medium">Actual Cost (Material Usage)</td>
                                    <td class="px-4 py-2 text-right text-orange-700">{{ number_format($reportData['actual_cost'], 0, ',', '.') }}</td>
                                    <td class="px-4 py-2 text-right">-</td>
                                </tr>
                                <tr>
                                    <td class="px-4 py-2 font-medium">Procurement (Value Received)</td>
                                    <td class="px-4 py-2 text-right">{{ number_format($reportData['procurement_value_received'], 0, ',', '.') }}</td>
                                    <td class="px-4 py-2 text-right">-</td>
                                </tr>
                                <tr class="border-t-2 border-gray-300">
                                    <td class="px-4 py-2 font-bold">Cost Variance (CV = EV - AC)</td>
                                    <td class="px-4 py-2 text-right font-bold {{ $reportData['cost_variance'] >= 0 ? 'text-green-600' : 'text-red-600' }}">
                                        {{ number_format($reportData['cost_variance'], 0, ',', '.') }}
                                    </td>
                                    <td class="px-4 py-2 text-right">-</td>
                                </tr>
                                {{-- Add Schedule Variance (SV) here later if Planned Value is implemented --}}
                            </tbody>
                        </table>
                    </div>
                     <p class="text-xs text-gray-500 mt-2">
                         * Positive Cost Variance means the project is under budget for the work completed. Negative means over budget.
                     </p>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>