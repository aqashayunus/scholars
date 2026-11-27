<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Auth\StudentRegistrationController;
use App\Http\Controllers\Controller;
use App\Models\Student;
use App\Services\Admin\LevelCleanupService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

/**
 * Admin > Students > Level clean-up: reduce students who registered for
 * several levels to one. List -> preview for a chosen level -> confirm.
 */
class LevelCleanupController extends Controller
{
    public function __construct(private LevelCleanupService $service) {}

    public function index(): View
    {
        return view('admin.students.level-cleanup.index', [
            'candidates' => $this->service->candidates(),
            'service' => $this->service,
        ]);
    }

    public function preview(Request $request, Student $student): View
    {
        $level = $request->validate([
            'level' => ['required', 'string', Rule::in(StudentRegistrationController::REGISTRABLE_LEVELS)],
        ])['level'];

        return view('admin.students.level-cleanup.preview', [
            'plan' => $this->service->plan($student, $level),
            'choices' => $this->service->levelChoices($student),
        ]);
    }

    public function apply(Request $request, Student $student): RedirectResponse
    {
        $data = $request->validate([
            'level' => ['required', 'string', Rule::in(StudentRegistrationController::REGISTRABLE_LEVELS)],
            'confirm' => ['accepted'],
        ], [
            'confirm.accepted' => 'Tick the box to confirm you have checked the level with the family.',
        ]);

        $name = $student->user->name ?? "Student #{$student->id}";

        try {
            $adminId = Auth::id();
            $result = $this->service->apply($student, $data['level'], $adminId === null ? null : (int) $adminId);
        } catch (\RuntimeException $e) {
            return redirect()
                ->route('admin.students.level-cleanup.preview', ['student' => $student->id, 'level' => $data['level']])
                ->with('error', $e->getMessage());
        }

        $parts = ["{$name} is now {$data['level']}.", "{$result['removed']} enrolment(s) removed, {$result['added']} added."];
        if ($result['cancelled'] !== []) {
            $parts[] = 'Cancelled '.implode(', ', $result['cancelled']).'.';
        }
        if ($result['new_invoice']) {
            $parts[] = "New invoice {$result['new_invoice']->invoice_number}: RM".number_format((float) $result['new_invoice']->total_amount, 2).' (emailed to the family).';
        }

        return redirect()->route('admin.students.level-cleanup.index')->with('success', implode(' ', $parts));
    }
}
