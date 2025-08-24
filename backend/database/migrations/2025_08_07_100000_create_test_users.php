<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // Tạo user admin test
        User::updateOrCreate(
            ['email' => 'admin@hungthinh.com'],
            [
                'name' => 'Admin Test',
                'email' => 'admin@hungthinh.com',
                'phone' => '0901234567',
                'role' => 'admin',
                'status' => 'active',
                'password' => 'admin123', // Sẽ được hash bởi mutator
            ]
        );

        User::updateOrCreate(
            ['email' => 'test@gmail.com'],
            [
                'name' => 'Test User',
                'email' => 'test@gmail.com',
                'phone' => '0901234999',
                'role' => 'resident',
                'status' => 'active',
                'password' => '123456', // Sẽ được hash bởi mutator
            ]
        );
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        User::where('email', 'admin@hungthinh.com')->delete();
        User::where('email', 'test@gmail.com')->delete();
    }
};
