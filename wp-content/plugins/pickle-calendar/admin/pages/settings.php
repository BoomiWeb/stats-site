<h2>Settings</h2>

<form class="pickle-calendar-settings-form" action="" method="post">
    <?php wp_nonce_field( 'update_settings', 'pickle_calendar_admin', true ); ?>  
    
    <h2>General</h2>
    
    <table class="form-table">
        <tbody>
        
            <tr>
                <th scope="row"><label for="adminlabel">Admin Label</label></th>
                <td><input name="settings[adminlabel]" type="text" id="adminlabel" value="<?php echo picklecalendar()->settings['adminlabel']; ?>" class="regular-text"></td>
            </tr>
        
            <tr>
                <th scope="row"><label for="cpt_single">Post Type Label (single)</label></th>
                <td><input name="settings[cpt_single]" type="text" id="cpt_single" value="<?php echo picklecalendar()->settings['cpt_single']; ?>" class="regular-text"></td>
            </tr>
        
            <tr>
                <th scope="row"><label for="cpt_plural">Post Type Label (plural)</label></th>
                <td><input name="settings[cpt_plural]" type="text" id="cpt_plural" value="<?php echo picklecalendar()->settings['cpt_plural']; ?>" class="regular-text"></td>
            </tr>
        
        </tbody>                
    </table>    
    
    <h2>Post Details (metabox)</h2> 

    <table class="form-table">
        <tbody>
        
            <tr>
                <th scope="row"><label for="disable_editor">Disable Editor</label></th>
                <td><label for="disable_editor"><input name="settings[disable_editor]" type="checkbox" id="disable_editor" value="1" <?php checked( picklecalendar()->settings['disable_editor'], 1 ); ?>>Disable Editor</label>
            </tr>
        
            <tr>
                <th scope="row"><label for="include_details">Details Box</label></th>
                <td><label for="include_details"><input name="settings[include_details]" type="checkbox" id="include_details" value="1" <?php checked( picklecalendar()->settings['include_details'], 1 ); ?>>Show Details Box</label>
            </tr>
        
            <tr class="details-box">
                <th scope="row"><label for="start_date">Show Start Date</label></th>
                <td><label for="start_date"><input name="settings[detail_options][start_date]" type="checkbox" id="start_date" value="1" <?php checked( picklecalendar()->settings['detail_options']['start_date'], 1 ); ?>>Show Start Date Box</label>
            </tr>
        
            <tr class="details-box">
                <th scope="row"><label for="end_date">Show End Date</label></th>
                <td><label for="end_date"><input name="settings[detail_options][end_date]" type="checkbox" id="end_date" value="1" <?php checked( picklecalendar()->settings['detail_options']['end_date'], 1 ); ?>>Show End Date Box</label>
            </tr>                                           
        
        </tbody>                
    </table>    
    
    <p class="submit"><input type="submit" name="submit" id="submit" class="button button-primary" value="Save Changes"></p>
    
</form>

<div class="metabox-holder">
    <div class="postbox">
        <h3><span><?php _e( 'Export Events' ); ?></span></h3>
        <div class="inside">
            <p><?php _e( 'Export events for this site as a .json file. This allows you to easily import the events into another site.' ); ?></p>
            <form method="post">
                <p><input type="hidden" name="pc_action" value="export_events" /></p>
                <p>
                    <?php wp_nonce_field( 'pickle_calendar_export_events_nonce', 'pickle_calendar_export_events_nonce' ); ?>
                    <?php submit_button( __( 'Export Events' ), 'secondary', 'submit', false ); ?>
                </p>
            </form>
        </div><!-- .inside -->
    </div><!-- .postbox -->

    <div class="postbox">
        <h3><span><?php _e( 'Import Events' ); ?></span></h3>
        <div class="inside">
            <p><?php _e( 'Import the events from a .json file. This file can be obtained by exporting the events on another site using the form above.' ); ?></p>
            <form method="post" enctype="multipart/form-data">
                <p>
                    <input type="file" name="import_file"/>
                </p>
                <p>
                    <input type="hidden" name="pc_action" value="import_events" />
                    <?php wp_nonce_field( 'pickle_calendar_import_events_nonce', 'pickle_calendar_import_events_nonce' ); ?>
                    <?php submit_button( __( 'Import Events' ), 'secondary', 'submit', false ); ?>
                </p>
            </form>
        </div><!-- .inside -->
    </div><!-- .postbox -->
</div><!-- .metabox-holder -->

<div class="metabox-holder">
    <div class="postbox">
        <h3><span><?php _e( 'Export Settings' ); ?></span></h3>
        <div class="inside">
            <p><?php _e( 'Export the plugin settings for this site as a .json file. This allows you to easily import the configuration into another site.' ); ?></p>
            <form method="post">
                <p><input type="hidden" name="pc_action" value="export_settings" /></p>
                <p>
                    <?php wp_nonce_field( 'pickle_calendar_export_nonce', 'pickle_calendar_export_nonce' ); ?>
                    <?php submit_button( __( 'Export' ), 'secondary', 'submit', false ); ?>
                </p>
            </form>
        </div><!-- .inside -->
    </div><!-- .postbox -->

    <div class="postbox">
        <h3><span><?php _e( 'Import Settings' ); ?></span></h3>
        <div class="inside">
            <p><?php _e( 'Import the plugin settings from a .json file. This file can be obtained by exporting the settings on another site using the form above.' ); ?></p>
            <form method="post" enctype="multipart/form-data">
                <p>
                    <input type="file" name="import_file"/>
                </p>
                <p>
                    <input type="hidden" name="pc_action" value="import_settings" />
                    <?php wp_nonce_field( 'pickle_calendar_import_nonce', 'pickle_calendar_import_nonce' ); ?>
                    <?php submit_button( __( 'Import' ), 'secondary', 'submit', false ); ?>
                </p>
            </form>
        </div><!-- .inside -->
    </div><!-- .postbox -->
</div><!-- .metabox-holder -->
