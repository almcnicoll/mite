@extends('layouts.app')

@section('title', 'Stats')

@section('head')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4/dist/chart.umd.min.js"></script>
<style>
    .tab-bar {
        display: flex;
        gap: 0.5rem;
        margin-bottom: 1.25rem;
    }

    .tab-btn {
        padding: 0.6rem 1.2rem;
        border: 2px solid #2c3e50;
        border-radius: 8px;
        background: white;
        color: #2c3e50;
        font-size: 1rem;
        font-weight: 600;
        cursor: pointer;
        transition: background 0.15s, color 0.15s;
        min-height: 48px;
    }

    .tab-btn.active {
        background: #2c3e50;
        color: white;
    }

    .tab-panel {
        display: none;
    }

    .tab-panel.active {
        display: block;
    }

    .chart-wrap {
        position: relative;
        width: 100%;
        /* Enough height to comfortably show bars without needing to scroll */
        height: clamp(280px, 50vw, 480px);
    }

    .chart-empty {
        text-align: center;
        padding: 2rem;
        color: #777;
        font-style: italic;
    }
</style>
@endsection

@section('content')
<h1>Stats</h1>

{{-- Tab navigation --}}
<div class="tab-bar" role="tablist">
    <button class="tab-btn active"
            role="tab"
            aria-selected="true"
            aria-controls="panel-alltime"
            id="tab-alltime"
            onclick="switchTab('alltime')">
        All Time
    </button>
    <button class="tab-btn"
            role="tab"
            aria-selected="false"
            aria-controls="panel-next"
            id="tab-next"
            onclick="switchTab('next')">
        Next Donation
    </button>
</div>

{{-- All Time panel --}}
<div class="tab-panel active card" id="panel-alltime" role="tabpanel" aria-labelledby="tab-alltime">
    <h2>All Time — total raised per cause</h2>
    @if(count($allTimeData) && collect($allTimeData)->sum('amount') > 0)
        <div class="chart-wrap">
            <canvas id="chartAllTime" aria-label="All time totals bar chart" role="img"></canvas>
        </div>
    @else
        <p class="chart-empty">No data yet — picks will appear here once causes are assigned.</p>
    @endif
</div>

{{-- Next Donation panel --}}
<div class="tab-panel card" id="panel-next" role="tabpanel" aria-labelledby="tab-next">
    <h2>Next Donation — undonated balance per cause</h2>
    @if(count($nextDonationData) && collect($nextDonationData)->sum('amount') > 0)
        <div class="chart-wrap">
            <canvas id="chartNext" aria-label="Next donation balances bar chart" role="img"></canvas>
        </div>
    @else
        <p class="chart-empty">Nothing pending — either no picks exist yet, or all raised funds have already been donated.</p>
    @endif
</div>

<script>
// ── Data from PHP ──────────────────────────────────────────────────────────────
const allTimeData      = @json($allTimeData);
const nextDonationData = @json($nextDonationData);

// ── Tab switching ──────────────────────────────────────────────────────────────
function switchTab(name) {
    document.querySelectorAll('.tab-btn').forEach(btn => {
        btn.classList.remove('active');
        btn.setAttribute('aria-selected', 'false');
    });
    document.querySelectorAll('.tab-panel').forEach(panel => {
        panel.classList.remove('active');
    });

    document.getElementById('tab-' + name).classList.add('active');
    document.getElementById('tab-' + name).setAttribute('aria-selected', 'true');
    document.getElementById('panel-' + name).classList.add('active');
}

// ── Chart factory ──────────────────────────────────────────────────────────────
function buildChart(canvasId, rows) {
    if (!document.getElementById(canvasId)) return;

    const labels     = rows.map(r => r.label);
    const amounts    = rows.map(r => r.amount);
    const colours    = rows.map(r => r.colour || '#2980b9');

    // Slightly transparent fill, full-opacity border for a clean look.
    const bgColours  = colours.map(c => c + 'cc');   // ~80 % opacity
    const bdColours  = colours;

    new Chart(document.getElementById(canvasId), {
        type: 'bar',
        data: {
            labels,
            datasets: [{
                label: 'Amount (£)',
                data: amounts,
                backgroundColor: bgColours,
                borderColor: bdColours,
                borderWidth: 2,
                borderRadius: 6,
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { display: false },
                tooltip: {
                    callbacks: {
                        label: ctx => ' £' + ctx.parsed.y.toFixed(2)
                    }
                }
            },
            scales: {
                x: {
                    ticks: {
                        font: { size: 14 },
                        maxRotation: 30,
                    },
                    grid: { display: false }
                },
                y: {
                    beginAtZero: true,
                    ticks: {
                        font: { size: 14 },
                        callback: v => '£' + v.toFixed(2)
                    }
                }
            }
        }
    });
}

// Build both charts on load.  The hidden panel's canvas still renders
// correctly because Chart.js measures the wrapping div, not visibility.
document.addEventListener('DOMContentLoaded', () => {
    buildChart('chartAllTime', allTimeData);
    buildChart('chartNext',    nextDonationData);
});
</script>
@endsection
