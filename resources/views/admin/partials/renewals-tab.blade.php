{{-- Admin Dashboard > Financials & Sales > Renewals & Subscriptions (phase 3, 2026-11-27; moved out of its own tab 2026-11-28) --}}
@php
    $sy = \App\Services\SchoolYear::current();
    $renewalService = app(\App\Services\RenewalService::class);
    $renewalRows = $sy
        ? \App\Models\StudentRenewal::with(['student.user', 'respondedBy'])->where('school_year', $sy->label)->orderBy('status')->orderBy('id')->get()
        : collect();
    $notInvited = $sy ? $renewalService->invitable($sy) : collect();
    $statusCounts = $renewalRows->countBy('status');
    $unanswered = $statusCounts[\App\Models\StudentRenewal::INVITED] ?? 0;
    $rErr = $errors->getBag('renewals');
    $val = fn (string $k, ?string $fallback = null) => old($k, $fallback);
    $badge = [
        'invited' => 'bg-blue-50 text-blue-700',
        'confirmed' => 'bg-emerald-50 text-emerald-700',
        'declined' => 'bg-gray-100 text-gray-600',
        'lapsed' => 'bg-amber-50 text-amber-800',
        'stopped' => 'bg-rose-50 text-rose-700',
    ];
@endphp
<div id="renewals-panel" class="space-y-6">
    <div class="flex flex-col sm:flex-row sm:items-end justify-between gap-3">
        <div>
            <h2 class="font-semibold text-lg text-gray-900">Renewals{{ $sy ? ' · '.$sy->label : '' }}</h2>
            <p class="text-xs text-gray-400 max-w-2xl">
                Families confirm "Continue in {{ $sy->label ?? 'the new year' }}?" (Form / Year, subjects, package) and get the first invoice.
                After that, invoices go out automatically on the 1st of each month. Every morning at 07:00 the system also cancels invoices
                left unpaid {{ \App\Services\MonthlyBillingService::GRACE_DAYS }} days past due (nothing paid) and pauses those students' classes.
                Paused families can still log in and come back.
            </p>
        </div>
    </div>

    {{-- Setup --}}
    <form method="POST" action="{{ route('admin.renewals.settings') }}" class="bg-white rounded-2xl border border-gray-200/80 shadow-xs p-5 space-y-4 text-xs">
        @csrf
        <div class="flex items-center justify-between">
            <h3 class="font-semibold text-sm text-gray-900">School year</h3>
            <span class="font-semibold {{ $sy ? 'text-emerald-700' : 'text-amber-700' }}">{{ $sy ? 'Set up' : 'Not set up yet: nothing is billed or sent' }}</span>
        </div>
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-3">
            <div>
                <label for="sy_label" class="block font-bold text-gray-700 mb-1">Name</label>
                <input id="sy_label" name="label" required maxlength="9" placeholder="2027" value="{{ $val('label', $sy?->label) }}"
                       class="w-full bg-gray-50 border border-gray-200 rounded-xl px-3 py-2 outline-none focus:ring-2 focus:ring-blue-500">
            </div>
            <div>
                <label for="sy_start" class="block font-bold text-gray-700 mb-1">Starts</label>
                <input id="sy_start" type="date" name="start" required value="{{ $val('start', $sy?->start->toDateString()) }}"
                       class="w-full bg-gray-50 border border-gray-200 rounded-xl px-3 py-2 outline-none focus:ring-2 focus:ring-blue-500">
            </div>
            <div>
                <label for="sy_end" class="block font-bold text-gray-700 mb-1">Ends</label>
                <input id="sy_end" type="date" name="end" required value="{{ $val('end', $sy?->end->toDateString()) }}"
                       class="w-full bg-gray-50 border border-gray-200 rounded-xl px-3 py-2 outline-none focus:ring-2 focus:ring-blue-500">
            </div>
            <div>
                <label for="sy_first" class="block font-bold text-gray-700 mb-1">First month billed</label>
                <input id="sy_first" type="month" name="first_billing" required value="{{ $val('first_billing', $sy?->firstBilling->format('Y-m')) }}"
                       class="w-full bg-gray-50 border border-gray-200 rounded-xl px-3 py-2 outline-none focus:ring-2 focus:ring-blue-500">
            </div>
            <div>
                <label for="sy_reply" class="block font-bold text-gray-700 mb-1">Reply by</label>
                <input id="sy_reply" type="date" name="reply_by" value="{{ $val('reply_by', $sy?->replyBy?->toDateString()) }}"
                       class="w-full bg-gray-50 border border-gray-200 rounded-xl px-3 py-2 outline-none focus:ring-2 focus:ring-blue-500">
            </div>
        </div>
        @if($rErr->any())
            <div class="text-rose-600 space-y-0.5">@foreach($rErr->all() as $e)<p>{{ $e }}</p>@endforeach</div>
        @endif
        <p class="text-gray-400">After the reply-by date, families who did not answer (or said they won't continue) have their classes paused.</p>
        <div class="flex justify-end">
            <button type="submit" class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-xl font-semibold cursor-pointer">Save school year</button>
        </div>
    </form>

    @php
        $emailWarnings = [];
        foreach ([\App\Services\RenewalService::AUTOMATION_INVITE => 'invitation', \App\Services\RenewalService::AUTOMATION_REMIND => 'reminder'] as $autoKey => $autoLabel) {
            $auto = \App\Services\RenewalService::automation($autoKey);
            if (! $auto) {
                $emailWarnings[] = "The {$autoLabel} email is missing (run the latest migration).";
            } elseif (! $auto->is_enabled) {
                $emailWarnings[] = "The {$autoLabel} email is switched off in Email Settings, so nothing will be sent.";
            } elseif (! $auto->template || $auto->template->status !== \App\Models\EmailTemplate::STATUS_ACTIVE) {
                $emailWarnings[] = "The {$autoLabel} email has no active template in Email Settings.";
            }
        }
    @endphp
    <div class="text-xs {{ $emailWarnings ? 'bg-amber-50 border border-amber-200 text-amber-900' : 'bg-gray-50 border border-gray-200 text-gray-600' }} rounded-xl px-4 py-3 flex flex-col sm:flex-row sm:items-center justify-between gap-2">
        <div>
            @forelse($emailWarnings as $w)<p>⚠ {{ $w }}</p>@empty
                <p>The wording of the invitation and reminder emails is edited in Email Settings (automations "Continue Next School Year"). Their button opens the family's Manage Subscription page.</p>
            @endforelse
        </div>
        <a href="{{ route('admin.dashboard') }}#emailsettings" onclick="if(typeof switchTab==='function') switchTab('tab-emailsettings')"
           class="font-bold hover:underline shrink-0">Open Email Settings →</a>
    </div>


    @if($sy)
        {{-- Counts + send --}}
        <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-3 text-xs">
            <div class="bg-white rounded-2xl border border-gray-200/80 p-4"><p class="text-gray-400 font-semibold">Not invited</p><p class="text-2xl font-black text-gray-900">{{ $notInvited->count() }}</p></div>
            @foreach(\App\Models\StudentRenewal::LABELS as $key => $label)
                <div class="bg-white rounded-2xl border border-gray-200/80 p-4"><p class="text-gray-400 font-semibold">{{ $label }}</p><p class="text-2xl font-black text-gray-900">{{ $statusCounts[$key] ?? 0 }}</p></div>
            @endforeach
        </div>

        <div class="flex flex-col lg:flex-row gap-3">
            @if($notInvited->isNotEmpty())
                <form method="POST" action="{{ route('admin.renewals.invite') }}" class="flex-1 bg-white rounded-2xl border border-gray-200/80 shadow-xs p-4 space-y-3 text-xs">
                    @csrf
                    <p class="font-semibold text-gray-900 text-sm">Send "Continue in {{ $sy->label }}?" to {{ $notInvited->count() }} student(s)</p>
                    <p class="text-gray-500">To the student and their linked parents:
                        {{ $notInvited->take(6)->map(fn ($s) => $s->user->name ?? '#'.$s->id)->implode(', ') }}{{ $notInvited->count() > 6 ? '…' : '' }}</p>
                    <label class="flex items-start gap-2"><input type="checkbox" name="confirm" value="1" required class="mt-0.5 rounded border-gray-300">
                        <span>Send the emails now.</span></label>
                    <button type="submit" class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-xl font-semibold cursor-pointer">Send invitations</button>
                </form>
            @endif
            @if($unanswered > 0)
                <form method="POST" action="{{ route('admin.renewals.remind') }}" class="flex-1 bg-white rounded-2xl border border-gray-200/80 shadow-xs p-4 space-y-3 text-xs"
                      onsubmit="return confirm('Send a reminder to {{ $unanswered }} family(ies) who have not replied?')">
                    @csrf
                    <p class="font-semibold text-gray-900 text-sm">{{ $unanswered }} family(ies) have not replied</p>
                    <p class="text-gray-500">Sends the same email again, marked "Reminder".</p>
                    <button type="submit" class="px-4 py-2 bg-white border border-gray-200 hover:bg-gray-50 text-gray-800 rounded-xl font-semibold cursor-pointer">Send reminder</button>
                </form>
            @endif
        </div>

        {{-- Table --}}
        <div class="bg-white rounded-2xl border border-gray-200/80 shadow-xs p-5 space-y-3">
            <h3 class="font-semibold text-sm text-gray-900">Students</h3>
            @if($renewalRows->isEmpty())
                <p class="text-xs text-gray-400">No invitations sent yet.</p>
            @else
                <div class="overflow-x-auto admin-scrollbar">
                    <table class="w-full text-left text-xs">
                        <thead>
                            <tr class="border-b border-gray-100 text-gray-400 uppercase text-[10px] bg-gray-50/50">
                                <th class="py-2.5 px-3">Student</th>
                                <th class="py-2.5 px-3">Now</th>
                                <th class="py-2.5 px-3">Status</th>
                                <th class="py-2.5 px-3">{{ $sy->label }}</th>
                                <th class="py-2.5 px-3">Billing</th>
                                <th class="py-2.5 px-3">Answered</th>
                                <th class="py-2.5 px-3 text-right">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 text-gray-700">
                            @foreach($renewalRows as $r)
                                <tr>
                                    <td class="py-2.5 px-3 font-semibold text-gray-900">#{{ $r->student_id }} {{ $r->student->user->name ?? '—' }}</td>
                                    <td class="py-2.5 px-3">{{ $r->student->level ?? '—' }}</td>
                                    <td class="py-2.5 px-3">
                                        <span class="px-2 py-0.5 rounded-full font-semibold {{ $badge[$r->status] ?? 'bg-gray-100 text-gray-600' }}">{{ $r->label() }}</span>
                                        @if($r->pause_reason)<p class="text-[10px] text-gray-400 mt-0.5">{{ $r->pause_reason }}</p>@endif
                                    </td>
                                    <td class="py-2.5 px-3">{{ $r->level ?? '—' }}</td>
                                    <td class="py-2.5 px-3 whitespace-nowrap">
                                        @if($r->billing_starts)
                                            {{ $r->billing_starts->format('M Y') }} → {{ $r->stops_after?->format('M Y') ?? 'ongoing' }}
                                        @else — @endif
                                    </td>
                                    <td class="py-2.5 px-3 whitespace-nowrap">
                                        {{ $r->responded_at?->format('d M') ?? '—' }}
                                        @if($r->respondedBy)<span class="text-gray-400">by {{ $r->respondedBy->role === 'admin' ? 'admin' : $r->respondedBy->name }}</span>@endif
                                    </td>
                                    <td class="py-2.5 px-3 text-right whitespace-nowrap space-x-2">
                                        <a href="{{ route('renew.show', $r->token) }}" class="text-blue-600 hover:underline font-semibold">
                                            {{ $r->status === \App\Models\StudentRenewal::CONFIRMED ? 'View' : 'Confirm for family' }}
                                        </a>
                                        @if($r->status === \App\Models\StudentRenewal::CONFIRMED)
                                            <form method="POST" action="{{ route('admin.renewals.stop', $r->id) }}" class="inline"
                                                  onsubmit="return confirm('Stop billing after this month?')">
                                                @csrf
                                                <button type="submit" class="text-rose-600 hover:underline font-semibold cursor-pointer">Stop</button>
                                            </form>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    @endif
</div>
