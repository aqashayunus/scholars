<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\FeeStructure;
use App\Models\Invoice;
use App\Services\Admin\BillingAdminService;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * Fee-structure, finance-settings, and admin invoice CRUD, extracted out of
 * AdminDashboardController as part of Phase 4's controller-split. Moved
 * verbatim -- same BillingAdminService delegation, same route names.
 */
class FinanceController extends Controller
{
    public function exportPaymentsCsv(BillingAdminService $billing): StreamedResponse
    {
        return $billing->exportPaymentsCsv();
    }

    public function exportEnrollmentsCsv(BillingAdminService $billing): StreamedResponse
    {
        return $billing->exportEnrollmentsCsv();
    }

    public function storeFeeStructure(Request $request, BillingAdminService $billing)
    {
        $fee = $billing->createFeeStructure($request);

        return back()->with('success', "Fee structure '{$fee->name}' created successfully!");
    }

    public function updateFeeStructure(Request $request, $id, BillingAdminService $billing)
    {
        $fee = FeeStructure::findOrFail($id);
        $fee = $billing->updateFeeStructure($fee, $request);

        return back()->with('success', "Fee structure '{$fee->name}' updated successfully.");
    }

    public function destroyFeeStructure($id, BillingAdminService $billing)
    {
        $name = $billing->deleteFeeStructure($id);

        return back()->with('success', "Fee structure '{$name}' removed.");
    }

    /**
     * Replace all subject associations for a fee structure package.
     */
    public function updateFeeStructureSubjects(Request $request, $id, BillingAdminService $billing)
    {
        $fee = FeeStructure::findOrFail($id);
        $billing->syncFeeStructureSubjects($fee, $request);

        return response()->json(['success' => true, 'message' => 'Subjects updated for "' . $fee->name . '".']);
    }

    /**
     * Replace all billing components (items) for a fee structure.
     */
    public function updateFeeStructureItems(Request $request, $id, BillingAdminService $billing)
    {
        $fee = FeeStructure::findOrFail($id);
        $billing->syncFeeStructureItems($fee, $request);

        return response()->json(['success' => true, 'message' => 'Billing components updated.']);
    }

    /**
     * Replace all pricing tiers for a tiered fee structure.
     */
    public function updateFeeStructureTiers(Request $request, $id, BillingAdminService $billing)
    {
        $fee = FeeStructure::findOrFail($id);
        $billing->syncFeeStructureTiers($fee, $request);

        return response()->json(['success' => true, 'message' => 'Pricing tiers updated.']);
    }

    /**
     * Duplicate an existing fee structure (copy config, subjects, items, tiers).
     * Does NOT copy enrollments, invoices, or payments.
     */
    public function duplicateFeeStructure(Request $request, $id, BillingAdminService $billing)
    {
        $original = FeeStructure::findOrFail($id);
        $new = $billing->duplicateFeeStructure($id, $request);

        return back()->with('success', "Package duplicated as \"Copy of {$original->name}\" (Draft). Edit it before activating.");
    }

    /**
     * Create a new version of an existing fee structure.
     * Existing students remain on the old version.
     */
    public function createFeeStructureVersion(Request $request, $id, BillingAdminService $billing)
    {
        $original = FeeStructure::findOrFail($id);
        $newVersion = $billing->createFeeStructureVersion($id, $request);

        return back()->with('success', "Version {$newVersion->version} of \"{$original->name}\" created as Draft. Activate it when ready.");
    }

    /**
     * API: Get subjects for a fee structure (for JS live preview in registration).
     */
    public function apiFeeStructureSubjects($id, BillingAdminService $billing)
    {
        return response()->json($billing->feeStructureSubjectsForApi($id));
    }

    /**
     * API: Return a pricing preview for a fee structure + subject selection.
     * Used by the registration form's live price preview.
     * NOTE: This is for UX only — server always recalculates on final submit.
     */
    public function apiFeeStructurePreview(Request $request, $id, BillingAdminService $billing)
    {
        $result = $billing->previewFeeStructurePricing($id, $request);

        return response()->json($result['pricing'] ?? ['error' => $result['error']], $result['status']);
    }

    public function updateFinanceSettings(Request $request, BillingAdminService $billing)
    {
        $billing->updateFinanceSettings($request);

        return back()->with('success', 'Finance and payment settings updated successfully.');
    }

    public function revealGatewayCredentials(Request $request, BillingAdminService $billing)
    {
        return response()->json($billing->revealGatewayCredentials($request));
    }

    public function testBillplzConnection(Request $request, BillingAdminService $billing)
    {
        return response()->json($billing->testBillplzConnection($request));
    }

    public function updateInvoice(Request $request, $id, BillingAdminService $billing)
    {
        $invoice = Invoice::findOrFail($id);
        $invoice = $billing->updateInvoice($invoice, $request);

        return redirect()->to(route('admin.dashboard') . '#financials')
            ->with('success', "Invoice {$invoice->invoice_number} updated successfully.");
    }

    public function updateInvoiceDueDate(Request $request, $id, BillingAdminService $billing)
    {
        $invoice = Invoice::findOrFail($id);
        $result = $billing->updateInvoiceDueDate($invoice, $request);

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => $result['message'],
                'invoice' => $result['invoice'],
            ]);
        }

        return redirect()->to(route('admin.dashboard') . '#financials')
            ->with('success', $result['message']);
    }

    public function updateDefaultDueDays(Request $request, BillingAdminService $billing)
    {
        $billing->updateDefaultDueDays($request);

        return redirect()->to(route('admin.dashboard') . '#financials')
            ->with('success', "Billing policies updated successfully.");
    }

    public function destroyInvoice($id, BillingAdminService $billing)
    {
        $number = $billing->deleteInvoice($id);

        return redirect()->to(route('admin.dashboard') . '#financials')
            ->with('success', "Invoice {$number} deleted. Linked pending payments have been reset.");
    }
}
