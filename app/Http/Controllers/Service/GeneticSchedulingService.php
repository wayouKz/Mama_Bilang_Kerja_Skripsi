<?php
namespace App\Http\Controllers\Service;

use Carbon\Carbon;
use App\Models\TGuruMapelDetails;
use App\Models\MKelas;

class GeneticSchedulingService
{
    // Parameter berdasarkan hasil pengujian optimal di jurnal [cite: 387]
    private $popSize = 30;
    private $maxGen = 75;
    private $probCross = 0.75;
    private $probMut = 0.30;

    private $hari = ['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat'];
    private $jamMulaiSekolah = "07:15";
    private $durasiJP = 45; // 1 JP = 45 Menit

    public function generate()
    {
        // Ambil data penugasan dari t_guru_mapel_detail
        $tugasDetail = TGuruMapelDetails::get();
        // 1. Inisialisasi Populasi Awal [cite: 108, 109]
        $populasi = [];
        for ($i = 0; $i < $this->popSize; $i++) {
            $populasi[] = $this->createChromosome($tugasDetail);
        }

        for ($gen = 1; $gen <= $this->maxGen; $gen++) {
            // 2. Evaluasi Fitness untuk seluruh populasi [cite: 112, 113]
            $scores = [];
            foreach ($populasi as $kromosom) {
                $scores[] = $this->calculateFitness($kromosom);
            }


            // Patokan: Jika sudah sempurna (1.0), kembalikan jadwal tersebut [cite: 19, 348]
            if (max($scores) == 1.0) {
                return $populasi[array_search(1.0, $scores)];
            }

            // 3. Seleksi menggunakan metode Roulette Wheel [cite: 118, 239]
            $nextGen = [];
            for ($i = 0; $i < $this->popSize; $i++) {
                $nextGen[] = $this->rouletteSelection($populasi, $scores);
            }

            // 4. Crossover (Persilangan) [cite: 121, 280]
            for ($i = 0; $i < $this->popSize; $i += 2) {
                if (isset($nextGen[$i + 1]) && (rand(0, 100) / 100 < $this->probCross)) {
                    $this->crossover($nextGen[$i], $nextGen[$i + 1]);
                }
            }

            // 5. Mutasi [cite: 127, 296]
            foreach ($nextGen as &$kromosom) {
                if (rand(0, 100) / 100 < $this->probMut) {
                    $this->mutate($kromosom);
                }
            }
            $populasi = $nextGen;

        }

        // Jika generasi maksimal tercapai tanpa fitness 1.0, ambil yang terbaik
        $finalScores = [];
        foreach ($populasi as $k) { $finalScores[] = $this->calculateFitness($k); }
        return $populasi[array_search(max($finalScores), $finalScores)];
    }


