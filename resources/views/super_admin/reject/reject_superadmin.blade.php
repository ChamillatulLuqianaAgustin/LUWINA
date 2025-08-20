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
        <table class="data-table table-bordered table-striped table-hover table-sm" id="reject-table" style="min-width: 100%">
            <thead style="text-align: center;">
                <tr>
                    <th style="min-width: 50px; border-top-left-radius: 10px;">NO</th>
                    <th>NAMA PROJECT</th>
                    <th>DESKRIPSI PROJECT</th>
                    <th>QE</th>
                    <th>TANGGAL UPLOAD</th>
                    <th>TANGGAL PENGERJAAN</th>
                    <th>TANGGAL SELESAI</th>
                    <th>STATUS</th>
                    <th>TOTAL</th>
                    <th style="min-width: 50px; border-top-right-radius: 10px;">DETAIL</th>
                </tr>
            </thead>
            <tbody>
                @forelse($rejectProjects as $index => $project)
                    <tr>
                        <td>{{ $index + 1 }}</td>
                        <td>{{ $project['nama_project'] ?? '-' }}</td>
                        <td>{{ $project['deskripsi'] ?? '-' }}</td>
                        <td>{{ $project['qe'] ?? '-' }}</td>
                        <td>{{ $project['tanggal_upload'] ?? '-' }}</td>
                        <td>{{ $project['tanggal_pengerjaan'] ?? '-' }}</td>
                        <td>{{ $project['tanggal_selesai'] ?? '-' }}</td>
                        <td>{{ $project['status'] ?? '-' }}</td>
                        <td>{{ number_format($project['total'] ?? 0, 2, ',', '.') }}</td>
                        <td>
                            <a href="{{ route('project.detail', $project['id']) }}" class="btn btn-sm btn-primary">Detail</a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="10">Belum ada project REJECT</td>
                    </tr>
                @endforelse
            </tbody>
            <tfoot>
                <tr>
                    <th colspan="7" class="text-end">TOTAL PROJECT</th>
                    <th colspan="4">{{ $totalProject }}</th>
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

    body { font-family: 'Poppins', sans-serif; background: var(--bg); }

    .page { padding: 10px 20px; }

    /* Top Controls */
    .top-controls {
        margin-top: 20px;
        display: flex;
        justify-content: flex-end;
        margin-bottom: 18px;
    }

    .controls-right { display: flex; align-items: flex-end; gap: 12px; }

    .date-group { display: flex; flex-direction: column; }
    .date-group label { margin-bottom: 4px; font-weight: 500; color: var(--blue); }
    .date-group input { padding: 6px 8px; border-radius: 6px; border: 1px solid var(--blue); color: #555; }

    /* Table */
    .table-responsive { overflow-x: auto; }

    .data-table {
        border-collapse: collapse;
        width: 100%;
        font-family: 'Poppins', sans-serif;
    }

    .data-table th,
    .data-table td {
        border: 1px solid #133995;
        padding: 10px;
        text-align: center;
    }

    .data-table th {
        background-color: var(--blue);
        color: #ffffff;
        height: 20px;
        /* Tinggi baris header lebih besar */
        font-family: 'Poppins', sans-serif;
        font-weight: 600 !important;
    }

    .data-table tfoot th {
        background-color: #F0F2F9;
        color: #133995;
        font-weight: 600;
        text-align: center;
    }
</style>

@endsection
