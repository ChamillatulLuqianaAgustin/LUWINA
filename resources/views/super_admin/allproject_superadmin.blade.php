@extends('layouts.super_admin.template_superadmin')
@section('title', 'All Project')

@section('header')
    @include('layouts.super_admin.header_superadmin')
@endsection

@section('content')

<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">

<div class="page">

  <div class="top-controls">
    <button class="btn-primary-custom">+ Add Project</button>

    <div class="controls-right">
      <label for="tanggal" style="margin-right:6px;">Tanggal:</label>
      <input type="date" id="tanggal" name="tanggal" value="{{ date('Y-m-d') }}" style="padding:6px 8px; border-radius:6px; border:1px solid #ccc;">
      <button class="btn-primary-custom">📥 Download All</button>
    </div>
  </div>

  <div class="charts-row">
    <div class="card left">
      <div class="card-title">Distribusi Total Project {{ date('Y') }}</div>
      <div class="chart-wrap">
        <canvas id="chartTotalProject"></canvas>
      </div>
    </div>

    <div class="right-column">
      <div class="card">
        <div class="card-title">Distribusi Total Project Hari Ini</div>
        <div class="chart-wrap">
          <canvas id="chartToday"></canvas>
        </div>
      </div>

      <div class="card">
        <div class="card-title">Distribusi Total All Project</div>
        <div class="chart-wrap">
          <canvas id="chartPie"></canvas>
        </div>
      </div>
    </div>
  </div>

  <div class="table-card">
    <table>
      <thead>
        <tr>
          <th>No</th>
          <th>NAMA PROJECT</th>
          <th>DESKRIPSI PROJECT</th>
          <th>QE</th>
          <th>TANGGAL UPLOAD</th>
          <th>TANGGAL PENGERJAAN</th>
        </tr>
      </thead>
      <tbody>
        @foreach($projects as $index => $project)
        <tr>
          <td>{{ $index + 1 }}</td>
          <td>{{ $project->nama_project ?? '-' }}</td>
          <td>{{ $project->deskripsi_project ?? '-' }}</td>
          <td>{{ $project->qe ?? '-' }}</td>
          <td>
            @if(isset($project->tanggal_upload) && is_object($project->tanggal_upload) && method_exists($project->tanggal_upload, 'toDateTime'))
              {{ $project->tanggal_upload ?? '-' }}
            @elseif(isset($project->tanggal_upload))
              {{ $project->tanggal_upload }}
            @else
              -
            @endif
          </td>
          <td>{{ $project->tanggal_pengerjaan ?? '-' }}</td>
        </tr>
        @endforeach
      </tbody>
      <tfoot>
        <tr>
          <td colspan="5" style="text-align:right">TOTAL ALL PROJECT</td>
          <td>{{ $totalProject }}</td>
        </tr>
        <tr>
          <td colspan="5" style="text-align:right">TOTAL REVENUE</td>
          <td>{{ number_format($totalRevenue, 2, ',', '.') }}</td>
        </tr>
      </tfoot>
    </table>
  </div>

</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
  const blue = '#133995';
  const lightBlue = '#4A6AC0';
  const red = '#ff4d4d';

  new Chart(document.getElementById('chartTotalProject'), {
    type: 'bar',
    data: {
      labels: ['Jan','Feb','Mar','Apr','Mei','Jun','Jul','Agu','Sep','Okt','Nov','Des'],
      datasets: [{
        data: @json($chartTotalProjectData),
        backgroundColor: blue
      }]
    },
    options: {
      maintainAspectRatio: false,
      responsive: true,
      plugins: { legend: { display: false } },
      scales: { y: { beginAtZero: true }, x: { title: { display: true, text: 'Bulan' } } }
    }
  });

  new Chart(document.getElementById('chartToday'), {
    type: 'bar',
    data: {
      labels: Object.keys(@json($chartTodayData)),
      datasets: [{
        data: Object.values(@json($chartTodayData)),
        backgroundColor: blue
      }]
    },
    options: {
      indexAxis: 'y',
      maintainAspectRatio: false,
      responsive: true,
      plugins: { legend: { display: false } },
      scales: { x: { beginAtZero: true } }
    }
  });

  new Chart(document.getElementById('chartPie'), {
    type: 'doughnut',
    data: {
      labels: Object.keys(@json($chartPieData)),
      datasets: [{
        data: Object.values(@json($chartPieData)),
        backgroundColor: [blue, lightBlue, red]
      }]
    },
    options: {
      maintainAspectRatio: false,
      responsive: true,
      plugins: { legend: { position: 'bottom' } }
    }
  });
</script>

<style>
  /* styling sama seperti sebelumnya */
  :root{
    --blue: #133995;
    --bg: white;
    --card-border: #dcdcdc;
  }
  body { font-family: 'Poppins', sans-serif; background: var(--bg); }

  .page {
    max-width: 1200px;
    margin: 0 20px;
    padding: 22px;
  }

  .top-controls {
    display:flex;
    justify-content:space-between;
    align-items:center;
    gap:12px;
    margin-bottom:18px;
  }
  .top-controls .controls-right { display:flex; align-items:center; gap:8px; }

  .btn-primary-custom{
    background: var(--blue);
    color:#fff;
    border: none;
    padding:8px 16px;
    border-radius:8px;
    font-weight:600;
    cursor:pointer;
  }

  .charts-row {
    display: grid;
    grid-template-columns: 2fr 1fr;
    gap: 18px;
    margin-bottom: 22px;
  }

  .card {
    border: 1px solid var(--card-border);
    background: #fff;
    border-radius: 10px;
    padding: 12px;
    display: flex;
    flex-direction: column;
    box-sizing: border-box;
  }

  .card.left {
    height: 420px;
  }

  .right-column {
    display: flex;
    flex-direction: column;
    gap: 18px;
    height: 420px;
  }
  .right-column .card {
    flex: 1;
    padding: 12px;
  }

  .card .card-title {
    text-align: center;
    color: var(--blue);
    font-weight:600;
    margin-bottom:10px;
  }

  .chart-wrap {
    flex: 1;
    min-height: 0;
    display: flex;
  }
  .chart-wrap canvas {
    width: 100% !important;
    height: 100% !important;
    display:block;
  }

  .table-card {
    border-radius: 10px;
    border: 1px solid var(--card-border);
    background: #fff;
    padding: 8px;
  }
  .table-card table {
    width: 100%;
    border-collapse: collapse;
    font-size: 14px;
  }
  .table-card thead th {
    background: var(--blue);
    color: #fff;
    padding: 10px;
  }
  .table-card td, .table-card th {
    border: 1px solid var(--card-border);
    padding: 10px;
    vertical-align: middle;
  }
  .table-card tfoot td {
    font-weight:700;
    padding:12px;
  }

  @media (max-width: 900px) {
    .charts-row { grid-template-columns: 1fr; }
    .right-column { height: auto; }
    .card.left { height: auto; }
    .chart-wrap canvas { height: 300px !important; }
  }
</style>

@endsection
