<?php
/**
 * Form Fields Component - توابع کمکی برای تولید فیلدهای فرم
 * 
 * Usage: 
 *   echo field_text('email', 'ایمیل', $old['email'] ?? '', ['required'=>true, 'icon'=>'fa-envelope']);
 *   echo field_select('role_id', 'نقش', $roles, $old['role_id'] ?? '', ['required'=>true]);
 *   echo field_switch('is_active', 'فعال', $old['is_active'] ?? true);
 */

/**
 * فیلد متنی
 */
function field_text($name, $label, $value = '', $options = []) {
    $type = $options['type'] ?? 'text';
    $required = ($options['required'] ?? false) ? 'required' : '';
    $disabled = ($options['disabled'] ?? false) ? 'disabled' : '';
    $readonly = ($options['readonly'] ?? false) ? 'readonly' : '';
    $placeholder = $options['placeholder'] ?? '';
    $icon = $options['icon'] ?? '';
    $class = $options['class'] ?? '';
    $error = $options['error'] ?? '';
    $help = $options['help'] ?? '';
    $dir = $options['dir'] ?? 'auto';
    $id = $options['id'] ?? $name;
    
    ob_start();
?>
<div class="mb-3">
    <label for="<?= $id ?>" class="form-label fw-bold"><?= $label ?> <?= $required ? '<span class="text-danger">*</span>' : '' ?></label>
    <?php if ($icon): ?><div class="input-group"><?php endif; ?>
        <?php if ($icon): ?><span class="input-group-text"><i class="fas <?= $icon ?>"></i></span><?php endif; ?>
        <input type="<?= $type ?>" class="form-control <?= $error ? 'is-invalid' : '' ?> <?= $class ?>" 
               id="<?= $id ?>" name="<?= $name ?>" value="<?= htmlspecialchars($value) ?>" 
               placeholder="<?= htmlspecialchars($placeholder) ?>" <?= $required ?> <?= $disabled ?> <?= $readonly ?> dir="<?= $dir ?>">
    <?php if ($icon): ?></div><?php endif; ?>
    <?php if ($error): ?><div class="invalid-feedback"><?= $error ?></div><?php endif; ?>
    <?php if ($help): ?><small class="form-text text-muted"><?= $help ?></small><?php endif; ?>
</div>
<?php return ob_get_clean(); }

/**
 * فیلد Select
 */
function field_select($name, $label, $options = [], $selected = '', $fieldOptions = []) {
    $required = ($fieldOptions['required'] ?? false) ? 'required' : '';
    $disabled = ($fieldOptions['disabled'] ?? false) ? 'disabled' : '';
    $error = $fieldOptions['error'] ?? '';
    $help = $fieldOptions['help'] ?? '';
    $placeholder = $fieldOptions['placeholder'] ?? 'انتخاب کنید...';
    $id = $fieldOptions['id'] ?? $name;
    $onchange = $fieldOptions['onchange'] ?? '';
    $class = $fieldOptions['class'] ?? '';
    
    ob_start();
?>
<div class="mb-3">
    <label for="<?= $id ?>" class="form-label fw-bold"><?= $label ?> <?= $required ? '<span class="text-danger">*</span>' : '' ?></label>
    <select class="form-select <?= $error ? 'is-invalid' : '' ?> <?= $class ?>" id="<?= $id ?>" name="<?= $name ?>" <?= $required ?> <?= $disabled ?> <?= $onchange ? "onchange=\"{$onchange}\"" : '' ?>>
        <option value=""><?= $placeholder ?></option>
        <?php foreach ($options as $val => $text): ?>
            <?php if (is_array($text)): // Optgroup ?>
            <optgroup label="<?= htmlspecialchars($val) ?>">
                <?php foreach ($text as $subVal => $subText): ?>
                <option value="<?= $subVal ?>" <?= $selected == $subVal ? 'selected' : '' ?>><?= htmlspecialchars($subText) ?></option>
                <?php endforeach; ?>
            </optgroup>
            <?php else: ?>
            <option value="<?= $val ?>" <?= $selected == $val ? 'selected' : '' ?>><?= htmlspecialchars($text) ?></option>
            <?php endif; ?>
        <?php endforeach; ?>
    </select>
    <?php if ($error): ?><div class="invalid-feedback"><?= $error ?></div><?php endif; ?>
    <?php if ($help): ?><small class="form-text text-muted"><?= $help ?></small><?php endif; ?>
</div>
<?php return ob_get_clean(); }

/**
 * فیلد Textarea
 */
function field_textarea($name, $label, $value = '', $options = []) {
    $required = ($options['required'] ?? false) ? 'required' : '';
    $rows = $options['rows'] ?? 3;
    $error = $options['error'] ?? '';
    $id = $options['id'] ?? $name;
    ob_start();
?>
<div class="mb-3"><label for="<?=$id?>" class="form-label fw-bold"><?=$label?> <?=$required?'<span class="text-danger">*</span>':''?></label><textarea class="form-control <?=$error?'is-invalid':''?>" id="<?=$id?>" name="<?=$name?>" rows="<?=$rows?>" <?=$required?>><?=htmlspecialchars($value)?></textarea><?php if($error):?><div class="invalid-feedback"><?=$error?></div><?php endif;?></div>
<?php return ob_get_clean(); }

/**
 * فیلد Switch/Toggle
 */
function field_switch($name, $label, $checked = false, $options = []) {
    $id = $options['id'] ?? $name;
    $disabled = ($options['disabled'] ?? false) ? 'disabled' : '';
    ob_start();
?>
<div class="mb-3"><div class="form-check form-switch"><input class="form-check-input" type="checkbox" id="<?=$id?>" name="<?=$name?>" value="1" <?=$checked?'checked':''?> <?=$disabled?>><label class="form-check-label fw-bold" for="<?=$id?>"><?=$label?></label></div></div>
<?php return ob_get_clean(); }

/**
 * فیلد تاریخ
 */
function field_date($name, $label, $value = '', $options = []) {
    $options['type'] = 'text';
    $options['class'] = ($options['class'] ?? '') . ' persian-datepicker';
    $options['placeholder'] = $options['placeholder'] ?? 'انتخاب تاریخ';
    $options['dir'] = 'ltr';
    return field_text($name, $label, $value, $options);
}

/**
 * فیلد آپلود فایل
 */
function field_file($name, $label, $options = []) {
    $id = $options['id'] ?? $name;
    $accept = $options['accept'] ?? '*';
    $preview = $options['preview'] ?? '';
    ob_start();
?>
<div class="mb-3"><label for="<?=$id?>" class="form-label fw-bold"><?=$label?></label><?php if($preview):?><div class="mb-2"><img src="<?=$preview?>" class="rounded" style="max-height:60px;"></div><?php endif;?><input type="file" class="form-control" id="<?=$id?>" name="<?=$name?>" accept="<?=$accept?>"></div>
<?php return ob_get_clean(); }

/**
 * فیلد عددی
 */
function field_number($name, $label, $value = '', $options = []) {
    $options['type'] = 'number';
    $options['dir'] = 'ltr';
    if (!isset($options['min'])) $options['min'] = $options['min'] ?? null;
    if (!isset($options['max'])) $options['max'] = $options['max'] ?? null;
    if (!isset($options['step'])) $options['step'] = $options['step'] ?? '1';
    $options['placeholder'] = $options['placeholder'] ?? '';
    return field_text($name, $label, $value, $options);
}
?>