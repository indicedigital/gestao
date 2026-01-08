@extends('layouts.mobile')

@section('title', 'Dashboard')

@push('styles')
<style>
    .month-selector {
        display: flex;
        align-items: center;
        justify-content: space-between;
        margin-bottom: 20px;
    }
    
    .month-nav {
        width: 36px;
        height: 36px;
        border-radius: 10px;
        background: var(--bg-color);
        display: flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        color: var(--text-muted);
    }
    
    .month-current {
        font-size: 16px;
        font-weight: 600;
        color: var(--dark-color);
    }
</style>
@endpush

@section('content')
<!-- Month Selector -->
<div class="month-selector">
    <div class="month-nav" onclick="changeMonth(-1)">
        <i class="fas fa-chevron-left"></i>
    </div>
    <div class="month-current">
        {{ $selectedMonth->locale('pt_BR')->translatedFormat('F \d\e Y') }}
    </div>
    <div class="month-nav" onclick="changeMonth(1)">
        <i class="fas fa-chevron-right"></i>
    </div>
</div>

<!-- Profit Card -->
<div class="balance-card">
    <div class="balance-label">Lucro do mês</div>
    <div class="balance-value" id="balanceValue">
        R$ {{ number_format($profitRealized, 2, ',', '.') }}
    </div>
    <div class="balance-toggle" onclick="toggleBalance()">
        <i class="fas fa-eye" id="balanceIcon"></i>
        <span>Ocultar lucro</span>
    </div>
</div>

<!-- Income and Expense Summary - Improved Cards -->
<div class="row g-3 mb-3">
    <div class="col-6">
        <div class="mobile-card" style="padding: 12px; background: linear-gradient(135deg, rgba(45, 206, 137, 0.1) 0%, rgba(45, 206, 137, 0.05) 100%); border-left: 3px solid var(--success-color);">
            <div class="d-flex flex-column">
                <div class="d-flex align-items-center gap-2 mb-2">
                    <div class="summary-icon income" style="width: 32px; height: 32px; font-size: 14px;">
                        <i class="fas fa-arrow-up"></i>
                    </div>
                    <div class="summary-label" style="font-size: 10px; opacity: 0.8; font-weight: 500;">Receitas</div>
                </div>
                <div class="summary-value positive" style="font-size: 16px; font-weight: 700; line-height: 1.2;">
                    <span style="font-size: 12px; font-weight: 500;">R$</span> {{ number_format($revenueRealized, 2, ',', '.') }}
                </div>
            </div>
        </div>
    </div>
    <div class="col-6">
        <div class="mobile-card" style="padding: 12px; background: linear-gradient(135deg, rgba(245, 54, 92, 0.1) 0%, rgba(245, 54, 92, 0.05) 100%); border-left: 3px solid var(--danger-color);">
            <div class="d-flex flex-column">
                <div class="d-flex align-items-center gap-2 mb-2">
                    <div class="summary-icon expense" style="width: 32px; height: 32px; font-size: 14px;">
                        <i class="fas fa-arrow-down"></i>
                    </div>
                    <div class="summary-label" style="font-size: 10px; opacity: 0.8; font-weight: 500;">Despesas</div>
                </div>
                <div class="summary-value negative" style="font-size: 16px; font-weight: 700; line-height: 1.2;">
                    <span style="font-size: 12px; font-weight: 500;">R$</span> {{ number_format($expensesRealized, 2, ',', '.') }}
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Financial History Chart (Faturamento x Despesas x Lucro) -->
<div class="mobile-card">
    <h6 style="font-size: 14px; font-weight: 600; margin-bottom: 16px; color: var(--dark-color);">
        Faturamento x Despesas x Lucro
    </h6>
    <div style="font-size: 11px; color: var(--text-muted); margin-bottom: 12px;">
        Últimos 6 meses + Projeções
    </div>
    <div class="chart-container" style="height: 250px;">
        <canvas id="financialChart"></canvas>
    </div>
</div>

<!-- Expenses by Category Chart -->
<div class="mobile-card">
    <h6 style="font-size: 14px; font-weight: 600; margin-bottom: 16px; color: var(--dark-color);">
        Despesas por categoria
    </h6>
    <div class="chart-container">
        <canvas id="expensesChart"></canvas>
    </div>
    <div class="chart-legend" id="chartLegend">
        <!-- Legend will be populated by JavaScript -->
    </div>
