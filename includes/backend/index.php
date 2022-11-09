<div class="wrap">
    <h1>Socialhero Settings</h1>
    <form method="post" action="options.php">
        <?php settings_fields( 'socialhero-settings-group' ); ?>
        <?php do_settings_sections( 'socialhero-settings-group' ); ?>
        <table class="form-table">
            <tr valign="top">
                <th scope="row">Branch Id</th>
                <td><input type="text" name="sh-branch_id" value="<?php echo esc_attr( get_option('sh-branch_id') ); ?>" /></td>
            </tr>    
            <tr valign="top">
                <th scope="row">Origin</th>
                <td>
                    <select name="sh-origin">
                        <option value="web" <?php if(esc_attr( get_option('sh-origin'))== 'web'){echo 'selected';} ?>>web</option>
                        <option value="fisica" <?php if(esc_attr( get_option('sh-origin'))== 'fisica'){echo 'selected';} ?>>fisica</option>
                    </select>
                </td>
            </tr>     
            
            <tr valign="top">
                <th scope="row">Mode</th>
                <td>
                    <select name="sh-mode">
                        <option value="test" <?php if(esc_attr( get_option('sh-mode'))== 'test'){echo 'selected';} ?>>Test</option>
                        <option value="live" <?php if(esc_attr( get_option('sh-mode'))== 'live'){echo 'selected';} ?>>Live</option>
                    </select>
                </td>
            </tr>  
            
            <tr valign="top">
                <th scope="row">Do you want to remove table from database after deactivating the plugin ?</th>
                <td>
                    <select name="sh-agree">
                        <option value="true" <?php if(esc_attr( get_option('sh-agree'))== 'true'){echo 'selected';} ?>>True</option>
                        <option value="false" <?php if(esc_attr( get_option('sh-agree'))== 'false'){echo 'selected';} ?>>False</option>
                    </select>
                </td>
            </tr>             
            
        </table>
        <?php submit_button(); ?>
    </form>
</div>