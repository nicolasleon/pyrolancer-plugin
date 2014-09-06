<?php namespace Responsiv\Pyrolancer\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class CreateSkillsTable extends Migration
{

    public function up()
    {
        Schema::create('responsiv_pyrolancer_skills', function($table)
        {
            $table->engine = 'InnoDB';
            $table->increments('id');
            $table->string('name')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('responsiv_pyrolancer_skills');
    }

}