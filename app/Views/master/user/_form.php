<?php
$isEdit = isset($user);
$action = $isEdit ? base_url('master/user/update/' . $user['id']) : base_url('master/user/store');
?>

<form action="<?= $action ?>" method="post" enctype="multipart/form-data" id="userForm">
    <?= csrf_field() ?>
    
    <div class="row">
        <div class="col-md-6">
            <div class="form-group">
                <label for="username">Username <span class="text-danger">*</span></label>
                <input type="text" class="form-control <?= isset($errors['username']) ? 'is-invalid' : '' ?>" 
                       id="username" name="username" value="<?= old('username', $user['username'] ?? '') ?>" required>
                <?php if (isset($errors['username'])): ?>
                    <div class="invalid-feedback"><?= $errors['username'] ?></div>
                <?php endif; ?>
            </div>
        </div>
        
        <div class="col-md-6">
            <div class="form-group">
                <label for="email">Email <span class="text-danger">*</span></label>
                <input type="email" class="form-control <?= isset($errors['email']) ? 'is-invalid' : '' ?>" 
                       id="email" name="email" value="<?= old('email', $user['email'] ?? '') ?>" required>
                <?php if (isset($errors['email'])): ?>
                    <div class="invalid-feedback"><?= $errors['email'] ?></div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
    <div class="row">
        <div class="col-md-6">
            <div class="form-group">
                <label for="full_name">Full Name <span class="text-danger">*</span></label>
                <input type="text" class="form-control <?= isset($errors['full_name']) ? 'is-invalid' : '' ?>" 
                       id="full_name" name="full_name" value="<?= old('full_name', $user['full_name'] ?? '') ?>" required>
                <?php if (isset($errors['full_name'])): ?>
                    <div class="invalid-feedback"><?= $errors['full_name'] ?></div>
                <?php endif; ?>
            </div>
        </div>
        
        <div class="col-md-6">
            <div class="form-group">
                <label for="phone">Phone</label>
                <input type="text" class="form-control <?= isset($errors['phone']) ? 'is-invalid' : '' ?>" 
                       id="phone" name="phone" value="<?= old('phone', $user['phone'] ?? '') ?>">
                <?php if (isset($errors['phone'])): ?>
                    <div class="invalid-feedback"><?= $errors['phone'] ?></div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
    <div class="row">
        <div class="col-md-6">
            <div class="form-group">
                <label for="password">Password <?= !$isEdit ? '<span class="text-danger">*</span>' : '' ?></label>
                <div class="input-group">
                    <input type="password" class="form-control <?= isset($errors['password']) ? 'is-invalid' : '' ?>" 
                           id="password" name="password" <?= !$isEdit ? 'required' : '' ?>>
                    <div class="input-group-append">
                        <button class="btn btn-outline-secondary" type="button" id="togglePassword">
                            <i class="fas fa-eye"></i>
                        </button>
                    </div>
                    <?php if (isset($errors['password'])): ?>
                        <div class="invalid-feedback"><?= $errors['password'] ?></div>
                    <?php endif; ?>
                </div>
                <?php if ($isEdit): ?>
                    <small class="form-text text-muted">Leave blank to keep current password</small>
                <?php endif; ?>
                <div id="password-strength" class="mt-2"></div>
            </div>
        </div>
        
        <div class="col-md-6">
            <div class="form-group">
                <label for="photo">Photo</label>
                <div class="custom-file">
                    <input type="file" class="custom-file-input <?= isset($errors['photo']) ? 'is-invalid' : '' ?>" 
                           id="photo" name="photo" accept="image/*">
                    <label class="custom-file-label" for="photo">Choose file</label>
                    <?php if (isset($errors['photo'])): ?>
                        <div class="invalid-feedback"><?= $errors['photo'] ?></div>
                    <?php endif; ?>
                </div>
                <?php if ($isEdit && !empty($user['photo'])): ?>
                    <div class="mt-2">
                        <img src="<?= base_url('uploads/users/' . $user['photo']) ?>" width="100" class="img-thumbnail">
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
    <div class="row">
        <div class="col-12">
            <button type="submit" class="btn btn-primary">
                <i class="fas fa-save"></i> <?= $isEdit ? 'Update' : 'Create' ?> User
            </button>
            <a href="<?= base_url('master/user') ?>" class="btn btn-secondary">
                <i class="fas fa-times"></i> Cancel
            </a>
        </div>
    </div>
</form>

<script>
$(document).ready(function() {
    // Toggle password visibility
    $('#togglePassword').click(function() {
        const passwordField = $('#password');
        const icon = $(this).find('i');
        
        if (passwordField.attr('type') === 'password') {
            passwordField.attr('type', 'text');
            icon.removeClass('fa-eye').addClass('fa-eye-slash');
        } else {
            passwordField.attr('type', 'password');
            icon.removeClass('fa-eye-slash').addClass('fa-eye');
        }
    });
    
    // Password strength indicator
    $('#password').on('input', function() {
        const password = $(this).val();
        const strengthDiv = $('#password-strength');
        
        if (password.length === 0) {
            strengthDiv.html('');
            return;
        }
        
        let strength = 0;
        if (password.length >= 6) strength++;
        if (password.length >= 10) strength++;
        if (/[a-z]/.test(password) && /[A-Z]/.test(password)) strength++;
        if (/\d/.test(password)) strength++;
        if (/[^a-zA-Z\d]/.test(password)) strength++;
        
        const labels = ['Very Weak', 'Weak', 'Fair', 'Good', 'Strong'];
        const colors = ['danger', 'warning', 'info', 'primary', 'success'];
        
        strengthDiv.html(`<span class="badge badge-${colors[strength - 1]}">${labels[strength - 1]}</span>`);
    });
    
    // Update file label
    $('.custom-file-input').on('change', function() {
        const fileName = $(this).val().split('\\').pop();
        $(this).next('.custom-file-label').html(fileName);
    });
});
</script>
