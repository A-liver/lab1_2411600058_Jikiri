<?php

namespace App\Http\Requests;

use App\Models\InventoryTransaction;
use App\Models\Product;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class StoreInventoryTransactionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // routes are already behind the auth middleware
    }

    public function rules(): array
    {
        return [
            'product_id' => ['required', 'integer', 'exists:products,id'],
            // Adjustments have their own form (Part 8), so only in/out here.
            'type' => ['required', Rule::in([
                InventoryTransaction::TYPE_IN,
                InventoryTransaction::TYPE_OUT,
            ])],
            'quantity' => ['required', 'integer', 'min:1', 'max:1000000'],
            'reference_number' => ['nullable', 'string', 'max:100'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ];
    }

    public function messages(): array
    {
        return [
            'product_id.exists' => 'The selected product does not exist.',
            'quantity.min' => 'Quantity must be at least 1.',
        ];
    }

    /** Runs only after the basic rules pass: stock-out cannot exceed available stock. */
    public function after(): array
    {
        return [
            function (Validator $validator) {
                if ($validator->errors()->isNotEmpty()
                    || $this->input('type') !== InventoryTransaction::TYPE_OUT) {
                    return;
                }

                $product = Product::find($this->input('product_id'));

                if ($product && (int) $this->input('quantity') > $product->quantity) {
                    $validator->errors()->add(
                        'quantity',
                        "Only {$product->quantity} of \"{$product->name}\" in stock; "
                        . "you cannot remove {$this->input('quantity')}."
                    );
                }
            },
        ];
    }
}