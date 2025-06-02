<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('social_accounts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('profile_id')->constrained()->onDelete('cascade');
            $table->enum('platform', ['facebook', 'twitter', 'instagram', 'linkedin', 'tiktok', 'youtube', 'pinterest', 'threads']);
            $table->string('account_name');
            $table->text('access_token');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('social_accounts');
    }
};
