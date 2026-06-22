@if ($paginator->hasPages())
    <nav class="flex justify-between items-center mt-6">

        @if ($paginator->onFirstPage())
            <button class="btn btn-disabled">
                « Previous
            </button>
        @else
            <a href="{{ $paginator->previousPageUrl() }}" class="btn">
                « Previous
            </a>
        @endif


        @if ($paginator->hasMorePages())
            <a href="{{ $paginator->nextPageUrl() }}" class="btn">
                Next »
            </a>
        @else
            <button class="btn btn-disabled">
                Next »
            </button>
        @endif

    </nav>
@endif
