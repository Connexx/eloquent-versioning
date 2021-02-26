<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCommentsTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up(): void {
		Schema::create('comments', static function (Blueprint $table) {
			$table->increments('id');
			$table->integer('latest_version');
			$table->text('title');
			$table->timestamps();
		});

		Schema::create('comments_version', static function (Blueprint $table) {
			$table->integer('ref_id')->unsigned();
			$table->integer('version')->unsigned();
			$table->text('content');

			$table->primary(['ref_id', 'version']);
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down(): void {
		Schema::dropIfExists('comments');
		Schema::dropIfExists('comments_version');
	}
}
