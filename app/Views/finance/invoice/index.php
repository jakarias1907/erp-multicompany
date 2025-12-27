<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<div class="card">
    <div class="card-header">
        <h3 class="card-title">Invoice List</h3>
        <div class="card-tools">
            <?php if (hasPermission('invoices', 'create')): ?>
            <a href="<?= base_url('finance/invoice/create') ?>" class="btn btn-primary btn-sm">
                <i class="fas fa-plus"></i> Create Invoice
            </a>
            <?php endif; ?>
        </div>
    </div>
    <div class="card-body">
        <div class="row mb-3">
            <div class="col-md-3">
                <select id="status-filter" class="form-control">
                    <option value="">All Status</option>
                    <option value="draft">Draft</option>
                    <option value="sent">Sent</option>
                    <option value="partial">Partial Paid</option>
                    <option value="paid">Paid</option>
                    <option value="overdue">Overdue</option>
                </select>
            </div>
        </div>
        
        <table id="invoiceTable" class="table table-bordered table-striped">
            <thead>
                <tr>
                    <th>Invoice #</th>
                    <th>Date</th>
                    <th>Customer</th>
                    <th>Total Amount</th>
                    <th>Paid Amount</th>
                    <th>Balance</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody></tbody>
        </table>
    </div>
</div>

<!-- Payment Modal -->
<div class="modal fade" id="paymentModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Record Payment</h5>
                <button type="button" class="close" data-dismiss="modal">&times;</button>
            </div>
            <div class="modal-body">
                <form id="paymentForm">
                    <input type="hidden" id="invoice-id" name="invoice_id">
                    <div class="form-group">
                        <label>Payment Amount</label>
                        <input type="number" step="0.01" class="form-control" name="amount" required>
                    </div>
                    <div class="form-group">
                        <label>Payment Date</label>
                        <input type="date" class="form-control" name="payment_date" value="<?= date('Y-m-d') ?>" required>
                    </div>
                    <div class="form-group">
                        <label>Payment Method</label>
                        <select class="form-control" name="payment_method" required>
                            <option value="cash">Cash</option>
                            <option value="bank_transfer">Bank Transfer</option>
                            <option value="credit_card">Credit Card</option>
                            <option value="check">Check</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Notes</label>
                        <textarea class="form-control" name="notes" rows="2"></textarea>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" id="save-payment">Save Payment</button>
            </div>
        </div>
    </div>
</div>
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
$(document).ready(function() {
    const table = $('#invoiceTable').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: "<?= base_url('finance/invoice/datatable') ?>",
            type: "POST",
            data: function(d) {
                d.<?= csrf_token() ?> = '<?= csrf_hash() ?>';
                d.status = $('#status-filter').val();
            }
        },
        columns: [
            { data: 'invoice_number' },
            { data: 'invoice_date' },
            { data: 'customer_name' },
            { data: 'total_amount' },
            { data: 'paid_amount' },
            { data: 'balance' },
            { data: 'status', orderable: false },
            { data: 'actions', orderable: false, searchable: false }
        ],
        order: [[1, 'desc']]
    });

    // Status filter
    $('#status-filter').change(function() {
        table.ajax.reload();
    });

    // Delete handler
    $('#invoiceTable').on('click', '.btn-delete', function() {
        const id = $(this).data('id');
        Swal.fire({
            title: 'Are you sure?',
            text: "You won't be able to revert this!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Yes, delete it!'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: "<?= base_url('finance/invoice/delete') ?>/" + id,
                    type: "POST",
                    data: { <?= csrf_token() ?>: '<?= csrf_hash() ?>' },
                    success: function(response) {
                        if (response.success) {
                            Swal.fire('Deleted!', response.message, 'success');
                            table.ajax.reload();
                        } else {
                            Swal.fire('Error!', response.message, 'error');
                        }
                    }
                });
            }
        });
    });

    // Payment modal
    $('#invoiceTable').on('click', '.btn-payment', function() {
        const id = $(this).data('id');
        $('#invoice-id').val(id);
        $('#paymentModal').modal('show');
    });

    // Save payment
    $('#save-payment').click(function() {
        const invoiceId = $('#invoice-id').val();
        const formData = $('#paymentForm').serialize();
        
        $.ajax({
            url: "<?= base_url('finance/invoice/payment') ?>/" + invoiceId,
            type: "POST",
            data: formData + '&<?= csrf_token() ?>=<?= csrf_hash() ?>',
            success: function(response) {
                if (response.success) {
                    Swal.fire('Success!', response.message, 'success');
                    $('#paymentModal').modal('hide');
                    table.ajax.reload();
                } else {
                    Swal.fire('Error!', response.message, 'error');
                }
            }
        });
    });
});
</script>
<?= $this->endSection() ?>
