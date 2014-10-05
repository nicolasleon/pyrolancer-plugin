<?php namespace Responsiv\Pyrolancer\Updates;

use Db;
use Schema;
use October\Rain\Database\Updates\Migration;

class AddFlagsToUsersTable extends Migration
{
    public function up()
    {
        Schema::table('users', function($table)
        {
            $table->boolean('is_worker')->default(false);
            $table->boolean('is_client')->default(false);
        });
    }

    public function down()
    {
        Schema::table('users', function($table)
        {
            $table->dropColumn('is_worker');
            $table->dropColumn('is_client');
        });
    }
}