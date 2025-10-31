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
                                <th style="width: 175px;">DESIGNATOR</th>
                                <th style="width: 300px;"> URAIAN</th>
                                <th style="width: 100px;">SATUAN</th>
                                <th style="width: 150px;">HARGA MATERIAL</th>
                                <th style="width: 150px;">HARGA JASA</th>
                                <th style="width: 100px;">VOLUME</th>
                                <th style="width: 150px;">TOTAL MATERIAL</th>
                                <th style="width: 150px;">TOTAL JASA</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($process['detail'] ?? [] as $index => $item)
                                <tr>
                                    <td>{{ $index + 1 }}</td>
                                    <td style="width: 150px;">
                                        <div style="position: relative;">
                                            <input type="text" name="designator[]" required class="input-dsg"
                                                placeholder="Masukkan Designator" oninput="filterDesignators(this)"
                                                value="{{ old('designator.' . $index, $item->designator ?? '') }}">
                                            <div class="suggestions" style="display: none;"></div>
                                        </div>
                                        <input type="hidden" name="detail_id[]" value="{{ $item->id }}" />
                                        <!-- ID detail -->
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
                                </tr>
                            @endforeach
                        </tbody>
                        <tfoot>
                            <tr>
                                <td colspan="9" class="text-center">
                                    <button type="button" id="addRow" class="btn btn-primary">
                                        + Tambah Material
                                    </button>
                                </td>
                            </tr>

                            <tr>
                                <th colspan="6" class="text-end">Material</th>
                                <th colspan="2" class="summary-material">
                                    {{ number_format($totals['material'], 0, ',', '.') }}</th>
                                <th></th>
                            </tr>
                            <tr>
                                <th colspan="6" class="text-end">Jasa</th>
                                <th colspan="2" class="summary-jasa">{{ number_format($totals['jasa'], 0, ',', '.') }}
                                </th>
                                <th></th>
                            </tr>
                            <tr>
                                <th colspan="6" class="text-end">Total</th>
                                <th colspan="2" class="summary-total">{{ number_format($totals['total'], 0, ',', '.') }}
                                </th>
                                <th></th>
                            </tr>
                            <tr>
                                <th colspan="6" class="text-end">PPN (11%)</th>
                                <th colspan="2" class="summary-ppn">{{ number_format($totals['ppn'], 0, ',', '.') }}
                                </th>
                                <th></th>
                            </tr>
                            <tr>
                                <th colspan="6" class="text-end">Total Setelah PPN</th>
                                <th colspan="2" class="summary-after-ppn">
                                    {{ number_format($totals['grand'], 0, ',', '.') }}</th>
                                <th></th>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </form>

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
                margin-top: 16px;
                overflow: visible;
                /* biar dropdown suggestions bisa tampil */
            }

            .table-wrapper td {
                position: relative;
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

            .button-group {
                margin-top: 10px;
                display: flex;
                gap: 5px;
            }

            .button-group button {
                width: 30px;
                height: 30px;
                font-size: 16px;
                border: 1px solid #ccc;
                border-radius: 100px;
                box-shadow: none;
                outline: none;
                background-color: #f5f5f5;
                cursor: pointer;
            }

            .button-group button:active {
                background-color: #ddd;
            }

            .suggestions {
                position: absolute;
                z-index: 9999;
                background: white;
                border: 1px solid #ccc;
                border-radius: 6px;
                max-height: 150px;
                overflow-y: auto;
                display: none;
                box-shadow: 0 2px 5px rgba(0, 0, 0, 0.15);
            }

            .suggestion-item {
                padding: 8px;
                cursor: pointer;
            }

            .suggestion-item:hover {
                background-color: #f0f0f0;
            }

            .select-dsg {
                width: 200px !important;
                /* sesuai dengan lebar cell */
            }

            .btn-add-row {
                background-color: #133995;
                color: white;
                border: none;
                border-radius: 6px;
                padding: 6px 14px;
                font-size: 14px;
                cursor: pointer;
                transition: 0.2s ease;
            }

            .btn-add-row:hover {
                background-color: #0f2e7a;
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

            document.querySelectorAll('#data-table tbody tr').forEach(row => {
                attachVolumeListener(row);
            });

            function filterDesignators(input) {
                const value = input.value.toLowerCase();
                let suggestionsContainer = document.getElementById("global-suggestions");

                // kalau belum ada, buat baru
                if (!suggestionsContainer) {
                    suggestionsContainer = document.createElement("div");
                    suggestionsContainer.id = "global-suggestions";
                    suggestionsContainer.className = "suggestions";
                    document.body.appendChild(suggestionsContainer);
                }

                suggestionsContainer.innerHTML = "";

                if (value) {
                    const filteredDesignators = dsgData.filter(dsg =>
                        dsg.designator.toLowerCase().includes(value)
                    );

                    if (filteredDesignators.length > 0) {
                        filteredDesignators.forEach(dsg => {
                            const suggestionItem = document.createElement("div");
                            suggestionItem.textContent = dsg.designator;
                            suggestionItem.classList.add("suggestion-item");
                            suggestionItem.onclick = () => selectDesignator(dsg, input, suggestionsContainer);
                            suggestionsContainer.appendChild(suggestionItem);
                        });
                    } else {
                        suggestionsContainer.innerHTML = "<div class='suggestion-item'>Designator tidak ditemukan</div>";
                    }

                    // ambil posisi input di layar
                    const rect = input.getBoundingClientRect();
                    suggestionsContainer.style.left = rect.left + "px";
                    suggestionsContainer.style.top = rect.bottom + window.scrollY + "px";
                    suggestionsContainer.style.width = rect.width + "px";
                    suggestionsContainer.style.display = "block";

                } else {
                    suggestionsContainer.style.display = "none";
                }
            }

            function selectDesignator(dsg, input, suggestionsContainer) {
                input.value = dsg.designator;
                suggestionsContainer.style.display = 'none';
                const row = input.closest('tr');
                const uraianBox = row.querySelector('.uraian-overflow');
                uraianBox.textContent = dsg.uraian;
                uraianBox.setAttribute('title', dsg.uraian);
                row.querySelector('.satuan').textContent = dsg.satuan;
                row.querySelector('.harga_material').textContent = Number(dsg.harga_material).toLocaleString('id-ID');
                row.querySelector('.harga_jasa').textContent = Number(dsg.harga_jasa).toLocaleString('id-ID');

                calculateRow(row);
                updateSummary();
            }

            // Hide suggestions when clicking outside
            document.addEventListener("click", function(e) {
                if (!e.target.classList.contains("input-dsg")) {
                    const suggestions = document.getElementById("global-suggestions");
                    if (suggestions) suggestions.style.display = "none";
                }
            });

            // Jalankan pertama kali saat halaman dimuat
            document.addEventListener('DOMContentLoaded', function() {
                const tableBody = document.querySelector('#data-table tbody');
                if (tableBody.rows.length === 1) {
                    document.getElementById('removeRow').disabled = true;
                }
                checkFormCompletion();
            });

            // Jalankan setiap ada perubahan input/select
            document.querySelectorAll('select[required], input[required]').forEach(el => {
                el.addEventListener('input', checkFormCompletion);
                el.addEventListener('change', checkFormCompletion);
            });

            document.addEventListener('DOMContentLoaded', function() {
                const addRowBtn = document.getElementById('addRow');
                const tableBody = document.querySelector('#data-table tbody');

                if (addRowBtn) {
                    addRowBtn.addEventListener('click', function() {
                        const rowCount = tableBody.rows.length + 1;

                        const newRow = document.createElement('tr');
                        newRow.innerHTML = `
                <td>${rowCount}</td>
                <td style="width:150px;">
                    <div style="position: relative;">
                        <input type="text" name="designator[]" required class="input-dsg"
                            placeholder="Masukkan Designator" oninput="filterDesignators(this)" value="">
                        <div class="suggestions" style="display: none;"></div>
                    </div>
                    <input type="hidden" name="detail_id[]" value="">
                </td>
                <td class="uraian" style="width:300px;">
                    <div class="uraian-overflow" title=""></div>
                </td>
                <td class="satuan"></td>
                <td class="harga_material">0</td>
                <td class="harga_jasa">0</td>
                <td style="width:60px;">
                    <input type="number" name="volume[]" class="form-control vol-field"
                        value="0" min="0" step="1" required>
                </td>
                <td class="total_material">0</td>
                <td class="total_jasa">0</td>
            `;

                        tableBody.appendChild(newRow);

                        // Hubungkan ulang event listener volume
                        attachVolumeListener(newRow);

                        // Pastikan perhitungan langsung diperbarui
                        calculateRow(newRow);
                        updateSummary();
                    });
                } else {
                    console.error("Button #addRow tidak ditemukan");
                }
            });
        </script>
    @endsection
