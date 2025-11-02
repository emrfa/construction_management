<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Import AHS Library') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-2xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">

                    @if(session('success'))
                        <div class="mb-4 p-4 bg-green-100 border border-green-300 text-green-800 rounded-md">
                            {{ session('success') }}
                        </div>
                    @endif
                    @if(session('error'))
                        <div class="mb-4 p-4 bg-red-100 border border-red-300 text-red-800 rounded-md">
                            {!! session('error') !!}
                        </div>
                    @endif

                    <div class="mb-4 p-4 bg-blue-50 border border-blue-200 rounded-md">
                        <h4 class="font-bold text-blue-800">Instructions</h4>
                        <ul class="list-disc list-inside text-sm text-blue-700 mt-2">
                            <li>Download the template by clicking "Export All" on the library page.</li>
                            <li>The file uses a "grouped" format. **One AHS header row**, followed by its component rows.</li>
                            <li>The import matches components by **name**, not code.</li>
                            <li>`component_name (Used for Match)` must **exactly match** an existing Item Name or Labor Type.</li>
                            <li>`ahs_code` is used to update items. If the code is new, a new AHS is created.</li>
                            <li>**Warning:** Importing will **overwrite all components** for any existing AHS code in the file.</li>
                            <li>The `total_cost` is calculated automatically.</li>
                        </ul>
                    </div>

                    <form method="POST" action="{{ route('ahs-library.import.analyze') }}" enctype="multipart/form-data">
                        @csrf
                        
                        <div>
                            <x-input-label for="file" :value="__('Excel File (.xlsx, .xls, .csv)')" />
                            <input id="file" class="block mt-1 w-full border-gray-300 rounded-md shadow-sm" type="file" name="file" required />
                            <x-input-error :messages="$errors->get('file')" class="mt-2" />
                        </div>

                        <div class="flex items-center justify-end mt-6">
                            <a href="{{ route('ahs-library.index') }}" class="text-sm text-gray-600 hover:text-gray-900 mr-4">
                                {{ __('Cancel') }}
                            </a>
                            <x-primary-button>
                                {{-- Changed button text --}}
                                {{ __('Analyze and Import File') }}
                            </x-primary-button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>