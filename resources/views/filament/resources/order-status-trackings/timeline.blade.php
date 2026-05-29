@php
/**
 * Variables provided by the Page:
 * - $order (App\Models\Orders)
 * - $events (array of ['key','label','description','timestamp','status'])
 */
@endphp

<div class="filament-page">
    <div class="filament-page-heading">
        <h2 style="font-size:1.25rem;font-weight:600;">Package Journey — {{ $order->order_number ?? 'N/A' }}</h2>
        <p style="color: #6b7280; margin-top: .25rem;">Track the lifecycle of this order and its status updates.</p>
    </div>

    <div style="margin-top:1.25rem;">
        <ul style="list-style: none; padding-left: 0; position: relative;">
            @foreach($events as $index => $event)
                @php
                    $isCompleted = $event['status'] === 'completed';
                    $nextExists = isset($events[$index + 1]);
                @endphp

                <li style="display:flex; gap:1rem; align-items:flex-start; margin-bottom:1.25rem;">
                    <div style="width:24px; display:flex; justify-content:center;">
                        @if($isCompleted)
                            <div style="width:18px; height:18px; border-radius:9999px; background:#10b981; display:flex; align-items:center; justify-content:center; color:white; font-size:12px;">✓</div>
                        @else
                            <div style="width:18px; height:18px; border-radius:9999px; background:#f59e0b; display:flex; align-items:center; justify-content:center; color:white; font-size:12px;">•</div>
                        @endif
                    </div>

                    <div style="flex:1;">
                        <div style="display:flex; justify-content:space-between; gap:1rem; align-items:start;">
                            <div>
                                <div style="font-weight:600;">{{ $event['label'] }}</div>
                                @if($event['description'])
                                    <div style="color:#6b7280; font-size:.95rem; margin-top:.15rem;">{{ $event['description'] }}</div>
                                @endif
                            </div>

                            <div style="color:#6b7280; font-size:.9rem; text-align:right; min-width:150px;">
                                @if($event['timestamp'])
                                    {{ \Carbon\Carbon::parse($event['timestamp'])->format('M j, Y g:i A') }}<br />
                                    <small style="color:#9ca3af;">{{ \Carbon\Carbon::parse($event['timestamp'])->diffForHumans() }}</small>
                                @else
                                    <span style="color:#f59e0b;">Pending</span>
                                @endif
                            </div>
                        </div>

                        @if($nextExists)
                            <div style="height:1px; background:#e5e7eb; margin-top:1rem; margin-left:12px;"></div>
                        @endif
                    </div>
                </li>
            @endforeach
        </ul>
    </div>
</div>
