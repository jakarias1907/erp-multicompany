<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<div class="card">
    <div class="card-header">
        <h3 class="card-title">Product List</h3>
        <div class="card-tools">
            <a href="<?= base_url('master/product/create') ?>" class="btn btn-primary btn-sm">
                <i class="fas fa-plus"></i> Add Product
            </a>
        </div>
    </div>
    <div class="card-body">
        <table id="productTable" class="table table-bordered table-striped">
            <thead>
                <tr>
                    <th>SKU</th>
                    <th>Image</th>
                    <th>Name</th>
                    <th>Category</th>
                    <th>Unit</th>
                    <th>Price</th>
                    <th>Stock Alert</th>
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
    const table = $('#productTable').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: "<?= base_url('master/product/datatable') ?>",
            type: "POST",
            data: function(d) {
                d.<?= csrf_token() ?> = '<?= csrf_hash() ?>';
            }
        },
        columns: [
            { data: 'sku' },
            { data: 'image', orderable: false, searchable: false },
            { data: 'name' },
            { data: 'category' },
            { data: 'unit' },
            { data: 'price', className: 'text-right' },
            { data: 'stock_alert', className: 'text-center' },
            { data: 'status', orderable: false },
            { data: 'actions', orderable: false, searchable: false }
        ],
        order: [[0, 'asc']]
    });

    // Delete handler
    $('#productTable').on('click', '.btn-delete', function() {
        const id = $(this).data('id');
        
        confirmDelete("<?= base_url('master/product/delete') ?>/" + id).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: "<?= base_url('master/product/delete') ?>/" + id,
                    type: "POST",
                    data: {
                        <?= csrf_token() ?>: '<?= csrf_hash() ?>'
                    },
                    success: function(response) {
                        if (response.success) {
                            showSuccessAlert(response.message);
                            table.ajax.reload();
                        } else {
                            showErrorAlert(response.message);
                        }
                    },
                    error: function() {
                        showErrorAlert('An error occurred');
                    }
                });
            }
        });
    });
});
</script>
<?= $this->endSection() ?>
