<?php

namespace App\Livewire;

use App\Models\Form;
use App\Models\FormSubmission;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class ActivityChart extends Component
{
    public string $range = '7d';

    public array $ranges = [
        '7d'  => '7 Hari',
        '1m'  => '1 Bulan',
        '3m'  => '3 Bulan',
        '6m'  => '6 Bulan',
        '1y'  => '1 Tahun',
    ];

    public function getChartDataProperty(): array
    {
        $user = Auth::user();
        $isSuper = $user->isSuperAdmin();
        $userId = Auth::id();

        $now = now();
        match ($this->range) {
            '7d' => $start = $now->copy()->subDays(6)->startOfDay(),
            '1m' => $start = $now->copy()->subDays(29)->startOfDay(),
            '3m' => $start = $now->copy()->subDays(89)->startOfDay(),
            '6m' => $start = $now->copy()->subDays(179)->startOfDay(),
            '1y' => $start = $now->copy()->subDays(364)->startOfDay(),
            default => $start = $now->copy()->subDays(6)->startOfDay(),
        };

        $query = FormSubmission::whereDate('submitted_at', '>=', $start);
        if (! $isSuper) {
            $query->whereIn('form_id', Form::where('user_id', $userId)->select('id'));
        }

        $raw = $query->selectRaw('DATE(submitted_at) as date, COUNT(*) as count')
            ->groupBy('date')
            ->pluck('count', 'date')
            ->toArray();

        $labels = [];
        $data = [];

        if (in_array($this->range, ['7d', '1m'])) {
            $period = $this->range === '7d' ? 7 : 30;
            for ($i = $period - 1; $i >= 0; $i--) {
                $date = $now->copy()->subDays($i)->format('Y-m-d');
                $labels[] = $now->copy()->subDays($i)->translatedFormat($this->range === '7d' ? 'D' : 'd M');
                $data[] = $raw[$date] ?? 0;
            }
        } else {
            if ($this->range === '3m') {
                for ($i = 12; $i >= 0; $i--) {
                    $weekStart = $now->copy()->subWeeks($i)->startOfWeek();
                    $weekEnd = $now->copy()->subWeeks($i)->endOfWeek();
                    $key = $weekStart->format('Y-m-d');
                    $labels[] = $weekStart->translatedFormat('d M');
                    $count = 0;
                    $cursor = $weekStart->copy();
                    while ($cursor->lte($weekEnd) && $cursor->lte($now)) {
                        $d = $cursor->format('Y-m-d');
                        $count += $raw[$d] ?? 0;
                        $cursor->addDay();
                    }
                    $data[] = $count;
                }
            } else {
                $months = $this->range === '6m' ? 6 : 12;
                for ($i = $months - 1; $i >= 0; $i--) {
                    $month = $now->copy()->subMonths($i);
                    $labels[] = $month->translatedFormat('M Y');
                    $monthStart = $month->copy()->startOfMonth();
                    $monthEnd = $month->copy()->endOfMonth();
                    $count = 0;
                    foreach ($raw as $dateStr => $c) {
                        $dateObj = \Carbon\Carbon::parse($dateStr);
                        if ($dateObj->gte($monthStart) && $dateObj->lte($monthEnd)) {
                            $count += $c;
                        }
                    }
                    $data[] = $count;
                }
            }
        }

        $total = array_sum($data);
        $max = max($data ?: [0]);

        return compact('labels', 'data', 'total', 'max');
    }

    public function render()
    {
        return view('livewire.activity-chart');
    }
}
