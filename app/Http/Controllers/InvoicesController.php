<?php

namespace App\Http\Controllers;

use App\Enums\InvoiceStatus;
use App\Enums\PaymentSource;
use App\Http\Requests\Invoice\AddInvoiceLine;
use App\Models\Invoice;
use App\Models\Product;
use App\Models\Setting;
use App\Repositories\Currency\Currency;
use App\Repositories\Money\MoneyConverter;
use App\Repositories\Tax\Tax;
use App\Services\Billing\BillingIntegrationRegistry;
use App\Services\Billing\NullBillingAdapter;
use App\Services\Invoice\InvoiceCalculator;
use App\Services\Invoice\InvoiceService;
use App\Services\InvoiceLine\InvoiceLineService;
use App\Services\InvoiceNumber\InvoiceNumberService;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Validator;
use InvalidArgumentException;
use Yajra\DataTables\Facades\DataTables;

class InvoicesController extends Controller
{
    protected $clients;

    protected $invoices;

    public function __construct(
        private BillingIntegrationRegistry $billing,
        private InvoiceService $invoiceService,
        private InvoiceLineService $invoiceLineService,
    ) {
        $this->middleware('permission:invoice-see', ['only' => ['index', 'show']]);
    }

    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function index()
    {
        return view('invoices.index');
    }

    /**
     * Display the specified resource.
     *
     * @return Response
     */
    public function show(Invoice $invoice)
    {
        $api = $this->billing->driver();

        $apiConnected    = ! ($api instanceof NullBillingAdapter);
        $invoiceContacts = [];
        $primaryContact  = null;

        if ($apiConnected) {
            $invoiceContacts = $api->getContacts();
            if (empty($invoiceContacts)) {
                $apiConnected = false;
            } else {
                $primaryContact = $api->getPrimaryContact($invoice->client);
            }
        }

        $invoiceCalculator = new InvoiceCalculator($invoice);
        $totalPrice        = $invoiceCalculator->getTotalPrice();
        $subPrice          = $invoiceCalculator->getSubTotal();
        $vatPrice          = $invoiceCalculator->getVatTotal();
        $amountDue         = $invoiceCalculator->getAmountDue();

        return view('invoices.show')
            ->withInvoice($invoice)
            ->withApiconnected($apiConnected)
            ->withContacts($invoiceContacts)
            ->withfinalPrice(app(MoneyConverter::class, ['money' => $totalPrice])->format())
            ->withsubPrice(app(MoneyConverter::class, ['money' => $subPrice])->format())
            ->withVatPrice(app(MoneyConverter::class, ['money' => $vatPrice])->format())
            ->withAmountDueFormatted(app(MoneyConverter::class, ['money' => $amountDue])->format())
            ->withPrimaryContact(optional($primaryContact)[0])
            ->withPaymentSources(PaymentSource::values())
            ->withAmountDue($amountDue)
            ->withSource($invoice->source)
            ->withCompanyName(Setting::cached()->company);
    }

    /**
     * Update the sent status.
     *
     * @return mixed
     */
    public function updateSentStatus(Request $request, $external_id)
    {
        if ( ! auth()->user()->can('invoice-send')) {
            session()->flash('flash_message_warning', __('You do not have permission to send an invoice'));

            return redirect()->route('invoices.show', $external_id);
        }
        /** @var Invoice $invoice */
        $invoice = $this->findByExternalId($external_id);
        if ($invoice->isSent()) {
            session()->flash('flash_message_warning', __('Invoice already sent'));

            return redirect()->route('invoices.show', $external_id);
        }

        $result = $this->invoiceService->submitToBilling($invoice, $request->invoiceContact);
        if ($request->sendMail && $request->invoiceContact) {
            $attachPdf = (bool) $request->attachPdf;
            $this->invoiceService->sendByEmail($invoice, $request->subject, $request->message, $request->recipientMail, $attachPdf);
        }

        $invoice->sent_at        = Carbon::now();
        $invoice->status         = InvoiceStatus::unpaid()->getStatus();
        $invoice->due_at         = $result['due_at'];
        $invoice->invoice_number = app(InvoiceNumberService::class)->setInvoiceNumber($result['invoice_number']);
        $invoice->save();

        return redirect()->back();
    }

