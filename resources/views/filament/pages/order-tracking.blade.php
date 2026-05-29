<x-filament-panels::page>
    <style>
        .tracking-timeline {
            position: relative;
            padding: 2rem 0;
        }

        .timeline-line {
            position: absolute;
            left: 30px;
            top: 0;
            bottom: 0;
            width: 3px;
            background: linear-gradient(to bottom, #10b981 0%, #10b981 50%, #d1d5db 50%, #d1d5db 100%);
            border-radius: 2px;
        }

        .timeline-event {
            position: relative;
            display: flex;
            align-items: center;
            margin-bottom: 2rem;
            padding-left: 4rem;
            opacity: 1;
            transition: all 0.3s ease;
        }

        .timeline-event.pending {
            opacity: 0.6;
        }

        .timeline-icon {
            position: absolute;
            left: -4rem;
            width: 60px;
            height: 60px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
            color: white;
            z-index: 10;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
            border: 4px solid white;
        }

        .timeline-icon.completed {
            background: linear-gradient(135deg, #10b981, #059669);
            animation: pulse-success 2s infinite;
        }

        .timeline-icon.pending {
            background: linear-gradient(135deg, #f59e0b, #d97706);
            animation: pulse-warning 2s infinite;
        }

        .timeline-icon.danger {
            background: linear-gradient(135deg, #ef4444, #dc2626);
        }

        .timeline-content {
            background: white;
            border-radius: 12px;
            padding: 1.5rem;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
            border-left: 4px solid #10b981;
            min-width: 300px;
            flex: 1;
            transition: transform 0.2s ease, box-shadow 0.2s ease;
        }

        .timeline-content:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
        }

        .timeline-content.pending {
            border-left-color: #f59e0b;
            background: #fffbeb;
        }

        .timeline-title {
            font-size: 1.25rem;
            font-weight: 600;
            color: #1f2937;
            margin-bottom: 0.5rem;
        }

        .timeline-description {
            color: #6b7280;
            margin-bottom: 1rem;
            line-height: 1.5;
        }

        .timeline-timestamp {
            font-size: 0.875rem;
            color: #9ca3af;
            font-weight: 500;
        }

        .timeline-details {
            margin-top: 1rem;
            padding-top: 1rem;
            border-top: 1px solid #e5e7eb;
        }

        .detail-item {
            display: flex;
            justify-content: space-between;
            margin-bottom: 0.5rem;
            font-size: 0.875rem;
        }

        .detail-label {
            font-weight: 500;
            color: #374151;
        }

        .detail-value {
            color: #6b7280;
        }

        .order-summary {
            background: linear-gradient(135deg, #3b82f6, #1d4ed8);
            color: white;
            border-radius: 12px;
            padding: 2rem;
            margin-bottom: 2rem;
            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1);
        }

        .order-summary h2 {
            font-size: 1.5rem;
            font-weight: 700;
            margin-bottom: 1rem;
        }

        .order-details-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1rem;
            margin-top: 1rem;
        }

        .order-detail-item {
            background: rgba(255, 255, 255, 0.1);
            padding: 1rem;
            border-radius: 8px;
            backdrop-filter: blur(10px);
        }

        .order-detail-label {
            font-size: 0.875rem;
            opacity: 0.8;
            margin-bottom: 0.25rem;
        }

        .order-detail-value {
            font-weight: 600;
            font-size: 1.125rem;
        }

        @keyframes pulse-success {
            0%, 100% { box-shadow: 0 0 0 0 rgba(16, 185, 129, 0.7); }
            50% { box-shadow: 0 0 0 10px rgba(16, 185, 129, 0); }
        }

        @keyframes pulse-warning {
            0%, 100% { box-shadow: 0 0 0 0 rgba(245, 158, 11, 0.7); }
            50% { box-shadow: 0 0 0 10px rgba(245, 158, 11, 0); }
        }

        .no-order-selected {
            text-align: center;
            padding: 4rem 2rem;
            color: #6b7280;
        }

        .no-order-icon {
            font-size: 4rem;
            margin-bottom: 1rem;
            opacity: 0.3;
        }

        @media (max-width: 768px) {
            .timeline-event {
                padding-left: 3rem;
            }
            
            .timeline-icon {
                left: -3rem;
                width: 40px;
                height: 40px;
                font-size: 1rem;
            }
            
            .timeline-line {
                left: 20px;
            }
        }
    </style>

    <div class="space-y-6">
        {{ $this->form }}

        @if($orderData)
            <!-- Order Summary -->
            <div class="order-summary">
                <h2>Order #{{ $orderData->order_number }}</h2>
                <div class="order-details-grid">
                    <div class="order-detail-item">
                        <div class="order-detail-label">Customer</div>
                        <div class="order-detail-value">{{ $orderData->customer?->full_name ?? 'Unknown' }}</div>
                    </div>
                    <div class="order-detail-item">
                        <div class="order-detail-label">Total Amount</div>
                        <div class="order-detail-value">${{ number_format($orderData->total_amount, 2) }}</div>
                    </div>
                    <div class="order-detail-item">
                        <div class="order-detail-label">Current Status</div>
                        <div class="order-detail-value">{{ $orderData->currentStatus?->name ?? 'Unknown' }}</div>
                    </div>
                    <div class="order-detail-item">
                        <div class="order-detail-label">Order Date</div>
                        <div class="order-detail-value">{{ $orderData->created_at->format('M j, Y g:i A') }}</div>
                    </div>
                </div>
            </div>

            <!-- Timeline -->
            @if(count($trackingEvents) > 0)
                <div class="tracking-timeline">
                    <div class="timeline-line"></div>
                    
                    @foreach($trackingEvents as $event)
                        <div class="timeline-event {{ $event['status'] }}">
                            <div class="timeline-icon {{ $event['status'] }}">
                                @if($event['status'] === 'completed')
                                    <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                                    </svg>
                                @elseif($event['status'] === 'pending')
                                    <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z" clip-rule="evenodd"/>
                                    </svg>
                                @endif
                            </div>
                            
                            <div class="timeline-content {{ $event['status'] }}">
                                <div class="timeline-title">{{ $event['title'] }}</div>
                                <div class="timeline-description">{{ $event['description'] }}</div>
                                
                                @if($event['timestamp'])
                                    <div class="timeline-timestamp">
                                        {{ \Carbon\Carbon::parse($event['timestamp'])->format('M j, Y g:i A') }}
                                        <span class="text-xs opacity-75">
                                            ({{ \Carbon\Carbon::parse($event['timestamp'])->diffForHumans() }})
                                        </span>
                                    </div>
                                @else
                                    <div class="timeline-timestamp text-yellow-600">Pending</div>
                                @endif
                                
                                @if(!empty($event['details']))
                                    <div class="timeline-details">
                                        @foreach($event['details'] as $label => $value)
                                            <div class="detail-item">
                                                <span class="detail-label">{{ $label }}:</span>
                                                <span class="detail-value">{{ $value }}</span>
                                            </div>
                                        @endforeach
                                    </div>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="text-center py-8 text-gray-500">
                    <div class="text-4xl mb-4">📦</div>
                    <p>No tracking events found for this order.</p>
                </div>
            @endif
        @else
            <div class="no-order-selected">
                <div class="no-order-icon">🚚</div>
                <h3 class="text-lg font-semibold mb-2">Select an Order to Track</h3>
                <p>Choose an order from the dropdown above to view its tracking timeline.</p>
            </div>
        @endif
    </div>
</x-filament-panels::page>