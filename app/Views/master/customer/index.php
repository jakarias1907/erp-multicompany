<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<div class="card">
    <div class="card-header">
        <h3 class="card-title">Customer List</h3>
        <div class="card-tools">
            <?php if (hasPermission('customers', 'create')): ?>
            <a href="<?= base_url('master/customer/create') ?>" class="btn btn-primary btn-sm">
                <i class="fas fa-plus"></i> Add Customer
            </a>
            <a href="<?= base_url('master/customer/export') ?>" class="btn btn-success btn-sm">
                <i class="fas fa-file-excel"></i> Export
            </a>
            <?php endif; ?>
        </div>
    </div>
    <div class="card-body">
        <table id="customerTable" class="table table-bordered table-striped">
            <thead>
                <tr>
                    <th>Code</th>
                    <th>Name</th>
                    <th>Type</th>
                    <th>Contact Person</th>
                    <th>Email</th>
                    <th>Phone</th>
                    <th>Credit Limit</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody></tbody>
        </table>
    </div>
</div>
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
$(document).ready(function() {
    const table = $('#customerTable').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: "<?= base_url('master/customer/datatable') ?>",
            type: "POST",
            data: function(d) {
                d.<?= csrf_token() ?> = '<?= csrf_hash() ?>';
            }
        },
        columns: [
            { data: 'code' },
            { data: 'name' },
            { data: 'type', orderable: false },
            { data: 'contact_person' },
            { data: 'email' },
            { data: 'phone' },
            { data: 'credit_limit' },
            { data: 'actions', orderable: false, searchable: false }
        ]
    });

    $('#customerTable').on('click', '.btn-delete', function() {
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
                    url: "<?= base_url('master/customer/delete') ?>/" + id,
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
});
</script>
<?= $this->endSection() ?>
