@extends('layouts.super_admin.template_superadmin')
@section('title', 'All Project')

@section('header')
    @include('layouts.super_admin.header_superadmin')
@endsection

@section('content')

    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <!-- Flatpickr CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">

    <div class="page">
        <!-- Add project -->
        <div class="top-controls">
            <a href="{{ url('/superadmin/makeproject') }}" class="btn-primary-custom">+ Add Project</a>

            <div class="controls-right">
                <!-- Input Tanggal -->
                <div class="date-group">
                    <label for="date_range">Tanggal Upload:</label>
                    <input type="text" id="date_range" class="form-control" placeholder="Pilih rentang tanggal">
                </div>
                <!-- Download -->
                <button class="btn-primary-custom">
                    <i class="fa-solid fa-download"></i> Download All
                </button>
            </div>
        </div>

        <!-- Menampilkan Chart -->
        <div class="charts-row">
            <div class="card left">
                <div class="card-title">Distribusi Total Project {{ date('Y') }}</div>
                <div class="chart-wrap">
                    <canvas id="chartTotalProject"></canvas>
                </div>
            </div>

            <div class="right-column">
                <div class="card">
                    <div class="card-title">Distribusi Quality Enhancement {{ date('Y') }}</div>
                    <div class="chart-wrap">
                        <canvas id="chartQE"></canvas>
                    </div>
                </div>

                <div class="card">
                    <div class="card-title">Distribusi Status All Project {{ date('Y') }}</div>
                    <div class="chart-wrap">
                        <canvas id="chartPie"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <!-- Tabel untuk Menampilkan Data -->
        <div class="table-responsive">
            <table class="data-table table-bordered table-striped table-hover table-sm" id="data-table"
                style="min-width: 100%">
                <thead style="text-align: center;">
                    <tr>
                        <th style="min-width: 50px;">NO</th>
                        <th style="width: 300px;">NAMA PROJECT</th>
                        <th style="width: 400px;">DESKRIPSI PROJECT</th>
                        <th style="width: 200px;">QE</th>
                        <th style="width: 200px;">TANGGAL UPLOAD</th>
                        <th style="width: 200px;">TANGGAL PENGERJAAN</th>
                        <th style="width: 200px;">TANGGAL SELESAI</th>
                        <th style="width: 100px;">STATUS</th>
                        <th style="width: 150px;">TOTAL</th>
                        <th style="width: 50px;">DETAIL</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($project_doc as $index => $project)
                        <tr>
                            <td style="width: 50px;">{{ $index + 1 }}</td>
                            <td style="max-width: 300px; white-space: nowrap; overflow-x: auto; overflow-y: hidden;">
                                {{ $project['nama_project'] }}</td>
                            <td style="max-width: 400px; white-space: nowrap; overflow-x: auto; overflow-y: hidden;">
                                {{ $project['deskripsi_project'] }}</td>
                            <td style="max-width: 200px; white-space: nowrap; overflow-x: auto; overflow-y: hidden;">
                                {{ $project['qe'] }}</td>
                            <td style="max-width: 200px; white-space: nowrap; overflow-x: auto; overflow-y: hidden;">
                                {{ $project['tgl_upload'] }}</td>
                            <td style="max-width: 200px; white-space: nowrap; overflow-x: auto; overflow-y: hidden;">
                                {{ $project['tgl_pengerjaan'] }}</td>
                            <td style="max-width: 200px; white-space: nowrap; overflow-x: auto; overflow-y: hidden;">
                                {{ $project['tgl_selesai'] }}</td>
                            <td style="max-width: 10px; white-space: nowrap; overflow-x: auto; overflow-y: hidden;">
                                @if ($project['status'] === 'ACC')
                                    <span style="color: #28a745; font-weight: 600;">{{ $project['status'] }}</span>
                                @elseif ($project['status'] === 'REJECT')
                                    <span style="color: #dc3545; font-weight: 600;">{{ $project['status'] }}</span>
                                @else
                                    <span>{{ $project['status'] }}</span>
                                @endif
                            </td>
                            <td style="max-width: 150px; white-space: nowrap; overflow-x: auto; overflow-y: hidden;">
                                {{ $project['total_formatted'] }}</td>
                            <td>
                                <a href="{{ route('superadmin.allproject_detail', $project['id']) }}" title="Lihat Detail">
                                    <img src="{{ asset('assets/detail.png') }}" alt="Detail"
                                        style="width:20px;height:20px;">
                            </td>
                        </tr>
                    @endforeach
                </tbody>
                <tfoot>
                    <tr>
                        <th colspan="8" class="text-end">TOTAL PROJECT</th>
                        <th colspan="2">{{ $grandTotal }}</th>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>

    <!-- Script Chart.JS -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <!-- Flatpickr JS -->
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
    <script src="https://cdn.jsdelivr.net/npm/flatpickr/dist/l10n/id.js"></script>

    <script>
        document.addEventListener("DOMContentLoaded", function() {
            flatpickr("#date_range", {
                mode: "range",
                dateFormat: "Y-m-d",
                locale: "id",
                onClose: function(selectedDates, dateStr, instance) {
                    if (selectedDates.length === 2) {
                        const start = selectedDates[0].toISOString().split('T')[0];
                        const end = selectedDates[1].toISOString().split('T')[0];

                        // Redirect ke URL dengan query param (atau bisa pakai AJAX)
                        window.location.href = `?start=${start}&end=${end}`;
                    }
                }
            });
        });

        // Inisialisasi Flatpickr Range
        document.addEventListener("DOMContentLoaded", function() {
            flatpickr("#date_range", {
                mode: "range",
                dateFormat: "Y-m-d",
                locale: "id"
            });
        });

        const blue = '#133995';
        const lightBlue = '#4A6AC0';
        const red = '#ff4d4d';

        // Chart Total Project per Bulan
        new Chart(document.getElementById('chartTotalProject'), {
            type: 'bar',
            data: {
                labels: ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'],
                datasets: [{
                    data: @json($chartTotalProjectData),
                    backgroundColor: blue
                }]
            },
            options: {
                maintainAspectRatio: false,
                responsive: true,
                plugins: {
                    legend: {
                        display: false
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true
                    },
                    x: {
                        title: {
                            display: true,
                            text: 'Bulan'
                        }
                    }
                }
            }
        });

        // Chart QE (Distribusi QE Tahun Ini)
        new Chart(document.getElementById('chartQE'), {
            type: 'bar',
            data: {
                labels: Object.keys(@json($chartQEData)),
                datasets: [{
                    data: Object.values(@json($chartQEData)),
                    backgroundColor: blue
                }]
            },
            options: {
                indexAxis: 'y',
                maintainAspectRatio: false,
                responsive: true,
                plugins: {
                    legend: {
                        display: false
                    }
                },
                scales: {
                    x: {
                        beginAtZero: true
                    }
                }
            }
        });


        // Chart Pie (Distribusi All Project)
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
                plugins: {
                    legend: {
                        position: 'bottom'
                    }
                }
            }
        });
    </script>

    <style>
        :root {
            --blue: #133995;
            --bg: white;
            --card-border: #dcdcdc;
        }

        body {
            font-family: 'Poppins', sans-serif;
            background: var(--bg);
        }

        .page {
            padding: 10px 20px;
        }

        /* Bagian Top Control */
        .top-controls {
            margin-top: 20px;
            display: flex;
            justify-content: space-between;
            align-items: flex-end;
            margin-bottom: 18px;
        }

        .controls-right {
            display: flex;
            align-items: flex-end;
            gap: 12px;
        }

        .date-group {
            display: flex;
            flex-direction: column;
        }

        .date-group label {
            margin-bottom: 4px;
            font-weight: 500;
            color: #133995;
        }

        .date-group input {
            padding: 6px 8px;
            border-radius: 6px;
            border: 1px solid #133995;
            color: #ADAEB3;
        }

        .date-group input:hover {
            border-color: #ADAEB3;
        }

        .btn-primary-custom {
            display: inline-block;
            padding: 8px 16px;
            background-color: #133995;
            color: #fff !important;
            border: none;
            border-radius: 7px;
            font-size: 14px;
            font-weight: 500;
            cursor: pointer;
            text-decoration: none;
            transition: background-color 0.3s;
        }

        .btn-primary-custom:hover {
            background-color: #fff;
            color: #133995 !important;
            border: 1px solid #CFD0D2;
            text-decoration: none;
        }

        .btn-primary-custom i {
            margin-right: 6px;
        }

        /* Bagian Chart */
        .charts-row {
            margin-top: 20px;
            display: grid;
            grid-template-columns: 2fr 1fr;
            gap: 18px;
            margin-bottom: 22px;
        }

        .card {
            border: 1px solid #133995;
            background: #F5F5F6;
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
            font-weight: 500;
            margin-bottom: 10px;
        }

        .chart-wrap {
            flex: 1;
            min-height: 0;
            display: flex;
        }

        .chart-wrap canvas {
            width: 100% !important;
            height: 100% !important;
            display: block;
        }

        /* Table */
        .table-responsive {
            overflow-x: auto;
        }

        #data-table {
            border-collapse: collapse;
            width: 100%;
            overflow: hidden;
            border-radius: 10px;
            font-family: 'Poppins', sans-serif;
            font-weight: normal !important;
            table-layout: fixed;
        }

        #data-table th,
        #data-table td {
            border: 1px solid #ccc;
            padding: 10px;
            text-align: center;
            overflow: hidden;
            white-space: nowrap;
        }

        #data-table th {
            background-color: #133995;
            color: #ffffff;
            height: 20px;
            font-family: 'Poppins', sans-serif;
            font-weight: 600 !important;
        }

        #data-table tfoot th {
            background-color: #EDF7FF;
            color: #133995;
            font-weight: 600;
            text-align: center;
        }

        #data-table td {
            overflow-x: auto;
            overflow-y: hidden;
            -ms-overflow-style: none;
            scrollbar-width: none;
        }

        #data-table td::-webkit-scrollbar {
            display: none;
        }

        #data-table td:first-child,
        #data-table th:first-child {
            width: 50px;
        }

        @media (max-width: 900px) {
            .charts-row {
                grid-template-columns: 1fr;
            }

            .right-column {
                height: auto;
            }

            .card.left {
                height: auto;
            }

            .chart-wrap canvas {
                height: 300px !important;
            }
        }
    </style>

@endsection
