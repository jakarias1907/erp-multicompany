<?php
$isEdit = isset($supplier);
$action = $isEdit ? base_url('master/supplier/update/' . $supplier['id']) : base_url('master/supplier/store');
?>
<form action="<?= $action ?>" method="post">
    <?= csrf_field() ?>
    <div class="row">
        <div class="col-md-6">
            <div class="form-group">
                <label>Code <span class="text-danger">*</span></label>
                <input type="text" class="form-control <?= isset($errors['code']) ? 'is-invalid' : '' ?>" 
                       name="code" value="<?= old('code', $supplier['code'] ?? '') ?>" required>
                <?php if (isset($errors['code'])): ?><div class="invalid-feedback"><?= $errors['code'] ?></div><?php endif; ?>
            </div>
        </div>
        <div class="col-md-6">
            <div class="form-group">
                <label>Name <span class="text-danger">*</span></label>
                <input type="text" class="form-control <?= isset($errors['name']) ? 'is-invalid' : '' ?>" 
                       name="name" value="<?= old('name', $supplier['name'] ?? '') ?>" required>
                <?php if (isset($errors['name'])): ?><div class="invalid-feedback"><?= $errors['name'] ?></div><?php endif; ?>
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-md-6">
            <div class="form-group">
                <label>Contact Person</label>
                <input type="text" class="form-control" name="contact_person" 
                       value="<?= old('contact_person', $supplier['contact_person'] ?? '') ?>">
            </div>
        </div>
        <div class="col-md-6">
            <div class="form-group">
                <label>Email</label>
                <input type="email" class="form-control <?= isset($errors['email']) ? 'is-invalid' : '' ?>" 
                       name="email" value="<?= old('email', $supplier['email'] ?? '') ?>">
                <?php if (isset($errors['email'])): ?><div class="invalid-feedback"><?= $errors['email'] ?></div><?php endif; ?>
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-md-6">
            <div class="form-group">
                <label>Phone</label>
                <input type="text" class="form-control" name="phone" 
                       value="<?= old('phone', $supplier['phone'] ?? '') ?>">
            </div>
        </div>
        <div class="col-md-6">
            <div class="form-group">
                <label>Payment Term (Days)</label>
                <input type="number" class="form-control" name="payment_term" 
                       value="<?= old('payment_term', $supplier['payment_term'] ?? '30') ?>">
            </div>
        </div>
    </div>
    <div class="form-group">
        <label>Address</label>
        <textarea class="form-control" name="address" rows="3"><?= old('address', $supplier['address'] ?? '') ?></textarea>
    </div>
    <div class="row">
        <div class="col-md-6">
            <div class="form-group">
                <label>Bank Name</label>
                <input type="text" class="form-control" name="bank_name" 
                       value="<?= old('bank_name', $supplier['bank_name'] ?? '') ?>">
            </div>
        </div>
        <div class="col-md-6">
            <div class="form-group">
                <label>Bank Account</label>
                <input type="text" class="form-control" name="bank_account" 
                       value="<?= old('bank_account', $supplier['bank_account'] ?? '') ?>">
            </div>
        </div>
    </div>
    <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> <?= $isEdit ? 'Update' : 'Create' ?></button>
    <a href="<?= base_url('master/supplier') ?>" class="btn btn-secondary"><i class="fas fa-times"></i> Cancel</a>
</form>
