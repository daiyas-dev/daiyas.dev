<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateCodesTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('codes', function(Blueprint $table)
		{
			$table->bigInteger('cd');
			$table->bigInteger('ord');
			$table->string('name');
			$table->string('abb');
			$table->bigInteger('sub1')->nullable();
			$table->bigInteger('sub2')->nullable();
			$table->bigInteger('user_id')->nullable();
			$table->date('update_at')->nullable();
			$table->primary(['cd','ord'], 'codes_pkey');
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('codes');
	}

}
