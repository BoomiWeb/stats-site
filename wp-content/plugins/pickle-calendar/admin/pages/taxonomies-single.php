<?php
$slug = isset( $_GET['slug'] ) ? $_GET['slug'] : '';
$taxonomy = pickle_calendar_get_taxonomy( $slug );
?>

<h2 class="wp-heading-inline">Taxonomy</h2>

<a href="<?php echo admin_url( 'options-general.php?page=pickle-calendar&tab=taxonomies&action=edit' ); ?>" class="page-title-action">Add New</a>

<form name="post" action="" method="post" class="taxonomy-form">
    <?php wp_nonce_field( 'update_taxonomy', 'pickle_calendar_admin' ); ?>

    <div id="poststuff">
        <div id="post-body" class="metabox-holder columns-2">
            <div id="post-body-content">
                <div class="tax-input-row">
                    <label for="tax_slug">Slug</label>
                    <input type="text" name="tax_details[slug]" id="tax_slug" class="" value="<?php echo $taxonomy['slug']; ?>" />
                </div>

                <div class="tax-input-row">
                    <label for="tax_label">Label</label>
                    <input type="text" name="tax_details[label]" id="tax_label" class="" value="<?php echo $taxonomy['label']; ?>" />
                </div>
                
                <div class="tax-input-row">
                    <label for="tax_label_plural">Label Plural</label>
                    <input type="text" name="tax_details[label_plural]" id="tax_label_plural" class="" value="<?php echo $taxonomy['label_plural']; ?>" />
                </div>              

                <div class="tax-input-row radio">
                    <label for="tax_display">Display (frontend)</label>
                    <div id="tax-display" class="radio-wrap">
                        <label for="tax_display_yes"><input type="radio" name="tax_details[display]" id="tax_display_yes" class="tax-display" value="1" <?php checked( $taxonomy['display'], 1 ); ?> />Yes</label><br />
                        <label for="tax_display_no"><input type="radio" name="tax_details[display]" id="tax_display_no" class="tax-display" value="0" <?php checked( $taxonomy['display'], 0 ); ?> />No</label>
                    
                        <div id="tax-display-type" class="sub-option">
                            <label for="tax_display_type_checkboxes"><input type="radio" name="tax_details[display_type]" id="tax_display_type_checkboxes" class="tax-display-type" value="checkbox" <?php checked( $taxonomy['display_type'], 'checkbox' ); ?> />Checkboxes</label><br />
                            <label for="tax_display_type_tabs"><input type="radio" name="tax_details[display_type]" id="tax_display_type_tabs" class="tax-display-type" value="tabs" <?php checked( $taxonomy['display_type'], 'tabs' ); ?> />Tabs</label>
                            
                            <div id="tax-tabs-dsiplay-all-tab" class="sub-option">
                                <label for="tax_type_tabs_display_all_tab"><input type="checkbox" name="tax_details[hide_all_tab]" id="tax_type_tabs_display_all_tab" class="" value="1" <?php checked( $taxonomy['hide_all_tab'], 1 ); ?> />Hide 'All' tab</label>
                            </div>
                        </div>                       
                    </div>
                </div>  
            </div>
            
            <div id="postbox-container-1" class="postbox-container">
                <div id="side-sortables" class="meta-box-sortables ui-sortable" style="">
                    <div id="" class="postbox ">
                        <div class="inside">
                            <div id="delete-action">
                                <a class="submitdelete deletion" href="<?php echo admin_url( 'options-general.php?page=pickle-calendar&tab=taxonomies&action=delete&slug=' . $taxonomy['slug'] ); ?>">Delete</a>
                            </div>
    
                            <div id="publishing-action">
                                <input name="save" type="submit" class="button button-primary button-large" id="publish" value="Update">
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        
        </div>
    </div>
</form>

