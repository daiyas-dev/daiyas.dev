<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateCoursesTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('courses', function(Blueprint $table)
		{
			$table->string('kbn', 1);
			$table->string('cd', 3);
			$table->string('name');
			$table->bigInteger('user_id')->nullable();
			$table->string('fac_kbn', 1)->nullable();
			$table->primary(['kbn','cd'], 'courses_pkey');
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('courses');
	}

}
