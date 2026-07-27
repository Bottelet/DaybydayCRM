<?php

use App\Models\InvoiceLine;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class AlterInvoicesTableAddSource extends Migration
{
    protected $invoiceLines;

    public function up()
    {
        $isSqlite = DB::getDriverName() === 'sqlite';

        Schema::table('invoices', static function (Blueprint $table) {
            $table->string('source_type')->nullable()->after('integration_type');
            $table->unsignedBigInteger('source_id')->nullable()->after('source_type');
            $table->index(['source_type', 'source_id']);
            $table->integer('offer_id')->unsigned()->nullable()->after('client_id');
            $table->foreign('offer_id')->references('id')->on('offers')->onDelete('set null');
        });

        if ( ! $isSqlite) {
            Schema::table('leads', static function (Blueprint $table) {
                $table->dropForeign('leads_invoice_id_foreign');
                $table->dropColumn('invoice_id');
            });
            Schema::table('tasks', static function (Blueprint $table) {
                $table->dropForeign('tasks_invoice_id_foreign');
                $table->dropColumn('invoice_id');
            });
        }
        // On SQLite we leave leads.invoice_id and tasks.invoice_id in place — the extra
        // nullable column is harmless for testing purposes.

        $this->invoiceLines = InvoiceLine::query()->pluck('invoice_id', 'id');

        if ( ! $isSqlite) {
            Schema::table('invoice_lines', function (Blueprint $table) {
                $table->integer('offer_id')->unsigned()->nullable()->after('price');
                $table->foreign('offer_id')->references('id')->on('offers')->onDelete('cascade');
                $table->dropForeign('invoice_lines_invoice_id_foreign');
                $table->dropColumn('invoice_id');
            });
        } else {
            // SQLite cannot ALTER COLUMN — recreate the table to add offer_id and make invoice_id nullable
            DB::statement('PRAGMA foreign_keys = OFF');
            DB::statement('CREATE TABLE invoice_lines_new (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                external_id VARCHAR NOT NULL,
                title VARCHAR NOT NULL,
                comment TEXT,
                price INTEGER NOT NULL,
                offer_id INTEGER UNSIGNED,
                invoice_id INTEGER UNSIGNED,
                type VARCHAR,
                quantity INTEGER,
                product_id VARCHAR,
                created_at TIMESTAMP,
                updated_at TIMESTAMP,
                deleted_at TIMESTAMP,
                FOREIGN KEY (invoice_id) REFERENCES invoices(id),
                FOREIGN KEY (offer_id) REFERENCES offers(id)
            )');
            DB::statement('INSERT INTO invoice_lines_new (id, external_id, title, comment, price, invoice_id, type, quantity, product_id, created_at, updated_at, deleted_at) SELECT id, external_id, title, comment, price, invoice_id, type, quantity, product_id, created_at, updated_at, deleted_at FROM invoice_lines');
            DB::statement('DROP TABLE invoice_lines');
            DB::statement('ALTER TABLE invoice_lines_new RENAME TO invoice_lines');
            DB::statement('PRAGMA foreign_keys = ON');
        }

        if ( ! $isSqlite) {
            Schema::table('invoice_lines', function (Blueprint $table) {
                $table->integer('invoice_id')->unsigned()->nullable()->after('price');
                $table->foreign('invoice_id')->references('id')->on('invoices')->onDelete('cascade');
            });

            foreach ($this->invoiceLines as $id => $invoiceId) {
                DB::table('invoice_lines')->where('id', $id)->update(['invoice_id' => $invoiceId]);
            }
        }
    }

    public function down() {}
}
