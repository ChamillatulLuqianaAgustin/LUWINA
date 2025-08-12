@extends('layouts.super_admin.template_superadmin')
@section('title', 'All Project')

@section('header')
    @include('layouts.super_admin.header_superadmin')
@endsection

@section('content')

<!-- Poppins -->
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">

<div class="page">

  <!-- Top controls -->
  <div class="top-controls">
    <button class="btn-primary-custom">+ Add Project</button>

    <div class="controls-right">
      <label for="tanggal" style="margin-right:6px;">Tanggal:</label>
      <input type="date" id="tanggal" value="{{ date('Y-m-d') }}" style="padding:6px 8px; border-radius:6px; border:1px solid #ccc;">
      <button class="btn-primary-custom">📥 Download All</button>
    </div>
  </div>

  <!-- Charts layout -->
  <div class="charts-row">
    <!-- LEFT (large) -->
    <div class="card left">
      <div class="card-title">Distribusi Total Project 2025</div>
      <div class="chart-wrap">
        <canvas id="chartTotalProject"></canvas>
      </div>
    </div>

    <!-- RIGHT (two small stacked) -->
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

  <!-- Table -->
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
        <tr>
          <td>1</td>
          <td>3MLG_MAGU_FAG/08_SAKLJ</td>
          <td>PEMBETULAN TIANG DI JL JAKARTA</td>
          <td>RECOVERY</td>
          <td>2025-07-16</td>
          <td>2025-07-25</td>
        </tr>
        <tr>
          <td>2</td>
          <td>3MLG_PREV_GESER ODP+TIANG_ODP-KLJ-FCF-06</td>
          <td>GESER TIANG ODP-KLJ-FCF/06</td>
          <td>RELOKASI</td>
          <td>2025-06-11</td>
          <td>-</td>
        </tr>
        <tr>
          <td>3</td>
          <td>3MLG_QPREV_THE_RITZ_INC35406882_KLJ</td>
          <td>PENGECORAN TIANG DI JL SURABAYA</td>
          <td>PREVENTIVE</td>
          <td>2025-07-09</td>
          <td>-</td>
        </tr>
      </tbody>
      <tfoot>
        <tr>
          <td colspan="5" style="text-align:right">TOTAL ALL PROJECT</td>
          <td>0</td>
        </tr>
        <tr>
          <td colspan="5" style="text-align:right">TOTAL REVENUE</td>
          <td>0</td>
        </tr>
      </tfoot>
    </table>
  </div>

</div> <!-- /.page -->

<!-- Chart.js (keep maintainAspectRatio: false so canvas fills container) -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
  const blue = '#133995';
  const lightBlue = '#4A6AC0';
  const red = '#ff4d4d';

  new Chart(document.getElementById('chartTotalProject'), {
    type: 'bar',
    data: {
      labels: ['1','2','3','4','5','6','7','8','9','10','11','12'],
      datasets: [{ data: [450,300,480,320,410,300,280,350,360,370,380,380], backgroundColor: blue }]
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
      labels: ['PROCESS','ACC','REJECT'],
      datasets: [{ data: [9,7,1], backgroundColor: blue }]
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
      labels: ['PROCESS','ACC','REJECT'],
      datasets: [{ data: [50,40,10], backgroundColor: [blue, lightBlue, red] }]
    },
    options: {
      maintainAspectRatio: false,
      responsive: true,
      plugins: { legend: { position: 'bottom' } }
    }
  });
</script>

<style>
  :root{
    --blue: #133995;
    --bg: #F5F5F6;
    --card-border: #dcdcdc;
  }
  body { font-family: 'Poppins', sans-serif; background: var(--bg); }

  .page {
    max-width: 1200px;
    margin: 0 20px;
    padding: 22px;
  }

  /* top controls */
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

  /* CHART LAYOUT: left big, right two stacked */
  .charts-row {
    display: grid;
    grid-template-columns: 2fr 1fr; /* left 2 / right 1 */
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

  /* Left big card */
  .card.left {
    height: 420px; /* total height for left */
  }

  /* Right column: two cards stacked with equal height that together equal left */
  .right-column {
    display: flex;
    flex-direction: column;
    gap: 18px;
    height: 420px; /* same total as left */
  }
  .right-column .card {
    flex: 1; /* split equally */
    padding: 12px;
  }

  .card .card-title {
    text-align: center;
    color: var(--blue);
    font-weight:600;
    margin-bottom:10px;
  }

  /* Chart container area that Chart.js will fill */
  .chart-wrap {
    flex: 1;
    min-height: 0; /* important for flex children to allow proper shrinking */
    display: flex;
  }
  .chart-wrap canvas {
    width: 100% !important;
    height: 100% !important;
    display:block;
  }

  /* Table styling */
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
  .table-card tfoot td { font-weight:700; padding:12px; }
  /* Responsive fallback: on small screens stack */
  @media (max-width: 900px) {
    .charts-row { grid-template-columns: 1fr; }
    .right-column { height: auto; }
    .card.left { height: auto; }
    .chart-wrap canvas { height: 300px !important; }
  }
</style>

@endsection
