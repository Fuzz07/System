@if ($paginator->hasPages())
<nav aria-label="Page navigation" class="ssc-pagination-nav">
    <div class="ssc-pagination-info">
        Showing
        <strong>{{ $paginator->firstItem() }}</strong>–<strong>{{ $paginator->lastItem() }}</strong>
        of <strong>{{ $paginator->total() }}</strong> records
    </div>
    <ul class="ssc-pagination">
        {{-- Previous Page Link --}}
        @if ($paginator->onFirstPage())
            <li class="ssc-page-item disabled">
                <span class="ssc-page-link"><i class="bi bi-chevron-left"></i></span>
            </li>
        @else
            <li class="ssc-page-item">
                <a class="ssc-page-link" href="{{ $paginator->previousPageUrl() }}" rel="prev" aria-label="Previous">
                    <i class="bi bi-chevron-left"></i>
                </a>
            </li>
        @endif

        {{-- Pagination Elements --}}
        @foreach ($elements as $element)
            {{-- "Three Dots" Separator --}}
            @if (is_string($element))
                <li class="ssc-page-item disabled">
                    <span class="ssc-page-link ssc-page-dots">{{ $element }}</span>
                </li>
            @endif

            {{-- Array Of Links --}}
            @if (is_array($element))
                @foreach ($element as $page => $url)
                    @if ($page == $paginator->currentPage())
                        <li class="ssc-page-item active">
                            <span class="ssc-page-link">{{ $page }}</span>
                        </li>
                    @else
                        <li class="ssc-page-item">
                            <a class="ssc-page-link" href="{{ $url }}">{{ $page }}</a>
                        </li>
                    @endif
                @endforeach
            @endif
        @endforeach

        {{-- Next Page Link --}}
        @if ($paginator->hasMorePages())
            <li class="ssc-page-item">
                <a class="ssc-page-link" href="{{ $paginator->nextPageUrl() }}" rel="next" aria-label="Next">
                    <i class="bi bi-chevron-right"></i>
                </a>
            </li>
        @else
            <li class="ssc-page-item disabled">
                <span class="ssc-page-link"><i class="bi bi-chevron-right"></i></span>
            </li>
        @endif
    </ul>
</nav>

<style>
.ssc-pagination-nav {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 16px 20px;
    border-top: 1px solid #e2e8f0;
    flex-wrap: wrap;
    gap: 12px;
}

.ssc-pagination-info {
    font-size: 0.8rem;
    color: #64748b;
    font-weight: 500;
}

.ssc-pagination-info strong {
    color: #1e293b;
    font-weight: 700;
}

.ssc-pagination {
    display: flex;
    align-items: center;
    gap: 4px;
    list-style: none;
    margin: 0;
    padding: 0;
}

.ssc-page-item {}

.ssc-page-link {
    display: flex;
    align-items: center;
    justify-content: center;
    width: 36px;
    height: 36px;
    border-radius: 8px;
    font-size: 0.82rem;
    font-weight: 600;
    color: #475569;
    background: #fff;
    border: 1px solid #e2e8f0;
    text-decoration: none;
    transition: all 0.18s ease;
    cursor: pointer;
    user-select: none;
    line-height: 1;
}

a.ssc-page-link:hover {
    background: #f1f5f9;
    border-color: #94a3b8;
    color: #1e293b;
    transform: translateY(-1px);
    box-shadow: 0 2px 6px rgba(0,0,0,0.08);
}

.ssc-page-item.active .ssc-page-link {
    background: linear-gradient(135deg, #1e3a5f 0%, #2563eb 100%);
    border-color: transparent;
    color: #fff;
    box-shadow: 0 2px 8px rgba(37, 99, 235, 0.35);
    cursor: default;
}

.ssc-page-item.disabled .ssc-page-link {
    background: #f8fafc;
    border-color: #e2e8f0;
    color: #cbd5e1;
    cursor: not-allowed;
    pointer-events: none;
}

.ssc-page-dots {
    width: auto;
    padding: 0 4px;
    background: transparent;
    border-color: transparent;
    color: #94a3b8;
    font-weight: 700;
    letter-spacing: 1px;
}

@media (max-width: 576px) {
    .ssc-pagination-nav {
        justify-content: center;
    }
    .ssc-pagination-info {
        width: 100%;
        text-align: center;
    }
}
</style>
@endif
