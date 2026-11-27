<x-admin-layout>
    <x-slot name="title">Level Clean-up</x-slot>

    <div class="space-y-6 max-w-5xl">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
            <div>
                <h2 class="font-heading font-black text-2xl text-gray-900">Level Clean-up</h2>
                <p class="text-xs text-gray-500 font-medium mt-0.5">
                    Students who registered for more than one Form / Year. Registration now allows one level only.
                    Pick each student's real level, check the preview, then confirm.
                </p>
            </div>
            <a href="{{ route('admin.dashboard') }}" class="text-sm text-indigo-600 hover:underline font-semibold shrink-0">← Admin Dashboard</a>
        </div>

        @if(session('success'))
            <div class="bg-green-50 border border-green-200 text-green-800 rounded-xl px-4 py-3 text-sm font-medium">✓ {{ session('success') }}</div>
        @endif
        @if(session('error'))
            <div class="bg-red-50 border border-red-200 text-red-800 rounded-xl px-4 py-3 text-sm font-medium">{{ session('error') }}</div>
        @endif

        @if($candidates->isEmpty())
            <div class="bg-white rounded-2xl border border-gray-100 shadow-sm px-6 py-10 text-center">
                <p class="text-lg font-bold text-gray-900">All clear</p>
                <p class="text-sm text-gray-500 mt-1">Every student has exactly one level.</p>
            </div>
        @else
            <div class="bg-amber-50 border border-amber-200 text-amber-900 rounded-xl px-4 py-3 text-sm">
                <strong>{{ $candidates->count() }}</strong> student(s) to fix. Confirm the real Form / Year with each family first.
                Nothing changes until you press <em>Confirm</em> on the preview page.
            </div>

            <div class="space-y-4">
                @foreach($candidates as $row)
                    @php $s = $row['student']; @endphp
                    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
                        <div class="px-5 py-4 flex flex-col md:flex-row md:items-center gap-4">
                            <div class="flex-1 min-w-0">
                                <p class="font-bold text-gray-900 truncate">
                                    <span class="text-gray-400 font-semibold">#{{ $s->id }}</span>
                                    {{ $s->user->name ?? '—' }}
                                </p>
                                <p class="text-xs text-gray-500 mt-0.5">
                                    Registered {{ $s->created_at?->format('j M Y') }} ·
                                    Level <span class="font-semibold text-gray-700">{{ $s->level }}</span>
                                </p>
                                <div class="flex flex-wrap gap-1.5 mt-2">
                                    @forelse($row['enrolled_years'] as $year => $n)
                                        <span class="text-[11px] font-semibold px-2 py-0.5 rounded-full bg-gray-100 text-gray-700">
                                            Enrolled {{ $year }} × {{ $n }}
                                        </span>
                                    @empty
                                        <span class="text-[11px] font-semibold px-2 py-0.5 rounded-full bg-gray-100 text-gray-500">No enrolments</span>
                                    @endforelse
                                    @forelse($row['open_invoices'] as $inv)
                                        <span class="text-[11px] font-semibold px-2 py-0.5 rounded-full bg-blue-50 text-blue-700">
                                            {{ $inv->invoice_number }} · RM{{ number_format((float) $inv->total_amount, 2) }} unpaid
                                        </span>
                                    @empty
                                        <span class="text-[11px] font-semibold px-2 py-0.5 rounded-full bg-gray-100 text-gray-500">No unpaid invoice</span>
                                    @endforelse
                                </div>
                            </div>

                            <form method="GET" action="{{ route('admin.students.level-cleanup.preview', $s->id) }}"
                                  class="flex items-center gap-2 shrink-0">
                                <label for="level-{{ $s->id }}" class="sr-only">Real level for #{{ $s->id }}</label>
                                <select id="level-{{ $s->id }}" name="level" required
                                        class="text-sm border border-gray-200 rounded-xl px-3 py-2 focus:ring-2 focus:ring-indigo-300 outline-none">
                                    <option value="" @selected(! $row['suggested'])>Choose level…</option>
                                    @foreach($service->levelChoices($s) as $choice)
                                        <option value="{{ $choice }}" @selected($row['suggested'] === $choice)>
                                            {{ $choice }}{{ in_array($choice, $row['levels'], true) ? '' : ' (other)' }}{{ $row['suggested'] === $choice ? ' — suggested' : '' }}
                                        </option>
                                    @endforeach
                                </select>
                                <button type="submit"
                                        class="bg-indigo-600 hover:bg-indigo-700 text-white font-semibold text-sm px-4 py-2 rounded-xl transition-colors">
                                    Preview
                                </button>
                            </form>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </div>
</x-admin-layout>