</div>

<!-- Bills Section -->
<div class="mobile-card">
    <h6 style="font-size: 14px; font-weight: 600; margin-bottom: 16px; color: var(--dark-color);">
        Contas a Pagar
    </h6>
    <div class="bills-tabs">
        <button class="bill-tab active" onclick="showBills('open')">
            Faturas abertas
        </button>
        <button class="bill-tab" onclick="showBills('closed')">
            Faturas fechadas
        </button>
    </div>
    
    <div id="billsOpen">
        @forelse($upcomingPayables7->take(3) as $payable)
        <div class="bill-item">
            <div class="bill-info">
                <div class="bill-name">{{ $payable->description ?? 'Sem descrição' }}</div>
                <div class="bill-due">
                    @php
                        $daysDiff = now()->diffInDays($payable->due_date, false);
                        if ($daysDiff < 0) {
                            $text = 'Venceu há ' . abs($daysDiff) . ' ' . (abs($daysDiff) == 1 ? 'dia' : 'dias');
                        } elseif ($daysDiff == 0) {
                            $text = 'Vence hoje';
                        } elseif ($daysDiff == 1) {
                            $text = 'Vence amanhã';
                        } else {
                            $text = 'Vence em ' . $daysDiff . ' dias';
                        }
                    @endphp
                    {{ $text }}
                </div>
            </div>
            <div style="display: flex; align-items: center;">
                <div class="bill-value">
                    R$ {{ number_format($payable->value, 2, ',', '.') }}
                </div>
                <button class="bill-button" onclick="payBill({{ $payable->id }})">
                    Pagar
                </button>
            </div>
        </div>
        @empty
        <div style="text-align: center; padding: 20px; color: var(--text-muted);">
            <i class="fas fa-check-circle" style="font-size: 32px; margin-bottom: 8px; opacity: 0.5;"></i>
            <p>Nenhuma conta a pagar</p>
        </div>
        @endforelse
    </div>
    
    <div id="billsClosed" style="display: none;">
        @forelse($upcomingPayables7->where('status', 'paid')->take(3) as $payable)
        <div class="bill-item">
            <div class="bill-info">
                <div class="bill-name">{{ $payable->description ?? 'Sem descrição' }}</div>
                <div class="bill-due">
                    Pago em {{ $payable->paid_date ? $payable->paid_date->format('d/m/Y') : '-' }}
                </div>
            </div>
            <div class="bill-value" style="color: var(--success-color);">
                R$ {{ number_format($payable->value, 2, ',', '.') }}
            </div>
        </div>
        @empty
        <div style="text-align: center; padding: 20px; color: var(--text-muted);">
            <i class="fas fa-check-circle" style="font-size: 32px; margin-bottom: 8px; opacity: 0.5;"></i>
            <p>Nenhuma conta paga</p>
        </div>
        @endforelse
    </div>
</div>

