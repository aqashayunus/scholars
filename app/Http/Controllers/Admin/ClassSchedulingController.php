<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreLiveClassRequest;
use App\Http\Requests\Admin\StoreRecordedClassRequest;
use App\Http\Requests\Admin\UpdateLiveClassRequest;
use App\Http\Requests\Admin\UpdateRecordedClassRequest;
use App\Models\LiveClass;
use App\Models\RecordedClass;
use App\Services\Admin\ClassSchedulingService;

/**
 * Live class and recorded class CRUD, extracted out of
 * AdminDashboardController as part of Phase 4's controller-split. Moved
 * verbatim -- same ClassSchedulingService delegation, same route names.
 */
class ClassSchedulingController extends Controller
{
    public function storeLiveClass(StoreLiveClassRequest $request, ClassSchedulingService $scheduling)
    {
        $liveClass = $scheduling->createLiveClass($request->validated());

        return redirect()->to(route('admin.dashboard') . '#courses-live')
            ->with('success', "Live Class '{$liveClass->title}' scheduled successfully.");
    }

    public function updateLiveClass(UpdateLiveClassRequest $request, $id, ClassSchedulingService $scheduling)
    {
        $liveClass = LiveClass::findOrFail($id);
        $liveClass = $scheduling->updateLiveClass($liveClass, $request->validated());

        return redirect()->to(route('admin.dashboard') . '#courses-live')
            ->with('success', "Live Class '{$liveClass->title}' updated successfully.");
    }

    public function destroyLiveClass($id, ClassSchedulingService $scheduling)
    {
        $title = $scheduling->deleteLiveClass($id);

        return redirect()->to(route('admin.dashboard') . '#courses-live')
            ->with('success', "Live Class '{$title}' removed.");
    }

    public function storeRecordedClass(StoreRecordedClassRequest $request, ClassSchedulingService $scheduling)
    {
        $recordedClass = $scheduling->createRecordedClass($request->validated(), $request);

        return redirect()->to(route('admin.dashboard') . '#courses-recorded')
            ->with('success', "Recorded Class '{$recordedClass->title}' added to vault.");
    }

    public function updateRecordedClass(UpdateRecordedClassRequest $request, $id, ClassSchedulingService $scheduling)
    {
        $recordedClass = RecordedClass::findOrFail($id);
        $recordedClass = $scheduling->updateRecordedClass($recordedClass, $request->validated(), $request);

        return redirect()->to(route('admin.dashboard') . '#courses-recorded')
            ->with('success', "Recorded Class '{$recordedClass->title}' updated successfully.");
    }

    public function destroyRecordedClass($id, ClassSchedulingService $scheduling)
    {
        $title = $scheduling->deleteRecordedClass($id);

        return redirect()->to(route('admin.dashboard') . '#courses-recorded')
            ->with('success', "Recorded Class '{$title}' deleted.");
    }
}
