<?php

namespace App\Services;

/**
 * PricingResult — Immutable value object returned by PackagePricingService::calculate().
 *
 * This is the canonical output of a server-side pricing calculation.
 * It contains everything needed to generate an Invoice + InvoiceItems.
 *
 * Important: the frontend may DISPLAY these values for UX, but should never
 * submit them back to the server as authoritative. The server always recalculates.
 */
final class PricingResult
{
    public function __construct(
        /** ID of the fee structure used */
        public readonly int    $feeStructureId,

        /** Snapshot of the package name at calculation time */
        public readonly string $packageName,

        /** Package type (individual, fixed_bundle, pick_n, tiered, custom) */
        public readonly string $packageType,

        /** Subject IDs selected by the student */
        public readonly array  $subjectIds,

        /** Base tuition price calculated by the engine */
        public readonly float  $tuitionPrice,

        /**
         * Line items ready for InvoiceItem::create().
         * Each element: [subject_id, description, item_type, package_name,
         *                billing_frequency, unit_price, quantity, amount]
         */
        public readonly array  $lineItems,

        /** Sum of all line item amounts */
        public readonly float  $subtotal,

        /** Total discount applied (0 until promotion engine is integrated) */
        public readonly float  $discountAmount,

        /** Final amount to charge (subtotal - discountAmount) */
        public readonly float  $totalAmount,
    ) {}

    /**
     * Human-readable breakdown for display or logging.
     */
    public function toArray(): array
    {
        return [
            'fee_structure_id' => $this->feeStructureId,
            'package_name'     => $this->packageName,
            'package_type'     => $this->packageType,
            'subject_ids'      => $this->subjectIds,
            'tuition_price'    => $this->tuitionPrice,
            'line_items'       => $this->lineItems,
            'subtotal'         => $this->subtotal,
            'discount_amount'  => $this->discountAmount,
            'total_amount'     => $this->totalAmount,
        ];
    }
}
