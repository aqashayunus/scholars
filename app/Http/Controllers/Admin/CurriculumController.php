<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreCourseRequest;
use App\Http\Requests\Admin\UpdateCourseRequest;
use App\Http\Requests\Admin\UpdateSubtopicRequest;
use App\Http\Requests\Admin\UpdateTopicRequest;
use App\Models\Course;
use App\Models\Setting;
use App\Models\Subject;
use App\Models\SubjectSubtopic;
use App\Models\SubjectTopic;
use App\Services\Admin\CurriculumService;
use Illuminate\Http\Request;

/**
 * Course/Subject/Topic/Subtopic curriculum-builder CRUD, extracted out of
 * AdminDashboardController as part of Phase 4's controller-split. Moved
 * verbatim -- same CurriculumService delegation, same route names.
 */
class CurriculumController extends Controller
{
    public function storeCourse(StoreCourseRequest $request, CurriculumService $curriculum)
    {
        [$course, $monthlyFee] = $curriculum->createCourse($request->validated());

        return redirect()->to(route('admin.dashboard') . '#courses-catalog')
            ->with('success', "Course '{$course->title}' created. Subject fee set to " . Setting::get('currency_symbol', 'RM') . ' ' . number_format($monthlyFee, 2) . "/month.");
    }

    public function updateCourse(UpdateCourseRequest $request, $id, CurriculumService $curriculum)
    {
        $course = Course::findOrFail($id);
        [$course, $monthlyFee] = $curriculum->updateCourse($course, $request->validated());

        return redirect()->to(route('admin.dashboard') . '#courses-catalog')
            ->with('success', "Course '{$course->title}' updated. Subject fee: " . Setting::get('currency_symbol', 'RM') . ' ' . number_format($monthlyFee, 2) . "/month.");
    }

    public function destroyCourse($id, CurriculumService $curriculum)
    {
        $title = $curriculum->deleteCourse($id);

        return redirect()->to(route('admin.dashboard') . '#courses-catalog')
            ->with('success', "Course '{$title}' removed from catalog.");
    }

    public function storeSubject(Request $request, CurriculumService $curriculum)
    {
        $result = $curriculum->storeSubject($request);

        if ($result['bulk']) {
            return redirect()->to(route('admin.dashboard') . '#courses-subjects')
                ->with('success', "{$result['count']} KPM curriculum subject(s) created successfully.");
        }

        $subject = $result['subject'];

        return redirect()->to(route('admin.dashboard') . '#courses-subjects')
            ->with('success', "Subject '{$subject->name}' ({$subject->code}) created successfully.");
    }

    public function updateSubject(Request $request, $id, CurriculumService $curriculum)
    {
        $subject = Subject::findOrFail($id);
        $subject = $curriculum->updateSubject($subject, $request);

        return redirect()->to(route('admin.dashboard') . '#courses-subjects')
            ->with('success', "Subject '{$subject->name}' updated successfully.");
    }

    public function destroySubject($id, CurriculumService $curriculum)
    {
        $name = $curriculum->deleteSubject($id);

        return redirect()->to(route('admin.dashboard') . '#courses-subjects')
            ->with('success', "Subject '{$name}' deleted successfully.");
    }

    public function showSubject($id, CurriculumService $curriculum)
    {
        $subject = $curriculum->findSubjectForJson((int) $id);

        if (request()->wantsJson() || request()->ajax()) {
            if (!$subject) {
                return response()->json(['error' => 'Subject not found'], 404);
            }
            return response()->json($subject);
        }

        return redirect()->to(route('admin.dashboard') . '#courses-subjects');
    }

    public function setSubjectDiagnosticTest(Request $request, $id, CurriculumService $curriculum)
    {
        $result = $curriculum->setSubjectDiagnosticTest($request, (int) $id);

        return redirect()->to(route('admin.dashboard') . '#courses-subjects')
            ->with('success', "Diagnostic test \"{$result['assignment']->title}\" attached to {$result['subject']->display_name}.");
    }

    public function createSubjectDiagnosticTest(Request $request, $id, CurriculumService $curriculum)
    {
        $result = $curriculum->createSubjectDiagnosticTest($request, (int) $id);
        $message = "Diagnostic test \"{$result['assignment']->title}\" saved and attached to {$result['subject']->display_name}.";

        if ($request->expectsJson() || $request->ajax()) {
            return response()->json([
                'success'       => true,
                'message'       => $message,
                'assignment_id' => $result['assignment']->id,
            ]);
        }

        return redirect()->to(route('admin.dashboard') . '#courses-subjects')->with('success', $message);
    }

    public function detachSubjectDiagnosticTest(Request $request, $id, CurriculumService $curriculum)
    {
        $result = $curriculum->detachSubjectDiagnosticTest((int) $id);

        return redirect()->to(route('admin.dashboard') . '#courses-subjects')
            ->with('success', "Diagnostic test \"{$result['testTitle']}\" detached from {$result['subject']->display_name}.");
    }

    public function storeTopic(Request $request, CurriculumService $curriculum)
    {
        $result = $curriculum->storeTopic($request);

        if (isset($result['error'])) {
            return redirect()->to(route('admin.dashboard') . '#courses-topics')
                ->with('error', $result['error']);
        }

        if ($result['bulk']) {
            return redirect()->to(route('admin.dashboard') . '#courses-topics')
                ->with('success', "{$result['count']} curriculum topic(s) added successfully.");
        }

        $topic = $result['topic'];

        return redirect()->to(route('admin.dashboard') . '#courses-topics')
            ->with('success', "Curriculum Topic 'Chapter {$topic->chapter_number}: {$topic->title}' added successfully.");
    }

    public function updateTopic(UpdateTopicRequest $request, $id, CurriculumService $curriculum)
    {
        $topic = SubjectTopic::findOrFail($id);
        $topic = $curriculum->updateTopic($topic, $request);

        return redirect()->to(route('admin.dashboard') . '#courses-topics')
            ->with('success', "Curriculum Topic '{$topic->title}' updated successfully.");
    }

    public function destroyTopic($id, CurriculumService $curriculum)
    {
        $title = $curriculum->deleteTopic($id);

        return redirect()->to(route('admin.dashboard') . '#courses-topics')
            ->with('success', "Curriculum Topic '{$title}' and its subtopics deleted successfully.");
    }

    public function storeSubtopic(Request $request, $topicId, CurriculumService $curriculum)
    {
        $result = $curriculum->storeSubtopic($topicId, $request);
        $topic = $result['topic'];

        if ($result['bulk']) {
            return redirect()->to(route('admin.dashboard') . '#courses-topics')
                ->with('success', "{$result['count']} checkpoint(s) added under '{$topic->title}'.");
        }

        $subtopic = $result['subtopic'];

        return redirect()->to(route('admin.dashboard') . '#courses-topics')
            ->with('success', "Subtopic '{$subtopic->title}' added under {$topic->title}.");
    }

    public function updateSubtopic(UpdateSubtopicRequest $request, $id, CurriculumService $curriculum)
    {
        $subtopic = SubjectSubtopic::findOrFail($id);
        $subtopic = $curriculum->updateSubtopic($subtopic, $request->validated());

        return redirect()->to(route('admin.dashboard') . '#courses-topics')
            ->with('success', "Subtopic '{$subtopic->title}' updated successfully.");
    }

    public function destroySubtopic($id, CurriculumService $curriculum)
    {
        $title = $curriculum->deleteSubtopic($id);

        return redirect()->to(route('admin.dashboard') . '#courses-topics')
            ->with('success', "Subtopic '{$title}' deleted successfully.");
    }
}
