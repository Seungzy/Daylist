<nav class="pagination" aria-label="Pagination">
    <span>@if($records->total())Showing {{ $records->firstItem() }}–{{ $records->lastItem() }} of {{ $records->total() }}@else No records @endif</span>
    @if($records->hasPages())
        @php
            $firstPage = max(1, $records->currentPage() - 2);
            $lastPage = min($records->lastPage(), $records->currentPage() + 2);
        @endphp
        <div class="pagination-links">
            @if($records->previousPageUrl())<a class="btn secondary small" rel="prev" href="{{ $records->previousPageUrl() }}">← Previous</a>@else<span class="btn secondary small pagination-disabled" aria-disabled="true">← Previous</span>@endif
            @if($firstPage > 1)<a class="btn secondary small" aria-label="Page 1" href="{{ $records->url(1) }}">1</a>@if($firstPage > 2)<span aria-hidden="true">…</span>@endif @endif
            @foreach($records->getUrlRange($firstPage, $lastPage) as $page => $url)
                @if($page === $records->currentPage())<span class="btn small pagination-current" aria-current="page" aria-label="Page {{ $page }}">{{ $page }}</span>@else<a class="btn secondary small" aria-label="Page {{ $page }}" href="{{ $url }}">{{ $page }}</a>@endif
            @endforeach
            @if($lastPage < $records->lastPage())@if($lastPage < $records->lastPage() - 1)<span aria-hidden="true">…</span>@endif<a class="btn secondary small" aria-label="Page {{ $records->lastPage() }}" href="{{ $records->url($records->lastPage()) }}">{{ $records->lastPage() }}</a>@endif
            @if($records->nextPageUrl())<a class="btn secondary small" rel="next" href="{{ $records->nextPageUrl() }}">Next →</a>@else<span class="btn secondary small pagination-disabled" aria-disabled="true">Next →</span>@endif
        </div>
    @else
        <span>Page {{ $records->currentPage() }} of {{ $records->lastPage() }}</span>
    @endif
</nav>
