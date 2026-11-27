{{-- School year form (Financials > Renewals & Subscriptions). Expects $sy (SchoolYear|null), $val, $rErr, $inputCls. --}}
<form method="POST" action="{{ route('admin.renewals.settings') }}" class="space-y-4 text-xs">
    @csrf
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-3">
        <div>
            <label for="sy_label_{{ $sy ? 'm' : 'i' }}" class="block font-bold text-gray-700 mb-1">Name</label>
            <input id="sy_label_{{ $sy ? 'm' : 'i' }}" name="label" required maxlength="9" placeholder="2027" value="{{ $val('label', $sy?->label) }}" class="{{ $inputCls }}">
        </div>
        <div>
            <label for="sy_start_{{ $sy ? 'm' : 'i' }}" class="block font-bold text-gray-700 mb-1">Starts</label>
            <input id="sy_start_{{ $sy ? 'm' : 'i' }}" type="date" name="start" required value="{{ $val('start', $sy?->start->toDateString()) }}" class="{{ $inputCls }}">
        </div>
        <div>
            <label for="sy_end_{{ $sy ? 'm' : 'i' }}" class="block font-bold text-gray-700 mb-1">Ends</label>
            <input id="sy_end_{{ $sy ? 'm' : 'i' }}" type="date" name="end" required value="{{ $val('end', $sy?->end->toDateString()) }}" class="{{ $inputCls }}">
        </div>
        <div>
            <label for="sy_first_{{ $sy ? 'm' : 'i' }}" class="block font-bold text-gray-700 mb-1">First month billed</label>
            <input id="sy_first_{{ $sy ? 'm' : 'i' }}" type="month" name="first_billing" required value="{{ $val('first_billing', $sy?->firstBilling->format('Y-m')) }}" class="{{ $inputCls }}">
        </div>
        <div>
            <label for="sy_reply_{{ $sy ? 'm' : 'i' }}" class="block font-bold text-gray-700 mb-1">Reply by</label>
            <input id="sy_reply_{{ $sy ? 'm' : 'i' }}" type="date" name="reply_by" value="{{ $val('reply_by', $sy?->replyBy?->toDateString()) }}" class="{{ $inputCls }}">
        </div>
    </div>
    @if($rErr->any())
        <div class="text-rose-600 space-y-0.5">@foreach($rErr->all() as $e)<p>{{ $e }}</p>@endforeach</div>
    @endif
    <p class="text-gray-400">After the reply-by date, students who did not answer (or will not continue) have their classes paused. They can still log in and renew.</p>
    <div class="flex justify-end">
        <button type="submit" class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-xl font-semibold cursor-pointer">Save School Year</button>
    </div>
</form>
