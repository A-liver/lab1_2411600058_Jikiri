<?php

namespace App\Exceptions;

use App\Models\Product;
use RuntimeException;

class InsufficientStockException extends RuntimeException
{
    public function __construct(public Product $product, public int $requested)
    {
        parent::__construct(
            "Insufficient stock for \"{$product->name}\": requested {$requested}, "
            . "only {$product->quantity} available."
        );
    }
}