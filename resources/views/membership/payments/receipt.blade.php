@extends('layouts.membership')
@section('title','Payment Receipt')
@section('subtitle','A clear record for your member and your files.')
@section('actions')<a class="btn danger" href="#delete-payment">Delete Payment</a><a class="btn secondary" href="{{ route('transactions.show',$payment->transaction) }}">View Transaction</a><button class="btn" data-print type="button">Print Receipt</button>@endsection
@section('content')<article class="panel receipt"><div class="receipt-heading"><div><p class="eyebrow">DAYLIST MEMBERSHIP</p><h2>Payment Receipt</h2><p class="mono">{{ $payment->receipt_number }}</p></div><span class="badge badge-paid">Recorded</span></div><dl class="details"><div><dt>Received From</dt><dd>{{ $payment->transaction->member->full_name }}</dd></div><div><dt>Membership Number</dt><dd class="mono">{{ $payment->transaction->member->membership_number }}</dd></div><div><dt>Transaction Reference</dt><dd class="mono">{{ $payment->transaction->reference_number }}</dd></div><div><dt>Category / Level</dt><dd>{{ $payment->transaction->member->category->name }} / {{ $payment->transaction->level->name }}</dd></div><div><dt>Payment Date</dt><dd>{{ $payment->paid_at->format('F d, Y') }}</dd></div><div><dt>Payment Method</dt><dd>{{ ucwords(str_replace('_',' ',$payment->method)) }}</dd></div><div><dt>External Reference</dt><dd>{{ $payment->external_reference ?: 'Not applicable' }}</dd></div><div><dt>Recorded By</dt><dd>{{ $payment->recorder->name }}</dd></div></dl><div class="receipt-total"><span>Amount received</span><strong>PHP {{ number_format($payment->amount,2) }}</strong></div><p class="muted">This receipt acknowledges the recorded payment. Membership approval is tracked separately on the transaction record.</p><div class="receipt-signature">Authorized signature</div></article>
<section class="panel no-print" id="delete-payment" aria-labelledby="delete-payment-title">
    <h2 id="delete-payment-title">Delete Payment</h2>
    <p>Permanently remove payment <strong class="mono">{{ $payment->receipt_number }}</strong> for <strong>₱{{ number_format($payment->amount, 2) }}</strong> and its receipt record.</p>
    <p class="muted">The member and transaction are kept. The balance and report totals update automatically. If the transaction is no longer fully paid, its approval and membership period are removed until it is paid and approved again. Downloaded or printed receipts are unaffected.</p>
    <form method="POST" action="{{ route('payments.destroy', $payment) }}" data-confirm="Permanently delete this payment and receipt? The transaction balance and approval will be recalculated. This cannot be undone.">
        @csrf @method('DELETE')
        <label class="check-label"><input type="checkbox" name="confirm_delete" value="1" required><span>I confirm deletion of this payment and its receipt record.</span></label>
        <div class="form-actions"><button class="btn danger" type="submit">Delete Payment</button></div>
    </form>
</section>
@endsection
