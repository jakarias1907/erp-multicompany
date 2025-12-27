<div class="row">
    <div class="col-md-6">
        <div class="form-group">
            <label for="sku">SKU/Code <span class="text-danger">*</span></label>
            <input type="text" class="form-control <?= isset($errors['sku']) ? 'is-invalid' : '' ?>" 
                   id="sku" name="sku" value="<?= old('sku', $product['sku'] ?? '') ?>" required>
            <?php if (isset($errors['sku'])): ?>
                <div class="invalid-feedback"><?= $errors['sku'] ?></div>
            <?php endif; ?>
        </div>
    </div>
    <div class="col-md-6">
        <div class="form-group">
            <label for="name">Product Name <span class="text-danger">*</span></label>
            <input type="text" class="form-control <?= isset($errors['name']) ? 'is-invalid' : '' ?>" 
                   id="name" name="name" value="<?= old('name', $product['name'] ?? '') ?>" required>
            <?php if (isset($errors['name'])): ?>
                <div class="invalid-feedback"><?= $errors['name'] ?></div>
            <?php endif; ?>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-6">
        <div class="form-group">
            <label for="category_id">Category</label>
            <select class="form-control select2" id="category_id" name="category_id">
                <option value="">-- Select Category --</option>
                <?php foreach ($categories as $category): ?>
                    <option value="<?= $category['id'] ?>" 
                        <?= old('category_id', $product['category_id'] ?? '') == $category['id'] ? 'selected' : '' ?>>
                        <?= esc($category['name']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
    </div>
    <div class="col-md-6">
        <div class="form-group">
            <label for="unit_id">Unit</label>
            <select class="form-control select2" id="unit_id" name="unit_id">
                <option value="">-- Select Unit --</option>
                <?php foreach ($units as $unit): ?>
                    <option value="<?= $unit['id'] ?>" 
                        <?= old('unit_id', $product['unit_id'] ?? '') == $unit['id'] ? 'selected' : '' ?>>
                        <?= esc($unit['name']) ?> (<?= esc($unit['code']) ?>)
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-4">
        <div class="form-group">
            <label for="price">Selling Price <span class="text-danger">*</span></label>
            <input type="number" step="0.01" class="form-control <?= isset($errors['price']) ? 'is-invalid' : '' ?>" 
                   id="price" name="price" value="<?= old('price', $product['price'] ?? 0) ?>" required>
            <?php if (isset($errors['price'])): ?>
                <div class="invalid-feedback"><?= $errors['price'] ?></div>
            <?php endif; ?>
        </div>
    </div>
    <div class="col-md-4">
        <div class="form-group">
            <label for="cost">Cost Price</label>
            <input type="number" step="0.01" class="form-control <?= isset($errors['cost']) ? 'is-invalid' : '' ?>" 
                   id="cost" name="cost" value="<?= old('cost', $product['cost'] ?? 0) ?>">
            <?php if (isset($errors['cost'])): ?>
                <div class="invalid-feedback"><?= $errors['cost'] ?></div>
            <?php endif; ?>
        </div>
    </div>
    <div class="col-md-4">
        <div class="form-group">
            <label for="stock_alert_level">Stock Alert Level</label>
            <input type="number" class="form-control" id="stock_alert_level" name="stock_alert_level" 
                   value="<?= old('stock_alert_level', $product['stock_alert_level'] ?? 0) ?>">
        </div>
    </div>
</div>

<div class="form-group">
    <label for="description">Description</label>
    <textarea class="form-control" id="description" name="description" rows="3"><?= old('description', $product['description'] ?? '') ?></textarea>
</div>

<div class="row">
    <div class="col-md-6">
        <div class="form-group">
            <label for="status">Status <span class="text-danger">*</span></label>
            <select class="form-control" id="status" name="status" required>
                <option value="active" <?= old('status', $product['status'] ?? 'active') == 'active' ? 'selected' : '' ?>>Active</option>
                <option value="inactive" <?= old('status', $product['status'] ?? '') == 'inactive' ? 'selected' : '' ?>>Inactive</option>
            </select>
        </div>
    </div>
    <div class="col-md-6">
        <div class="form-group">
            <label for="image">Product Image</label>
            <div class="custom-file">
                <input type="file" class="custom-file-input <?= isset($errors['image']) ? 'is-invalid' : '' ?>" 
                       id="image" name="image" accept="image/*">
                <label class="custom-file-label" for="image">Choose file</label>
                <?php if (isset($errors['image'])): ?>
                    <div class="invalid-feedback"><?= $errors['image'] ?></div>
                <?php endif; ?>
            </div>
            <small class="form-text text-muted">Max size: 2MB. Accepted formats: JPG, PNG, GIF</small>
        </div>
        <?php if (isset($product['image']) && $product['image']): ?>
            <div class="mt-2">
                <label>Current Image:</label><br>
                <img src="<?= base_url('uploads/products/' . $product['image']) ?>" 
                     alt="Current Image" class="img-thumbnail" style="max-width: 200px;">
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
