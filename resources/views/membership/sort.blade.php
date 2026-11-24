<div class="list-sort">
    <label for="sort">Sort By</label>
    <select id="sort" name="sort">
        @foreach(['newest' => 'Newest First', 'oldest' => 'Oldest First', 'name_asc' => ($nameLabel ?? 'Name').' (A–Z)', 'name_desc' => ($nameLabel ?? 'Name').' (Z–A)'] as $value => $text)
            <option value="{{ $value }}" @selected(request('sort', $sortDefault ?? 'newest') === $value)>{{ $text }}</option>
        @endforeach
        @if($includeFee ?? false)
            <option value="fee_asc" @selected(request('sort', $sortDefault ?? 'newest') === 'fee_asc')>Fee (Low to High)</option>
            <option value="fee_desc" @selected(request('sort', $sortDefault ?? 'newest') === 'fee_desc')>Fee (High to Low)</option>
        @endif
    </select>
</div>
