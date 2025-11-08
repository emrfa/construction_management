<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            <a href="{{ route('goods-receipts.index') }}" class="text-indigo-600 hover:text-indigo-900">
                &larr; Receipts
            </a>
            <span class="text-gray-500">/</span>
            <span>Receive Items for PO {{ $goodsReceipt->purchaseOrder->po_number }}</span>
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg" x-data="receiptForm()">
                
                <form method="POST" action="{{ route('goods-receipts.post', $goodsReceipt) }}"
                      @submit.prevent="handleSubmit" 
                      x-ref="receiptForm">
                    @csrf
                    
                    <input type="hidden" name="back_order_action" x-ref="backOrderAction" value="create">

                    <div class="p-6">
                        @if ($errors->any())
                            <div class="mb-4 p-4 bg-red-100 border border-red-300 text-red-800 rounded-md">
                                <strong class="font-bold">Whoops! Something went wrong.</strong>
                                <ul class="mt-2 list-disc list-inside text-sm">
                                    @foreach ($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif
                        @if (session('info'))
                            <div class="mb-4 p-4 bg-blue-100 border border-blue-300 text-blue-800 rounded-md">
                                {{ session('info') }}
                            </div>
                        @endif
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <div>
                                <x-input-label for="receipt_date" :value="__('Receipt Date')" />
                                <x-text-input id="receipt_date" class="block mt-1 w-full" type="date" name="receipt_date" :value="old('receipt_date', $goodsReceipt->receipt_date->format('Y-m-d'))" required />
                            </div>
                            <div>
                                <x-input-label :value="__('PO Reference')" />
                                <p class="mt-2 text-gray-700 font-semibold">{{ $goodsReceipt->purchaseOrder->po_number }}</p>
                            </div>
                            <div>
                                <x-input-label :value="__('Supplier')" />
                                <p class="mt-2 text-gray-700">{{ $goodsReceipt->supplier->name }}</p>
                            </div>
                            <div class="md:col-span-3">
                                <x-input-label for="notes" :value="__('Notes')" />
                                <textarea id="notes" name="notes" rows="2" class="block mt-1 w-full border-gray-300 rounded-md shadow-sm">{{ old('notes', $goodsReceipt->notes) }}</textarea>
                            </div>
                        </div>
                    </div>

                    <div class="p-6 border-t">
                        <h3 class="text-lg font-semibold mb-2">Items to Receive</h3>
                        <div class="overflow-x-auto border rounded">
                            <table class="min-w-full divide-y divide-gray-200 text-sm">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Code</th>
                                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Description</th>
                                        <th class="px-4 py-2 text-right text-xs font-medium text-gray-500 uppercase">Ordered</th>
                                        <th class="px-4 py-2 text-right text-xs font-medium text-gray-500 uppercase">Already Received</th>
                                        <th class="px-4 py-2 text-right text-xs font-medium text-gray-500 uppercase">Receiving Now</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    @foreach($itemsFromPO as $index => $item)
                                    <tr>
                                        <td class="px-4 py-2 whitespace-nowrap">{{ $item['item_code'] }}</td>
                                        <td class="px-4 py-2 whitespace-nowrap">{{ $item['item_name'] }} ({{ $item['uom'] }})</td>
                                        <td class="px-4 py-2 whitespace-nowrap text-right">{{ number_format($item['quantity_ordered'], 2) }}</td>
                                        <td class="px-4 py-2 whitespace-nowrap text-right">{{ number_format($item['quantity_already_received_on_po'], 2) }}</td>
                                        <td class="px-4 py-2 whitespace-nowrap text-right">
                                            <input type="hidden" name="items[{{ $index }}][goods_receipt_item_id]" value="{{ $item['goods_receipt_item_id'] }}">
                                            <input type="hidden" name="items[{{ $index }}][max_receivable]" value="{{ $item['max_receivable'] }}" x-ref="max_receivable">
                                            
                                            <input type="number" step="0.01" min="0" max="{{ $item['max_receivable'] }}"
                                                   name="items[{{ $index }}][quantity_to_receive]"
                                                   class="w-32 text-right border-gray-300 rounded-md shadow-sm"
                                                   value="{{ old('items.'.$index.'.quantity_to_receive', $item['quantity_to_receive']) }}"
                                                   x-ref="quantity_to_receive">
                                            
                                            <span class="text-xs text-gray-500 ml-1">Max: {{ number_format($item['max_receivable'], 2) }}</span>
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                    
                    <div class="flex items-center justify-end p-6 bg-gray-50 border-t">
                        <a href="{{ route('goods-receipts.index') }}" class="text-sm text-gray-600 hover:text-gray-900 rounded-md">
                            {{ __('Cancel') }}
                        </a>
                        <x-primary-button class="ml-4" type="submit">
                            {{ __( 'Receive Items') }}
                        </x-primary-button>
                    </div>
                </form>

                {{-- NEW: Confirmation Modal --}}
                <x-modal name="confirm-back-order" focusable>
                    <div class="p-6">
                        <h2 class="text-lg font-medium text-gray-900">
                            Partial Receipt Confirmation
                        </h2>
                        <p class="mt-1 text-sm text-gray-600">
                            You are receiving less than the total outstanding quantity. What would you like to do?
                        </p>
                        <div class="mt-6 flex justify-end space-x-3">
                            <x-secondary-button x-on:click="submitChoice('close')">
                                Mark as Fully Received
                            </x-secondary-button>
                            <x-primary-button x-on:click="submitChoice('create')">
                                Create Back-order
                            </x-primary-button>
                        </div>
                    </div>
                </x-modal>
                {{-- END: Confirmation Modal --}}

            </div>
        </div>
    </div>

    @push('scripts')
    <script>
    document.addEventListener('alpine:init', () => {
        Alpine.data('receiptForm', () => ({
            
            // This function is called by the modal buttons
            submitChoice(choice) {
                this.$refs.backOrderAction.value = choice;
                this.$dispatch('close-modal', 'confirm-back-order'); // Close the modal
                this.$refs.receiptForm.submit(); // Manually submit the form
            },

            // This function intercepts the main form submit
            handleSubmit() {
                let isReceivingSomething = false;
                let totalQty = 0;
                let totalMax = 0;

                const quantities = this.$refs.quantity_to_receive;
                const maxes = this.$refs.max_receivable;

                const qtyArray = Array.isArray(quantities) ? quantities : [quantities];
                const maxArray = Array.isArray(maxes) ? maxes : [maxes];

                for (let i = 0; i < qtyArray.length; i++) {
                    const qty = parseFloat(qtyArray[i].value) || 0;
                    const max = parseFloat(maxArray[i].value) || 0;

                    if (qty > 0) {
                        isReceivingSomething = true;
                    }
                    totalQty += qty;
                    totalMax += max;
                }
                
                // Is this a partial receipt?
                const isPartial = isReceivingSomething && totalQty < (totalMax - 0.001);

                if (isPartial) {
                    // It's partial. Stop the submit and open the modal.
                    this.$dispatch('open-modal', 'confirm-back-order');
                } else {
                    // It's not partial (or user is receiving 0). 
                    // Set default action and submit normally.
                    this.$refs.backOrderAction.value = 'create';
                    this.$refs.receiptForm.submit();
                }
            }
        }));
    });
    </script>
    @endpush
</x-app-layout>