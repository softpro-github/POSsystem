<?php

namespace App\Http\Controllers\Customers;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CustomerController extends Controller
{
    public function index(Request $request): View
    {
        $customers = Customer::withCount('sales')
            ->when($request->filled('search'), function ($query) use ($request) {
                $term = $request->string('search');
                $query->where(function ($q) use ($term) {
                    $q->where('name', 'like', "%{$term}%")->orWhere('phone', 'like', "%{$term}%");
                });
            })
            ->orderBy('name')
            ->paginate(20)
            ->withQueryString();

        return view('customers.index', compact('customers'));
    }

    public function create(): View
    {
        return view('customers.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $this->validated($request);

        Customer::create($validated);

        return redirect()->route('customers.index')->with('success', 'Customer created.');
    }

    public function show(Customer $customer): View
    {
        $sales = $customer->sales()->latest('sold_at')->paginate(10);
        $warranties = $customer->warranties()->with('product')->latest()->get();
        $repairTickets = $customer->repairTickets()->latest()->get();

        return view('customers.show', compact('customer', 'sales', 'warranties', 'repairTickets'));
    }

    public function edit(Customer $customer): View
    {
        return view('customers.edit', compact('customer'));
    }

    public function update(Request $request, Customer $customer): RedirectResponse
    {
        $validated = $this->validated($request, $customer);

        $customer->update($validated);

        return redirect()->route('customers.index')->with('success', 'Customer updated.');
    }

    public function destroy(Customer $customer): RedirectResponse
    {
        if ($customer->sales()->exists()) {
            $customer->update(['is_active' => false]);

            return back()->with('success', 'Customer has sales history, so it was deactivated instead of deleted.');
        }

        $customer->delete();

        return redirect()->route('customers.index')->with('success', 'Customer deleted.');
    }

    private function validated(Request $request, ?Customer $customer = null): array
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'phone' => ['required', 'string', 'max:30', 'unique:customers,phone'.($customer ? ",{$customer->id}" : '')],
            'email' => ['nullable', 'email', 'max:255'],
            'address' => ['nullable', 'string', 'max:255'],
            'is_active' => ['boolean'],
        ]);

        $validated['is_active'] = $request->boolean('is_active', true);

        return $validated;
    }
}
