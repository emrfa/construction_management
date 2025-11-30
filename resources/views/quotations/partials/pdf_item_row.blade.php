<tr class="{{ $item->children->count() > 0 ? 'item-parent' : '' }}">
    <td class="col-no">{{ $level === 0 ? $number : '' }}</td>
    <td class="col-desc {{ $level > 0 ? 'indent-' . $level : '' }}">
        {{ $item->description }}
    </td>
    <td class="col-unit">{{ $item->uom }}</td>
    <td class="col-qty">{{ $item->quantity > 0 ? $item->quantity : '' }}</td>
    <td class="col-price">
        {{ $item->unit_price > 0 ? number_format($item->unit_price, 0, ',', '.') : '' }}
    </td>
    <td class="col-total">
        {{ $item->subtotal > 0 ? number_format($item->subtotal, 0, ',', '.') : '' }}
    </td>
</tr>

@if($item->children->count() > 0)
    @foreach($item->children->sortBy('sort_order') as $child)
        @include('quotations.partials.pdf_item_row', ['item' => $child, 'level' => $level + 1, 'number' => ''])
    @endforeach
@endif
