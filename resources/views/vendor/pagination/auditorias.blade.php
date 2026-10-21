@if ($paginator->hasPages())
    <nav class="pagination-modern" role="navigation" aria-label="Paginación de auditorías">
        <p class="pagination-summary">
            Mostrando
            <strong>{{ $paginator->firstItem() }}</strong>–<strong>{{ $paginator->lastItem() }}</strong>
            de <strong>{{ $paginator->total() }}</strong> registros
        </p>

        <div class="pagination-controls" aria-label="Páginas">
            @if ($paginator->onFirstPage())
                <span class="page-control is-disabled" aria-disabled="true">‹ Anterior</span>
            @else
                <a class="page-control" href="{{ $paginator->previousPageUrl() }}" rel="prev">‹ Anterior</a>
            @endif

            @foreach ($elements as $element)
                @if (is_string($element))
                    <span class="page-ellipsis" aria-hidden="true">{{ $element }}</span>
                @endif
                @if (is_array($element))
                    @foreach ($element as $page => $url)
                        @if ($page == $paginator->currentPage())
                            <span class="page-number is-current" aria-current="page" aria-label="Página {{ $page }}">{{ $page }}</span>
                        @else
                            <a class="page-number" href="{{ $url }}" aria-label="Ir a la página {{ $page }}">{{ $page }}</a>
                        @endif
                    @endforeach
                @endif
            @endforeach

            @if ($paginator->hasMorePages())
                <a class="page-control" href="{{ $paginator->nextPageUrl() }}" rel="next">Siguiente ›</a>
            @else
                <span class="page-control is-disabled" aria-disabled="true">Siguiente ›</span>
            @endif
        </div>
    </nav>
@endif
