@extends('layouts.super_admin.template_superadmin')
@section('title', 'Dashboard')

@section('header')
    @include('layouts.super_admin.header_superadmin')
@endsection

@section('content')

<!-- Tombol atas -->
<div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:20px;">
    <button style="background-color:#004080; color:white; border:none; padding:10px 20px; border-radius:5px;">
        + Tambah Proyek
    </button>

    <div style="display:flex; align-items:center; gap:10px;">
        <label for="tanggal">Tanggal:</label>
        <input type="date" id="tanggal" value="{{ date('Y-m-d') }}">
        <button style="background-color:#004080; color:white; border:none; padding:10px 15px; border-radius:5px;">
            📥 Unduh Semua
        </button>
    </div>
</div>

<!-- Bagian grafik -->
<div style="display:grid; grid-template-columns:2fr 1fr; gap:20px; margin-bottom:20px;">
    <!-- Grafik Distribusi Total Proyek 2025 -->
    <div style="border:1px solid #ccc; padding:10px;">
        <h4 style="text-align:center;">Distribusi Total Proyek 2025</h4>
        <canvas id="chartTotalProject"></canvas>
    </div>

    <!-- Grafik Distribusi Total Proyek Hari Ini -->
    <div style="border:1px solid #ccc; padding:10px;">
        <h4 style="text-align:center;">Distribusi Total Proyek Hari Ini</h4>
        <canvas id="chartToday"></canvas>
    </div>
</div>

<!-- Grafik Pie Distribusi Total Semua Proyek -->
<div style="border:1px solid #ccc; padding:10px; margin-bottom:20px;">
    <h4 style="text-align:center;">Distribusi Total Semua Proyek</h4>
    <canvas id="chartPie"></canvas>
</div>

<!-- Tabel proyek -->
<table border="1" cellspacing="0" cellpadding="5" width="100%">
    <thead style="background-color:#004080; color:white;">
        <tr>
            <th>NO</th>
            <th>NAMA PROYEK</th>
            <th>DESKRIPSI PROYEK</th>
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
            <td colspan="5" style="text-align:right;">TOTAL SEMUA PROYEK</td>
            <td>0</td>
        </tr>
        <tr>
            <td colspan="5" style="text-align:right;">TOTAL PENDAPATAN</td>
            <td>0</td>
        </tr>
    </tfoot>
</table>

<!-- Script Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    // Grafik bar total proyek
    new Chart(document.getElementById('chartTotalProject'), {
        type: 'bar',
        data: {
            labels: ['Jan','Feb','Mar','Apr','Mei','Jun','Jul','Agu','Sep','Okt','Nov','Des'],
            datasets: [{
                label: 'Total Proyek',
                data: [450, 300, 480, 320, 410, 300, 280, 350, 360, 370, 380, 380],
                backgroundColor: '#004080'
            }]
        },
        options: {
            responsive: true,
            scales: {
                y: {
                    beginAtZero: true
                }
            }
        }
    });

    // Grafik horizontal bar hari ini
    new Chart(document.getElementById('chartToday'), {
        type: 'bar',
        data: {
            labels: ['PROCESS', 'ACC', 'REJECT'],
            datasets: [{
                label: 'Total Proyek',
                data: [9, 7, 1],
                backgroundColor: '#004080'
            }]
        },
        options: {
            indexAxis: 'y',
            responsive: true,
            scales: {
                x: {
                    beginAtZero: true
                }
            }
        }
    });

    // Grafik pie total semua proyek
    new Chart(document.getElementById('chartPie'), {
        type: 'pie',
        data: {
            labels: ['PROCESS', 'ACC', 'REJECT'],
            datasets: [{
                data: [50, 40, 10],
                backgroundColor: ['#004080', '#00aaff', '#ff4444']
            }]
        }
    });
</script>

@endsection