    private function createChromosome($tugasDetail)
{
    // Ambil semua ID kelas dari master data m_kelas
    $allKelas = MKelas::pluck('id')->toArray();

    $kromosom = [];
    foreach ($tugasDetail as $t) {
        // Berdasarkan permintaan: 1 guru = 6 JP.
        // Jika 1 pertemuan = 2 JP (akumulasi_jam), maka dibuat 3 pertemuan (gen).
        for ($pertemuan = 0; $pertemuan < 3; $pertemuan++) {
            $kromosom[] = [
                'guru_mapel_id_detail' => $t->id,
                'guru_id' => $t->guru_mapel_id,
                'mapel_id' => $t->mapel_id,
                // KELAS DITENTUKAN DI SINI: Pilih ID kelas secara acak dari master data
                'kelas_id' => $allKelas[array_rand($allKelas)],
                'hari' => $this->hari[array_rand($this->hari)],
                'jam_ke' => rand(1, 8),
            ];
        }
    }
    return $kromosom;

}


private function calculateFitness($kromosom)
{
    $cg = 0;  // Konflik Guru (Bentrok Waktu) [cite: 225]
    $ck = 0;  // Konflik Kelas (Bentrok Waktu) [cite: 225]
    $cms = 0; // Konflik Guru Mapel Sama (Guru mengajar mapel X > 1 kali di hari yang sama)
    $cks = 0; // Konflik Kelas Mapel Sama (Kelas menerima mapel X > 1 kali di hari yang sama)
    $csm = 0; // Konflik Satu Minggu (Mapel muncul > 1 kali seminggu di kelas yang sama)
    $cjm = 0; // Konflik Konsistensi Pengajar (1 Mapel di 1 kelas harus oleh guru yang sama)

    foreach ($kromosom as $i => $genA) {
        foreach ($kromosom as $j => $genB) {
            if ($i == $j) continue;

            // 1. Batasan: 1 Mapel HANYA boleh muncul 1 kali dalam seminggu untuk kelas yang sama
            // Sesuai permintaan Anda: Mencegah mapel yang sama muncul lebih dari 1 kali dalam seminggu
            if ($genA['kelas_id'] == $genB['kelas_id'] &&
                $genA['mapel_id'] == $genB['mapel_id']) {
                $csm++;
            }

            // 2. Batasan: Konsistensi Pengajar dalam Seminggu
            // Memastikan satu mapel di kelas yang sama tidak diajar oleh guru berbeda
            if ($genA['kelas_id'] == $genB['kelas_id'] &&
                $genA['mapel_id'] == $genB['mapel_id'] &&
                $genA['guru_id'] != $genB['guru_id']) {
                $cjm++;
            }

            // 3. Batasan: Guru tidak boleh mengajar mapel yang sama di hari yang sama
            if ($genA['guru_id'] == $genB['guru_id'] &&
                $genA['mapel_id'] == $genB['mapel_id'] &&
                $genA['hari'] == $genB['hari']) {
                $cms++;
            }

            // 4. Batasan: Satu Hari Satu Mapel per Kelas
            if ($genA['kelas_id'] == $genB['kelas_id'] &&
                $genA['mapel_id'] == $genB['mapel_id'] &&
                $genA['hari'] == $genB['hari']) {
                $cks++;
            }

            // 5. Batasan Standar: Bentrok Waktu (Guru/Kelas di jam & hari yang sama) [cite: 183, 184]
            if ($genA['hari'] == $genB['hari'] && $genA['jam_ke'] == $genB['jam_ke']) {
                // Pelanggaran jika guru mengajar di 2 tempat di waktu yang sama [cite: 183, 217]
                if ($genA['guru_id'] == $genB['guru_id']) $cg++;
                // Pelanggaran jika kelas memiliki 2 pelajaran di waktu yang sama [cite: 184, 219]
                if ($genA['kelas_id'] == $genB['kelas_id']) $ck++;
            }
        }
    }

    // Perhitungan nilai fitness berdasarkan total penalti konflik [cite: 19, 224]
    // Nilai 1.0 adalah solusi optimal di mana seluruh batasan terpenuhi [cite: 19, 348]
    return 1 / (1 + ($cg + $ck + $cms + $cks + $csm + $cjm));
}
    private function rouletteSelection($populasi, $scores)
    {
        $totalFitness = array_sum($scores);
        $rand = rand(0, 10000) / 10000 * $totalFitness;
        $current = 0;

        foreach ($populasi as $index => $kromosom) {
            $current += $scores[$index];
            if ($current >= $rand) {
                return $kromosom;
            }
        }
        return $populasi[0];
    }

    private function crossover(&$parent1, &$parent2)
    {
        // Menentukan titik potong secara acak [cite: 287, 288]
        $point = rand(1, count($parent1) - 1);

        for ($i = $point; $i < count($parent1); $i++) {
            // Tukar gen antara dua induk [cite: 122, 125, 302]
            $temp = $parent1[$i];
            $parent1[$i] = $parent2[$i];
            $parent2[$i] = $temp;
        }
    }

    private function mutate(&$kromosom)
    {
        // Pilih satu gen secara acak untuk dimutasi [cite: 128, 295]
        $targetGen = rand(0, count($kromosom) - 1);

        // Ubah hari atau jam secara acak [cite: 128, 295]
        $kromosom[$targetGen]['hari'] = $this->hari[array_rand($this->hari)];
        $kromosom[$targetGen]['jam_ke'] = rand(1, 8);
    }


    public function formatToDatabase($bestChromosome)
{
    $results = [];
    foreach ($bestChromosome as $gen) {
        // Tentukan jam mulai awal berdasarkan jam_ke (Blok 90 menit)
        $menitMulai = ($gen['jam_ke'] - 1) * 90;
        $jamMulai = Carbon::parse($this->jamMulaiSekolah)->addMinutes($menitMulai);

        // --- LOGIKA MELOMPAT ISTIRAHAT ---

        // 1. Cek Istirahat Pertama (09:00 - 10:00)
        // Jika jam mulai berada di antara jam 9 atau sebelum jam 10
        if ($jamMulai->between('09:00', '09:59')) {
            $jamMulai = Carbon::parse('10:00');
        }

        // 2. Cek Istirahat Kedua (12:30 - 13:00)
        if ($jamMulai->between('12:30', '12:59')) {
            $jamMulai = Carbon::parse('13:00');
        }

        $jamSelesai = $jamMulai->copy()->addMinutes(90);

        $results[] = [
            'guru_mapel_id_detail' => $gen['guru_mapel_id_detail'],
            'hari' => $gen['hari'],
            'jam_mulai' => $jamMulai->format('H:i:s'),
            'jam_selesai' => $jamSelesai->format('H:i:s'),
            'kelas_id' => $gen['kelas_id'],
            'created_at' => now(), //
            'updated_at' => now(), //
        ];
    }
    return $results;
}
}
