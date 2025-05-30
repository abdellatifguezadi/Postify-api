<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('posts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('social_account_id')->constrained('social_accounts')->onDelete('cascade');
            $table->text('content');
            $table->enum('status', ['draft', 'queued', 'sent'])->default('draft');
            $table->date('scheduled_date')->nullable();
            $table->string('scheduled_time')->nullable();
            $table->timestamp('published_at')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down()
    {
        Schema::dropIfExists('posts');
    }
};
