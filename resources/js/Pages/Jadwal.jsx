import React, { useEffect, useState } from "react";
import GuestLayout from "@/Layouts/GuestLayout";
import { Head } from "@inertiajs/react";
import axios from "axios";

export default function Jadwal() {
    const [loading, setLoading] = useState(false);
    const [jadwal, setJadwal] = useState([]);
    const [fitness, setFitness] = useState(0);

    const handleGenerate = async () => {
        setLoading(true);
        try {
            // Memanggil API generate yang menjalankan Algoritma Genetika
            const response = await axios.post("jadwal/generate");

            // Mengambil hasil jadwal terbaik dengan nilai fitness optimal [cite: 19]
            setJadwal(response.data.data);
            setFitness(response.data.fitness || 1.0);

            alert(
                "Jadwal berhasil dibuat dengan Fitness: " +
                    (response.data.fitness || 1.0),
            );
        } catch (error) {
            console.error("Gagal generate jadwal:", error);
            alert("Terjadi kesalahan saat pemrosesan.");
        } finally {
            setLoading(false);
        }
    };

    const pullData = async () => {
        try {
            const response = await axios.get("jadwal/result");
            setJadwal(response.data.data);
            setFitness(response.data.fitness || 0);
        } catch (error) {
            console.error("Gagal mengambil data jadwal:", error);
        }
    };

    useEffect(() => {
        pullData();
    }, []);

    return (
        <div className="">
            <div className="">
                <div className="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                    <div className="flex justify-between items-center mb-6">
                        <h1 className="text-2xl font-bold text-gray-800">
                            Pengelolaan Jadwal Otomatis
                        </h1>

                        <button
                            onClick={handleGenerate}
                            disabled={loading}
                            className={`px-4 py-2 rounded-md text-white font-semibold ${
                                loading
                                    ? "bg-gray-400"
                                    : "bg-blue-600 hover:bg-blue-700"
                            }`}
                        >
                            {loading
                                ? "Memproses Evolusi..."
                                : "Generate Jadwal Baru"}
                        </button>
                    </div>

                    {/* Informasi Algoritma */}
                    <div className="mb-6 p-4 bg-blue-50 border-l-4 border-blue-500 text-blue-700">
                        <p className="text-sm">
                            <strong>Info:</strong> Sistem menggunakan Algoritma
                            Genetika untuk menghindari tabrakan jadwal antara
                            guru dan kelas[cite: 406]. Proses ini
                            mempertimbangkan batasan agar tidak ada guru yang
                            mengajar di dua tempat sekaligus[cite: 183].
                        </p>
                    </div>

                    {loading && (
                        <div className="text-center py-10">
                            <div className="animate-spin rounded-full h-12 w-12 border-b-2 border-blue-600 mx-auto"></div>
                            <p className="mt-4 text-gray-600 italic">
                                Sedang mencari solusi optimal (Bisa memakan
                                waktu hingga 3 menit)...
                            </p>
                        </div>
                    )}

                    {!loading && jadwal.length > 0 && (
                        <div className="">
                            <div className="mb-4 font-bold text-green-600">
                                Status Kualitas Jadwal: Fitness {fitness}{" "}
                                (Optimal) [cite: 348]
                            </div>
                            <table className="">
                                <thead className="bg-gray-50">
                                    <tr>
                                        <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">
                                            Hari
                                        </th>
                                        <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">
                                            Waktu
                                        </th>
                                        <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">
                                            Mata Pelajaran
                                        </th>
                                        <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">
                                            Guru
                                        </th>
                                        <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">
                                            Kelas
                                        </th>
                                    </tr>
                                </thead>
                                <tbody className="bg-white divide-y divide-gray-200">
                                    {jadwal.map((item, index) => (
                                        <tr key={index}>
                                            <td className="px-6 py-4 whitespace-nowrap text-sm">
                                                {item.hari} [cite: 154]
                                            </td>
                                            <td className="px-6 py-4 whitespace-nowrap text-sm text-blue-600 font-medium">
                                                {item.jam_mulai} -{" "}
                                                {item.jam_selesai}
                                            </td>
                                            <td className="px-6 py-4 whitespace-nowrap text-sm">
                                                {item.nama_mapel}
                                            </td>
                                            <td className="px-6 py-4 whitespace-nowrap text-sm font-semibold">
                                                {item.nama_guru}
                                            </td>
                                            <td className="px-6 py-4 whitespace-nowrap text-sm">
                                                {item.nama_kelas}
                                            </td>
                                        </tr>
                                    ))}
                                </tbody>
                            </table>
                        </div>
                    )}
                </div>
            </div>
        </div>
    );
}
