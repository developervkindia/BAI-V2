<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('employee_profiles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('organization_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('employee_id', 50)->nullable();
            $table->string('designation')->nullable();
            $table->string('department')->nullable();
            $table->date('date_of_joining')->nullable();
            $table->enum('employment_type', ['full_time', 'part_time', 'contract', 'intern'])->nullable();
            $table->foreignId('reporting_manager_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('work_location')->nullable();
            $table->string('shift', 50)->nullable();
            $table->string('phone', 20)->nullable();
            $table->string('personal_email')->nullable();
            $table->string('work_phone', 20)->nullable();
            $table->date('date_of_birth')->nullable();
            $table->enum('gender', ['male', 'female', 'other', 'prefer_not_to_say'])->nullable();
            $table->enum('marital_status', ['single', 'married', 'divorced', 'widowed'])->nullable();
            $table->string('blood_group', 10)->nullable();
            $table->string('nationality', 50)->nullable();
            $table->string('emergency_contact_name')->nullable();
            $table->string('emergency_contact_phone', 20)->nullable();
            $table->text('current_address')->nullable();
            $table->text('permanent_address')->nullable();
            $table->string('bank_name')->nullable();
            $table->text('account_number')->nullable(); // encrypted
            $table->string('ifsc_code', 20)->nullable();
            $table->string('bank_branch')->nullable();
            $table->enum('status', ['active', 'inactive', 'on_leave'])->default('active');
            $table->timestamp('deactivated_at')->nullable();
            $table->timestamps();

            $table->unique(['organization_id', 'user_id']);
        });

        Schema::create('employee_education', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_profile_id')->constrained('employee_profiles')->cascadeOnDelete();
            $table->string('degree');
            $table->string('institution');
            $table->string('field_of_study')->nullable();
            $table->smallInteger('start_year')->nullable();
            $table->smallInteger('end_year')->nullable();
            $table->string('grade', 20)->nullable();
            $table->timestamps();
        });

        Schema::create('employee_experience', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_profile_id')->constrained('employee_profiles')->cascadeOnDelete();
            $table->string('company');
            $table->string('designation');
            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();
            $table->text('description')->nullable();
            $table->timestamps();
        });

        Schema::create('employee_documents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_profile_id')->constrained('employee_profiles')->cascadeOnDelete();
            $table->string('type', 50); // aadhaar, pan, passport, offer_letter, resume, other
            $table->string('name')->nullable();
            $table->text('document_number')->nullable(); // encrypted
            $table->string('file_path', 500);
            $table->date('expiry_date')->nullable();
            $table->foreignId('uploaded_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        Schema::create('employee_assets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_profile_id')->constrained('employee_profiles')->cascadeOnDelete();
            $table->string('type', 50); // laptop, phone, id_card, monitor, other
            $table->string('name');
            $table->string('asset_tag', 50)->nullable();
            $table->string('serial_number', 100)->nullable();
            $table->date('assigned_date')->nullable();
            $table->date('return_date')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        Schema::create('employee_skills', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_profile_id')->constrained('employee_profiles')->cascadeOnDelete();
            $table->string('name');
            $table->string('category', 30)->nullable(); // skill, certification, badge
            $table->string('issued_by')->nullable();
            $table->date('issued_date')->nullable();
            $table->date('expiry_date')->nullable();
            $table->timestamps();
        });

        Schema::create('organization_invitations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('organization_id')->constrained()->cascadeOnDelete();
            $table->string('email');
            $table->foreignId('role_id')->nullable()->constrained()->nullOnDelete();
            $table->enum('system_role', ['admin', 'member'])->default('member');
            $table->string('token', 100)->unique();
            $table->foreignId('invited_by')->nullable()->constrained('users')->nullOnDelete();
            $table->enum('status', ['pending', 'accepted', 'declined', 'expired'])->default('pending');
            $table->timestamp('expires_at');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('organization_invitations');
        Schema::dropIfExists('employee_skills');
        Schema::dropIfExists('employee_assets');
        Schema::dropIfExists('employee_documents');
        Schema::dropIfExists('employee_experience');
        Schema::dropIfExists('employee_education');
        Schema::dropIfExists('employee_profiles');
    }
};
