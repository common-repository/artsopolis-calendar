<?php
    if (! is_array($category_opts)) {
        $category_opts = array();
    }
    
	$category_opts_keys_parent = array();
   	if ($category_opts) {
        foreach ($category_opts as $key => $category_opt) {
            if (isset($category_opt['name']) && $category_opt['name']) {
                $category_opts_keys_parent[] = $key;
            }
        }
    }

    foreach ($categories as $category):
	   	$key     =  $category['key']; 
	   	$name    =  $category['name'];
	   	$subs    =  $category['subcats'];  
	    $active  = $category['active'];

	   	if ($category_opts && isset($category_opts[$key]) && isset($category_opts[$key]['subs'])) {
			$category_opts_keys_subs = array_keys($category_opts[$key]['subs']);		
	   	} else {
	   		$category_opts_keys_subs = array();
	   	}
	   
?>      
        <div >
            <input <?php echo $active == 0 ? 'disabled' : '' ?>
                    <?php if (in_array($key, $category_opts_keys_parent)) {echo 'checked=true';} ?> type="checkbox"
        			name="<?php echo $option_key ?>[category][<?php echo $key ?>][name]" 
                   	value="<?php echo $name; ?>" id="artsopolis-calendar-settings-category-<?php echo $key; ?>" />
            <label for="artsopolis-calendar-settings-category-<?php echo str_replace(' ', '-', $key) ?>"><?php echo $name; ?></label>
            <?php if ($subs) : ?>
            <span class="hide-show-subcategory">&#9658;</span>
            <?php endif; ?>
        </div>
        <?php if ($subs) : ?>
        <div class="sub-row">
            <?php foreach ($subs as $sub_key => $sub_name) : ?>
                <div>
                    <input 
                        <?php 
                            if (isset($category_opts[$key]) && in_array($sub_key, $category_opts_keys_subs)) {
                                echo 'checked=true';
                            } 
                        ?> name="<?php echo $option_key ?>[category][<?php echo $key ?>][subs][<?php echo $sub_key; ?>]" type="checkbox" value="<?php echo $sub_name; ?>" />
                    <label><?php echo $sub_name; ?></label>
                </div>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>
    <?php endforeach; ?>