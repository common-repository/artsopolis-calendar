<?php
$defaultCategoryFilter = self::get_value_field('default-category-filter');
?>

<div class="row ac-default-category">
    <div class="desc"><?php _e('Default Category', 'ac'); ?></div>
    <select class="arts-select" name="<?php echo $option_key ?>[default-category-filter]">
        <option value=""><?php _e('Default category', 'ac'); ?></option>
        <?php
        if ($categories) :
            foreach ($categories as $cat) :
            $subCats = $cat['subcats'];
            ?>
                <option value="<?php echo $cat['key']; ?>" <?php echo ($defaultCategoryFilter == $cat['key'] ? 'selected': ''); ?>><?php echo $cat['name']; ?></option>
                <?php
                if (!empty($subCats)) :
                    foreach ($subCats as $sub_key => $sub_name) : ?>

                    <option value="<?php echo $sub_key?>" <?php echo ($defaultCategoryFilter == $sub_key ? 'selected': ''); ?>>-- <?php echo $sub_name; ?></option>

                <?php
                    endforeach;
                endif;
                ?>
                <?php
            endforeach;
        endif;

        ?>
    </select>
    <div class="desc-option"><i><?php _e('Please select the same category checkbox in the "Category Selection" section.', 'ac'); ?></i></div>
</div>