<div class="bg-white dark:bg-slate-800 rounded-2xl shadow-sm border border-gray-100 dark:border-slate-700 p-6">
    <div class="flex items-center justify-between mb-6 flex-wrap gap-3">
        <div class="flex items-center gap-3">
            <div class="w-9 h-9 rounded-xl bg-gradient-to-br from-kbb-500 to-kbb-700 flex items-center justify-center shadow-sm">
                <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 12l3-3 3 3 4-4M8 21l4-4 4 4M3 4h18M4 4h16v12a1 1 0 01-1 1H5a1 1 0 01-1-1V4z"/></svg>
            </div>
            <div>
                <h2 class="text-lg font-semibold text-gray-900 dark:text-gray-100">Aktivitas Pengiriman</h2>
                <p class="text-xs text-gray-400 dark:text-gray-500">Jumlah pengiriman formulir per periode</p>
            </div>
        </div>
        <div class="flex items-center gap-2.5">
            @if($this->chartData['total'] > 0)
            <span class="text-xs bg-gradient-to-r from-kbb-50 to-kbb-100 dark:from-kbb-500/20 dark:to-kbb-500/10 text-kbb-700 dark:text-kbb-400 px-3 py-1.5 rounded-full font-semibold shadow-sm border border-kbb-200/50 dark:border-kbb-500/20">{{ number_format($this->chartData['total']) }} total</span>
            @endif
            <div class="relative">
                <select wire:model.live="range"
                    class="text-sm bg-gray-50 dark:bg-slate-700 border border-gray-200 dark:border-slate-600 text-gray-700 dark:text-gray-200 rounded-xl px-3 py-1.5 pr-8 focus:ring-2 focus:ring-kbb-500 focus:border-kbb-500 transition cursor-pointer appearance-none">
                    @foreach($ranges as $key => $label)
                    <option value="{{ $key }}">{{ $label }}</option>
                    @endforeach
                </select>
                <svg class="absolute right-2 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400 dark:text-gray-500 pointer-events-none" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
            </div>
        </div>
    </div>

    @if($this->chartData['max'] > 0)
    <div style="position:relative;height:280px;">
        <canvas id="activity-chart-canvas-{{ $this->getId() }}"></canvas>
    </div>
    @if(count($this->chartData['data']) > 0)
    <div class="flex items-center justify-between mt-4 pt-3 border-t border-gray-100 dark:border-slate-700">
        <div class="flex items-center gap-4 text-xs text-gray-400 dark:text-gray-500">
            <span>Tertinggi: <strong class="text-gray-700 dark:text-gray-300">{{ number_format($this->chartData['max']) }}</strong></span>
            <span>Rata-rata: <strong class="text-gray-700 dark:text-gray-300">{{ number_format(round($this->chartData['total'] / max(count($this->chartData['data']), 1), 1)) }}</strong></span>
        </div>
    </div>
    @endif
    @else
    <div class="text-center py-14">
        <div class="w-16 h-16 bg-gray-50 dark:bg-slate-700 rounded-2xl flex items-center justify-center mx-auto mb-4 border border-gray-100 dark:border-slate-600">
            <svg class="w-8 h-8 text-gray-300 dark:text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M7 12l3-3 3 3 4-4M8 21l4-4 4 4M3 4h18M4 4h16v12a1 1 0 01-1 1H5a1 1 0 01-1-1V4z"/></svg>
        </div>
        <p class="text-sm text-gray-400 dark:text-gray-500 mb-1">Belum ada data pengiriman</p>
        <p class="text-xs text-gray-300 dark:text-gray-600">Pada periode {{ $ranges[$range] ?? $range }}</p>
    </div>
    @endif
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.7/dist/chart.umd.min.js"></script>
<script>
(function() {
    const chartData = @json($this->chartData);
    const canvasId = 'activity-chart-canvas-{{ $this->getId() }}';
    const canvas = document.getElementById(canvasId);
    if (!canvas || chartData.max <= 0) return;

    const ctx = canvas.getContext('2d');
    const isDark = document.documentElement.classList.contains('dark');

    const gridColor = isDark ? 'rgba(148,163,184,0.1)' : 'rgba(0,0,0,0.05)';
    const textColor = isDark ? '#94a3b8' : '#6b7280';

    const gradient = ctx.createLinearGradient(0, 0, 0, canvas.parentElement.clientHeight || 280);
    gradient.addColorStop(0, 'rgba(58,107,181,0.9)');
    gradient.addColorStop(1, 'rgba(26,74,138,0.9)');

    const hoverGradient = ctx.createLinearGradient(0, 0, 0, canvas.parentElement.clientHeight || 280);
    hoverGradient.addColorStop(0, 'rgba(90,131,200,1)');
    hoverGradient.addColorStop(1, 'rgba(58,107,181,1)');

    if (window['chart_instance_' + canvasId]) {
        window['chart_instance_' + canvasId].destroy();
    }

    window['chart_instance_' + canvasId] = new Chart(ctx, {
        type: 'bar',
        data: {
            labels: chartData.labels,
            datasets: [{
                label: 'Pengiriman',
                data: chartData.data,
                backgroundColor: gradient,
                hoverBackgroundColor: hoverGradient,
                borderRadius: 6,
                borderSkipped: false,
                barPercentage: 0.7,
                categoryPercentage: 0.8,
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            interaction: {
                intersect: false,
                mode: 'index',
            },
            plugins: {
                legend: { display: false },
                tooltip: {
                    backgroundColor: isDark ? '#1e293b' : '#111827',
                    titleColor: '#f1f5f9',
                    bodyColor: '#e2e8f0',
                    borderColor: isDark ? '#334155' : '#374151',
                    borderWidth: 1,
                    cornerRadius: 10,
                    padding: 12,
                    titleFont: { size: 13, weight: '600' },
                    bodyFont: { size: 12 },
                    displayColors: false,
                    callbacks: {
                        title: function(items) {
                            return items[0].label;
                        },
                        label: function(item) {
                            return item.formattedValue + ' pengiriman';
                        }
                    }
                }
            },
            scales: {
                x: {
                    grid: { display: false },
                    ticks: {
                        color: textColor,
                        font: { size: 11 },
                        maxRotation: 45,
                        minRotation: 0,
                    },
                    border: { display: false },
                },
                y: {
                    beginAtZero: true,
                    grid: { color: gridColor },
                    ticks: {
                        color: textColor,
                        font: { size: 11 },
                        stepSize: 1,
                        padding: 8,
                    },
                    border: { display: false },
                }
            },
            animation: {
                duration: 800,
                easing: 'easeOutQuart',
            }
        }
    });
})();
</script>
