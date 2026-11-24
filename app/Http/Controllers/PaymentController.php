<?php

namespace App\Http\Controllers;

use App\Models\Member;
use App\Models\MembershipTransaction;
use App\Models\Payment;
use App\Services\MembershipService;
use App\Support\AdminListSort;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class PaymentController extends Controller
{
    public function index(Request $request)
    {
        $memberName = fn ($column) => Member::select('members.'.$column)->join('membership_transactions', 'membership_transactions.member_id', '=', 'members.id')->whereColumn('membership_transactions.id', 'payments.membership_transaction_id');

        return view('membership.payments.index', ['payments' => AdminListSort::apply(Payment::with('transaction.member', 'recorder'), $request, [$memberName('first_name'), $memberName('last_name')])->paginate(15)->withQueryString()]);
    }

    public function create(MembershipTransaction $transaction)
    {
        $transaction->load('member', 'payments');

        return view('membership.payments.form', compact('transaction'));
    }

    public function store(Request $request, MembershipTransaction $transaction, MembershipService $service)
    {
        $data = $request->validate(['amount' => 'required|numeric|decimal:0,2|gt:0|max:99999999.99', 'method' => ['required', Rule::in(['cash', 'bank_transfer', 'e_wallet'])], 'external_reference' => 'nullable|string|max:255|required_unless:method,cash', 'paid_at' => 'required|date_format:Y-m-d|before_or_equal:today|after_or_equal:'.$transaction->created_at->format('Y-m-d')]);
        $payment = $service->pay($transaction, $data, $request->user()->id);

        return redirect()->route('payments.show', $payment)->with('success', 'Payment recorded.');
    }

    public function show(Payment $payment)
    {
        $payment->load('transaction.member.category', 'transaction.level', 'recorder');

        return view('membership.payments.receipt', compact('payment'));
    }

    public function destroy(Request $request, Payment $payment, MembershipService $service)
    {
        $request->validate(['confirm_delete' => 'accepted']);
        $transactionId = $payment->membership_transaction_id;
        $service->deletePayment($payment);

        return redirect()->route('transactions.show', $transactionId)->with('success', 'Payment and receipt deleted. The transaction balance, approval status and report totals have been updated. The member record was kept.');
    }
}
