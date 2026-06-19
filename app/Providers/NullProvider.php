<?php
class NullProvider implements AiProviderInterface {
    public function chat(string $systemPrompt, string $userMessage): ?string {
        return null;
    }

    public function generateInsight(string $type, array $snap): array {
        if ($type === 'bottleneck') {
            if (!empty($snap['overloaded_designers'])) {
                $names = implode(', ', $snap['overloaded_designers']);
                return [
                    'insight_text'   => $names . ' dalam kondisi overload saat ini.',
                    'recommendation' => 'Pertimbangkan redistribusi task ke designer dengan workload di bawah 70%.',
                    'severity'       => 'warning',
                ];
            }
            if ((int)($snap['approval_stuck'] ?? 0) > 3) {
                return [
                    'insight_text'   => (int)$snap['approval_stuck'] . ' request stuck di tahap review lebih dari 48 jam.',
                    'recommendation' => 'Creative Manager perlu segera melakukan approval.',
                    'severity'       => 'warning',
                ];
            }
            return ['insight_text' => 'Tidak ada bottleneck terdeteksi saat ini.', 'recommendation' => 'Pantau terus workload tim.', 'severity' => 'info'];
        }

        if ($type === 'performance') {
            $rate = (float)($snap['completion_rate_pct'] ?? 100);
            if ($rate < 60) {
                return [
                    'insight_text'   => 'Completion rate ' . $rate . '% jauh di bawah target 80%.',
                    'recommendation' => 'Review bottleneck di tahap review dan approval.',
                    'severity'       => 'critical',
                ];
            }
            if ($rate < 80) {
                return [
                    'insight_text'   => 'Completion rate ' . $rate . '% belum mencapai target 80%.',
                    'recommendation' => 'Fokus menyelesaikan request yang sudah di tahap ready_review.',
                    'severity'       => 'warning',
                ];
            }
            return ['insight_text' => 'Completion rate ' . $rate . '% — performa tim baik bulan ini.', 'recommendation' => 'Pertahankan tempo dan kualitas.', 'severity' => 'info'];
        }

        if ($type === 'product') {
            $top = $snap['top_product'] ?? '';
            if ($top !== '') {
                return [
                    'insight_text'   => $top . ' adalah produk dengan permintaan asset terbanyak bulan ini.',
                    'recommendation' => 'Pastikan kapasitas tim cukup untuk mendukung demand produk ini.',
                    'severity'       => 'info',
                ];
            }
            return ['insight_text' => 'Belum ada data produk yang cukup.', 'recommendation' => 'Isi field product saat membuat request.', 'severity' => 'info'];
        }

        if ($type === 'priority_rec') {
            $count = (int)($snap['critical_today'] ?? 0);
            if ($count > 0) {
                return [
                    'insight_text'   => $count . ' request Critical memiliki deadline hari ini.',
                    'recommendation' => 'Prioritaskan penyelesaian request Critical sebelum request lain.',
                    'severity'       => 'critical',
                ];
            }
            return ['insight_text' => 'Tidak ada request Critical deadline hari ini.', 'recommendation' => 'Fokus pada request High yang mendekati deadline.', 'severity' => 'info'];
        }

        if ($type === 'forecast') {
            $forecast = (int)($snap['forecast_next_week'] ?? 0);
            return [
                'insight_text'   => 'Prediksi ' . $forecast . ' request masuk minggu depan berdasarkan rata-rata 4 minggu terakhir.',
                'recommendation' => $forecast > ($snap['team_capacity_tasks'] ?? 20) ? 'Persiapkan tambahan kapasitas.' : 'Kapasitas tim masih mencukupi.',
                'severity'       => $forecast > ($snap['team_capacity_tasks'] ?? 20) ? 'warning' : 'info',
            ];
        }

        return ['insight_text' => 'Analisis ' . $type . ' sedang diproses.', 'recommendation' => 'Cek kembali setelah data terkumpul.', 'severity' => 'info'];
    }
}