<!-- Receivables Section -->
<div class="mobile-card">
    <h6 style="font-size: 14px; font-weight: 600; margin-bottom: 16px; color: var(--dark-color);">
        Contas a Receber
    </h6>
    <div class="bills-tabs">
        <button class="bill-tab active" onclick="showReceivables('open')">
            Faturas abertas
        </button>
        <button class="bill-tab" onclick="showReceivables('closed')">
            Faturas fechadas
        </button>
    </div>
    
    <div id="receivablesOpen">
        @forelse($upcomingReceivables7->take(3) as $receivable)
        <div class="bill-item">
            <div class="bill-info">
                <div class="bill-name">{{ $receivable->client->name ?? 'Cliente' }} - {{ $receivable->description ?? 'Sem descrição' }}</div>
                <div class="bill-due">
                    @php
                        $daysDiff = now()->diffInDays($receivable->due_date, false);
                        if ($daysDiff < 0) {
                            $text = 'Venceu há ' . abs($daysDiff) . ' ' . (abs($daysDiff) == 1 ? 'dia' : 'dias');
                        } elseif ($daysDiff == 0) {
                            $text = 'Vence hoje';
                        } elseif ($daysDiff == 1) {
                            $text = 'Vence amanhã';
                        } else {
                            $text = 'Vence em ' . $daysDiff . ' dias';
                        }
                    @endphp
                    {{ $text }}
                </div>
            </div>
            <div style="display: flex; align-items: center;">
                <div class="bill-value" style="color: var(--success-color);">
                    R$ {{ number_format($receivable->value, 2, ',', '.') }}
                </div>
                <button class="bill-button" style="background: var(--success-color);" onclick="receiveBill({{ $receivable->id }})">
                    Receber
                </button>
            </div>
        </div>
        @empty
        <div style="text-align: center; padding: 20px; color: var(--text-muted);">
            <i class="fas fa-check-circle" style="font-size: 32px; margin-bottom: 8px; opacity: 0.5;"></i>
            <p>Nenhuma conta a receber</p>
        </div>
        @endforelse
    </div>
    
    <div id="receivablesClosed" style="display: none;">
        @forelse($upcomingReceivables7->where('status', 'paid')->take(3) as $receivable)
        <div class="bill-item">
            <div class="bill-info">
                <div class="bill-name">{{ $receivable->client->name ?? 'Cliente' }} - {{ $receivable->description ?? 'Sem descrição' }}</div>
                <div class="bill-due">
                    Recebido em {{ $receivable->paid_date ? $receivable->paid_date->format('d/m/Y') : '-' }}
                </div>
            </div>
            <div class="bill-value" style="color: var(--success-color);">
                R$ {{ number_format($receivable->value, 2, ',', '.') }}
            </div>
        </div>
        @empty
        <div style="text-align: center; padding: 20px; color: var(--text-muted);">
            <i class="fas fa-check-circle" style="font-size: 32px; margin-bottom: 8px; opacity: 0.5;"></i>
            <p>Nenhuma conta recebida</p>
        </div>
        @endforelse
    </div>
</div>

