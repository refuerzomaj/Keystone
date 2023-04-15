<?php
$group_columns  = $cl_meta_group_field["columns"] ?? '12';
$placeholder    = $cl_meta_group_field["placeholder"] ?? '';
$group_desc     = $cl_meta_group_field["desc"] ?? '';
$rows           = $cl_meta_group_field["rows"] ?? '4';
$min            = $cl_meta_group_field["min"] ?? '';
$max            = $cl_meta_group_field["max"] ?? '';

// -- Adding std Value -- //
if (isset($cl_meta_group_field['std']) && $meta_val == false) {
    $group_meta_val = $cl_meta_group_field['std'];
} else {
    if ($clone == true) {
        $group_meta_val = $meta_val[$key][$cl_meta_group_field["id"]] ?? '';
    } else {
        $group_meta_val = $meta_val[$cl_meta_group_field["id"]] ?? '';
    }
}

// -- Clone Variable -- //
$meta_group_clone_val = meta_clone_group_variable_declaration($clone, $key, $cl_meta_field["id"], $cl_meta_group_field["id"], $group_meta_val);

?>
<div class="column col-<?php echo esc_attr($group_columns); ?>" data-group_field_id="<?php echo esc_attr($cl_meta_group_field["id"]); ?>">
    <label for="cl-meta-number-input" class="cl-meta-number-input-label"><?php echo esc_html($cl_meta_group_field["name"]); ?></label>
    <input min="<?php echo esc_attr($min); ?>" max="<?php echo esc_attr($max); ?>" placeholder="<?php echo esc_attr($placeholder); ?>" class="cl-meta-number-input" type="number" name="<?php echo esc_attr($meta_group_clone_val['cl_name']); ?>" value="<?php echo esc_attr($meta_group_clone_val['group_meta_val']); ?>">
    <?php
    if (!empty($group_desc)) { ?>
        <div class="desc"><?php echo esc_html($group_desc); ?></div>
    <?php } ?>
</div>