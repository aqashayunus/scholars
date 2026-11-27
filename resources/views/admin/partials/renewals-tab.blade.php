{{-- Admin Dashboard > Financials & Sales > Renewals & Subscriptions (phase 3, 2026-11-27; moved into Financials and restyled 2026-11-28) --}}
@php
    $sy = \App\Services\SchoolYear::current();
    $renewalService = app(\App\Services\RenewalService::class);
    $renewalRows = $sy
        ? \App\Models\StudentRenewal::with(['student.user', 'student.parents', 'respondedBy'])->where('school_year', $sy->label)->orderBy('id')->get()
        : collect();
    $notInvited = $sy ? $renewalService->invitable($sy) : collect();
    $statusCounts = $renewalRows->countBy('status');
    $unanswered = $statusCounts[\App\Models\StudentRenewal::INVITED] ?? 0;
    $rErr = $errors->getBag('renewals');
    $val = fn (string $k, ?string $fallback = null) => old($k, $fallback);

    // One row per student: renewal rows first, then students not invited yet.
    $statusOrder = ['invited' => 1, 'confirmed' => 2, 'stopped' => 3, 'lapsed' => 4, 'declined' => 5];
    $rows = $renewalRows->sortBy(fn ($r) => [$statusOrder[$r->status] ?? 9, $r->student->user->name ?? ''])->map(fn ($r) => [
        'student' => $r->student, 'renewal' => $r, 'status' => $r->status,
    ])->values()->concat($notInvited->map(fn ($s) => ['student' => $s, 'renewal' => null, 'status' => 'not_invited']));

    $badge = [
        'not_invited' => ['Not invited', 'bg-gray-50 text-gray-500 border-gray-200'],
        'invited' => ['Awaiting reply', 'bg-blue-50 text-blue-700 border-blue-200'],
        'confirmed' => ['Continuing', 'bg-emerald-50 text-emerald-700 border-emerald-200'],
        'stopped' => ['Stopping', 'bg-rose-50 text-rose-700 border-rose-200'],
        'lapsed' => ['Paused', 'bg-amber-50 text-amber-700 border-amber-200'],
        'declined' => ['Not continuing', 'bg-gray-100 text-gray-600 border-gray-200'],
    ];
    $filters = ['all' => 'All', 'not_invited' => 'Not invited', 'invited' => 'Awaiting reply', 'confirmed' => 'Continuing', 'stopped' => 'Stopping', 'lapsed' => 'Paused', 'declined' => 'Not continuing'];
    $counts = ['all' => $rows->count(), 'not_invited' => $notInvited->count()] + $statusCounts->all();

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
    $inputCls = 'w-full bg-gray-50 border border-gray-200 rounded-xl px-3 py-2 text-xs outline-none focus:ring-2 focus:ring-blue-500';
@endphp

