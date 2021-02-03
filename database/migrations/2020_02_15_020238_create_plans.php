<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePlans extends Migration {
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up() {
		Schema::create('plans', function (Blueprint $table) {
			$table->bigIncrements('id');
			$table->string('name');
			if (env('DB_CONNECTION') == 'mysql') {
				$table->enum('type', ['free', 'paid', 'fixed']);
			} else {
				$table->string('type');
			}

			$table->integer('portal_limit')->nullable();
			$table->integer('user_limit')->nullable();
			$table->integer('module_limit')->nullable();
			$table->string('is_free_module');
			$table->string('is_hidden_plan');
			$table->float('price')->nullable();
			if (env('DB_CONNECTION') == 'mysql') {
				$table->enum('currency', ['USD', 'AUD', 'CAD', 'EURO', 'GBP'])->nullable();
			} else {
				$table->string('currency')->nullable();
			}

			if (env('DB_CONNECTION') == 'mysql') {
				$table->enum('interval', ['monthly', 'annually'])->nullable();
			} else {
				$table->string('interval')->nullable();
			}

			$table->string('trial_period')->nullable();
			if (env('DB_CONNECTION') == 'mysql') {
				$table->timestamp('created_at')->default(DB::raw('CURRENT_TIMESTAMP'));
				$table->timestamp('updated_at')->default(DB::raw('CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP'));
			} else {
				$table->timestamps();
			}
			$table->softDeletes();
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down() {
		Schema::dropIfExists('plans');
	}
}
