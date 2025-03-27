<?php
// This is a template snippet showing how to fix the floating label issue
// by modifying the input fields in add-seed-form-modern.php
?>

<!-- Original code -->
<div class="esc-floating-label">
    <input type="text" id="esc-seed-type" name="seed_type" required>
    <label for="esc-seed-type">Seed Type *</label>
</div>

<div class="esc-floating-label">
    <input type="text" id="esc-variety-name" name="variety_name">
    <label for="esc-variety-name">Variety (Optional)</label>
</div>

<!-- Fixed code - adds placeholder attribute and has-value class for pre-filled fields -->
<div class="esc-floating-label">
    <input type="text" id="esc-seed-type" name="seed_type" placeholder=" " required 
           value="<?php echo esc_attr(isset($_POST['seed_type']) ? $_POST['seed_type'] : ''); ?>"
           class="<?php echo !empty($_POST['seed_type']) ? 'has-value' : ''; ?>">
    <label for="esc-seed-type">Seed Type *</label>
</div>

<div class="esc-floating-label">
    <input type="text" id="esc-variety-name" name="variety_name" placeholder=" "
           value="<?php echo esc_attr(isset($_POST['variety_name']) ? $_POST['variety_name'] : ''); ?>"
           class="<?php echo !empty($_POST['variety_name']) ? 'has-value' : ''; ?>">
    <label for="esc-variety-name">Variety (Optional)</label>
</div>

<?php
// The key changes are:
// 1. Adding placeholder=" " attribute to all inputs
// 2. Adding value attribute with proper escaping
// 3. Adding has-value class when the field has a value
// 4. These changes should be applied to ALL floating label inputs in the form
?>