<div id="renewals-panel" class="space-y-4"
     x-data="{ yearModal: {{ $rErr->any() && $sy ? 'true' : 'false' }}, inviteModal: false, status: 'all', q: '' }">

    <div class="bg-white rounded-2xl border border-gray-200/80 shadow-xs p-5 space-y-4">
        {{-- Header --}}
        <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-3">
            <div>
                <h3 class="font-semibold text-base text-gray-900">Renewals &amp; Subscriptions{{ $sy ? ' · '.$sy->label : '' }}</h3>
                <p class="text-xs text-gray-400">"Continue next school year?" answers and who is billed monthly. Invoices go out automatically on the 1st of each month.</p>
            </div>
            @if($sy)
                <div class="flex flex-wrap items-center gap-2">
                    <input x-model="q" type="text" placeholder="Search student, level..." aria-label="Search students"
                           class="bg-gray-50 border border-gray-200 rounded-xl px-3 py-1.5 text-xs outline-none focus:ring-2 focus:ring-gray-300 w-48 font-normal">
                    <button type="button" @click="yearModal = true"
                            class="px-3 py-1.5 bg-white border border-gray-200 hover:bg-gray-50 text-gray-700 rounded-xl text-xs font-semibold transition cursor-pointer">
                        School Year
                    </button>
                    @if($unanswered > 0)
                        <form method="POST" action="{{ route('admin.renewals.remind') }}" class="inline"
                              onsubmit="return confirm('Send a reminder to {{ $unanswered }} family(ies) who have not replied?')">
                            @csrf
                            <button type="submit" class="px-3 py-1.5 bg-white border border-gray-200 hover:bg-gray-50 text-gray-700 rounded-xl text-xs font-semibold transition cursor-pointer">
                                Send Reminder ({{ $unanswered }})
                            </button>
                        </form>
                    @endif
                    @if($notInvited->isNotEmpty())
                        <button type="button" @click="inviteModal = true"
                                class="px-3 py-1.5 bg-blue-600 hover:bg-blue-700 text-white rounded-xl text-xs font-semibold transition cursor-pointer flex items-center gap-1.5 shadow-xs">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                            <span>Send Invitations ({{ $notInvited->count() }})</span>
                        </button>
                    @endif
                </div>
            @endif
        </div>

        @if(! $sy)
            {{-- Not set up yet --}}
            <div class="rounded-xl border border-amber-200 bg-amber-50/60 px-4 py-3 text-xs text-amber-900">
                <strong>Not set up yet.</strong> Nothing is billed, sent or auto-cancelled until the school year is saved.
            </div>
            @include('admin.partials.renewals-year-form')
        @else
            {{-- School year summary --}}
            <dl class="grid grid-cols-2 lg:grid-cols-4 gap-px bg-gray-100 rounded-xl overflow-hidden border border-gray-100 text-xs">
                <div class="bg-gray-50/60 px-4 py-2.5"><dt class="text-[10px] uppercase font-semibold text-gray-400">School year</dt><dd class="font-semibold text-gray-900 mt-0.5">{{ $sy->label }} · {{ $sy->start->format('j M Y') }} – {{ $sy->end->format('j M Y') }}</dd></div>
                <div class="bg-gray-50/60 px-4 py-2.5"><dt class="text-[10px] uppercase font-semibold text-gray-400">First month billed</dt><dd class="font-semibold text-gray-900 mt-0.5">{{ $sy->firstBilling->format('F Y') }}</dd></div>
                <div class="bg-gray-50/60 px-4 py-2.5"><dt class="text-[10px] uppercase font-semibold text-gray-400">Reply by</dt><dd class="font-semibold text-gray-900 mt-0.5">{{ $sy->replyBy?->format('j M Y') ?? '—' }}</dd></div>
                <div class="bg-gray-50/60 px-4 py-2.5"><dt class="text-[10px] uppercase font-semibold text-gray-400">Unpaid invoices</dt><dd class="font-semibold text-gray-900 mt-0.5">Cancelled {{ \App\Services\MonthlyBillingService::GRACE_DAYS }} days past due, classes paused</dd></div>
            </dl>

            {{-- Email notice --}}
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 text-xs rounded-xl px-4 py-2.5 border {{ $emailWarnings ? 'bg-amber-50 border-amber-200 text-amber-900' : 'bg-white border-gray-100 text-gray-500' }}">
                <div>
                    @forelse($emailWarnings as $w)<p>⚠ {{ $w }}</p>@empty
                        <p>Invitation and reminder wording: Email Settings › "Continue Next School Year". The email button opens the family's Manage Subscription page.</p>
                    @endforelse
                </div>
                <a href="{{ route('admin.dashboard') }}#emailsettings" onclick="if(typeof switchTab==='function') switchTab('tab-emailsettings')"
                   class="font-semibold text-blue-600 hover:text-blue-800 shrink-0">Email Settings →</a>
            </div>

            {{-- Status filter --}}
            <div class="flex flex-wrap items-center gap-1.5 text-xs font-semibold" role="group" aria-label="Filter by status">
                @foreach($filters as $key => $label)
                    @if($key === 'all' || ($counts[$key] ?? 0) > 0)
                        <button type="button" @click="status = '{{ $key }}'"
                                :class="status === '{{ $key }}' ? 'bg-blue-600 text-white shadow-xs' : 'bg-gray-50 text-gray-600 hover:bg-gray-100 border border-gray-200'"
                                class="px-3 py-1.5 rounded-xl transition cursor-pointer flex items-center gap-1.5">
                            <span>{{ $label }}</span>
                            <span class="px-1.5 rounded-full text-[10px] font-bold"
                                  :class="status === '{{ $key }}' ? 'bg-white/20 text-white' : 'bg-gray-200 text-gray-700'">{{ $counts[$key] ?? 0 }}</span>
                        </button>
                    @endif
                @endforeach
            </div>

            {{-- Students table --}}
            <div class="overflow-x-auto admin-scrollbar">
                <table class="w-full text-left text-xs" id="renewalsTable">
                    <thead>
                        <tr class="border-b border-gray-100 text-gray-400 font-medium uppercase text-[10px] bg-gray-50/50">
                            <th class="py-3 px-4">Student &amp; Parent</th>
                            <th class="py-3 px-4">Current Level</th>
                            <th class="py-3 px-4">{{ $sy->label }} Level</th>
                            <th class="py-3 px-4">Status</th>
                            <th class="py-3 px-4">Billing</th>
                            <th class="py-3 px-4">Answered</th>
                            <th class="py-3 px-4 text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 font-medium text-gray-700">
                        @forelse($rows as $row)
                            @php
                                $s = $row['student'];
                                $r = $row['renewal'];
                                $name = $s->user->name ?? 'Student #'.$s->id;
                                $parentName = $s->parents->first()?->name;
                                $search = mb_strtolower($name.' '.($parentName ?? '').' '.($s->level ?? '').' '.($r?->level ?? '').' STU-'.str_pad($s->id, 4, '0', STR_PAD_LEFT));
                                [$bLabel, $bClass] = $badge[$row['status']] ?? [ucfirst($row['status']), 'bg-gray-50 text-gray-600 border-gray-200'];
                            @endphp
                            <tr class="hover:bg-gray-50/60 transition"
                                x-show="(status === 'all' || status === @js($row['status'])) && (q === '' || @js($search).includes(q.toLowerCase()))">
                                <td class="py-3 px-4">
                                    <p class="font-semibold text-gray-900">{{ $name }}</p>
                                    <p class="text-[10px] text-gray-400 font-normal">STU-{{ str_pad($s->id, 4, '0', STR_PAD_LEFT) }}{{ $parentName ? ' · '.$parentName : '' }}</p>
                                </td>
                                <td class="py-3 px-4 whitespace-nowrap">{{ $s->level ?: '—' }}</td>
                                <td class="py-3 px-4 whitespace-nowrap {{ $r?->level ? 'text-gray-900 font-semibold' : 'text-gray-400' }}">{{ $r?->level ?: '—' }}</td>
                                <td class="py-3 px-4">
                                    <span class="inline-block px-2 py-0.5 rounded-lg border text-[10px] font-bold {{ $bClass }}">{{ $bLabel }}</span>
                                    @if($r?->pause_reason)
                                        <p class="text-[10px] text-gray-400 font-normal mt-0.5 max-w-[14rem]">{{ $r->pause_reason }}</p>
                                    @endif
                                </td>
                                <td class="py-3 px-4 whitespace-nowrap">
                                    @if($r?->billing_starts)
                                        {{ $r->billing_starts->format('M Y') }} → {{ $r->stops_after?->format('M Y') ?? 'ongoing' }}
                                    @else
                                        <span class="text-gray-400">—</span>
                                    @endif
                                </td>
                                <td class="py-3 px-4 whitespace-nowrap">
                                    @if($r?->responded_at)
                                        <p>{{ $r->responded_at->format('d M Y') }}</p>
                                        <p class="text-[10px] text-gray-400 font-normal">by {{ $r->respondedBy?->role === 'admin' ? 'admin' : ($r->respondedBy?->name ?? '—') }}</p>
                                    @elseif($r?->invited_at)
                                        <p class="text-gray-400">Invited {{ $r->invited_at->format('d M') }}</p>
                                        @if($r->reminded_at)<p class="text-[10px] text-gray-400 font-normal">Reminded {{ $r->reminded_at->format('d M') }}</p>@endif
                                    @else
                                        <span class="text-gray-400">—</span>
                                    @endif
                                </td>
                                <td class="py-3 px-4 text-right whitespace-nowrap">
                                    <div class="flex items-center justify-end gap-1.5">
                                        <a href="{{ route('subscription.go', $s->id) }}"
                                           class="px-2.5 py-1 rounded-lg border border-blue-200/60 text-[11px] font-semibold text-blue-600 hover:text-blue-800 hover:bg-blue-50 transition">
                                            {{ $row['status'] === 'confirmed' ? 'View' : 'Confirm for family' }}
                                        </a>
                                        @if($row['status'] === 'confirmed')
                                            <form method="POST" action="{{ route('admin.renewals.stop', $r->id) }}" class="inline"
                                                  onsubmit="return confirm({{ \Illuminate\Support\Js::from('Stop billing '.$name.' after this month?') }})">
                                                @csrf
                                                <button type="submit" class="px-2.5 py-1 rounded-lg border border-rose-200/60 text-[11px] font-semibold text-rose-600 hover:text-rose-800 hover:bg-rose-50 transition cursor-pointer">Stop</button>
                                            </form>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="7" class="p-10 text-center bg-gray-50/70 text-xs text-gray-400 font-medium">No students with classes yet.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        @endif
    </div>

    @if($sy)
        {{-- School year modal --}}
        <div x-show="yearModal" x-cloak class="fixed inset-0 bg-slate-900/70 backdrop-blur-xs z-50 flex items-center justify-center p-4"
             @click.self="yearModal = false" @keydown.escape.window="yearModal = false">
            <div class="bg-white rounded-3xl max-w-2xl w-full shadow-2xl flex flex-col overflow-hidden" @click.stop>
                <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100">
                    <div>
                        <h3 class="font-semibold text-base text-gray-900">School Year</h3>
                        <p class="text-xs text-gray-400">Dates used for invitations, the reply-by date and monthly billing</p>
                    </div>
                    <button type="button" @click="yearModal = false" class="text-gray-400 hover:text-gray-600 text-xl leading-none cursor-pointer" aria-label="Close">&times;</button>
                </div>
                <div class="p-6">@include('admin.partials.renewals-year-form')</div>
            </div>
        </div>

        {{-- Send invitations modal --}}
        @if($notInvited->isNotEmpty())
            <div x-show="inviteModal" x-cloak class="fixed inset-0 bg-slate-900/70 backdrop-blur-xs z-50 flex items-center justify-center p-4"
                 @click.self="inviteModal = false" @keydown.escape.window="inviteModal = false">
                <form method="POST" action="{{ route('admin.renewals.invite') }}"
                      class="bg-white rounded-3xl max-w-2xl w-full shadow-2xl flex flex-col max-h-[90vh] overflow-hidden" @click.stop>
                    @csrf
                    <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100 shrink-0">
                        <div>
                            <h3 class="font-semibold text-base text-gray-900">Send "Continue in {{ $sy->label }}?"</h3>
                            <p class="text-xs text-gray-400">To each student and their linked parents. Reply by {{ $sy->replyBy?->format('j M Y') ?? '—' }}.</p>
                        </div>
                        <button type="button" @click="inviteModal = false" class="text-gray-400 hover:text-gray-600 text-xl leading-none cursor-pointer" aria-label="Close">&times;</button>
                    </div>
                    <div class="overflow-y-auto admin-scrollbar px-6 py-3">
                        <table class="w-full text-left text-xs">
                            <thead>
                                <tr class="border-b border-gray-100 text-gray-400 font-medium uppercase text-[10px]">
                                    <th class="py-2 pr-2">Student</th>
                                    <th class="py-2 pr-2">Level</th>
                                    <th class="py-2">Parent</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100 text-gray-700">
                                @foreach($notInvited as $s)
                                    <tr>
                                        <td class="py-2 pr-2 font-semibold text-gray-900">{{ $s->user->name ?? 'Student #'.$s->id }}</td>
                                        <td class="py-2 pr-2">{{ $s->level ?: '—' }}</td>
                                        <td class="py-2">{{ $s->parents->first()?->name ?? '—' }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    <div class="px-6 py-4 border-t border-gray-100 space-y-3 shrink-0">
                        <label class="flex items-start gap-2 text-xs text-gray-800">
                            <input type="checkbox" name="confirm" value="1" required class="mt-0.5 rounded border-gray-300">
                            <span>Send {{ $notInvited->count() }} invitation(s) now.</span>
                        </label>
                        <div class="flex justify-end gap-2">
                            <button type="button" @click="inviteModal = false" class="px-3 py-2 text-xs font-semibold text-gray-600 hover:bg-gray-100 rounded-xl cursor-pointer">Cancel</button>
                            <button type="submit" class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-xl text-xs font-semibold cursor-pointer">Send Invitations</button>
                        </div>
                    </div>
                </form>
            </div>
        @endif
    @endif
</div>
