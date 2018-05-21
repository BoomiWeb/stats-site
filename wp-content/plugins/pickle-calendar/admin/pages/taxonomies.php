<?php picklecalendar()->admin->check_remove_taxonomy(); ?>

<h2 class="wp-heading-inline">Taxonomies</h2>

<a href="<?php echo admin_url( 'options-general.php?page=pickle-calendar&tab=taxonomies&action=edit' ); ?>" class="page-title-action">Add New</a>

<form id="pickle-calendar-taxonomies" method="post">

<table class="wp-list-table widefat fixed striped pickle-calendar-taxonomies">
    <thead>
        <tr>
            <td id="cb" class="manage-column column-cb check-column">
                <label class="screen-reader-text" for="cb-select-all-1">Select All</label>
                <input id="cb-select-all-1" type="checkbox">
            </td>
            
            <th scope="col" id="name" class="manage-column column-name column-primary">
                <span>Name</span>
            </th>
            
            <th scope="col" id="actions" class="manage-column column-actions">
                <span></span>
            </th>
        </tr>
    </thead>

    <tbody id="the-list">
        <?php foreach ( picklecalendar()->settings['taxonomies'] as $taxonomy ) : ?>
            <tr id="taxonomy-<?php echo $taxonomy['slug']; ?>" class="taxonomy-<?php echo $taxonomy['slug']; ?> taxonomy hentry">
                <th scope="row" class="check-column">
                    <label class="screen-reader-text" for="cb-select-<?php echo $taxonomy['slug']; ?>">Select <?php echo ucwords( $taxonomy['label'] ); ?></label>
                    <input id="cb-select-<?php echo $taxonomy['slug']; ?>" type="checkbox" name="pickle_calendar_taxonomy[]" value="<?php echo $taxonomy['slug']; ?>">
                </th>
                
                <td class="name column-name column-primary" data-colname="Name">
                    <strong><a class="row-name" href="<?php echo admin_url( 'options-general.php?page=pickle-calendar&tab=taxonomies&action=edit&slug=' . $taxonomy['slug'] ); ?>" aria-label="“<?php echo ucwords( $taxonomy['label'] ); ?>” (Edit)"><?php echo ucwords( $taxonomy['label'] ); ?></a></strong>
                </td>
                
                <td class="actions column-actions" data-colname="Actions">
                    <strong><a class="delete" href="<?php echo admin_url( 'options-general.php?page=pickle-calendar&tab=taxonomies&action=delete&slug=' . $taxonomy['slug'] ); ?>" aria-label="delete">Delete</a></strong>
                </td>               
            </tr>
        <?php endforeach; ?>
    </tbody>
</table>

<div class="tablenav bottom">
    <div class="alignleft actions bulkactions">
        <label for="bulk-action-selector-bottom" class="screen-reader-text">Select bulk action</label>
        <select name="action" id="bulk-action-selector-bottom">
            <option value="-1">Bulk Actions</option>
            <option value="deleteall">Delete</option>
        </select>
        
        <input type="submit" id="doaction" class="button action" value="Apply">
    </div>
    <br class="clear">
</div>

</form>
