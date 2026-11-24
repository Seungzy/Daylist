<?php

namespace App\Http\Controllers;

use App\Models\Member;
use App\Models\MembershipLevel;
use App\Models\MembershipTransaction;
use App\Models\Payment;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class DashboardController extends Controller
{
    public function __invoke(Request $request)
    {
        $months = (int) ($request->validate(['months' => ['nullable', Rule::in([6, 12])]])['months'] ?? 6);
        $through = today();
        $periodStart = $through->copy()->startOfMonth()->subMonthsNoOverflow($months - 1);
        $monthlyPayments = Payment::where('paid_at', '>=', $periodStart->toDateString())->where('paid_at', '<', $through->copy()->addDay()->toDateString())
            ->selectRaw('SUBSTR(paid_at, 1, 7) as month, SUM(amount) as collected, COUNT(*) as payment_count')
            ->groupByRaw('SUBSTR(paid_at, 1, 7)')->get()->keyBy('month');
        $paymentSeries = collect(range(0, $months - 1))->map(function ($offset) use ($periodStart, $monthlyPayments) {
            $month = $periodStart->copy()->addMonthsNoOverflow($offset);
            $payments = $monthlyPayments->get($month->format('Y-m'));

            return ['month' => $month->format('Y-m'), 'label' => $month->format('M'), 'full_label' => $month->format('F Y'),
                'cents' => (int) round((float) ($payments?->collected ?? 0) * 100), 'count' => (int) ($payments?->payment_count ?? 0)];
        });
        $periodCents = $paymentSeries->sum('cents');
        $periodPayments = $paymentSeries->sum('count');
        $chartMaximum = max(100, (int) ceil($paymentSeries->max('cents') / 10000) * 100);
        $chartPoints = $paymentSeries->map(fn ($point, $index) => $point + [
            'x' => round(78 + ($index * 652 / ($months - 1)), 2),
            'y' => round(232 - ($point['cents'] / 100 / $chartMaximum * 202), 2),
        ]);
        $chartLine = $chartPoints->map(fn ($point) => $point['x'].','.$point['y'])->implode(' ');

        $total = Member::count();
        $colors = ['#023e8a', '#0077b6', '#37a3b7', '#ff8a5b', '#8075c7', '#c89c38'];
        $levelDistribution = MembershipLevel::withCount('members')->orderBy('fee')->orderBy('id')->get()
            ->filter(fn ($level) => $level->members_count > 0)->values()->map(fn ($level, $index) => [
                'id' => $level->id, 'name' => $level->name, 'count' => $level->members_count,
                'percent' => $total ? round($level->members_count / $total * 100, 1) : 0,
                'color' => $colors[$index % count($colors)],
            ]);
        $running = 0;
        $donutGradient = $levelDistribution->map(function ($level) use (&$running, $total) {
            $start = $running;
            $running += $level['count'] / max(1, $total) * 100;

            return $level['color'].' '.round($start, 5).'% '.round($running, 5).'%';
        })->implode(', ');

        return view('membership.dashboard', compact('total', 'months', 'through', 'periodStart', 'paymentSeries', 'periodCents', 'periodPayments', 'chartMaximum', 'chartPoints', 'chartLine', 'levelDistribution', 'donutGradient') + [
            'active' => Member::withActiveMembership()->count(),
            'pending' => MembershipTransaction::where('status', 'pending')->count(),
            'collected' => Payment::sum('amount'),
            'recent' => MembershipTransaction::with('member', 'level', 'payments')->latest('id')->limit(6)->get(),
        ]);
    }
}
