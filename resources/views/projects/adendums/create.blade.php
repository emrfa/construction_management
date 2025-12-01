<x-app-layout>
    <x-slot name="breadcrumbs">
        <x-breadcrumbs :items="[
            ['label' => 'Projects', 'url' => route('projects.index')],
            ['label' => $project->project_code, 'url' => route('projects.show', $project)],
            ['label' => 'Adendums', 'url' => route('projects.adendums.index', $project)],
            ['label' => 'New Adendum', 'url' => '']
        ]" />
    </x-slot>

    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Create Adendum for: ') }} {{ $project->quotation->project_name ?? $project->name }}
        </h2>
    </x-slot>

    <div class="py-12" x-data="adendumForm()">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <form action="{{ route('projects.adendums.store', $project) }}" method="POST" class="p-6 text-gray-900">
                    @csrf

                    {{-- Header Fields --}}
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                        <div>
                            <x-input-label for="date" :value="__('Date')" />
                            <x-text-input id="date" class="block mt-1 w-full" type="date" name="date" :value="old('date', date('Y-m-d'))" required />
                            <x-input-error :messages="$errors->get('date')" class="mt-2" />
                        </div>

                        <div>
                            <x-input-label for="subject" :value="__('Subject')" />
                            <x-text-input id="subject" class="block mt-1 w-full" type="text" name="subject" :value="old('subject')" required placeholder="e.g. Perubahan Pagar" />
                            <x-input-error :messages="$errors->get('subject')" class="mt-2" />
                        </div>

                        <div class="col-span-2">
                            <x-input-label for="description" :value="__('Description / Notes')" />
                            <textarea id="description" name="description" rows="3" class="block mt-1 w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">{{ old('description') }}</textarea>
                            <x-input-error :messages="$errors->get('description')" class="mt-2" />
                        </div>

                        <div>
                            <x-input-label for="time_extension_days" :value="__('Time Extension (Days)')" />
                            <x-text-input id="time_extension_days" class="block mt-1 w-full" type="number" name="time_extension_days" :value="old('time_extension_days', 0)" min="0" />
                            <x-input-error :messages="$errors->get('time_extension_days')" class="mt-2" />
                        </div>
                    </div>

                    <hr class="my-6 border-gray-200">

                    {{-- Items Section --}}
                    <div class="mb-6">
                        <h3 class="text-lg font-medium mb-4">Adendum Items</h3>

                        {{-- Item Adder --}}
                        <div class="flex gap-4 mb-4 p-4 bg-gray-50 rounded-md items-end border border-gray-200">
                            <div>
                                <x-input-label value="Type" />
                                <select x-model="newItemType" class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">
                                    <option value="add_new">Tambah Kerja Baru (New Work)</option>
                                    <option value="add_existing">Tambah Volume (Add to Existing)</option>
                                    <option value="deduct">Kurang Kerja (Deduct)</option>
                                </select>
                            </div>

                            <div class="flex-1" x-show="newItemType === 'deduct' || newItemType === 'add_existing'">
                                <x-input-label value="Select Existing Item" />
                                <select x-model="selectedQuotationItem" class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">
                                    <option value="">-- Select Item --</option>
                                    @foreach ($quotationItems as $item)
                                        <option value="{{ $item->id }}" 
                                            data-description="{{ $item->description }}"
                                            data-price="{{ $item->unit_price }}"
                                            data-uom="{{ $item->uom }}">
                                            {{ $item->item_code }} - {{ $item->description }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="flex-1" x-show="newItemType === 'add_new'">
                                <x-input-label value="Select from AHS (Optional)" />
                                <select x-model="selectedAhsItem" class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">
                                    <option value="">-- Manual Input --</option>
                                    @foreach ($ahsItems as $ahs)
                                        <option value="{{ $ahs->id }}" 
                                            data-name="{{ $ahs->name }}"
                                            data-price="{{ $ahs->total_cost }}"
                                            data-uom="{{ $ahs->unit }}">
                                            {{ $ahs->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <button type="button" @click="addItem()" class="px-4 py-2 bg-gray-800 text-white rounded-md hover:bg-gray-700 mb-[2px]">
                                Add Item
                            </button>
                        </div>

                        {{-- Items Table --}}
                        <div class="overflow-x-auto border rounded-lg">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Description</th>
                                        <th class="px-4 py-2 text-right text-xs font-medium text-gray-500 uppercase">Qty</th>
                                        <th class="px-4 py-2 text-center text-xs font-medium text-gray-500 uppercase">UoM</th>
                                        <th class="px-4 py-2 text-right text-xs font-medium text-gray-500 uppercase">Unit Price</th>
                                        <th class="px-4 py-2 text-right text-xs font-medium text-gray-500 uppercase">Total</th>
                                        <th class="px-4 py-2 text-center text-xs font-medium text-gray-500 uppercase">Action</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    <template x-for="(item, index) in items" :key="index">
                                        <tr>
                                            <td class="px-4 py-2">
                                                <input type="hidden" :name="`items[${index}][quotation_item_id]`" :value="item.quotation_item_id">
                                                <input type="text" :name="`items[${index}][description]`" x-model="item.description" class="w-full text-sm border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm" required />
                                            </td>
                                            <td class="px-4 py-2">
                                                <input type="number" step="0.0001" :name="`items[${index}][quantity]`" x-model="item.quantity" 
                                                    @change="if(item.type === 'deduct') item.quantity = -Math.abs(item.quantity); else item.quantity = Math.abs(item.quantity)"
                                                    class="w-24 text-right text-sm border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm" required />
                                                <div x-show="item.type === 'deduct'" class="text-xs text-red-500 mt-1">Deduction</div>
                                            </td>
                                            <td class="px-4 py-2">
                                                <input type="text" :name="`items[${index}][uom]`" x-model="item.uom" class="w-16 text-center text-sm border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm" />
                                            </td>
                                            <td class="px-4 py-2">
                                                <input type="number" step="0.01" :name="`items[${index}][unit_price]`" x-model="item.unit_price" class="w-32 text-right text-sm border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm" :readonly="!!item.quotation_item_id" required />
                                            </td>
                                            <td class="px-4 py-2 text-right text-sm font-medium">
                                                <span x-text="formatMoney(item.quantity * item.unit_price)"></span>
                                            </td>
                                            <td class="px-4 py-2 text-center">
                                                <button type="button" @click="removeItem(index)" class="text-red-600 hover:text-red-900 font-bold">
                                                    &times;
                                                </button>
                                            </td>
                                        </tr>
                                    </template>
                                    <tr x-show="items.length === 0">
                                        <td colspan="6" class="px-4 py-8 text-center text-gray-500">
                                            No items added yet.
                                        </td>
                                    </tr>
                                </tbody>
                                <tfoot class="bg-gray-50 font-bold">
                                    <tr>
                                        <td colspan="4" class="px-4 py-2 text-right">Total Adendum:</td>
                                        <td class="px-4 py-2 text-right">
                                            <span x-text="formatMoney(calculateTotal())"></span>
                                        </td>
                                        <td></td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                        <x-input-error :messages="$errors->get('items')" class="mt-2" />
                    </div>

                    <div class="flex items-center justify-end mt-4">
                        <a href="{{ route('projects.adendums.index', $project) }}" class="text-gray-600 hover:text-gray-900 underline mr-4">Cancel</a>
                        <x-primary-button>
                            {{ __('Save Draft') }}
                        </x-primary-button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.data('adendumForm', () => ({
                newItemType: 'add_new',
                selectedQuotationItem: '',
                selectedAhsItem: '',
                items: [],

                addItem() {
                    let newItem = {
                        description: '',
                        quantity: 0,
                        unit_price: 0,
                        uom: '',
                        quotation_item_id: null,
                        type: this.newItemType, // Store the type
                    };

                    if ((this.newItemType === 'deduct' || this.newItemType === 'add_existing') && this.selectedQuotationItem) {
                        // Find selected option element to get data attributes
                        const select = document.querySelector('select[x-model="selectedQuotationItem"]');
                        const option = select.options[select.selectedIndex];
                        
                        if (option) {
                            let prefix = this.newItemType === 'deduct' ? '[DEDUCT] ' : '[ADD] ';
                            newItem.description = `${prefix}${option.dataset.description}`;
                            newItem.unit_price = parseFloat(option.dataset.price);
                            newItem.uom = option.dataset.uom;
                            newItem.quotation_item_id = this.selectedQuotationItem;
                        }
                    } else if (this.newItemType === 'add_new' && this.selectedAhsItem) {
                        // Find selected option element to get data attributes
                        const select = document.querySelector('select[x-model="selectedAhsItem"]');
                        const option = select.options[select.selectedIndex];
                        
                        if (option) {
                            newItem.description = option.dataset.name;
                            newItem.unit_price = parseFloat(option.dataset.price);
                            newItem.uom = option.dataset.uom;
                        }
                    }

                    this.items.push(newItem);
                    this.selectedQuotationItem = ''; // Reset
                    this.selectedAhsItem = ''; // Reset
                },

                removeItem(index) {
                    this.items.splice(index, 1);
                },

                calculateTotal() {
                    return this.items.reduce((sum, item) => {
                        return sum + (parseFloat(item.quantity || 0) * parseFloat(item.unit_price || 0));
                    }, 0);
                },

                formatMoney(amount) {
                    return new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR' }).format(amount);
                }
            }));
        });
    </script>
</x-app-layout>
