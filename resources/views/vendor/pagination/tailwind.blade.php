@if ($paginator->hasPages())
    <nav role="navigation" aria-label="{{ __('Pagination Navigation') }}" class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
        <p class="text-sm text-base-content/60">
            {!! __('Showing') !!}
            @if ($paginator->firstItem())
                <span class="font-medium">{{ $paginator->firstItem() }}</span>
                {!! __('to') !!}
                <span class="font-medium">{{ $paginator->lastItem() }}</span>
            @else
                {{ $paginator->count() }}
            @endif
            {!! __('of') !!}
            <span class="font-medium">{{ $paginator->total() }}</span>
            {!! __('results') !!}
        </p>

        <div class="join">
            {{-- Previous --}}
            @if ($paginator->onFirstPage())
                <button class="join-item btn btn-disabled" aria-label="{{ __('pagination.previous') }}">«</button>
            @else
                <a href="{{ $paginator->previousPageUrl() }}" rel="prev" class="join-item btn" aria-label="{{ __('pagination.previous') }}">«</a>
            @endif

            {{-- Page Numbers --}}
            @foreach ($elements as $element)
                @if (is_string($element))
                    <button class="join-item btn btn-disabled hidden sm:inline-flex">{{ $element }}</button>
                @endif

                @if (is_array($element))
                    @foreach ($element as $page => $url)
                        @if ($page == $paginator->currentPage())
                            <button class="join-item btn btn-primary" aria-current="page">{{ $page }}</button>
                        @else
                            <a href="{{ $url }}" class="join-item btn hidden sm:inline-flex" aria-label="{{ __('Go to page :page', ['page' => $page]) }}">{{ $page }}</a>
                        @endif
                    @endforeach
                @endif
            @endforeach

            {{-- Next --}}
            @if ($paginator->hasMorePages())
                <a href="{{ $paginator->nextPageUrl() }}" rel="next" class="join-item btn" aria-label="{{ __('pagination.next') }}">»</a>
            @else
                <button class="join-item btn btn-disabled" aria-label="{{ __('pagination.next') }}">»</button>
            @endif
        </div>
    </nav>
@endif
