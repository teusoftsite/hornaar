@if($paginator->hasPages())
<nav class="row">
  @if($paginator->onFirstPage())<span class="btn ghost small" style="opacity:.4">← Vorige</span>@else<a class="btn ghost small" href="{{ $paginator->previousPageUrl() }}">← Vorige</a>@endif
  <span class="small muted">Pagina {{ $paginator->currentPage() }} van {{ $paginator->lastPage() }}</span>
  @if($paginator->hasMorePages())<a class="btn ghost small" href="{{ $paginator->nextPageUrl() }}">Volgende →</a>@else<span class="btn ghost small" style="opacity:.4">Volgende →</span>@endif
</nav>
@endif
