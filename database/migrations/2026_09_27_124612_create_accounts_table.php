<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('accounts', function (Blueprint $table) {

            // Primary key
            $table->id();


            // =========================
            // علاقة المديونية بالعميل
            // =========================

            // كل مديونية تخص Customer واحد
            $table->foreignId('customer_id')
                ->constrained('customers')
                ->cascadeOnDelete();


            // =========================
            // الجهة صاحبة المديونية
            // =========================

            // البنك / الشركة
            // مثل:
            // Bank Misr
            // B.TECH
            // CIB
            $table->foreignId('organization_id')
                ->constrained('organizations')
                ->restrictOnDelete();


            // =========================
            // بيانات الحساب / القرض
            // =========================

            // رقم الحساب أو رقم القرض
            $table->string('account_number')
                ->unique();


            // قيمة المديونية الأصلية
            $table->decimal('original_amount', 12, 2);


            // المبلغ المتبقي حاليًا
            $table->decimal('remaining_amount', 12, 2);


            // تاريخ فتح القرض
            $table->date('loan_opened_at')
                ->nullable();


            // تاريخ آخر دفعة
            $table->date('last_payment_date')
                ->nullable();


            // قيمة آخر دفعة
            $table->decimal('last_payment_amount', 12, 2)
                ->nullable();


            // تاريخ انتهاء القرض
            $table->date('loan_expiry_date')
                ->nullable();


            // =========================
            // حالة المديونية
            // =========================

            $table->enum('status', [
                'active',
                'paid',
                'closed',
                'defaulted',
            ])->default('active');


            // =========================
            // موظف التحصيل المسؤول
            // =========================

            // ممكن الحساب لسه ما اتوزعش على Collector
            $table->foreignId('assigned_collector_id')
                ->nullable()
                ->constrained('employees')
                ->nullOnDelete();


            // created_at + updated_at
            $table->timestamps();
        });
    }

    public function down(): void
    {
        // حذف جدول accounts
        Schema::dropIfExists('accounts');
    }
};