@push('scripts')
<script>
    // Chart Data
    const expensesData = @json($expensesByCategoryChart);
    const financialHistory = @json($financialHistory);
    
    // Prepare expenses chart data
    const labels = expensesData.map(item => item.label);
    const values = expensesData.map(item => item.value);
    const colors = expensesData.map(item => item.color || '#667eea');
    
    // Create donut chart
    const ctx = document.getElementById('expensesChart').getContext('2d');
    const expensesChart = new Chart(ctx, {
        type: 'doughnut',
        data: {
            labels: labels,
            datasets: [{
                data: values,
                backgroundColor: colors,
                borderWidth: 0
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: false
                }
            },
            cutout: '70%'
        }
    });
    
    // Populate expenses legend
    const legendContainer = document.getElementById('chartLegend');
    expensesData.forEach((item, index) => {
        const legendItem = document.createElement('div');
        legendItem.className = 'chart-legend-item';
        legendItem.innerHTML = `
            <div class="chart-legend-color" style="background: ${item.color}"></div>
            <div class="chart-legend-label">${item.label}</div>
            <div class="chart-legend-value">R$ ${parseFloat(item.value).toLocaleString('pt-BR', {minimumFractionDigits: 2, maximumFractionDigits: 2})}</div>
        `;
        legendContainer.appendChild(legendItem);
    });
    
    // Financial History Chart (Line Chart)
    const financialCtx = document.getElementById('financialChart').getContext('2d');
    const financialChart = new Chart(financialCtx, {
        type: 'line',
        data: {
            labels: financialHistory.map(item => item.month),
            datasets: [
                {
                    label: 'Faturamento',
                    data: financialHistory.map(item => item.revenue),
                    borderColor: '#2dce89',
                    backgroundColor: 'rgba(45, 206, 137, 0.1)',
                    borderWidth: 2,
                    fill: true,
                    tension: 0.4
                },
                {
                    label: 'Despesas',
                    data: financialHistory.map(item => item.expenses),
                    borderColor: '#f5365c',
                    backgroundColor: 'rgba(245, 54, 92, 0.1)',
                    borderWidth: 2,
                    fill: true,
                    tension: 0.4
                },
                {
                    label: 'Lucro',
                    data: financialHistory.map(item => item.profit),
                    borderColor: '#667eea',
                    backgroundColor: 'rgba(102, 126, 234, 0.1)',
                    borderWidth: 2,
                    fill: true,
                    tension: 0.4
                }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: true,
                    position: 'bottom',
                    labels: {
                        usePointStyle: true,
                        padding: 12,
                        font: {
                            size: 11
                        }
                    }
                },
                tooltip: {
                    mode: 'index',
                    intersect: false,
                    callbacks: {
                        label: function(context) {
                            return context.dataset.label + ': R$ ' + 
                                parseFloat(context.parsed.y).toLocaleString('pt-BR', {
                                    minimumFractionDigits: 2, 
                                    maximumFractionDigits: 2
                                });
                        }
                    }
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        callback: function(value) {
                            return 'R$ ' + parseFloat(value).toLocaleString('pt-BR', {
                                minimumFractionDigits: 0,
                                maximumFractionDigits: 0
                            });
                        },
                        font: {
                            size: 10
                        }
                    },
                    grid: {
                        color: 'rgba(0, 0, 0, 0.05)'
                    }
                },
                x: {
                    ticks: {
                        font: {
                            size: 10
                        }
                    },
                    grid: {
                        display: false
                    }
                }
            }
        }
    });
    
    // Toggle balance visibility
    let balanceVisible = true;
    function toggleBalance() {
        balanceVisible = !balanceVisible;
        const balanceValue = document.getElementById('balanceValue');
        const balanceIcon = document.getElementById('balanceIcon');
        
        if (balanceVisible) {
            balanceValue.textContent = 'R$ {{ number_format($profitRealized, 2, ',', '.') }}';
            balanceIcon.className = 'fas fa-eye';
            balanceIcon.parentElement.querySelector('span').textContent = 'Ocultar lucro';
        } else {
            balanceValue.textContent = '••••••';
            balanceIcon.className = 'fas fa-eye-slash';
            balanceIcon.parentElement.querySelector('span').textContent = 'Mostrar lucro';
        }
    }
    
    // Show bills tabs
    function showBills(type) {
        const openTab = document.querySelector('.bill-tab:first-child');
        const closedTab = document.querySelector('.bill-tab:last-child');
        const openBills = document.getElementById('billsOpen');
        const closedBills = document.getElementById('billsClosed');
        
        if (type === 'open') {
            openTab.classList.add('active');
            closedTab.classList.remove('active');
            openBills.style.display = 'block';
            closedBills.style.display = 'none';
        } else {
            openTab.classList.remove('active');
            closedTab.classList.add('active');
            openBills.style.display = 'none';
            closedBills.style.display = 'block';
        }
    }
    
    // Change month
    function changeMonth(direction) {
        const currentMonth = '{{ $monthFilter }}';
        const date = new Date(currentMonth + '-01');
        date.setMonth(date.getMonth() + direction);
        const newMonth = date.toISOString().slice(0, 7);
        window.location.href = '{{ route("company.dashboard") }}?month=' + newMonth;
    }
    
    // Pay bill
    function payBill(id) {
        if (confirm('Deseja marcar esta conta como paga?')) {
            window.location.href = '{{ route("company.payables.index") }}';
        }
    }
    
    // Show receivables tabs
    function showReceivables(type) {
        const receivablesCard = document.getElementById('receivablesOpen').closest('.mobile-card');
        const receivablesTabs = receivablesCard.querySelectorAll('.bill-tab');
        const openReceivables = document.getElementById('receivablesOpen');
        const closedReceivables = document.getElementById('receivablesClosed');
        
        if (receivablesTabs.length >= 2) {
            const openTabReceivables = receivablesTabs[0];
            const closedTabReceivables = receivablesTabs[1];
            
            if (type === 'open') {
                openTabReceivables.classList.add('active');
                closedTabReceivables.classList.remove('active');
                openReceivables.style.display = 'block';
                closedReceivables.style.display = 'none';
            } else {
                openTabReceivables.classList.remove('active');
                closedTabReceivables.classList.add('active');
                openReceivables.style.display = 'none';
                closedReceivables.style.display = 'block';
            }
        }
    }
    
    // Receive bill
    function receiveBill(id) {
        if (confirm('Deseja marcar esta conta como recebida?')) {
            window.location.href = '{{ route("company.receivables.index") }}';
        }
    }
</script>
@endpush

@endsection
