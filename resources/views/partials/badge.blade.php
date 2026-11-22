<span class="badge b-{{ $s }}">{{ \App\Support\NestStatus::LABELS[$s] ?? (\App\Models\Invoice::STATUSES[$s] ?? $s) }}</span>
