@php $t = $repairTicket ?? new \App\Models\RepairTicket(); @endphp

<div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
    <div>
        <x-input-label for="customer_id" value="Customer" />
        <select id="customer_id" name="customer_id" class="mt-1 block w-full bg-surface-hover border-border-strong text-ink rounded-md shadow-sm" required>
            <option value="">Select customer</option>
            @foreach ($customers as $customer)
                <option value="{{ $customer->id }}" @selected(old('customer_id', $t->customer_id) == $customer->id)>{{ $customer->name }} ({{ $customer->phone }})</option>
            @endforeach
        </select>
        <x-input-error :messages="$errors->get('customer_id')" class="mt-2" />
    </div>

    <div>
        <x-input-label for="technician_id" value="Technician (optional)" />
        <select id="technician_id" name="technician_id" class="mt-1 block w-full bg-surface-hover border-border-strong text-ink rounded-md shadow-sm">
            <option value="">Unassigned</option>
            @foreach ($technicians as $technician)
                <option value="{{ $technician->id }}" @selected(old('technician_id', $t->technician_id) == $technician->id)>{{ $technician->name }}</option>
            @endforeach
        </select>
        <x-input-error :messages="$errors->get('technician_id')" class="mt-2" />
    </div>

    <div>
        <x-input-label for="device_type" value="Device Type" />
        <x-text-input id="device_type" name="device_type" type="text" class="mt-1 block w-full" value="{{ old('device_type', $t->device_type) }}" placeholder="e.g. Smartphone, Laptop" required />
        <x-input-error :messages="$errors->get('device_type')" class="mt-2" />
    </div>

    <div>
        <x-input-label for="device_brand" value="Brand" />
        <x-text-input id="device_brand" name="device_brand" type="text" class="mt-1 block w-full" value="{{ old('device_brand', $t->device_brand) }}" />
    </div>

    <div>
        <x-input-label for="device_model" value="Model" />
        <x-text-input id="device_model" name="device_model" type="text" class="mt-1 block w-full" value="{{ old('device_model', $t->device_model) }}" />
    </div>

    <div>
        <x-input-label for="imei_serial" value="IMEI/Serial (optional)" />
        <x-text-input id="imei_serial" name="imei_serial" type="text" class="mt-1 block w-full" value="{{ old('imei_serial', $t->imei_serial) }}" />
    </div>

    <div>
        <x-input-label for="estimated_cost" value="Estimated Cost" />
        <x-text-input id="estimated_cost" name="estimated_cost" type="number" step="0.01" min="0" class="mt-1 block w-full" value="{{ old('estimated_cost', $t->estimated_cost) }}" />
    </div>

    <div>
        <x-input-label for="final_cost" value="Final Cost (once repaired)" />
        <x-text-input id="final_cost" name="final_cost" type="number" step="0.01" min="0" class="mt-1 block w-full" value="{{ old('final_cost', $t->final_cost) }}" />
    </div>
</div>

<div>
    <x-input-label for="issue_description" value="Issue Description" />
    <textarea id="issue_description" name="issue_description" rows="3" class="mt-1 block w-full bg-surface-hover border-border-strong text-ink rounded-md shadow-sm" required>{{ old('issue_description', $t->issue_description) }}</textarea>
    <x-input-error :messages="$errors->get('issue_description')" class="mt-2" />
</div>
