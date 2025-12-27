<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<div class="card">
    <div class="card-header">
        <h3 class="card-title">Manage Permissions - <?= esc($role['name']) ?></h3>
    </div>
    <form action="<?= base_url('master/role/update-permissions/' . $role['id']) ?>" method="post">
        <?= csrf_field() ?>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th>Module</th>
                            <?php foreach ($actions as $action): ?>
                            <th class="text-center">
                                <?= ucfirst($action) ?><br>
                                <input type="checkbox" class="select-all-action" data-action="<?= $action ?>">
                            </th>
                            <?php endforeach; ?>
                            <th class="text-center">All</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($modules as $moduleKey => $moduleName): ?>
                        <tr>
                            <td><strong><?= $moduleName ?></strong></td>
                            <?php foreach ($actions as $action): ?>
                            <td class="text-center">
                                <?php 
                                $checked = isset($permissionMap[$moduleKey]) && in_array($action, $permissionMap[$moduleKey]);
                                ?>
                                <input type="checkbox" name="permissions[]" 
                                       value="<?= $moduleKey ?>.<?= $action ?>" 
                                       class="permission-checkbox module-<?= $moduleKey ?> action-<?= $action ?>"
                                       <?= $checked ? 'checked' : '' ?>>
                            </td>
                            <?php endforeach; ?>
                            <td class="text-center">
                                <input type="checkbox" class="select-all-module" data-module="<?= $moduleKey ?>">
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
        <div class="card-footer">
            <button type="submit" class="btn btn-primary">
                <i class="fas fa-save"></i> Save Permissions
            </button>
            <a href="<?= base_url('master/role') ?>" class="btn btn-secondary">
                <i class="fas fa-times"></i> Cancel
            </a>
        </div>
    </form>
</div>
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
$(document).ready(function() {
    // Select all for action column
    $('.select-all-action').change(function() {
        const action = $(this).data('action');
        const checked = $(this).prop('checked');
        $('.action-' + action).prop('checked', checked);
    });

    // Select all for module row
    $('.select-all-module').change(function() {
        const module = $(this).data('module');
        const checked = $(this).prop('checked');
        $('.module-' + module).prop('checked', checked);
    });
});
</script>
<?= $this->endSection() ?>
