<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('customers', function (Blueprint $table) {

            // Primary key
            $table->id();

            // =========================
            // بيانات العميل الأساسية
            // =========================

            // اسم العميل
            $table->string('name');

            // الرقم القومي
            // Nullable لأن ممكن البيانات تكون غير متوفرة
            $table->string('national_id')
                ->nullable()
                ->index();

            // رقم تليفون العميل
            $table->string('phone')
                ->index();

            // البريد الإلكتروني
            $table->string('email')
                ->nullable();

            // المحافظة
            $table->string('governorate')
                ->nullable();

            // عنوان العميل
            $table->text('address')
                ->nullable();


            // =========================
            // بيانات عمل العميل
            // =========================

            // اسم جهة العمل
            $table->string('employer_name')
                ->nullable();

            // المسمى الوظيفي
            $table->string('job_title')
                ->nullable();

            // رقم تليفون العمل
            $table->string('work_phone')
                ->nullable();

            // عنوان العمل
            $table->text('work_address')
                ->nullable();


            // تاريخ إنشاء وتعديل العميل
            $table->timestamps();
        });
    }

    public function down(): void
    {
        // حذف جدول customers
        Schema::dropIfExists('customers');
    }
};