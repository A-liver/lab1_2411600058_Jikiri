<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    /** Mark one notification as read, then go to the product it is about. */
    public function read(Request $request, string $id): RedirectResponse
    {
        // Scoped to the logged-in user, so nobody can read someone else's alerts.
        $notification = $request->user()->notifications()->findOrFail($id);
        $notification->markAsRead();

        $productId = $notification->data['product_id'] ?? null;

        if ($productId && Product::whereKey($productId)->exists()) {
            return redirect()->route('products.show', $productId);
        }

        return back();
    }

    public function readAll(Request $request): RedirectResponse
    {
        $request->user()->unreadNotifications->markAsRead();

        return back()->with('success', 'All notifications marked as read.');
    }
}