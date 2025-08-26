@extends('layouts.super_admin.template_superadmin')
@section('title', 'Reject')

@section('header')
    @include('layouts.super_admin.header_superadmin')
@endsection

@section('content')

    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">

    <div class="page">
        <!-- Filter Tanggal -->
        <div class="top-controls">
            <div class="controls-right">
                <div class="date-group">
                    <label for="date_range">Tanggal Upload:</label>
                    <input type="text" id="date_range" class="form-control" placeholder="Pilih rentang tanggal">
                </div>
            </div>
        </div>

        <!-- Tabel REJECT -->
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
                    @foreach ($reject_doc as $index => $reject)
                        <tr>
                            <td style="width: 50px;">{{ $index + 1 }}</td>
                            <td style="max-width: 300px; white-space: nowrap; overflow-x: auto; overflow-y: hidden;">
                                {{ $reject['nama_project'] }}</td>
                            <td style="max-width: 400px; white-space: nowrap; overflow-x: auto; overflow-y: hidden;">
                                {{ $reject['deskripsi_project'] }}</td>
                            <td style="max-width: 200px; white-space: nowrap; overflow-x: auto; overflow-y: hidden;">
                                {{ $reject['qe'] }}</td>
                            <td style="max-width: 200px; white-space: nowrap; overflow-x: auto; overflow-y: hidden;">
                                {{ $reject['tgl_upload'] }}</td>
                            <td style="max-width: 200px; white-space: nowrap; overflow-x: auto; overflow-y: hidden;">
                                {{ $reject['tgl_pengerjaan'] }}</td>
                            <td style="max-width: 200px; white-space: nowrap; overflow-x: auto; overflow-y: hidden;">
                                {{ $reject['tgl_selesai'] }}</td>
                            <td style="max-width: 10px; white-space: nowrap; overflow-x: auto; overflow-y: hidden;">
                                {{ $reject['status'] }}</td>
                            <td style="max-width: 150px; white-space: nowrap; overflow-x: auto; overflow-y: hidden;">
                                {{ $reject['total'] }}</td>
                            <td>
                                <a href="{{ route('superadmin.reject_detail', $reject['id']) }}" title="Lihat Detail">
                                    <img src="{{ asset('assets/detail.png') }}" alt="Detail" style="width:20px;height:20px;">
                                </a>
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

    <!-- Flatpickr -->
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
    <script src="https://cdn.jsdelivr.net/npm/flatpickr/dist/l10n/id.js"></script>
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            flatpickr("#date_range", {
                mode: "range",
                dateFormat: "Y-m-d",
                locale: "id"
            });
        });
    </script>

    <style>
        :root {
            --blue: #133995;
            --bg: white;
        }

        body {
            font-family: 'Poppins', sans-serif;
            background: var(--bg);
        }

        .page {
            padding: 10px 20px;
        }

        /* Top Controls */
        .top-controls {
            margin-top: 20px;
            display: flex;
            justify-content: flex-end;
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
            color: var(--blue);
        }

        .date-group input {
            padding: 6px 8px;
            border-radius: 6px;
            border: 1px solid var(--blue);
            color: #555;
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
    </style>

@endsection
