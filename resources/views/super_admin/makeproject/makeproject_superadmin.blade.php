@extends('layouts.super_admin.template_superadmin')
@section('title', 'Make Project')

@section('header')
    @include('layouts.super_admin.header_superadmin')
@endsection

@section('content')

    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">

    <div class="page">

        <form action="{{ route('superadmin.makeproject_store') }}" method="POST" class="form-project">
            @csrf

            <div class="form-group">
                <div class="flex-container">
                    <label for="qe" class="label-qe">QE:</label>
                    <select id="qe" name="qe" required class="select-field" onchange="changeFontColor(this)">
                        <option value="" disabled selected hidden>Pilih Quality Enhancement (QE)</option>
                        @foreach ($qeOptions as $qe)
                            <option value="{{ $qe['id'] }}" {{ old('qe') == $qe['id'] ? 'selected' : '' }}>
                                {{ $qe['label'] }}
                            </option>
                        @endforeach
                    </select>
                    <button type="button" class="btn-make-project">Make Project</button>
                </div>
            </div>

            <div class="form-group">
                <label for="pekerjaan" class="label-pekerjaan">Pekerjaan:</label>
                <input type="text" id="pekerjaan" name="pekerjaan" required class="input-field"
                    placeholder="Masukkan nama pekerjaan">
            </div>

            <div class="form-group">
                <label for="deskripsi" class="label-deskripsi">Deskripsi:</label>
                <input type="text" id="deskripsi" name="deskripsi" required class="input-field"
                    placeholder="Masukkan deskripsi pekerjaan">
            </div>

            <div class="form-group">
                <label for="khs" class="label-khs">Nomor KHS:</label>
                <input type="text" id="khs" name="khs" required class="input-field"
                    placeholder="Masukkan nomor KHS">
            </div>

            <div class="form-group">
                <label for="pelaksana" class="label-pelaksana">Pelaksana:</label>
                <input type="text" id="pelaksana" name="pelaksana" required class="input-field"
                    placeholder="Masukkan pelaksana pekerjaan">
            </div>

            <div class="form-group">
                <label for="witel" class="label-witel">Witel:</label>
                <input type="text" id="witel" name="witel" required class="input-field"
                    placeholder="Masukkan witel">
            </div>

            <!-- Tabel untuk Menampilkan Data -->
            <div class="table-responsive">
                <table class="data-table table-bordered table-striped table-hover table-sm" id="data-table"
                    style="min-width: 100%">
                    <thead style="text-align: center;">
                        <tr>
                            <th style="min-width: 50px; border-top-left-radius: 10px;">NO</th>
                            <th>DESIGNATOR</th>
                            <th style="width: 300px;">URAIAN</th>
                            <th>SATUAN</th>
                            <th>HARGA MATERIAL</th>
                            <th>HARGA JASA</th>
                            <th>VOLUME</th>
                            <th>TOTAL MATERIAL</th>
                            <th style="min-width: 50px; border-top-right-radius: 10px;">TOTAL JASA</th>
                        </tr>
                    </thead>
                    <tbody style="text-align: center;">
                        <tr>
                            <td>1</td>
                            <td style="width: 200px;">
                                <select name="designator[]" required class="select-dsg" onchange="changeFontColor(this)">
                                    <option value="" disabled selected hidden>Pilih Designator</option>
                                    @foreach ($project_ta_doc as $dsg)
                                        <option value="{{ $dsg['id'] }}"
                                            {{ old('dsg') == $dsg['id'] ? 'selected' : '' }}>
                                            {{ $dsg['designator'] }}
                                        </option>
                                    @endforeach
                                </select>
                            </td>
                            <td class="uraian" style="width: 300px;">
                                <div class="uraian-overflow" title=""></div>
                            </td>
                            <td class="satuan"></td>
                            <td class="harga_material"></td>
                            <td class="harga_jasa"></td>
                            <td style="width: 60px;"><input type="number" name="volume[]" required class="vol-field" value="0">
                            </td>
                            <td class="total_material"></td>
                            <td class="total_jasa"></td>
                        </tr>
                    </tbody>
                </table>
                <div class="button-group">
                    <button id="addRow" style="background-color: #133995; color:#ffffff">+</button>
                    <button id="removeRow" style="background-color: #881A14; color:#ffffff"">-</button>
                </div>
                <!-- Tabel Ringkasan -->
                <div class="summary-section" style="margin-top:20px;">
                    <table class="summary-table table-bordered table-striped table-hover table-sm" id="summary-table"
                        style="min-width: 100%; margin-top:20px;">
                        <tr>
                            <td style="width: 1150px;"><strong>Material</strong></td>
                            <td class="summary-material">0</td>
                        </tr>
                        <tr>
                            <td style="width: 1150px;"><strong>Jasa</strong></td>
                            <td class="summary-jasa">0</td>
                        </tr>
                        <tr>
                            <td style="width: 1150px;"><strong>Total</strong></td>
                            <td class="summary-total">0</td>
                        </tr>
                        <tr>
                            <td style="width: 1150px;"><strong>PPN (11%)</strong></td>
                            <td class="summary-ppn">0</td>
                        </tr>
                        <tr>
                            <td style="width: 1150px;"><strong>Total Setelah PPN</strong></td>
                            <td class="summary-after-ppn">0</td>
                        </tr>
                    </table>
                </div>
            </div>
        </form>


    </div>

    <style>
        body {
            font-family: 'Poppins', sans-serif;
            background: var(--bg);
        }

        .page {
            padding: 10px 20px;
        }

        .flex-container {
            display: flex;
            /* Use flexbox for layout */
            align-items: center;
            /* Center items vertically */
            justify-content: space-between;
            /* Space between items */
            width: 100%;
            /* Full width */
        }

        .btn-make-project {
            background-color: #133995;
            color: white;
            border: none;
            border-radius: 7px;
            padding: 7px 15px;
            cursor: pointer;
            margin-left: auto;
            font-family: 'Poppins', sans-serif;
            font-weight: 500;
            border: 1.5px solid transparent;
            transition: background-color 0.3s;
        }

        .btn-make-project:hover {
            background-color: white;
            color: #133995;
            border-color: #CFD0D2;
        }

        .input-field {
            width: 300px;
            padding: 8px;
            border: 1px solid #ccc;
            border-radius: 6px;
            font-family: 'Poppins', sans-serif;
            color: #84858C;
            border-color: #133995;
        }

        .select-field {
            width: 320px;
            padding: 8px 8px 8px 12px;
            border: 1px solid #ccc;
            border-radius: 6px;
            font-family: 'Poppins', sans-serif;
            color: #84858C;
            appearance: none;
            background: url('/assets/arrow.png') no-repeat right 10px center;
            background-size: 10px;
            border-color: #133995;
        }

        .form-project {
            margin-top: 20px;
        }

        .form-group {
            margin-bottom: 20px;
            /* Space between form groups */
        }

        /* Custom margins for labels */
        .label-qe {
            margin-right: 104.6px;
            /* Specific margin for QE label */
        }

        .label-pekerjaan {
            margin-right: 42px;
            /* Specific margin for Pekerjaan label */
        }

        .label-deskripsi {
            margin-right: 50px;
            /* Specific margin for Deskripsi label */
        }

        .label-khs {
            margin-right: 32.5px;
            /* Specific margin for KHS label */
        }

        .label-pelaksana {
            margin-right: 38.5px;
            /* Specific margin for Pelaksana label */
        }

        .label-witel {
            margin-right: 81.5px;
            /* Specific margin for Witel label */
        }

        label {
            color: #133995;
            /* Label color */
            font-family: 'Poppins', sans-serif;
            /* Ensure label uses Poppins */
        }

        #data-table {
            border-collapse: collapse;
            width: 100%;
            overflow: hidden;
            border-top-left-radius: 10px;
            border-top-right-radius: 10px;
            font-family: 'Poppins', sans-serif;
            font-weight: normal !important;
            table-layout: fixed;
        }

        #data-table th,
        #data-table td {
            /* border: 1px solid #ccc; */
            padding: 10px;
            text-align: center;
        }

        #data-table th {
            background-color: #133995;
            color: #ffffff;
            height: 20px;
            /* Tinggi baris header lebih besar */
            font-family: 'Poppins', sans-serif;
            font-weight: 600 !important;
        }

        .table-responsive {
            overflow-x: auto;
        }

        .select-dsg {
            width: 200px;
            padding: 8px 8px 8px 12px;
            border: 1px solid #ccc;
            border-radius: 6px;
            font-family: 'Poppins', sans-serif;
            color: #84858C;
            appearance: none;
            background: url('/assets/arrow.png') no-repeat right 10px center;
            background-size: 10px;
            border-color: #133995;
        }

        .vol-field {
            width: 60px;
            padding: 8px 8px 8px 12px;
            border: 1px solid #ccc;
            border-radius: 6px;
            font-family: 'Poppins', sans-serif;
            color: #000000;
            background-size: 10px;
            border-color: #133995;
        }

        .uraian .uraian-overflow {
            max-width: 300px;
            white-space: nowrap;
            overflow-x: auto;
            overflow-y: hidden;
        }

        #summary-table {
            border-collapse: collapse;
            width: 100%;
            overflow: hidden;
            border-bottom-left-radius: 10px;
            border-bottom-right-radius: 10px;
            font-family: 'Poppins', sans-serif;
            font-weight: normal !important;
            background-color: #EDF7FF;
        }

        #summary-table td {
            /* border: 1px solid #ccc; */
            padding: 10px;
            text-align: center;
            font-weight: 600 !important;
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

        .button-group button:disabled {
            opacity: 0.5;
            cursor: not-allowed;
        }
    </style>

    <script>
        const dsgData = @json($project_ta_doc);

        // Fungsi untuk menghitung total material dan jasa dalam satu baris
        function calculateRow(row) {
            const volume = parseFloat(row.querySelector('.vol-field').value) || 0;
            const hargaMaterial = parseFloat(row.querySelector('.harga_material').textContent.replace(/\./g, '').replace(
                /,/g, '')) || 0;
            const hargaJasa = parseFloat(row.querySelector('.harga_jasa').textContent.replace(/\./g, '').replace(/,/g,
                '')) || 0;

            const totalMaterial = volume * hargaMaterial;
            const totalJasa = volume * hargaJasa;

            row.querySelector('.total_material').textContent = totalMaterial.toLocaleString('id-ID');
            row.querySelector('.total_jasa').textContent = totalJasa.toLocaleString('id-ID');

            // Panggil updateSummary() di luar fungsi ini
        }

        // Fungsi untuk mengaitkan event listener ke field volume
        function attachVolumeListener(row) {
            const volumeInput = row.querySelector('.vol-field');
            volumeInput.addEventListener('input', () => {
                calculateRow(row);
                updateSummary(); // Panggil updateSummary() setelah perhitungan
            });
        }

        // Mengaitkan listener ke setiap baris yang sudah ada di tabel
        document.querySelectorAll('#data-table tbody tr').forEach(row => attachVolumeListener(row));

        // Event listener untuk menambahkan baris baru
        document.getElementById('addRow').addEventListener('click', function() {
            const tableBody = document.querySelector('#data-table tbody');
            const rowCount = tableBody.rows.length + 1;

            const optionsHtml = dsgData
                .map(d => `<option value="${d.id}">${d.designator}</option>`)
                .join('');

            const newRow = `
    <tr>
        <td>${rowCount}</td>
        <td style="width:200px;">
            <select name="designator[]" required class="select-dsg" onchange="changeFontColor(this)">
                <option value="" disabled selected hidden>Pilih Designator</option>
                ${optionsHtml}
            </select>
        </td>
        <td class="uraian" style="width:300px;">
            <div class="uraian-overflow" title=""></div>
        </td>
        <td class="satuan"></td>
        <td class="harga_material"></td>
        <td class="harga_jasa"></td>
        <td style="width:60px;"><input type="number" name="volume[]" class="vol-field" value="0"></td>
        <td class="total_material"></td>
        <td class="total_jasa"></td>
    </tr>
    `;
            tableBody.insertAdjacentHTML('beforeend', newRow);

            // Mengaitkan listener untuk volume di baris baru
            attachVolumeListener(tableBody.lastElementChild);

            // Enable tombol removeRow karena sekarang ada lebih dari 1 row
            document.getElementById('removeRow').disabled = false;
        });

        // Event listener untuk menghapus baris terakhir
        document.getElementById('removeRow').addEventListener('click', function() {
            const tableBody = document.querySelector('#data-table tbody');
            if (tableBody.rows.length > 1) {
                tableBody.deleteRow(tableBody.rows.length - 1);
            }
            // Jika tersisa 1 row, disable tombol removeRow
            if (tableBody.rows.length === 1) {
                document.getElementById('removeRow').disabled = true;
            }
        });

        // Fungsi untuk mengubah warna font saat memilih designator
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

                    calculateRow(row); // Hitung total jika sudah pilih designator
                    updateSummary(); // Panggil updateSummary() setelah mengubah harga
                }
            } else {
                selectElement.style.color = '';
            }
        }

        // Fungsi untuk memeriksa apakah semua form terisi
        function checkFormCompletion() {
            let allFilled = true;

            // Cek semua select yang wajib diisi
            document.querySelectorAll('select[required]').forEach(select => {
                if (!select.value) {
                    allFilled = false;
                }
            });

            // Cek semua input yang wajib diisi
            document.querySelectorAll('input[required]').forEach(input => {
                if (!input.value.trim()) {
                    allFilled = false;
                }
            });

            // Enable tombol + dan - jika semua terisi
            document.getElementById('addRow').disabled = !allFilled;
            document.getElementById('removeRow').disabled = !allFilled;

            // // Enable/disable select dan input dalam tabel
            // const dsgFields = document.querySelectorAll('.select-dsg');
            // const volumeFields = document.querySelectorAll('.vol-field');

            // dsgFields.forEach(field => {
            //     field.disabled = !allFilled;
            // });

            // volumeFields.forEach(field => {
            //     field.disabled = !allFilled;
            // });

            // Cek apakah field harga bisa diubah
            const hargaMaterials = document.querySelectorAll('.harga_material');
            const hargaJasas = document.querySelectorAll('.harga_jasa');

            hargaMaterials.forEach(field => {
                field.contentEditable = allFilled;
                if (!allFilled) {
                    field.textContent = ''; // Kosongkan jika tidak diizinkan
                }
            });

            hargaJasas.forEach(field => {
                field.contentEditable = allFilled;
                if (!allFilled) {
                    field.textContent = ''; // Kosongkan jika tidak diizinkan
                }
            });
        }

        // Fungsi untuk memperbarui ringkasan total
        function updateSummary() {
            let totalMaterial = 0;
            let totalJasa = 0;

            document.querySelectorAll('#data-table tbody tr').forEach(row => {
                const material = parseFloat(row.querySelector('.total_material').textContent.replace(/\./g, '')
                    .replace(/,/g, '')) || 0;
                const jasa = parseFloat(row.querySelector('.total_jasa').textContent.replace(/\./g, '').replace(
                    /,/g, '')) || 0;
                totalMaterial += material;
                totalJasa += jasa;
            });

            const total = totalMaterial + totalJasa;
            const ppn = total * 0.11;
            const totalAfterPpn = total - ppn;

            document.querySelector('.summary-material').textContent = totalMaterial.toLocaleString('id-ID');
            document.querySelector('.summary-jasa').textContent = totalJasa.toLocaleString('id-ID');
            document.querySelector('.summary-total').textContent = total.toLocaleString('id-ID');
            document.querySelector('.summary-ppn').textContent = ppn.toLocaleString('id-ID');
            document.querySelector('.summary-after-ppn').textContent = totalAfterPpn.toLocaleString('id-ID');
        }

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
    </script>


@endsection
