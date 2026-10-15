@extends('layouts.app')

@section('title', 'Stats')

@section('head')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4/dist/chart.umd.min.js"></script>
<style>
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

    .ready-banner {
        display: block;
        width: 100%;
        margin-bottom: 1.25rem;
        padding: 0.8rem 1.2rem;
        border: 2px solid #27ae60;
        border-radius: 8px;
        background: #eafaf1;
        color: #1e8449;
        font-size: 1.05rem;
        font-weight: 600;
        text-align: left;
        cursor: pointer;
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

@if (count($readyCauses))
<button type="button" class="ready-banner" onclick="tabs.select('next')">
    @if (count($readyCauses) === 1)
        {{ $readyCauses[0]['label'] }} is ready for a new donation
    @else
        {{ count($readyCauses) }} causes are ready for donations
    @endif
</button>
@endif

{{-- Tab navigation (shared selector; last choice is remembered per browser) --}}
@include('partials.tab-bar', ['tabs' => ['alltime' => 'All Time', 'next' => 'Next Donation']])

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

    @if (count($readyCauses))
    <h2 style="margin-top:1.5rem;">Ready for donation (£{{ number_format($threshold, 2) }} or more)</h2>
    <table>
        <thead>
            <tr><th>Cause</th><th>Next donation</th><th></th></tr>
        </thead>
        <tbody>
            @foreach ($readyCauses as $row)
            <tr>
                <td>{{ $row['label'] }}</td>
                <td>£{{ number_format($row['amount'], 2) }}</td>
                <td>
                    <a href="{{ route('donations.create', ['cause_id' => $row['id']]) }}"
                       class="btn btn-success">Donate now</a>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
    @endif
</div>

<script>
// ── Data from PHP ──────────────────────────────────────────────────────────────
const allTimeData      = @json($allTimeData);
const nextDonationData = @json($nextDonationData);

// ── Tab switching (remembered per browser via miteTabs in the layout) ──────────
const tabs = miteTabs('stats', 'alltime', name => {
    document.querySelectorAll('.tab-panel').forEach(panel => {
        panel.classList.toggle('active', panel.id === 'panel-' + name);
    });
});

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
