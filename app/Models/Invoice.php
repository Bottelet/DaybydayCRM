<?php
namespace App\Models;

use App\Repositories\BillingIntegration\BillingIntegrationInterface;
use App\Repositories\Money\Money;
use App\Services\InvoiceNumber\InvoiceNumberService;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * @property mixed sent_at
 * @property string integration_invoice_id
 * @property string integration_type
 * @property mixed reference
 */
class Invoice extends Model
{
    use  SoftDeletes;

    const STATUS_SENT = "sent";

    protected $fillable = [
        'sent_at',
        'status',
        'sent_at',
        'due_at',
        'client_id',
        'integration_invoice_id',
        'integration_type',
        'external_id'
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

    public function task()
    {
        return $this->hasOne(Task::class);
    }

    public function lead()
    {
        return $this->hasOne(Lead::class);
    }

    public function reference()
    {
        if (!is_null($this->lead) && !is_null($this->task)) {
            throw new \Exception("Invoice can't have both a reference to tasks and leads");
        }

        if (!is_null($this->lead())) {
            return $this->lead();
        } else {
            return $this->task();
        }
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
                    'description' => $this->reference->title,
                    'contact_id' => $contactId,
                    'invoice_lines' => $this->invoiceLines,
                ]);
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
}
