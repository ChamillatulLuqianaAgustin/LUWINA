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
            <a href="{{ route('superadmin.process_detail', $process['id']) }}" class="btn-back">
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
                        value="{{ old('nama_project', $process['nama_project']) }}" required>
                    <button type="submit" class="btn-done">Done</button>
                </div>

                <!-- Tabel Edit -->
                <div class="table-responsive">
                    <table class="data-table table-bordered table-striped table-hover table-sm" id="data-table"
                        style="min-width: 100%">
                        <thead style="text-align: center;">
                            <tr>
                                <th style="min-width: 50px;">NO</th>
                                <th style="width: 150px;">DESIGNATOR</th>
                                <th style="width: 300px;"> URAIAN</th>
                                <th style="width: 100px;">SATUAN</th>
                                <th style="width: 150px;">HARGA MATERIAL</th>
                                <th style="width: 150px;">HARGA JASA</th>
                                <th style="width: 100px;">VOLUME</th>
                                <th style="width: 150px;">TOTAL MATERIAL</th>
                                <th style="width: 150px;">TOTAL JASA</th>
                                <th style="width: 50px;">DELETE</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($process['detail'] ?? [] as $index => $item)
                                <tr>
                                    <td>{{ $index + 1 }}</td>
                                    {{-- <td style="width: 150px;">
                                        <div style="position: relative;">
                                            <input type="text" name="designator[]" required class="input-dsg"
                                                placeholder="Masukkan Designator"
                                                value="{{ old('designator.' . $index, $item->designator ?? '') }}"
                                                oninput="filterDesignators(this)">
                                            <div class="suggestions" style="display: none;"></div>
                                        </div>
                                    </td> --}}
                                    <td style="width: 150px;">
                                        <div style="position: relative;">
                                            <input type="text" name="designator[]" required class="input-dsg"
                                                placeholder="Masukkan Designator"
                                                value="{{ old('designator.' . $index, $item->designator ?? '') }}"
                                                oninput="filterDesignators(this)">
                                            <div class="suggestions" style="display: none;"></div>
                                        </div>
                                    </td>
                                    <td class="uraian" style="width:300px;">
                                        <div class="uraian-overflow" title="{{ $item->uraian }}">{{ $item->uraian }}</div>
                                    </td>
                                    <td class="satuan">{{ $item->satuan }}</td>
                                    <td class="harga_material">{{ number_format($item->harga_material, 0, ',', '.') }}</td>
                                    <td class="harga_jasa">{{ number_format($item->harga_jasa, 0, ',', '.') }}</td>
                                    <td style="width:60px;">
                                        <input type="number" name="volume[]" class="form-control vol-field"
                                            value="{{ old('volume.' . $index, $item->volume) }}" min="0"
                                            step="1" required>
                                    </td>
                                    <td class="total_material">{{ number_format($item->total_material, 0, ',', '.') }}</td>
                                    <td class="total_jasa">{{ number_format($item->total_jasa, 0, ',', '.') }}</td>
                                    <td>
                                        <form action="{{ route('superadmin.process_destroy', $item->id) }}" method="POST"
                                            class="form-delete">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" style="background:none;border:none;cursor:pointer;">
                                                <img src="{{ asset('assets/delete.png') }}" alt="Delete"
                                                    style="width:20px;height:20px;">
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                        <tfoot>
                            <tr>
                                <th colspan="7" class="text-end">Material</th>
                                <th colspan="2" class="summary-material">
                                    {{ number_format($totals['material'], 0, ',', '.') }}</th>
                                <th></th>
                            </tr>
                            <tr>
                                <th colspan="7" class="text-end">Jasa</th>
                                <th colspan="2" class="summary-jasa">{{ number_format($totals['jasa'], 0, ',', '.') }}
                                </th>
                                <th></th>
                            </tr>
                            <tr>
                                <th colspan="7" class="text-end">Total</th>
                                <th colspan="2" class="summary-total">{{ number_format($totals['total'], 0, ',', '.') }}
                                </th>
                                <th></th>
                            </tr>
                            <tr>
                                <th colspan="7" class="text-end">PPN (11%)</th>
                                <th colspan="2" class="summary-ppn">{{ number_format($totals['ppn'], 0, ',', '.') }}
                                </th>
                                <th></th>
                            </tr>
                            <tr>
                                <th colspan="7" class="text-end">Total Setelah PPN</th>
                                <th colspan="2" class="summary-after-ppn">
                                    {{ number_format($totals['grand'], 0, ',', '.') }}</th>
                                <th></th>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </form>
    </div>

    {{-- SweetAlert --}}
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    @if (session('success'))
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
            /* table-layout: fixed; */
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

        .input-dsg {
            width: 150px;
            /* Match the width you want for the input */
            padding: 8px 8px 8px 12px;
            border: 1px solid #ccc;
            border-radius: 6px;
            font-family: 'Poppins', sans-serif;
            color: #84858C;
            border-color: #133995;
            position: relative;
            /* Position relative for absolute children */
        }

        .vol-field {
            width: 70px;
            padding: 6px 8px;
            border: 1px solid #133995;
            border-radius: 6px;
            font-size: 14px;
            font-family: 'Poppins', sans-serif;
            text-align: center;
        }

        .input-edit {
            width: 100%;
            padding: 6px 8px;
            border: 1px solid #133995;
            border-radius: 4px;
            font-size: 14px;
        }

        .suggestions {
            border: 1px solid #ccc;
            top: 100%;
            left: 0;
            width: 100%;
            border-radius: 6px;
            background: white;
            position: absolute;
            z-index: 1000;
            max-height: 150px;
            overflow-y: auto;
            display: none;
            box-sizing: border-box;
        }

        .suggestion-item {
            padding: 8px;
            cursor: pointer;
        }

        .suggestion-item:hover {
            background-color: #f0f0f0;
        }
    </style>

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <script>
        const dsgData = @json($project_ta_doc);

        // Function to calculate total material and total jasa based on volume
        function calculateRow(row) {
            const volume = parseFloat(row.querySelector('.vol-field').value) || 0;
            const hargaMaterial = parseFloat(row.querySelector('.harga_material').textContent.replace(/\./g, '').replace(
                /,/g, '')) || 0;
            const hargaJasa = parseFloat(row.querySelector('.harga_jasa').textContent.replace(/\./g, '').replace(/,/g,
                '')) || 0;

            // Calculate totals
            const totalMaterial = volume * hargaMaterial;
            const totalJasa = volume * hargaJasa;

            // Update the total fields in the row
            row.querySelector('.total_material').textContent = totalMaterial.toLocaleString('id-ID');
            row.querySelector('.total_jasa').textContent = totalJasa.toLocaleString('id-ID');

            // Update the overall summary
            updateSummary();
        }

        // Attach event listeners to volume inputs
        function attachVolumeListener(row) {
            const volumeInput = row.querySelector('.vol-field');
            volumeInput.addEventListener('input', () => {
                calculateRow(row);
            });
        }

        // Attach listeners to each existing row in the table
        document.querySelectorAll('#data-table tbody tr').forEach(row => attachVolumeListener(row));

        // Function to update the summary totals
        function updateSummary() {
            let totalMaterial = 0;
            let totalJasa = 0;

            document.querySelectorAll('#data-table tbody tr').forEach(row => {
                totalMaterial += parseFloat(row.querySelector('.total_material').textContent.replace(/\./g, '')
                    .replace(/,/g, '')) || 0;
                totalJasa += parseFloat(row.querySelector('.total_jasa').textContent.replace(/\./g, '').replace(
                    /,/g, '')) || 0;
            });

            const total = totalMaterial + totalJasa;
            const ppn = total * 0.11;
            const totalAfterPpn = total + ppn;

            // Update the summary display elements if they exist
            document.querySelector('.summary-material').textContent = totalMaterial.toLocaleString('id-ID');
            document.querySelector('.summary-jasa').textContent = totalJasa.toLocaleString('id-ID');
            document.querySelector('.summary-total').textContent = total.toLocaleString('id-ID');
            document.querySelector('.summary-ppn').textContent = ppn.toLocaleString('id-ID');
            document.querySelector('.summary-after-ppn').textContent = totalAfterPpn.toLocaleString('id-ID');
        }

        function changeFontColor(selectElement) {
            if (selectElement.value) {
                selectElement.style.color = 'black';
                const selectedId = selectElement.value;
                const row = selectElement.closest('tr');
                const selectedDsg = dsgData.find(dsg => dsg.id == selectedId);

                if (selectedDsg) {
                    const uraianBox = row.querySelector('.uraian-overflow');
                    uraianBox.textContent = selectedDsg.uraian;
                    uraianBox.setAttribute('title', selectedDsg.uraian);

                    row.querySelector('.satuan').textContent = selectedDsg.satuan;
                    row.querySelector('.harga_material').textContent = selectedDsg.harga_material;
                    row.querySelector('.harga_jasa').textContent = selectedDsg.harga_jasa;

                    calculateRow(row);
                    updateSummary();
                }
            }
        }

        document.addEventListener('DOMContentLoaded', function() {
            document.querySelectorAll('#data-table tbody tr').forEach(row => {
                attachVolumeListener(row);
                calculateRow(row);
            });
            updateSummary();
        });

        // SweetAlert untuk Delete
        document.querySelectorAll('.btn-delete').forEach(btn => {
            btn.addEventListener('click', function() {
                let id = this.dataset.id;

                Swal.fire({
                    title: 'Yakin?',
                    text: "Material ini akan dihapus permanen!",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#133995',
                    cancelButtonColor: '#d33',
                    confirmButtonText: 'Ya, hapus!',
                    cancelButtonText: 'Batal'
                }).then((result) => {
                    if (result.isConfirmed) {
                        let form = document.createElement('form');
                        form.action = `/superadmin/process/detail/${id}`;
                        form.method = 'POST';

                        let token = document.createElement('input');
                        token.type = 'hidden';
                        token.name = '_token';
                        token.value = '{{ csrf_token() }}';

                        let method = document.createElement('input');
                        method.type = 'hidden';
                        method.name = '_method';
                        method.value = 'DELETE';

                        form.appendChild(token);
                        form.appendChild(method);
                        document.body.appendChild(form);
                        form.submit();
                    }
                });
            });
        });

        function filterDesignators(input) {
            const value = input.value.toLowerCase();
            const suggestionsContainer = input.nextElementSibling;
            suggestionsContainer.innerHTML = '';

            if (value) {
                const filteredDesignators = dsgData.filter(dsg => dsg.designator.toLowerCase().includes(value));
                if (filteredDesignators.length > 0) {
                    suggestionsContainer.style.display = 'block';
                    filteredDesignators.forEach(dsg => {
                        const suggestionItem = document.createElement('div');
                        suggestionItem.textContent = dsg.designator;
                        suggestionItem.classList.add('suggestion-item');
                        suggestionItem.onclick = () => selectDesignator(dsg, input, suggestionsContainer);
                        suggestionsContainer.appendChild(suggestionItem);
                    });
                } else {
                    suggestionsContainer.style.display = 'block';
                    suggestionsContainer.innerHTML = '<div>Designator tidak ditemukan</div>';
                }
            } else {
                suggestionsContainer.style.display = 'none';
            }
        }

        function selectDesignator(dsg, input, suggestionsContainer) {
            input.value = dsg.designator; // Set input value to selected designator
            suggestionsContainer.style.display = 'none'; // Hide suggestions
            const row = input.closest('tr');
            const uraianBox = row.querySelector('.uraian-overflow');
            uraianBox.textContent = dsg.uraian;
            uraianBox.setAttribute('title', dsg.uraian);
            row.querySelector('.satuan').textContent = dsg.satuan;
            row.querySelector('.harga_material').textContent = Number(dsg.harga_material).toLocaleString('id-ID');
            row.querySelector('.harga_jasa').textContent = Number(dsg.harga_jasa).toLocaleString('id-ID');

            // Call calculateRow to update totals
            calculateRow(row);
            updateSummary();
        }

        // Hide suggestions when clicking outside
        document.querySelectorAll('.input-dsg').forEach(input => {
            input.addEventListener('input', function() {
                filterDesignators(this);
            });
        });
    </script>

@endsection
