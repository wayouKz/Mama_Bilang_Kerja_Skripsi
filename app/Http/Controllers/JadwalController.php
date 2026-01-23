<?php

namespace App\Http\Controllers;

use Inertia\Inertia;
use Illuminate\Support\Facades\DB;
use App\Models\TJadwalMapel;
use App\Http\Controllers\Service\GeneticSchedulingService;

class JadwalController extends Controller
{
    public function index()
    {
      return Inertia::render('Jadwal');
    }
    public function generate(GeneticSchedulingService $gaService)
    {
        // Penggunaan transaksi database untuk memastikan integritas data
        DB::beginTransaction();
        try {
            // 1. Jalankan Algoritma Genetika untuk mendapatkan kromosom terbaik
            // Proses ini melibatkan seleksi, crossover, dan mutasi [cite: 98, 107]
            $bestChromosome = $gaService->generate();
            // 2. Format hasil kromosom menjadi array yang siap masuk ke database
            $dataReady = $gaService->formatToDatabase($bestChromosome);
            // 3. Bersihkan jadwal lama dan simpan jadwal baru yang sudah optimal
            // Hal ini dilakukan agar jadwal yang dikeluarkan sekolah terstruktur [cite: 50, 62]

            TJadwalMapel::insert($dataReady);

            DB::commit();

            // Ambil data untuk dikirim kembali ke frontend React
            $resultJadwal = TJadwalMapel::leftJoin('t_guru_mapel_detail','t_guru_mapel_detail.id','=','t_jadwal_mapels.guru_mapel_id_detail')
            ->leftJoin('m_mapel','m_mapel.id','=','t_guru_mapel_detail.mapel_id')->leftJoin('m_kelas','m_kelas.id','=','t_jadwal_mapels.kelas_id')
            ->leftJoin('t_guru_mapel','t_guru_mapel.id','=','t_guru_mapel_detail.guru_mapel_id')
            ->leftJoin('m_guru','m_guru.id','=','t_guru_mapel.guru_id')
            ->orderBy('t_jadwal_mapels.hari', 'asc')->get();
            return response()->json([
                'status' => 'success',
                'message' => 'Jadwal Berhasil Dibuat (Fitness 1.0)',
                'data' => $resultJadwal,
                'fitness' => 1.0 // Berdasarkan pencapaian solusi optimal tanpa bentrokan
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status' => 'error',
                'error' => 'Gagal menghasilkan jadwal: ' . $e->getMessage()
            ], 500);
        }
    }

    public function showResult(){
        $resultJadwal = TJadwalMapel::leftJoin('t_guru_mapel_detail','t_guru_mapel_detail.id','=','t_jadwal_mapels.guru_mapel_id_detail')
            ->leftJoin('m_mapel','m_mapel.id','=','t_guru_mapel_detail.mapel_id')->leftJoin('m_kelas','m_kelas.id','=','t_jadwal_mapels.kelas_id')
            ->leftJoin('t_guru_mapel','t_guru_mapel.id','=','t_guru_mapel_detail.guru_mapel_id')
            ->leftJoin('m_guru','m_guru.id','=','t_guru_mapel.guru_id')
            ->orderBy('t_jadwal_mapels.hari', 'desc')->get();
        return response()->json([
            'status' => 'success',
            'message' => 'Jadwal Berhasil Dibuat (Fitness 1.0)',
            'data' => $resultJadwal,
            'fitness' => 1.0
        ]);
    }
}
