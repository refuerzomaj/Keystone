<?php
$group_columns  = $cl_meta_group_field["columns"] ?? '12';
$placeholder    = $cl_meta_group_field["placeholder"] ?? '';
$group_desc     = $cl_meta_group_field["desc"] ?? '';
$rows           = $cl_meta_group_field["rows"] ?? '4';

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
    <label for=" cl-meta-textarea-input" class="cl-meta-textarea-input-label"><?php echo esc_html($cl_meta_group_field["name"]); ?></label>
    <textarea placeholder="<?php echo esc_attr($placeholder); ?>" class="cl-meta-textarea-input" name="<?php echo esc_attr($meta_group_clone_val['cl_name']); ?>" rows="<?php echo esc_attr($rows); ?>"><?php echo esc_attr($meta_group_clone_val['group_meta_val']); ?></textarea>
    <?php
    if (!empty($group_desc)) { ?>
        <div class="desc"><?php echo esc_html($group_desc); ?></div>
    <?php } ?>
</div>