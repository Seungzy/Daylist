<section class="member-card-section" aria-labelledby="membership-card-title">
    <div class="member-card-intro">
        <p class="eyebrow">YOUR PLACE IN THE COMMUNITY</p>
        <h2 id="membership-card-title">Your Membership Card</h2>
        <p class="muted">Your membership details, together in one place.</p>
        @if($cardStatus === 'Pending Activation')
            <p class="small-copy muted">Your card is ready to view. Your membership becomes active after payment and approval.</p>
        @elseif($cardStatus === 'Upcoming')
            <p class="small-copy muted">Your approved membership starts on {{ $cardMembership->valid_from->format('M d, Y') }}.</p>
        @elseif($cardStatus === 'Expired')
            <p class="small-copy muted">This membership period has ended. Contact your administrator about renewal.</p>
        @else
            <p class="small-copy muted">Your current approved membership is shown below.</p>
        @endif
    </div>
    <article class="digital-membership-card" aria-label="Digital membership card for {{ $member->full_name }}">
        <div class="member-card-top">
            <span class="member-card-brand"><span class="member-card-brand-icon" aria-hidden="true">d.</span><span class="member-card-wordmark">daylist.</span></span>
            <span class="member-card-status" data-state="{{ strtolower(str_replace(' ', '-', $cardStatus)) }}">{{ $cardStatus }}</span>
        </div>
        <p class="member-card-caption">MEMBERSHIP CARD</p>
        <h3 class="member-card-name">{{ $member->full_name }}</h3>
        <dl class="member-card-details">
            <div class="member-card-number"><dt>Member Number</dt><dd>{{ $member->membership_number }}</dd></div>
            <div><dt>Membership Level</dt><dd>{{ $cardMembership?->level->name ?? $member->level->name }}</dd></div>
            <div><dt>Category</dt><dd>{{ $member->category->name }}</dd></div>
            <div><dt>Valid From</dt><dd>{{ $cardMembership?->valid_from?->format('M d, Y') ?? 'Awaiting approval' }}</dd></div>
            <div><dt>Valid Until</dt><dd>{{ $cardMembership?->valid_until?->format('M d, Y') ?? 'Not active yet' }}</dd></div>
        </dl>
        <div class="member-card-footer"><span>MEMBER SINCE {{ $member->created_at->format('Y') }}</span><span>Belong. Connect. Grow.</span></div>
    </article>
</section>
