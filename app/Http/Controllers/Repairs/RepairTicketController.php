<?php

namespace App\Http\Controllers\Repairs;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\RepairTicket;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\View\View;

class RepairTicketController extends Controller
{
    public function index(Request $request): View
    {
        $repairTickets = RepairTicket::with(['customer', 'technician'])
            ->when($request->filled('status'), fn ($q) => $q->where('status', $request->string('status')))
            ->when($request->filled('search'), function ($query) use ($request) {
                $query->where('ticket_number', 'like', '%'.$request->string('search').'%');
            })
            ->latest()
            ->paginate(20)
            ->withQueryString();

        return view('repairs.index', compact('repairTickets'));
    }

    public function create(): View
    {
        $customers = Customer::where('is_active', true)->orderBy('name')->get();
        $technicians = User::orderBy('name')->get();

        return view('repairs.create', compact('customers', 'technicians'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $this->validated($request);

        $ticket = DB::transaction(function () use ($validated, $request) {
            $ticket = RepairTicket::create([
                ...$validated,
                'ticket_number' => $this->generateTicketNumber(),
                'status' => 'received',
                'received_date' => now(),
            ]);

            $ticket->statusLogs()->create([
                'status' => 'received',
                'note' => 'Ticket created.',
                'changed_by' => $request->user()->id,
            ]);

            return $ticket;
        });

        return redirect()->route('repair-tickets.show', $ticket)->with('success', 'Repair ticket created.');
    }

    public function show(RepairTicket $repairTicket): View
    {
        $repairTicket->load(['customer', 'technician', 'statusLogs.changedBy']);
        $technicians = User::orderBy('name')->get();

        return view('repairs.show', compact('repairTicket', 'technicians'));
    }

    public function edit(RepairTicket $repairTicket): View
    {
        $customers = Customer::where('is_active', true)->orderBy('name')->get();
        $technicians = User::orderBy('name')->get();

        return view('repairs.edit', compact('repairTicket', 'customers', 'technicians'));
    }

    public function update(Request $request, RepairTicket $repairTicket): RedirectResponse
    {
        $repairTicket->update($this->validated($request));

        return redirect()->route('repair-tickets.show', $repairTicket)->with('success', 'Repair ticket updated.');
    }

    public function destroy(RepairTicket $repairTicket): RedirectResponse
    {
        $repairTicket->delete();

        return redirect()->route('repair-tickets.index')->with('success', 'Repair ticket deleted.');
    }

    public function updateStatus(Request $request, RepairTicket $repairTicket): RedirectResponse
    {
        $validated = $request->validate([
            'status' => ['required', 'in:received,diagnosing,awaiting_parts,in_repair,ready_for_pickup,completed,cancelled'],
            'note' => ['nullable', 'string', 'max:255'],
        ]);

        DB::transaction(function () use ($validated, $repairTicket, $request) {
            $repairTicket->update([
                'status' => $validated['status'],
                'completed_date' => $validated['status'] === 'completed' ? now() : $repairTicket->completed_date,
            ]);

            $repairTicket->statusLogs()->create([
                'status' => $validated['status'],
                'note' => $validated['note'] ?? null,
                'changed_by' => $request->user()->id,
            ]);
        });

        return back()->with('success', 'Repair status updated.');
    }

    private function validated(Request $request): array
    {
        return $request->validate([
            'customer_id' => ['required', 'exists:customers,id'],
            'device_type' => ['required', 'string', 'max:255'],
            'device_brand' => ['nullable', 'string', 'max:255'],
            'device_model' => ['nullable', 'string', 'max:255'],
            'imei_serial' => ['nullable', 'string', 'max:255'],
            'issue_description' => ['required', 'string'],
            'technician_id' => ['nullable', 'exists:users,id'],
            'estimated_cost' => ['nullable', 'numeric', 'min:0'],
            'final_cost' => ['nullable', 'numeric', 'min:0'],
        ]);
    }

    private function generateTicketNumber(): string
    {
        do {
            $number = 'RPR-'.now()->format('ymd').'-'.strtoupper(Str::random(5));
        } while (RepairTicket::where('ticket_number', $number)->exists());

        return $number;
    }
}
