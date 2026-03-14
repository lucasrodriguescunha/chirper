<nav class="flex justify-between items-center mt-6">

    @if ($paginator->onFirstPage())
        <button class="btn btn-disabled btn-sm">
            « Previous
        </button>
    @else
        <a href="{{ $paginator->previousPageUrl() }}" class="btn btn-sm">
            « Previous
        </a>
    @endif


    @if ($paginator->hasMorePages())
        <a href="{{ $paginator->nextPageUrl() }}" class="btn btn-sm">
            Next »
        </a>
    @else
        <button class="btn btn-disabled btn-sm">
            Next »
        </button>
    @endif

</nav>
