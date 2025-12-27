<?php
$isEdit = isset($role);
$action = $isEdit ? base_url('master/role/update/' . $role['id']) : base_url('master/role/store');
?>
<form action="<?= $action ?>" method="post">
    <?= csrf_field() ?>
    <div class="form-group">
        <label>Name <span class="text-danger">*</span></label>
        <input type="text" class="form-control <?= isset($errors['name']) ? 'is-invalid' : '' ?>" 
               name="name" value="<?= old('name', $role['name'] ?? '') ?>" required>
        <?php if (isset($errors['name'])): ?>
            <div class="invalid-feedback"><?= $errors['name'] ?></div>
        <?php endif; ?>
    </div>
    <div class="form-group">
        <label>Description</label>
        <textarea class="form-control" name="description" rows="3"><?= old('description', $role['description'] ?? '') ?></textarea>
    </div>
    <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> <?= $isEdit ? 'Update' : 'Create' ?></button>
    <a href="<?= base_url('master/role') ?>" class="btn btn-secondary"><i class="fas fa-times"></i> Cancel</a>
</form>
