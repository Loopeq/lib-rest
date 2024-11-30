<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email')->unique();            
            $table->string('password');
            $table->string('is_admin')->default(false); 
            $table->string('token', 16)->unique();
            $table->timestamps();
        });

        try{
            DB::table('users')->insert([
                'name' => env('ADMIN_NAME', 'admin'), 
                'email' => env('ADMIN_EMAIL', 'admin@gmail.com'), 
                'password' => Hash::make(env('ADMIN_PASSWORD', '12345')),
                'is_admin' => true,
                'token' => env('ADMIN_TOKEN', '0e40beb3c8cf3504'),
            ]);
        } catch (\Exception $e){
            Log::error('Error while creating admin' . $e->getMessage());
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};
