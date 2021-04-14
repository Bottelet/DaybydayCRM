<?php

use App\Models\Permission;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateProductsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('external_id');
            $table->text('description')->nullable();
            $table->string('number');
            $table->string('default_type');
            $table->boolean('archived');
            $table->nullableMorphs('integration');
            $table->integer('price');
            $table->softDeletes();
            $table->timestamps();
        });

        $p1 = Permission::create([
            'display_name' => 'Add product',
            'name' => 'product-create',
            'description' => 'Be able to create an product',
            'grouping' => 'product',
        ]);

        $p2 = Permission::create([
            'display_name' => 'Edit product',
            'name' => 'product-edit',
            'description' => 'Be able to edit an product',
            'grouping' => 'product',
        ]);

        $p3 = Permission::create([
            'display_name' => 'Delete product',
            'name' => 'product-delete',
            'description' => 'Be able to delete an product',
            'grouping' => 'product',
        ]);

        $roles = \App\Models\Role::whereIn('name', ['owner', 'administrator'])->get();
        foreach ($roles as $role) {
            $role->permissions()->attach([$p1->id, $p2->id, $p3->id]);
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('products');
    }
}
