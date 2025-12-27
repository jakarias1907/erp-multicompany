<?php
$isEdit = isset($customer);
$action = $isEdit ? base_url('master/customer/update/' . $customer['id']) : base_url('master/customer/store');
?>
<form action="<?= $action ?>" method="post">
    <?= csrf_field() ?>
    <div class="row">
        <div class="col-md-6">
            <div class="form-group">
                <label>Code <span class="text-danger">*</span></label>
                <input type="text" class="form-control <?= isset($errors['code']) ? 'is-invalid' : '' ?>" 
                       name="code" value="<?= old('code', $customer['code'] ?? '') ?>" required>
                <?php if (isset($errors['code'])): ?><div class="invalid-feedback"><?= $errors['code'] ?></div><?php endif; ?>
            </div>
        </div>
        <div class="col-md-6">
            <div class="form-group">
                <label>Name <span class="text-danger">*</span></label>
                <input type="text" class="form-control <?= isset($errors['name']) ? 'is-invalid' : '' ?>" 
                       name="name" value="<?= old('name', $customer['name'] ?? '') ?>" required>
                <?php if (isset($errors['name'])): ?><div class="invalid-feedback"><?= $errors['name'] ?></div><?php endif; ?>
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-md-6">
            <div class="form-group">
                <label>Type <span class="text-danger">*</span></label>
                <select class="form-control <?= isset($errors['type']) ? 'is-invalid' : '' ?>" name="type" required>
                    <option value="">Select Type</option>
                    <option value="retail" <?= old('type', $customer['type'] ?? '') == 'retail' ? 'selected' : '' ?>>Retail</option>
                    <option value="wholesale" <?= old('type', $customer['type'] ?? '') == 'wholesale' ? 'selected' : '' ?>>Wholesale</option>
                </select>
                <?php if (isset($errors['type'])): ?><div class="invalid-feedback"><?= $errors['type'] ?></div><?php endif; ?>
            </div>
        </div>
        <div class="col-md-6">
            <div class="form-group">
                <label>Contact Person</label>
                <input type="text" class="form-control" name="contact_person" 
                       value="<?= old('contact_person', $customer['contact_person'] ?? '') ?>">
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-md-6">
            <div class="form-group">
                <label>Email</label>
                <input type="email" class="form-control <?= isset($errors['email']) ? 'is-invalid' : '' ?>" 
                       name="email" value="<?= old('email', $customer['email'] ?? '') ?>">
                <?php if (isset($errors['email'])): ?><div class="invalid-feedback"><?= $errors['email'] ?></div><?php endif; ?>
            </div>
        </div>
        <div class="col-md-6">
            <div class="form-group">
                <label>Phone</label>
                <input type="text" class="form-control" name="phone" 
                       value="<?= old('phone', $customer['phone'] ?? '') ?>">
            </div>
        </div>
    </div>
    <div class="form-group">
        <label>Address</label>
        <textarea class="form-control" name="address" rows="3"><?= old('address', $customer['address'] ?? '') ?></textarea>
    </div>
    <div class="row">
        <div class="col-md-6">
            <div class="form-group">
                <label>Credit Limit</label>
                <input type="number" step="0.01" class="form-control" name="credit_limit" 
                       value="<?= old('credit_limit', $customer['credit_limit'] ?? '0') ?>">
            </div>
        </div>
        <div class="col-md-6">
            <div class="form-group">
                <label>Payment Term (Days)</label>
                <input type="number" class="form-control" name="payment_term" 
                       value="<?= old('payment_term', $customer['payment_term'] ?? '30') ?>">
            </div>
        </div>
    </div>
    <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> <?= $isEdit ? 'Update' : 'Create' ?></button>
    <a href="<?= base_url('master/customer') ?>" class="btn btn-secondary"><i class="fas fa-times"></i> Cancel</a>
</form>
