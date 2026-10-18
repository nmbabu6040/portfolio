@extends('admin.base')
@section('c')
    @php
        $lab = $types[$d->type][0];
        $rows = old(
            'items',
            $items
                ->map(fn($i) => ['description' => $i->description, 'qty' => $i->qty + 0, 'price' => $i->price + 0])
                ->all(),
        );
    @endphp
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
        <h1 class="h4 mb-0">{{ $d->id ? $lab . ' ' . $d->number : 'New ' . strtolower($lab) }}@if ($d->id)
                <span class="badge text-bg-secondary fs-6">{{ $d->status }}</span>
            @endif
        </h1>
        <a class="btn btn-outline-secondary" href="/admin/documents"><i class="bi bi-arrow-left me-1"></i>All documents</a>
    </div>

    @if ($d->id)
        <div class="d-flex flex-wrap gap-2 mb-3">
            <a class="btn btn-primary" href="/admin/documents/{{ $d->id }}/pdf" target="_blank"><i
                    class="bi bi-file-earmark-pdf me-1"></i>Open PDF</a>
            <button class="btn btn-outline-primary" type="button" data-bs-toggle="collapse" data-bs-target="#mailbox"><i
                    class="bi bi-send me-1"></i>Email to client</button>
            @if ($d->type == 'quotation')
                <form method="post" action="/admin/documents/{{ $d->id }}/convert">@csrf<button
                        class="btn btn-outline-secondary">Make invoice</button></form>
            @endif
            <form method="post" action="/admin/documents/{{ $d->id }}/duplicate">@csrf<button
                    class="btn btn-outline-secondary">Duplicate</button></form>
            <form method="post" action="/admin/documents/{{ $d->id }}/status" class="d-flex gap-2">@csrf<select
                    name="status" class="form-select">
                    @foreach ($statuses as $st)
                        <option @selected($d->status == $st)>{{ $st }}</option>
                    @endforeach
                </select><button class="btn btn-outline-secondary">Set status</button></form>
            <form method="post" action="/admin/documents/{{ $d->id }}"
                onsubmit="return confirm('Delete this document?')">@csrf @method('DELETE')<button
                    class="btn btn-outline-danger">Delete</button></form>
        </div>
        <div class="collapse mb-4" id="mailbox">
            <form method="post" action="/admin/documents/{{ $d->id }}/send" class="acard p-4"
                style="max-width:640px">@csrf
                <div class="mb-3"><label class="form-label fw-semibold">Send to</label><input type="email"
                        name="to" class="form-control" value="{{ old('to', $d->client_email) }}" required></div>
                <div class="mb-3"><label class="form-label fw-semibold">Message (optional)</label>
                    <textarea name="message" rows="4" class="form-control" placeholder="Leave empty to use a short default message">{{ old('message') }}</textarea>
                    <div class="form-text">The PDF is attached. Save your changes first, the email uses the saved version.
                    </div>
                </div>
                <button class="btn btn-primary">Send email</button>
            </form>
        </div>
    @endif

    <form method="post" action="/admin/documents{{ $d->id ? '/' . $d->id : '' }}" id="docform">@csrf
        @unless ($d->id)
            <input type="hidden" name="type" value="{{ $d->type }}">
        @endunless
        <input type="hidden" name="lead_id" value="{{ old('lead_id', $d->lead_id) }}">
        <div class="row g-4">
            <div class="col-lg-7">
                <div class="acard p-4 h-100">
                    <h2 class="h6 mb-3">Client</h2>
                    <div class="row g-3">
                        <div class="col-md-6"><label class="form-label">Name</label><input name="client_name"
                                class="form-control" value="{{ old('client_name', $d->client_name) }}" required></div>
                        <div class="col-md-6"><label class="form-label">Email</label><input type="email"
                                name="client_email" class="form-control"
                                value="{{ old('client_email', $d->client_email) }}"></div>
                        <div class="col-md-6"><label class="form-label">Phone</label><input name="client_phone"
                                class="form-control" value="{{ old('client_phone', $d->client_phone) }}"></div>
                        <div class="col-12"><label class="form-label">Address</label>
                            <textarea name="client_address" rows="2" class="form-control">{{ old('client_address', $d->client_address) }}</textarea>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-5">
                <div class="acard p-4 h-100">
                    <h2 class="h6 mb-3">Details</h2>
                    <div class="row g-3">
                        <div class="col-6"><label class="form-label">Date</label><input type="date" name="issue_date"
                                class="form-control" value="{{ old('issue_date', $d->issue_date) }}" required></div>
                        <div class="col-6"><label
                                class="form-label">{{ $d->type == 'quotation' ? 'Valid until' : 'Due date' }}</label><input
                                type="date" name="due_date" class="form-control"
                                value="{{ old('due_date', $d->due_date) }}"></div>
                        <div class="col-4"><label class="form-label">Currency</label><input name="currency"
                                class="form-control text-uppercase" maxlength="8"
                                value="{{ old('currency', $d->currency) }}" required></div>
                        <div class="col-4"><label class="form-label">Tax %</label><input type="number" step="0.01"
                                min="0" max="100" name="tax_percent" id="tax" class="form-control"
                                value="{{ old('tax_percent', $d->tax_percent + 0) }}"></div>
                        <div class="col-4"><label class="form-label">Discount</label><input type="number"
                                step="0.01" min="0" name="discount" id="discount" class="form-control"
                                value="{{ old('discount', $d->discount + 0) }}"></div>
                    </div>
                </div>
            </div>
        </div>

        <div class="acard p-4 mt-4">
            <h2 class="h6 mb-3">Items</h2>
            <div class="table-responsive">
                <table class="table align-middle mb-2">
                    <thead>
                        <tr>
                            <th>Description</th>
                            <th style="width:100px">Qty</th>
                            <th style="width:140px">Price</th>
                            <th style="width:130px" class="text-end">Line total</th>
                            <th style="width:44px"></th>
                        </tr>
                    </thead>
                    <tbody id="rows">
                        @foreach ($rows as $i => $r)
                            <tr>
                                <td><input name="items[{{ $i }}][description]" class="form-control"
                                        value="{{ $r['description'] }}" required></td>
                                <td><input type="number" step="0.01" min="0.01"
                                        name="items[{{ $i }}][qty]" class="form-control" data-q
                                        value="{{ $r['qty'] }}" required></td>
                                <td><input type="number" step="0.01" min="0"
                                        name="items[{{ $i }}][price]" class="form-control" data-p
                                        value="{{ $r['price'] }}" required></td>
                                <td class="text-end"><span data-l>0.00</span></td>
                                <td><button type="button" class="icon-btn del" data-rm title="Remove"><i
                                            class="bi bi-x-lg"></i></button></td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <button type="button" class="btn btn-sm btn-outline-primary" id="add"><i
                    class="bi bi-plus-lg me-1"></i>Add item</button>
            <div class="ms-auto mt-3" style="max-width:320px">
                <div class="d-flex justify-content-between py-1"><span class="text-body-secondary">Subtotal</span><b
                        id="t-sub">0.00</b></div>
                <div class="d-flex justify-content-between py-1"><span class="text-body-secondary">Discount</span><b
                        id="t-disc">0.00</b></div>
                <div class="d-flex justify-content-between py-1"><span class="text-body-secondary">Tax</span><b
                        id="t-tax">0.00</b></div>
                <div class="d-flex justify-content-between py-2 border-top fs-5"><span>Total</span><b
                        id="t-total">0.00</b></div>
            </div>
        </div>

        <div class="row g-4 mt-0">
            <div class="col-lg-6">
                <div class="acard p-4 h-100"><label class="form-label fw-semibold">Notes (shown on the PDF)</label>
                    <textarea name="notes" rows="4" class="form-control">{{ old('notes', $d->notes) }}</textarea>
                </div>
            </div>
            <div class="col-lg-6">
                <div class="acard p-4 h-100"><label class="form-label fw-semibold">Terms (shown on the PDF)</label>
                    <textarea name="terms" rows="4" class="form-control">{{ old('terms', $d->terms) }}</textarea>
                </div>
            </div>
        </div>
        <div class="mt-4"><button class="btn btn-primary px-4"><i class="bi bi-check2 me-1"></i>Save</button> <a
                class="btn btn-outline-secondary" href="/admin/documents">Cancel</a></div>
    </form>

    <template id="rowtpl">
        <tr>
            <td><input name="items[__i__][description]" class="form-control" required></td>
            <td><input type="number" step="0.01" min="0.01" name="items[__i__][qty]" class="form-control"
                    data-q value="1" required></td>
            <td><input type="number" step="0.01" min="0" name="items[__i__][price]" class="form-control"
                    data-p value="0" required></td>
            <td class="text-end"><span data-l>0.00</span></td>
            <td><button type="button" class="icon-btn del" data-rm title="Remove"><i class="bi bi-x-lg"></i></button>
            </td>
        </tr>
    </template>
    <script>
        (function() {
            var body = document.getElementById('rows'),
                tpl = document.getElementById('rowtpl').innerHTML,
                n = body.children.length + 100;

            function num(v) {
                v = parseFloat(v);
                return isNaN(v) ? 0 : v;
            }

            function money(v) {
                return v.toLocaleString(undefined, {
                    minimumFractionDigits: 2,
                    maximumFractionDigits: 2
                });
            }

            function calc() {
                var sub = 0;
                body.querySelectorAll('tr').forEach(function(tr) {
                    var l = num(tr.querySelector('[data-q]').value) * num(tr.querySelector('[data-p]').value);
                    sub += l;
                    tr.querySelector('[data-l]').textContent = money(l);
                });
                var disc = Math.min(num(document.getElementById('discount').value), sub),
                    tax = (sub - disc) * num(document.getElementById('tax').value) / 100;
                document.getElementById('t-sub').textContent = money(sub);
                document.getElementById('t-disc').textContent = money(disc);
                document.getElementById('t-tax').textContent = money(tax);
                document.getElementById('t-total').textContent = money(sub - disc + tax);
            }
            document.getElementById('add').onclick = function() {
                var t = document.createElement('table');
                t.innerHTML = '<tbody>' + tpl.replace(/__i__/g, n++) + '</tbody>';
                body.appendChild(t.querySelector('tr'));
                calc();
            };
            body.addEventListener('click', function(e) {
                var b = e.target.closest('[data-rm]');
                if (b && body.children.length > 1) {
                    b.closest('tr').remove();
                    calc();
                }
            });
            document.getElementById('docform').addEventListener('input', calc);
            calc();
        })();
    </script>
@endsection
