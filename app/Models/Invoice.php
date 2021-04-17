<?php
namespace App\Models;

use Carbon\Carbon;
use App\Enums\OfferStatus;
use App\Enums\InvoiceStatus;
use App\Repositories\Money\Money;
use Illuminate\Database\Eloquent\Model;
use App\Services\Invoice\InvoiceCalculator;
use Illuminate\Database\Eloquent\SoftDeletes;

use App\Services\InvoiceNumber\InvoiceNumberService;
use App\Repositories\BillingIntegration\BillingIntegrationInterface;

/**
 * @property mixed sent_at
 * @property string integration_invoice_id
 * @property string integration_type
 * @property mixed reference
 */
class Invoice extends Model
{
    use SoftDeletes;

    const STATUS_SENT = "sent";

    protected $fillable = [
        'status',
        'sent_at',
        'due_at',
        'client_id',
        'integration_invoice_id',
        'integration_type',
        'source_id',
        'source_type',
        'external_id',
        'offer_id',
    ];

    protected $dates = [
        'due_at',
    ];

    /**
     * Get the route key for the model.
     *
     * @return string
     */
    public function getRouteKeyName()
    {
        return 'external_id';
    }

    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    public function invoiceLines()
    {
        return $this->hasMany(InvoiceLine::class);
    }

    public function offer()
    {
        return $this->belongsTo(Offer::class);
    }

    public function source()
    {
        return $this->morphTo('source');
    }

    public function payments()
    {
        return $this->hasMany(Payment::class, 'invoice_id', 'id');
    }

    public function canUpdateInvoice()
    {
        if ($this->isSent()) {
            return false;
        }
        return true;
    }

    public function isSent()
    {
        return $this->sent_at != null;
    }

    public function removeReference(): bool
    {
        return $this->update([
            'integration_invoice_id' => null,
            'integration_type' => null,
            'source_id' => null,
            'source_type' => null,
        ]);
    }

    /**
     * @param $contactId
     * @param bool $sendMail
     * @return array
     */
    public function invoice($contactId)
    {
        /** @var BillingIntegrationInterface $api */
        $api = Integration::initBillingIntegration();
        if ($api && $contactId) {
            $results = $api->createInvoice(
                [
                    'currency' => Setting::first()->currency,
                    'show_lines_incl_vat' => true,
                    'description' => $this->source->title,
                    'contact_id' => $contactId,
                    'invoice_lines' => $this->invoiceLines,
                ]
            );
            $this->integration_invoice_id = $results->invoiceId;
            $this->integration_type = get_class($api);
            $this->save();

            $booked = $api->bookInvoice($results->invoiceId, $results->timestamp);
        }

        return [
            'invoice_number' => isset($booked) ? $booked->invoiceNumber : app(InvoiceNumberService::class)->nextInvoiceNumber(),
            'due_at' => isset($booked) ? Carbon::parse($booked->paymentDate) : Carbon::today()->addDays(14)
        ];
    }

    public function sendMail($subject, $message, $recipient, $attachPdf = false)
    {
        /** @var BillingIntegrationInterface $api */
        $api = Integration::initBillingIntegration();

        if (!$api) {
            return false;
        }

        $api->sendInvoice($this, $subject, $message, $recipient, $attachPdf);

        activity("task")
            ->performedOn($this)
            ->withProperties(['action' => "sent_invoice"])
            ->log("user has send the invoice to the customer");

        return true;
    }

    public function scopePastDueAt($query)
    {
        return $query->where('due_at', '<', now())->NotFullyPaid();
    }

    public function scopeNotFullyPaid($query)
    {
        return $query->where('status', '!=', InvoiceStatus::paid()->getStatus());
    }

    public function getTotalPriceAttribute()
    {
        $invoiceCalculator = new InvoiceCalculator($this);
        return $invoiceCalculator->getTotalPrice();
    }
}
