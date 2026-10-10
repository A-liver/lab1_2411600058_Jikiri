<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * MySQL/MariaDB triggers = the last line of defence at the database level.
     * Laravel (observer + DB::transaction) does the normal work; these make sure
     * bad data can never get in even if someone bypasses the app (e.g. phpMyAdmin).
     *
     *  1. BEFORE INSERT: reject any movement that would make stock negative.
     *  2. BEFORE UPDATE: ledger rows are immutable (audit trail). Corrections are
     *     made with a NEW adjustment transaction, never by editing history.
     */
    public function up(): void
    {
        DB::unprepared('DROP TRIGGER IF EXISTS trg_inv_tx_prevent_negative_stock');
        DB::unprepared('DROP TRIGGER IF EXISTS trg_inv_tx_immutable');

        DB::unprepared(<<<'SQL'
CREATE TRIGGER trg_inv_tx_prevent_negative_stock
BEFORE INSERT ON inventory_transactions
FOR EACH ROW
BEGIN
    DECLARE current_qty INT DEFAULT 0;
    DECLARE delta INT DEFAULT 0;

    SELECT quantity INTO current_qty FROM products WHERE id = NEW.product_id;

    IF NEW.type = 'stock_out' THEN
        SET delta = -NEW.quantity;
    ELSE
        SET delta = NEW.quantity;  -- stock_in (positive) / adjustment (signed)
    END IF;

    IF current_qty + delta < 0 THEN
        SIGNAL SQLSTATE '45000'
            SET MESSAGE_TEXT = 'Insufficient stock: this movement would make inventory negative.';
    END IF;
END
SQL);

        DB::unprepared(<<<'SQL'
CREATE TRIGGER trg_inv_tx_immutable
BEFORE UPDATE ON inventory_transactions
FOR EACH ROW
BEGIN
    SIGNAL SQLSTATE '45000'
        SET MESSAGE_TEXT = 'Inventory transactions are immutable. Create an adjustment instead.';
END
SQL);
    }

    public function down(): void
    {
        DB::unprepared('DROP TRIGGER IF EXISTS trg_inv_tx_prevent_negative_stock');
        DB::unprepared('DROP TRIGGER IF EXISTS trg_inv_tx_immutable');
    }
};
