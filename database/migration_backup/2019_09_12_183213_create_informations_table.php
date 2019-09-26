<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateInformationsTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('informations', function(Blueprint $table)
		{
			$table->bigInteger('info_no', true);
			$table->string('info_title');
			$table->text('info_memo');
			$table->bigInteger('user_id');
			$table->date('start_date');
			$table->date('end_date');
			$table->timestamps();
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('informations');
	}

}
