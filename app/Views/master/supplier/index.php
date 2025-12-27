<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<div class="card">
    <div class="card-header">
        <h3 class="card-title">Supplier List</h3>
        <div class="card-tools">
            <?php if (hasPermission('suppliers', 'create')): ?>
            <a href="<?= base_url('master/supplier/create') ?>" class="btn btn-primary btn-sm">
                <i class="fas fa-plus"></i> Add Supplier
            </a>
            <a href="<?= base_url('master/supplier/export') ?>" class="btn btn-success btn-sm">
                <i class="fas fa-file-excel"></i> Export
            </a>
            <?php endif; ?>
        </div>
    </div>
    <div class="card-body">
        <table id="supplierTable" class="table table-bordered table-striped">
            <thead>
                <tr>
                    <th>Code</th>
                    <th>Name</th>
                    <th>Contact Person</th>
                    <th>Email</th>
                    <th>Phone</th>
                    <th>Payment Term</th>
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
    const table = $('#supplierTable').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: "<?= base_url('master/supplier/datatable') ?>",
            type: "POST",
            data: function(d) {
                d.<?= csrf_token() ?> = '<?= csrf_hash() ?>';
            }
        },
        columns: [
            { data: 'code' },
            { data: 'name' },
            { data: 'contact_person' },
            { data: 'email' },
            { data: 'phone' },
            { data: 'payment_term' },
            { data: 'actions', orderable: false, searchable: false }
        ]
    });

    $('#supplierTable').on('click', '.btn-delete', function() {
        const id = $(this).data('id');
        Swal.fire({
            title: 'Are you sure?',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Yes, delete it!'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: "<?= base_url('master/supplier/delete') ?>/" + id,
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
