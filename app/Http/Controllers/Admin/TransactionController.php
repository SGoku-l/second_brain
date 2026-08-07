<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\View\View;

class TransactionController extends Controller
{
    public function index(Request $request): View
    {
        $status = $request->string('status')->toString();
        abort_unless($status === '' || in_array($status, ['pending', 'completed', 'failed', 'expired'], true), 404);

        return view('admin.transactions', [
            'transactions' => Transaction::query()->with(['user:id,name,email', 'plan:id,name'])->when($status !== '', fn ($query) => $query->where('status', $status))->latest()->paginate(20)->withQueryString(),
            'status' => $status,
        ]);
    }
}
