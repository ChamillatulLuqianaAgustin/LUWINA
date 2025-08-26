@extends('layouts.super_admin.template_superadmin')
@section('title', 'Edit Process')

@section('header')
    @include('layouts.super_admin.header_superadmin')
@endsection

@section('content')

<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

<div class="page">
    <!-- Tombol Back -->
    <div class="action-bar">
        <a href="{{ route('superadmin.process_edit') }}" class="btn-back">
            <i class="fa fa-arrow-left" style="margin-right: 8px;"></i> Back
        </a>
    </div>

    <!-- Form Edit -->
    <form action="{{ route('superadmin.process_update', $process['id']) }}" method="POST">
        @csrf
        @method('PUT')

        <!-- Nama Project + Table wrapper -->
        <div class="table-wrapper">
            <!-- Header Nama Project -->
            <div class="project-header">
                <input type="text" name="nama_project" class="input-edit-project"
                    value="{{ old('nama_project', $process['nama_project']) }}">
                <button type="submit" class="btn-done">Done</button>
            </div>

            <!-- Tabel Edit -->
            <div class="table-responsive">
                <table class="data-table table-bordered table-striped table-hover table-sm" id="data-table"
                    style="min-width: 100%">
                    <thead style="text-align: center;">
                        <tr>
                            <th>NO</th>
                            <th>DESIGNATOR</th>
                            <th>URAIAN</th>
                            <th>SATUAN</th>
                            <th>HARGA MATERIAL</th>
                            <th>HARGA JASA</th>
                            <th>VOLUME</th>
                            <th>TOTAL MATERIAL</th>
                            <th>TOTAL JASA</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($process['detail'] ?? [] as $index => $item)
                            <tr>
                                <td>{{ $index+1 }}</td>
                                <td>
                                    <input type="text" name="detail[{{ $index }}][designator]" 
                                        class="input-edit"
                                        value="{{ old('detail.'.$index.'.designator', $item->designator) }}">
                                </td>
                                <td>{{ $item->uraian }}</td>
                                <td>{{ $item->satuan }}</td>
                                <td>{{ number_format($item->harga_material, 0, ',', '.') }}</td>
                                <td>{{ number_format($item->harga_jasa, 0, ',', '.') }}</td>
                                <td>
                                    <input type="number" name="detail[{{ $index }}][volume]" 
                                        class="input-edit"
                                        value="{{ old('detail.'.$index.'.volume', $item->volume) }}">
                                </td>
                                <td>{{ number_format($item->total_material, 0, ',', '.') }}</td>
                                <td>{{ number_format($item->total_jasa, 0, ',', '.') }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                    <tfoot>
                        <tr>
                            <th colspan="7" class="text-end">MATERIAL</th>
                            <th colspan="2">{{ number_format($totals['material'], 0, ',', '.') }}</th>
                        </tr>
                        <tr>
                            <th colspan="7" class="text-end">JASA</th>
                            <th colspan="2">{{ number_format($totals['jasa'], 0, ',', '.') }}</th>
                        </tr>
                        <tr>
                            <th colspan="7" class="text-end">TOTAL</th>
                            <th colspan="2">{{ number_format($totals['total'], 0, ',', '.') }}</th>
                        </tr>
                        <tr>
                            <th colspan="7" class="text-end">PPN</th>
                            <th colspan="2">{{ number_format($totals['ppn'], 0, ',', '.') }}</th>
                        </tr>
                        <tr>
                            <th colspan="7" class="text-end">TOTAL SETELAH PPN</th>
                            <th colspan="2">{{ number_format($totals['grand'], 0, ',', '.') }}</th>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </form>
</div>

{{-- SweetAlert --}}
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
@if(session('success'))
<script>
    Swal.fire({
        icon: 'success',
        title: 'Berhasil',
        text: "{{ session('success') }}",
        showConfirmButton: false,
        timer: 2000
    });
</script>
@endif

<style>
    :root {
        --blue: #133995;
    }

    body {
        font-family: 'Poppins', sans-serif;
    }

    .page {
        padding: 20px;
    }

    .action-bar {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 16px;
    }

    .btn-back {
        background: var(--blue);
        color: white;
        padding: 10px 16px;
        border-radius: 8px;
        font-size: 14px;
        text-decoration: none;
        display: flex;
        align-items: center;
    }

    .btn-back:hover {
        background-color: #fff;
        color: #133995 !important;
        border: 1px solid #CFD0D2;
        text-decoration: none;
    }

    .table-wrapper {
        border: 1px solid #ccc;
        border-radius: 10px;
        overflow: hidden;
        margin-top: 16px;
    }

    .project-header {
        background: #F5F5F6;
        padding: 12px 16px;
        border-bottom: 1px solid #ccc;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    .input-edit-project {
        color: #595961;
        padding: 6px 10px;
        border: 1px solid #133995;
        border-radius: 6px;
        font-size: 18px !important;
        font-weight: 500 !important;
        max-width: 500px;
        background: #F5F5F6;
    }

    .btn-done {
        background: none;
        border: none;
        font-size: 14px;
        font-weight: 500;
        color: #133995;
        cursor: pointer;
        padding: 0;
    }

    .btn-done:hover {
        text-decoration: underline;
        color: #133995;
    }

    .table-responsive {
        overflow-x: auto;
    }

    #data-table {
        border-collapse: collapse;
        width: 100%;
        font-family: 'Poppins', sans-serif;
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
        background-color: var(--blue);
        color: white;
        font-weight: 600 !important;
    }

    #data-table tfoot th {
        background-color: #EDF7FF;
        color: #000;
        font-weight: 700 !important;
        text-align: center;
        border: none !important;
    }

    .input-edit {
        width: 100%;
        padding: 6px 8px;
        border: 1px solid #999;
        border-radius: 4px;
        font-size: 14px;
    }
</style>

@endsection