    /**
     * Add new invoice line.
     *
     * @return mixed
     *
     * @throws Exception
     */
    public function newItem($external_id, AddInvoiceLine $request)
    {
        if ( ! auth()->user()->can('modify-invoice-lines')) {
            session()->flash('flash_message_warning', __('You do not have permission to modify invoice lines'));

            return redirect()->route('invoices.show', $external_id);
        }

        $error = $this->createInvoiceLine($external_id, $request->all());

        if ($error !== null) {
            session()->flash('flash_message_warning', $error);

            return redirect()->back();
        }

        return redirect()->back();
    }

    public function newItems($external_id, Request $request)
    {
        if ( ! auth()->user()->can('modify-invoice-lines')) {
            return response()->json(['message' => __('You do not have permission to modify invoice lines')], 403);
        }

        foreach ($request->all() as $invoiceLine) {
            $error = $this->createInvoiceLine($external_id, $invoiceLine);

            if ($error !== null) {
                return response()->json(['message' => $error], 422);
            }
        }

        return response()->json(['message' => 'Invoice lines updated'], 200);
    }

    public function findByExternalId($external_id)
    {
        return Invoice::whereExternalId($external_id)->first();
    }

    public function paymentsDataTable(Invoice $invoice)
    {
        $payments = $invoice->payments()->select(
            ['external_id', 'amount', 'payment_date', 'description', 'payment_source']
        );

        return Datatables::of($payments)
            ->editColumn('amount', function ($payments) {
                return app(MoneyConverter::class, ['money' => $payments->price])->format();
            })
            ->editColumn('payment_date', function ($payments) {
                return $payments->payment_date ? with(new Carbon($payments->payment_date))
                    ->format(carbonDate()) : '';
            })
            ->editColumn('payment_source', function ($payments) {
                return __($payments->payment_source);
            })
            ->editColumn('description', function ($payments) {
                return mb_substr($payments->description, 0, 80);
            })
            ->addColumn('delete', '
                <form action="{{ route(\'payment.destroy\', $external_id) }}" method="POST">
            <input type="hidden" name="_method" value="DELETE">
            <input type="submit" name="submit" value="' . __('Delete') . '" class="btn btn-link" onClick="return confirm(\'Are you sure you want to delete the payment?\')"">
            {{csrf_field()}}
            </form>')
            ->rawColumns(['delete'])
            ->make(true);
    }

    public function moneyFormat()
    {
        $formats                  = [];
        $currency                 = app(Currency::class, ['code' => Setting::query()->select('currency')->first()?->currency ?? 'USD']);
        $formats                  = array_merge($formats, $currency->toArray());
        $formats['vatPercentage'] = app(Tax::class)->multipleVatRate();
        $formats['vatRate']       = app(Tax::class)->vatRate();

        return $formats;
    }

    public function overdue()
    {
        $invoices = Invoice::pastDueAt()->get();

        return view('invoices.overdue')->withInvoices($invoices);
    }

    /**
     * Validate and create a single invoice line from raw payload data.
     *
     * Shared by newItem() (real HTTP request, already re-validated by the
     * AddInvoiceLine type-hint) and newItems() (a batch of plain arrays that
     * never go through FormRequest's own validation pipeline, since it only
     * runs on container-resolved requests, not manually constructed ones).
     *
     * @return string|null the error message on failure, or null on success
     */
    private function createInvoiceLine(string $external_id, array $data): ?string
    {
        $validator = Validator::make($data, (new AddInvoiceLine())->rules());

        if ($validator->fails()) {
            return $validator->errors()->first();
        }

        $invoice = $this->findByExternalId($external_id);

        $product = null;
        if ( ! empty($data['product_id'])) {
            $product = $data['product_id'];
        } elseif ( ! empty($data['product'])) {
            $product = Product::whereExternalId($data['product'])->first()?->id;
        }

        try {
            $this->invoiceLineService->createLine(
                $invoice,
                $data['title'],
                $data['type'],
                $data['quantity'],
                (float) $data['price'],
                $data['comment'] ?? null,
                $product
            );
        } catch (InvalidArgumentException $exception) {
            return __("Can't insert new invoice line, to already sent invoice");
        }

        return null;
    }
}
