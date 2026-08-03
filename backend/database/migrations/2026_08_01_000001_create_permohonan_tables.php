<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('permohonan', function (Blueprint $table) {
            $table->id();
            $table->string('nomor_permohonan')->unique();
            $table->foreignId('pemohon_id')->constrained('users')->onDelete('cascade');
            $table->string('judul_project');
            $table->text('deskripsi');
            $table->enum('status', ['draft', 'submitted', 'revision', 'approved', 'rejected'])->default('draft');
            $table->timestamp('submitted_at')->nullable();
            $table->timestamps();

            $table->index(['status', 'pemohon_id', 'created_at'], 'idx_permohonan_status_pemohon');
        });

        Schema::create('permohonan_documents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('permohonan_id')->constrained('permohonan')->onDelete('cascade');
            $table->string('original_name');
            $table->string('file_path');
            $table->integer('file_size');
            $table->string('mime_type');
            $table->timestamps();
        });

        Schema::create('permohonan_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('permohonan_id')->constrained('permohonan')->onDelete('cascade');
            $table->foreignId('actor_id')->constrained('users')->onDelete('cascade');
            $table->string('action');
            $table->string('status_from')->nullable();
            $table->string('status_to')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['permohonan_id', 'actor_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('permohonan_logs');
        Schema::dropIfExists('permohonan_documents');
        Schema::dropIfExists('permohonan');
    }
};
