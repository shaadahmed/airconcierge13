<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

/**
 * Fresh-baseline alter for Laravel stock table `users`.
 * Adds only columns beyond a default Laravel 5.1 migration.
 * Generated from live DB schema (schema only).
 */
class AddExtraColumnsToUsersTable extends Migration
{
    public function up()
    {
        if (!Schema::hasTable('users')) {
            return;
        }

        if (!Schema::hasColumn('users', 'firstname')) {
            Schema::table('users', function (Blueprint $table) {
                $table->string('firstname', 255);
            });
        }

        if (!Schema::hasColumn('users', 'lastname')) {
            Schema::table('users', function (Blueprint $table) {
                $table->string('lastname', 255)->nullable();
            });
        }

        if (!Schema::hasColumn('users', 'username')) {
            Schema::table('users', function (Blueprint $table) {
                $table->string('username', 255);
            });
        }

        if (!Schema::hasColumn('users', 'raw_password')) {
            Schema::table('users', function (Blueprint $table) {
                $table->string('raw_password', 255)->nullable();
            });
        }

        if (!Schema::hasColumn('users', 'phone')) {
            Schema::table('users', function (Blueprint $table) {
                $table->string('phone', 20)->nullable();
            });
        }

        if (!Schema::hasColumn('users', 'ext')) {
            Schema::table('users', function (Blueprint $table) {
                $table->string('ext', 10)->nullable();
            });
        }

        if (!Schema::hasColumn('users', 'fax')) {
            Schema::table('users', function (Blueprint $table) {
                $table->string('fax', 20)->nullable();
            });
        }

        if (!Schema::hasColumn('users', 'website')) {
            Schema::table('users', function (Blueprint $table) {
                $table->string('website', 100)->nullable();
            });
        }

        if (!Schema::hasColumn('users', 'address')) {
            Schema::table('users', function (Blueprint $table) {
                $table->string('address', 100)->nullable();
            });
        }

        if (!Schema::hasColumn('users', 'city')) {
            Schema::table('users', function (Blueprint $table) {
                $table->string('city', 100)->nullable();
            });
        }

        if (!Schema::hasColumn('users', 'state')) {
            Schema::table('users', function (Blueprint $table) {
                $table->string('state', 50)->nullable();
            });
        }

        if (!Schema::hasColumn('users', 'zipcode')) {
            Schema::table('users', function (Blueprint $table) {
                $table->string('zipcode', 20)->nullable();
            });
        }

        if (!Schema::hasColumn('users', 'lastlogin')) {
            Schema::table('users', function (Blueprint $table) {
                $table->dateTime('lastlogin')->nullable();
            });
        }

        if (!Schema::hasColumn('users', 'columns')) {
            Schema::table('users', function (Blueprint $table) {
                $table->text('columns')->nullable();
            });
        }

        if (!Schema::hasColumn('users', 'active')) {
            Schema::table('users', function (Blueprint $table) {
                $table->boolean('active')->nullable()->default(1);
            });
        }

        if (!Schema::hasColumn('users', 'default_investor')) {
            Schema::table('users', function (Blueprint $table) {
                $table->integer('default_investor')->nullable()->default(0);
            });
        }

        if (!Schema::hasColumn('users', 'alert')) {
            Schema::table('users', function (Blueprint $table) {
                $table->text('alert')->nullable();
            });
        }

        if (!Schema::hasColumn('users', 'deleted')) {
            Schema::table('users', function (Blueprint $table) {
                $table->boolean('deleted')->nullable()->default(0);
            });
        }

        if (!Schema::hasColumn('users', 'pwd_updated_at')) {
            Schema::table('users', function (Blueprint $table) {
                $table->dateTime('pwd_updated_at')->default(DB::raw('CURRENT_TIMESTAMP'));
            });
        }

        if (!Schema::hasColumn('users', 'reset_pwd_token')) {
            Schema::table('users', function (Blueprint $table) {
                $table->string('reset_pwd_token', 255)->nullable();
            });
        }

        if (!Schema::hasColumn('users', 'token_timestamp')) {
            Schema::table('users', function (Blueprint $table) {
                $table->dateTime('token_timestamp')->nullable();
            });
        }

        $indexExists = collect(DB::select('SHOW INDEX FROM `users`'))->contains(function ($row) {
            return $row->Key_name === 'users_username_unique';
        });
        if (!$indexExists) {
            Schema::table('users', function (Blueprint $table) {
                $table->unique('username', 'users_username_unique');
            });
        }

    }

