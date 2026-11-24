<?php

namespace Tests\Unit;

use App\Models\Setting;
use App\Services\Billplz\BillplzService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * X-Signature against the worked examples in Billplz's own API docs
 * (support.billplz.com/api, Security > X Signature). If any of these fail,
 * real Billplz callbacks/redirects would be rejected (or forged ones
 * accepted) in production.
 */
class BillplzSignatureTest extends TestCase
{
    use RefreshDatabase;

    /** The sample X Signature key used throughout the Billplz docs. */
    private const DOCS_KEY = 'S-s7b4yWpp9h7rrkNM1i3Z_g';

    private BillplzService $billplz;

    protected function setUp(): void
    {
        parent::setUp();
        $this->billplz = new BillplzService;
    }

    /** @return array<string, string> */
    private function docsCallback(): array
    {
        return [
            'id' => 'zq0tm2wc',
            'collection_id' => 'yhx5t1pp',
            'paid' => 'true',
            'state' => 'paid',
            'amount' => '100',
            'paid_amount' => '100',
            'due_at' => '2018-9-27',
            'email' => 'tester@test.com',
            'mobile' => '',
            'name' => 'TESTER',
            'url' => 'http://www.billplz-sandbox.com/bills/zq0tm2wc',
            'paid_at' => '2018-09-27 15:15:09 +0800',
            'x_signature' => '0fe0a20b8d557eeae570377783d062a3816a9ea80f368860bacfa7ec3ca4d00e',
        ];
    }

    /** @return array<string, array<string, string>> */
    private function docsRedirect(): array
    {
        return ['billplz' => [
            'id' => 'zq0tm2wc',
            'paid' => 'true',
            'paid_at' => '2018-09-27 15:15:09 +0800',
            'x_signature' => '4aab095fe5a39b1d534500988f9a0cb085cd1b6d5bbb55dd4e02ea6fa102b47b',
        ]];
    }

    public function test_callback_source_string_matches_the_docs(): void
    {
        $this->assertSame(
            'amount100|collection_idyhx5t1pp|due_at2018-9-27|emailtester@test.com|idzq0tm2wc|mobile|nameTESTER|paid_amount100|paid_at2018-09-27 15:15:09 +0800|paidtrue|statepaid|urlhttp://www.billplz-sandbox.com/bills/zq0tm2wc',
            $this->billplz->signatureSource($this->docsCallback())
        );
    }

    public function test_callback_signature_matches_the_docs_example(): void
    {
        $this->assertSame(
            '0fe0a20b8d557eeae570377783d062a3816a9ea80f368860bacfa7ec3ca4d00e',
            $this->billplz->generateSignature($this->docsCallback(), self::DOCS_KEY)
        );
    }

    public function test_redirect_nested_fields_are_prefixed_and_signature_matches_the_docs(): void
    {
        $this->assertSame(
            'billplzidzq0tm2wc|billplzpaid_at2018-09-27 15:15:09 +0800|billplzpaidtrue',
            $this->billplz->signatureSource($this->docsRedirect())
        );
        $this->assertSame(
            '4aab095fe5a39b1d534500988f9a0cb085cd1b6d5bbb55dd4e02ea6fa102b47b',
            $this->billplz->generateSignature($this->docsRedirect(), self::DOCS_KEY)
        );
    }

    public function test_nested_list_example_from_the_docs(): void
    {
        $data = ['collections' => [
            ['id' => 'inbmmepb', 'title' => 'testing'],
            ['id' => 'xyzabc', 'title' => 'testing x 2'],
        ], 'page' => '1'];

        $this->assertSame(
            'collectionsidinbmmepb|collectionsidxyzabc|collectionstitletesting|collectionstitletesting x 2|page1',
            $this->billplz->signatureSource($data)
        );
    }

    public function test_verify_accepts_genuine_callback_and_redirect(): void
    {
        Setting::set('billplz_x_signature_key', self::DOCS_KEY, 'finance');

        $this->assertTrue($this->billplz->verifySignature($this->docsCallback()));
        $this->assertTrue($this->billplz->verifySignature($this->docsRedirect()));
    }

    public function test_verify_rejects_tampered_values(): void
    {
        Setting::set('billplz_x_signature_key', self::DOCS_KEY, 'finance');

        $tampered = $this->docsCallback();
        $tampered['paid_amount'] = '1';
        $this->assertFalse($this->billplz->verifySignature($tampered));

        $redirect = $this->docsRedirect();
        $redirect['billplz']['id'] = 'someone-elses-bill';
        $this->assertFalse($this->billplz->verifySignature($redirect));

        $unsigned = $this->docsCallback();
        unset($unsigned['x_signature']);
        $this->assertFalse($this->billplz->verifySignature($unsigned));
    }

    public function test_verify_fails_closed_when_no_key_is_configured(): void
    {
        Setting::set('billplz_x_signature_key', '', 'finance');
        config(['services.billplz.x_signature_key' => null]);

        $this->assertFalse($this->billplz->verifySignature($this->docsCallback()));
    }
}
