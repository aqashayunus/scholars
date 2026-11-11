<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreCertificateRequest;
use App\Http\Requests\Admin\UpdateAdminProfileRequest;
use App\Http\Requests\Admin\UpdatePlatformSettingsRequest;
use App\Http\Requests\Admin\UpdatePricingPlansRequest;
use App\Http\Requests\Admin\UpdateSecuritySettingsRequest;
use App\Models\Course;
use App\Models\Enrollment;
use App\Models\Payment;
use App\Models\Setting;
use App\Models\User;
use App\Services\Admin\AttendanceService;
use App\Services\Admin\CertificateService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Hash;

/**
 * Settings & platform-config cluster, extracted out of AdminDashboardController
 * as part of Phase 4's controller-split (the cluster that had no dedicated
 * service, moved verbatim — behavior-preserving, same route names).
 */
class SettingsController extends Controller
{
    public function settings()
    {
        $admin = auth()->user();

        // Load all settings from DB (not session)
        $platformSettings = [
            'platform_name' => Setting::get('platform_name', 'OlympiaScholar'),
            'platform_tagline' => Setting::get('platform_tagline', 'Excel Beyond Limits'),
            'support_email' => Setting::get('support_email', 'support@olympiascholar.com'),
            'support_phone' => Setting::get('support_phone', '+60 12-345 6789'),
            'teacher_cut_pct' => Setting::get('teacher_cut_pct', 65),
            'currency_code' => Setting::get('currency_code', 'MYR'),
            'currency_symbol' => Setting::get('currency_symbol', 'RM'),
            'invoice_due_days' => (int) Setting::get('billing.invoice_due_days', 7),
            'allow_registrations' => Setting::get('allow_registrations', 1),
            'allow_gig_claims' => Setting::get('allow_gig_claims', 1),
            'maintenance_mode' => Setting::get('maintenance_mode', 0),
        ];

        $pricingPlans = [
            'monthly_price' => Setting::get('plan_monthly_price', 'RM 99'),
            'monthly_per' => Setting::get('plan_monthly_per', '/month'),
            'monthly_desc' => Setting::get('plan_monthly_desc', 'Access to 1 subject'),
            'subject_price' => Setting::get('plan_subject_price', 'From RM 150'),
            'subject_per' => Setting::get('plan_subject_per', '/subject'),
            'subject_desc' => Setting::get('plan_subject_desc', '2-4 subjects bundled'),
            'premium_price' => Setting::get('plan_premium_price', 'From RM 199'),
            'premium_per' => Setting::get('plan_premium_per', '/month'),
            'premium_desc' => Setting::get('plan_premium_desc', 'Unlimited subjects'),
        ];

        $securitySettings = [
            'default_temp_password' => Setting::get('default_temp_password', 'Temp@1234'),
        ];

        $diagnostics = [
            'php_version' => PHP_VERSION,
            'laravel_version' => app()->version(),
            'db_connection' => config('database.default'),
            'db_host' => config('database.connections.mysql.host', '127.0.0.1'),
            'db_name' => config('database.connections.mysql.database', 'olympiascholar'),
            'cache_driver' => config('cache.default', 'file'),
            'app_env' => config('app.env', 'local'),
            'app_debug' => config('app.debug') ? 'Enabled' : 'Disabled',
            'timezone' => config('app.timezone', 'UTC'),
            'total_users' => User::count(),
            'total_payments' => Payment::count(),
            'total_courses' => Course::count(),
            'total_enrollments' => Enrollment::count(),
            'total_settings' => Setting::count(),
        ];

        return view('admin.settings', compact(
            'admin', 'diagnostics', 'platformSettings', 'pricingPlans', 'securitySettings'
        ));
    }

    public function updateProfile(UpdateAdminProfileRequest $request)
    {
        $admin = auth()->user();

        $validated = $request->validated();

        $admin->name = $validated['name'];
        $admin->email = $validated['email'];
        $admin->phone = $validated['phone'] ?? null;

        if (! empty($validated['password'])) {
            $admin->password = Hash::make($validated['password']);
        }
        $admin->save();

        return back()->with('success', 'Admin profile and credentials updated successfully.');
    }