    public function down()
    {
        if (!Schema::hasTable('users')) {
            return;
        }

        if (Schema::hasColumn('users', 'firstname')) {
            Schema::table('users', function (Blueprint $table) {
                $table->dropColumn('firstname');
            });
        }

        if (Schema::hasColumn('users', 'lastname')) {
            Schema::table('users', function (Blueprint $table) {
                $table->dropColumn('lastname');
            });
        }

        if (Schema::hasColumn('users', 'username')) {
            Schema::table('users', function (Blueprint $table) {
                $table->dropColumn('username');
            });
        }

        if (Schema::hasColumn('users', 'raw_password')) {
            Schema::table('users', function (Blueprint $table) {
                $table->dropColumn('raw_password');
            });
        }

        if (Schema::hasColumn('users', 'phone')) {
            Schema::table('users', function (Blueprint $table) {
                $table->dropColumn('phone');
            });
        }

        if (Schema::hasColumn('users', 'ext')) {
            Schema::table('users', function (Blueprint $table) {
                $table->dropColumn('ext');
            });
        }

        if (Schema::hasColumn('users', 'fax')) {
            Schema::table('users', function (Blueprint $table) {
                $table->dropColumn('fax');
            });
        }

        if (Schema::hasColumn('users', 'website')) {
            Schema::table('users', function (Blueprint $table) {
                $table->dropColumn('website');
            });
        }

        if (Schema::hasColumn('users', 'address')) {
            Schema::table('users', function (Blueprint $table) {
                $table->dropColumn('address');
            });
        }

        if (Schema::hasColumn('users', 'city')) {
            Schema::table('users', function (Blueprint $table) {
                $table->dropColumn('city');
            });
        }

        if (Schema::hasColumn('users', 'state')) {
            Schema::table('users', function (Blueprint $table) {
                $table->dropColumn('state');
            });
        }

        if (Schema::hasColumn('users', 'zipcode')) {
            Schema::table('users', function (Blueprint $table) {
                $table->dropColumn('zipcode');
            });
        }

        if (Schema::hasColumn('users', 'lastlogin')) {
            Schema::table('users', function (Blueprint $table) {
                $table->dropColumn('lastlogin');
            });
        }

        if (Schema::hasColumn('users', 'columns')) {
            Schema::table('users', function (Blueprint $table) {
                $table->dropColumn('columns');
            });
        }

        if (Schema::hasColumn('users', 'active')) {
            Schema::table('users', function (Blueprint $table) {
                $table->dropColumn('active');
            });
        }

        if (Schema::hasColumn('users', 'default_investor')) {
            Schema::table('users', function (Blueprint $table) {
                $table->dropColumn('default_investor');
            });
        }

        if (Schema::hasColumn('users', 'alert')) {
            Schema::table('users', function (Blueprint $table) {
                $table->dropColumn('alert');
            });
        }

        if (Schema::hasColumn('users', 'deleted')) {
            Schema::table('users', function (Blueprint $table) {
                $table->dropColumn('deleted');
            });
        }

        if (Schema::hasColumn('users', 'pwd_updated_at')) {
            Schema::table('users', function (Blueprint $table) {
                $table->dropColumn('pwd_updated_at');
            });
        }

        if (Schema::hasColumn('users', 'reset_pwd_token')) {
            Schema::table('users', function (Blueprint $table) {
                $table->dropColumn('reset_pwd_token');
            });
        }

        if (Schema::hasColumn('users', 'token_timestamp')) {
            Schema::table('users', function (Blueprint $table) {
                $table->dropColumn('token_timestamp');
            });
        }

    }
}
