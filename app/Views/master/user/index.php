<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<div class="card">
    <div class="card-header">
        <h3 class="card-title">User List</h3>
        <div class="card-tools">
            <?php if (hasPermission('users', 'create')): ?>
            <a href="<?= base_url('master/user/create') ?>" class="btn btn-primary btn-sm">
                <i class="fas fa-plus"></i> Add User
            </a>
            <?php endif; ?>
        </div>
    </div>
    <div class="card-body">
        <table id="userTable" class="table table-bordered table-striped">
            <thead>
                <tr>
                    <th>Username</th>
                    <th>Photo</th>
                    <th>Full Name</th>
                    <th>Email</th>
                    <th>Phone</th>
                    <th>Last Login</th>
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
    const table = $('#userTable').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: "<?= base_url('master/user/datatable') ?>",
            type: "POST",
            data: function(d) {
                d.<?= csrf_token() ?> = '<?= csrf_hash() ?>';
            }
        },
        columns: [
            { data: 'username' },
            { data: 'photo', orderable: false, searchable: false },
            { data: 'full_name' },
            { data: 'email' },
            { data: 'phone' },
            { data: 'last_login' },
            { data: 'status', orderable: false },
            { data: 'actions', orderable: false, searchable: false }
        ],
        order: [[0, 'asc']]
    });

    // Delete handler
    $('#userTable').on('click', '.btn-delete', function() {
        const id = $(this).data('id');
        
        Swal.fire({
            title: 'Are you sure?',
            text: "You won't be able to revert this!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Yes, delete it!'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: "<?= base_url('master/user/delete') ?>/" + id,
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

    // Reset password handler
    $('#userTable').on('click', '.btn-reset-password', function() {
        const id = $(this).data('id');
        
        Swal.fire({
            title: 'Reset Password?',
            text: "This will reset the user's password to default",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Yes, reset it!'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: "<?= base_url('master/user/reset-password') ?>/" + id,
                    type: "POST",
                    data: {
                        <?= csrf_token() ?>: '<?= csrf_hash() ?>'
                    },
                    success: function(response) {
                        if (response.success) {
                            Swal.fire('Success!', response.message, 'success');
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

    // Toggle status handler
    $('#userTable').on('click', '.btn-toggle-status', function() {
        const id = $(this).data('id');
        const currentStatus = $(this).data('status');
        const newStatus = currentStatus === 'active' ? 'inactive' : 'active';
        
        Swal.fire({
            title: 'Change Status?',
            text: `Change user status to ${newStatus}?`,
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Yes, change it!'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: "<?= base_url('master/user/toggle-status') ?>/" + id,
                    type: "POST",
                    data: {
                        <?= csrf_token() ?>: '<?= csrf_hash() ?>'
                    },
                    success: function(response) {
                        if (response.success) {
                            Swal.fire('Success!', response.message, 'success');
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
