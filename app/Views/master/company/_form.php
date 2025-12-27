<div class="row">
    <div class="col-md-8">
        <div class="form-group">
            <label for="name">Company Name <span class="text-danger">*</span></label>
            <input type="text" class="form-control <?= isset($errors['name']) ? 'is-invalid' : '' ?>" 
                   id="name" name="name" value="<?= old('name', $company['name'] ?? '') ?>" required>
            <?php if (isset($errors['name'])): ?>
                <div class="invalid-feedback"><?= $errors['name'] ?></div>
            <?php endif; ?>
        </div>
    </div>
    <div class="col-md-4">
        <div class="form-group">
            <label for="code">Company Code <span class="text-danger">*</span></label>
            <input type="text" class="form-control <?= isset($errors['code']) ? 'is-invalid' : '' ?>" 
                   id="code" name="code" value="<?= old('code', $company['code'] ?? '') ?>" required>
            <?php if (isset($errors['code'])): ?>
                <div class="invalid-feedback"><?= $errors['code'] ?></div>
            <?php endif; ?>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-6">
        <div class="form-group">
            <label for="email">Email</label>
            <input type="email" class="form-control <?= isset($errors['email']) ? 'is-invalid' : '' ?>" 
                   id="email" name="email" value="<?= old('email', $company['email'] ?? '') ?>">
            <?php if (isset($errors['email'])): ?>
                <div class="invalid-feedback"><?= $errors['email'] ?></div>
            <?php endif; ?>
        </div>
    </div>
    <div class="col-md-6">
        <div class="form-group">
            <label for="phone">Phone</label>
            <input type="text" class="form-control" id="phone" name="phone" 
                   value="<?= old('phone', $company['phone'] ?? '') ?>">
        </div>
    </div>
</div>

<div class="form-group">
    <label for="address">Address</label>
    <textarea class="form-control" id="address" name="address" rows="3"><?= old('address', $company['address'] ?? '') ?></textarea>
</div>

<div class="row">
    <div class="col-md-6">
        <div class="form-group">
            <label for="tax_id">Tax ID / NPWP</label>
            <input type="text" class="form-control <?= isset($errors['tax_id']) ? 'is-invalid' : '' ?>" 
                   id="tax_id" name="tax_id" value="<?= old('tax_id', $company['tax_id'] ?? '') ?>">
            <?php if (isset($errors['tax_id'])): ?>
                <div class="invalid-feedback"><?= $errors['tax_id'] ?></div>
            <?php endif; ?>
        </div>
    </div>
    <div class="col-md-6">
        <div class="form-group">
            <label for="status">Status <span class="text-danger">*</span></label>
            <select class="form-control" id="status" name="status" required>
                <option value="active" <?= old('status', $company['status'] ?? 'active') == 'active' ? 'selected' : '' ?>>Active</option>
                <option value="inactive" <?= old('status', $company['status'] ?? '') == 'inactive' ? 'selected' : '' ?>>Inactive</option>
            </select>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-6">
        <div class="form-group">
            <label for="logo">Company Logo</label>
            <div class="custom-file">
                <input type="file" class="custom-file-input <?= isset($errors['logo']) ? 'is-invalid' : '' ?>" 
                       id="logo" name="logo" accept="image/*">
                <label class="custom-file-label" for="logo">Choose file</label>
                <?php if (isset($errors['logo'])): ?>
                    <div class="invalid-feedback"><?= $errors['logo'] ?></div>
                <?php endif; ?>
            </div>
            <small class="form-text text-muted">Max size: 2MB. Accepted formats: JPG, PNG, GIF</small>
        </div>
        <?php if (isset($company['logo']) && $company['logo']): ?>
            <div class="mt-2">
                <label>Current Logo:</label><br>
                <img src="<?= base_url('uploads/companies/' . $company['logo']) ?>" 
                     alt="Current Logo" class="img-thumbnail" style="max-width: 200px;">
            </div>
        <?php endif; ?>
    </div>
</div>

<script>
// Update file input label
$('.custom-file-input').on('change', function() {
    let fileName = $(this).val().split('\\').pop();
    $(this).next('.custom-file-label').html(fileName);
});
</script>