    public function updatePlatformSettings(UpdatePlatformSettingsRequest $request)
    {
        Setting::set('platform_name', $request->platform_name, 'general', 'Platform Name');
        Setting::set('platform_tagline', $request->platform_tagline ?? '', 'general', 'Platform Tagline');
        Setting::set('support_email', $request->support_email, 'general', 'Support Email');
        Setting::set('support_phone', $request->support_phone ?? '', 'general', 'Support Phone');
        Setting::set('teacher_cut_pct', $request->teacher_cut_pct, 'financial', 'Teacher Revenue Cut (%)');
        Setting::set('currency_code', $request->currency_code, 'financial', 'Currency Code');
        Setting::set('currency_symbol', $request->currency_symbol, 'financial', 'Currency Symbol');
        if ($request->filled('invoice_due_days')) {
            Setting::set('billing.invoice_due_days', (string) $request->invoice_due_days, 'billing', 'Invoice Due Days (after issue date)');
        }
        Setting::set('allow_registrations', $request->has('allow_registrations') ? '1' : '0', 'features', 'Allow Public Registrations');
        Setting::set('allow_gig_claims', $request->has('allow_gig_claims') ? '1' : '0', 'features', 'Allow Teacher Gig Claiming');
        Setting::set('maintenance_mode', $request->has('maintenance_mode') ? '1' : '0', 'features', 'Maintenance Mode');

        Setting::clearCache();

        return back()->with('success', 'Platform configuration saved to database successfully.');
    }

    public function updatePricingPlans(UpdatePricingPlansRequest $request)
    {
        Setting::set('plan_monthly_price', $request->monthly_price, 'pricing', 'Monthly Plan — Display Price');
        Setting::set('plan_monthly_per', $request->monthly_per, 'pricing', 'Monthly Plan — Billing Period');
        Setting::set('plan_subject_price', $request->subject_price, 'pricing', 'Subject Package — Display Price');
        Setting::set('plan_subject_per', $request->subject_per, 'pricing', 'Subject Package — Billing Period');
        Setting::set('plan_premium_price', $request->premium_price, 'pricing', 'Premium Plan — Display Price');
        Setting::set('plan_premium_per', $request->premium_per, 'pricing', 'Premium Plan — Billing Period');

        Setting::clearCache();

        return back()->with('success', 'Public subscription plan pricing updated in database. Changes are now live on /pricing.');
    }

    public function updateSecuritySettings(UpdateSecuritySettingsRequest $request)
    {
        Setting::set('default_temp_password', $request->default_temp_password, 'security', 'Default Temporary Password');
        Setting::clearCache();

        return back()->with('success', 'Security settings updated in database.');
    }

    public function clearSystemCache()
    {
        Artisan::call('cache:clear');
        Artisan::call('view:clear');
        Artisan::call('route:clear');
        Setting::clearCache();

        return back()->with('success', 'Application cache, compiled views, route cache, and settings cache cleared successfully.');
    }

    /**
     * Admin Attendance Hub: redirect to the unified dashboard Attendance tab.
     */
    public function attendance(Request $request)
    {
        return redirect('/admin/dashboard#attendance');
    }

    public function updateAttendanceSettings(Request $request, AttendanceService $attendance)
    {
        $attendance->updateAttendanceSettings($request);

        return back()->with('success', 'Attendance policies updated successfully.');
    }

    public function exportAttendanceCsv(Request $request, AttendanceService $attendance)
    {
        return $attendance->exportAttendanceCsv($request);
    }

    /**
     * Store and award an official certificate to a student.
     */
    public function storeCertificate(StoreCertificateRequest $request, CertificateService $certificates)
    {
        $certificates->issue($request->validated(), $request->file('file'));

        return back()->with('success', 'Official Certificate successfully awarded and issued to student!');
    }

    /**
     * Delete an issued certificate.
     */
    public function destroyCertificate($id, CertificateService $certificates)
    {
        $certificates->destroy($id);

        return back()->with('success', 'Certificate removed successfully.');
    }
}
