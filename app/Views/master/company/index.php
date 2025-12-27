<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<div class="card">
    <div class="card-header">
        <h3 class="card-title">Company List</h3>
        <div class="card-tools">
            <a href="<?= base_url('master/company/create') ?>" class="btn btn-primary btn-sm">
                <i class="fas fa-plus"></i> Add Company
            </a>
        </div>
    </div>
    <div class="card-body">
        <table id="companyTable" class="table table-bordered table-striped">
            <thead>
                <tr>
                    <th>Code</th>
                    <th>Logo</th>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Phone</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <!-- Data loaded via AJAX -->
            </tbody>
        </table>
    </div>
</div>
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
$(document).ready(function() {
    // Initialize DataTable
    const table = $('#companyTable').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: "<?= base_url('master/company/datatable') ?>",
            type: "POST",
            data: function(d) {
                d.<?= csrf_token() ?> = '<?= csrf_hash() ?>';
            }
        },
        columns: [
            { data: 'code' },
            { data: 'logo', orderable: false, searchable: false },
            { data: 'name' },
            { data: 'email' },
            { data: 'phone' },
            { data: 'status', orderable: false },
            { data: 'actions', orderable: false, searchable: false }
        ],
        order: [[0, 'asc']]
    });

    // Delete handler
    $('#companyTable').on('click', '.btn-delete', function() {
        const id = $(this).data('id');
        
        Swal.fire({
            title: 'Are you sure?',
            text: "You won't be able to revert this!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Yes, delete it!'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: "<?= base_url('master/company/delete') ?>/" + id,
                    type: "POST",
                    data: {
                        <?= csrf_token() ?>: '<?= csrf_hash() ?>'
                    },
                    success: function(response) {
                        if (response.success) {
                            Swal.fire('Deleted!', response.message, 'success');
                            table.ajax.reload();
                        } else {
                            Swal.fire('Error!', response.message, 'error');
                        }
                    },
                    error: function() {
                        Swal.fire('Error!', 'An error occurred', 'error');
                    }
                });
            }
        });
    });
});
</script>
<?= $this->endSection() ?>
