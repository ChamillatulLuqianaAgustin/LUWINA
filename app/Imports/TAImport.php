<?php

namespace App\Imports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;

class TAImport implements ToCollection
{
    public $headerData = [];
    public $detailData = [];

    public function collection(Collection $rows)
    {
        $this->detailData = [];

        // DETEKSI FORMAT BARU
        $isNewFormat = strtoupper(trim($rows[0][0] ?? '')) == 'NAMA PROJECT:';

        if ($isNewFormat) {

            // ======================
            // FORMAT BARU
            // ======================

            $this->headerData = [
                'ta_project_pekerjaan' => trim($rows[0][1] ?? ''),
                'ta_project_ihld'       => trim($rows[1][1] ?? ''),
                'ta_project_pelaksana' => trim($rows[2][1] ?? ''),
                'ta_project_witel'     => trim($rows[3][1] ?? ''),
            ];

            // Mulai baca setelah header DESIGNATOR | HARGA | VOLUME
            for ($i = 6; $i < count($rows); $i++) {

                $designator = trim($rows[$i][0] ?? '');

                $hargaMaterial = $rows[$i][1] ?? 0;
                $hargaJasa     = $rows[$i][2] ?? 0;
                $volume        = $rows[$i][3] ?? null;

                if ($designator == '' || $volume === null || $volume === '') {
                    continue;
                }

                // membersihkan format angka
                $hargaMaterial = str_replace(['Rp', '.', ',', ' '], '', $hargaMaterial);
                $hargaJasa     = str_replace(['Rp', '.', ',', ' '], '', $hargaJasa);

                $this->detailData[] = [
                    'designator'      => $designator,
                    'harga_material'  => (float)$hargaMaterial,
                    'harga_jasa'      => (float)$hargaJasa,
                    'volume'          => (float)$volume,
                ];
            }
        } else {

            // ======================
            // FORMAT LAMA
            // ======================

            $this->headerData = [
                'ta_project_pekerjaan' => $rows[1][3] ?? '',
                'ta_project_ihld'       => $rows[2][3] ?? '',
                'ta_project_pelaksana' => isset($rows[3][3]) ? str_replace(':', '', trim($rows[3][3])) : '',
                'ta_project_witel'     => isset($rows[4][3]) ? str_replace(':', '', trim($rows[4][3])) : '',
            ];

            for ($i = 11; $i < count($rows); $i++) {

                $designator = trim($rows[$i][2] ?? '');
                $volume     = $rows[$i][7] ?? null;

                if ($designator == '' || $volume === null || $volume === '') {
                    continue;
                }

                $this->detailData[] = [
                    'designator' => $designator,
                    'volume'     => (float)$volume,
                ];
            }
        }
    }
}
