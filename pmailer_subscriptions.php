<?php
/*
Plugin Name: pMailer Subscription Widget
Plugin URI: http://www.pmailer.co.za/
Description: Adds a subscription form widget that will subscribe users to your desired pmailer lists.
Version: 1.4.1
Author: pMailer
Author URI: http://www.prefix.co.za
License: GPL
*/

/**
 * Include required files if not included by another pmailer plugin.
 */
if ( class_exists('PMailerSubscriptionApiV1_0') === false )
{
    require_once 'pmailer_api.php';
}

/* Runs when plugin is activated */
register_activation_hook(__FILE__, 'pmailer_sub_install');

/* Runs on plugin deactivation*/
register_deactivation_hook(__FILE__, 'pmailer_sub_remove');

/**
 * Runs code when Pmailer widget is activated.
 */
function pmailer_sub_install()
{
    // Add database options
    add_option('pmailer_sub_valid', '', '', 'yes');
    add_option('pmailer_sub_url', '', '', 'yes');
    add_option('pmailer_sub_api_key', '', '', 'yes');

    // subscription form defaults
    add_option('pmailer_sub_title_content', 'Subscribe to our newsletter', 'yes');
    add_option('pmailer_sub_email_title', 'Email address', 'yes');
    add_option('pmailer_sub_submit_button_text', 'Subscribe', 'yes');
    add_option('pmailer_sub_ajax_form', 'no', 'yes');
    add_option('pmailer_sub_smart_from_enabled', 'no', 'yes');
    add_option('pmailer_sub_include_first_name', 'no', 'yes');
    add_option('pmailer_sub_first_name_title', 'First name', 'yes');
    add_option('pmailer_sub_include_last_name', 'no', 'yes');
    add_option('pmailer_sub_last_name_title', 'Last name', 'yes');
    add_option('pmailer_sub_smart_form_page_view_activation', '5', 'yes');

}

/**
 * Runs clean-up code when pmailer is de-activated.
 */
function pmailer_sub_remove()
{
    // Remove database options
    delete_option('pmailer_sub_selected_lists');
    delete_option('pmailer_sub_available_lists');
    delete_option('pmailer_sub_valid');
    delete_option('pmailer_sub_url');
    delete_option('pmailer_sub_api_key');
    delete_option('pmailer_sub_ajax_form');
    delete_option('pmailer_sub_title_content');
    delete_option('pmailer_sub_email_title');
    delete_option('pmailer_sub_submit_button_text');
    delete_option('pmailer_sub_smart_from_enabled');
    delete_option('pmailer_sub_include_first_name');
    delete_option('pmailer_sub_include_last_name');
    delete_option('pmailer_sub_smart_form_page_view_activation');
    delete_option('pmailer_sub_first_name_title');
    delete_option('pmailer_sub_last_name_title');

}

// create custom plugin settings menu
add_action('admin_menu', 'pmailer_sub_create_menu');

function pmailer_sub_create_menu()
{

    //create new top-level menu
    add_options_page('pMailer', 'pMailer Subscriptions', 'manage_options', 'pmailer-subscriptions', 'pmailer_sub_settings_page');

}

function pmailer_sub_save_enterprise_details()
{
    // Update options if form has been submitted:
    if ( isset($_POST['pmailer_api_details']) === true )
    {
        // save details
        update_option('pmailer_sub_url', $_POST['pmailer_sub_url']);
        update_option('pmailer_sub_api_key', $_POST['pmailer_sub_api_key']);

        $url = $_POST['pmailer_sub_url'];
        if (substr($url, 0, 4) === 'http') {            
            $parts = parse_url($url);
            if (empty($parts['host'])) {
                echo '<div class="error"><p>Invalid URL Provided.</p></div>';
                return;
            }
            $url = $parts['host'];
        }

        $pmailerApi = new PMailerSubscriptionApiV1_0($url, $_POST['pmailer_sub_api_key']);
        try
        {
        	$lists = $pmailerApi->getLists();
        }
        catch ( PMailerSubscriptionException $e )
        {
        	echo '<div class="error"><p>'.$e->getMessage().'</p></div>';
        	return;
        }

        update_option('pmailer_sub_valid', 'yes');
        update_option('pmailer_sub_available_lists', serialize($lists));
        echo '<div class="updated"><p>API details successfully updated.</p></div>';

    }
}

function pmailer_sub_reset_enterprise_details()
{
	if ( isset($_POST['pmailer_reset_details']) === true )
    {
    	update_option('pmailer_sub_valid', '');
    	// clear lists
    	update_option('pmailer_sub_available_lists', '');
    }
}

function pmailer_sub_save_subscription_form_details()
{
    if ( isset($_POST['pmailer_sub_form_details']) === true )
    {
    	if ( is_array($_POST['pmailer_sub_selected_lists']) === true )
    	{
    		$selected_lists = array();
    		foreach ( $_POST['pmailer_sub_selected_lists'] as $list_id )
    		{
    			$selected_lists[] = $list_id;
    		}
    		update_option('pmailer_sub_selected_lists', serialize($selected_lists));
    	}
    	else
    	{
    		// clear the selected lists
    		update_option('pmailer_sub_selected_lists', '');
    		// warn if they are not filled in
    		echo '<div class="error"><p>Please select at least one list to subscribe contacts to.</p></div>';
            return;
    	}
    	update_option('pmailer_sub_ajax_form', $_POST['pmailer_sub_ajax_form']);
	    update_option('pmailer_sub_title_content', $_POST['pmailer_sub_title_content']);
	    update_option('pmailer_sub_email_title', $_POST['pmailer_sub_email_title']);
	    update_option('pmailer_sub_submit_button_text', $_POST['pmailer_sub_submit_button_text']);
	    update_option('pmailer_sub_smart_from_enabled', $_POST['pmailer_sub_smart_from_enabled']);
	    update_option('pmailer_sub_smart_form_page_view_activation', (int)$_POST['pmailer_sub_smart_form_page_view_activation']);
	    update_option('pmailer_sub_include_first_name', $_POST['pmailer_sub_include_first_name']);
        update_option('pmailer_sub_include_last_name', $_POST['pmailer_sub_include_last_name']);
        update_option('pmailer_sub_first_name_title', $_POST['pmailer_sub_first_name_title']);
        update_option('pmailer_sub_last_name_title', $_POST['pmailer_sub_last_name_title']);

	    echo '<div class="updated"><p>Successfully updated subscription form details.</p></div>';
    }
}

function pmailer_sub_refresh_lists()
{
	if ( isset($_POST['pmailer_sub_refresh_lists']) === true )
	{
        $pmailerApi = new PMailerSubscriptionApiV1_0(get_option('pmailer_sub_url'), get_option('pmailer_sub_api_key'));
        try
        {
            $lists = $pmailerApi->getLists();
        }
        catch ( PMailerSubscriptionException $e )
        {
            echo '<div class="error"><p>'.$e->getMessage().'</p></div>';
            return;
        }
        update_option('pmailer_sub_available_lists', serialize($lists));
        echo '<div class="updated"><p>Successfully refreshed lists.</p></div>';
	}
}

function pmailer_sub_settings_page()
{
	if ( is_admin() )
	{
		// update enterprise url and api on form submit:
	    pmailer_sub_save_enterprise_details();
	    pmailer_sub_reset_enterprise_details();
	    pmailer_sub_save_subscription_form_details();
	    pmailer_sub_refresh_lists();
	}

?>
<div class="wrap">
<div class="icon32" id="icon-options-general"><br>
</div>
<h2>pMailer subscription widget</h2>
    <?php
    $valid = get_option('pmailer_sub_valid');
    if ( empty($valid) === true ):
    ?>
    <div style="background-color:white; padding:10px;">
    <strong>Please enter your pMailer details:</strong>
	<form method="post" action="<?php echo str_replace( '%7E', '~', $_SERVER['REQUEST_URI']); ?>">
		<table class="form-table">
			<tr valign="top">
				<th scope="row">pMailer URL</th>
				<td><input type="text" name="pmailer_sub_url" value="<?php echo get_option('pmailer_sub_url'); ?>" /> - e.g. live.pmailer.co.za</td>
			</tr>
			<tr valign="top">
				<th scope="row">API key</th>
				<td><input type="text" name="pmailer_sub_api_key" size="40" value="<?php echo get_option('pmailer_sub_api_key'); ?>" /></td>
			</tr>
		</table>
		<input type="hidden" name="pmailer_api_details" value="Y">
		<p class="submit"><input type="submit" class="button-primary" value="<?php _e('Next') ?>" /></p>
	</form>
	</div>
    <?php
    endif;
    ?>

    <?php
    if ( $valid === 'yes' ):
    ?>
<div style="background-color:white; padding:10px;">
<form method="post" action="<?php echo str_replace( '%7E', '~', $_SERVER['REQUEST_URI']); ?>">
	<strong>Enterprise details:</strong><br />
	Enterprise URL: <i><?php echo get_option('pmailer_sub_url'); ?></i><br />
	API key: <i><?php echo get_option('pmailer_sub_api_key'); ?></i> <input type="hidden" name="pmailer_reset_details" value="Y">
	<p class="submit"><input type="submit" class="button-primary" value="<?php _e('Change enterprise & API details') ?>" /></p>
</form>
</div>
<br />

<div style="background-color:white; padding:10px;">
<p><strong>Subscription form details:</strong></p>

<form method="post" action="<?php echo str_replace( '%7E', '~', $_SERVER['REQUEST_URI']); ?>">
    <input type="hidden" name="pmailer_sub_refresh_lists" value="yes">
    <input type="submit" class="button-primary" value="<?php _e('Refresh lists') ?>" />
</form>
<form method="post" action="<?php echo str_replace( '%7E', '~', $_SERVER['REQUEST_URI']); ?>">
<input type="hidden" name="pmailer_sub_form_details" value="Y">
<table class="form-table">
	<tr valign="top">
		<th scope="row">Please select the list(s) that the contact will be subscribed to.
		</th>
		<td>
		  <select name="pmailer_sub_selected_lists[]" multiple="multiple" style="height: 110px">
                <?php
                $selected_lists = get_option('pmailer_sub_selected_lists');
                if ( is_string($selected_lists) === true )
                {
                	$selected_lists = unserialize($selected_lists);
                }
                if ( is_array($selected_lists) === false )
                {
                            $selected_lists = array();
                }
	            $available_lists = get_option('pmailer_sub_available_lists');
	            if ( is_array($available_lists) === false )
	            {
	            	$available_lists = unserialize($available_lists);
	            }
	            foreach ( $available_lists['data'] as $key => $list ):
	            ?>
	              <option value="<?php echo $list['list_id']; ?>"<?php echo ( in_array($list['list_id'], $selected_lists) === true ) ? 'selected="selected"' : ''; ?>><?php echo $list['list_name']; ?></option>
	            <?php
	            endforeach;
	            ?>
			</select>
		</td>
	</tr>
	<tr valign="top">
		<th scope="row">Ajax form?</th>
		<?php
		$ajax_checked_yes = ( get_option('pmailer_sub_ajax_form') === 'yes' ) ? 'checked="checked"' : '';
		$ajax_checked_no = ( get_option('pmailer_sub_ajax_form') === 'no' ) ? 'checked="checked"' : '';
		?>
		<td>Yes <input name="pmailer_sub_ajax_form" type="radio" value="yes" <?php echo $ajax_checked_yes; ?> />
		No <input name="pmailer_sub_ajax_form" type="radio" value="no" <?php echo $ajax_checked_no; ?> /> <i> - Submits the form without reloading the page.</i></td>
	</tr>
	<tr valign="top">
        <th scope="row">Enable smart form?</th>
        <?php
        $smart_checked_yes = ( get_option('pmailer_sub_smart_from_enabled') === 'yes' ) ? 'checked="checked"' : '';
        $smart_checked_no = ( get_option('pmailer_sub_smart_from_enabled') === 'no' ) ? 'checked="checked"' : '';
        ?>
        <td>Yes <input name="pmailer_sub_smart_from_enabled" type="radio" value="yes" <?php echo $smart_checked_yes; ?> />
        No <input name="pmailer_sub_smart_from_enabled" type="radio" value="no" <?php echo $smart_checked_no; ?> /> <i> - Displays a popup subscription form when a user browsers <?php echo get_option('pmailer_sub_smart_form_page_view_activation'); ?> or more pages on the site.</i></td>
    </tr>
    <tr valign="top">
        <th scope="row">Show smart form after how many page views?</th>
        <td><input name="pmailer_sub_smart_form_page_view_activation" type="text"
            value="<?php echo get_option('pmailer_sub_smart_form_page_view_activation'); ?>" /></td>
    </tr>
    <tr valign="top">
        <th scope="row">Display contact first name field?</th>
        <?php
        $display_firstname_checked_yes = ( get_option('pmailer_sub_include_first_name') === 'yes' ) ? 'checked="checked"' : '';
        $display_firstname_checked_no = ( get_option('pmailer_sub_include_first_name') === 'no' ) ? 'checked="checked"' : '';
        ?>
        <td>Yes <input name="pmailer_sub_include_first_name" type="radio" value="yes" <?php echo $display_firstname_checked_yes; ?> />
        No <input name="pmailer_sub_include_first_name" type="radio" value="no" <?php echo $display_firstname_checked_no; ?> /> <i> - Displays the first name field on the subscription form.</i></td>
    </tr>
    <tr valign="top">
        <th scope="row">First name title</th>
        <td><input name="pmailer_sub_first_name_title" type="text"
            value="<?php echo get_option('pmailer_sub_first_name_title'); ?>" /></td>
    </tr>
    <tr valign="top">
        <th scope="row">Display contact last name field?</th>
        <?php
        $display_lastname_checked_yes = ( get_option('pmailer_sub_include_last_name') === 'yes' ) ? 'checked="checked"' : '';
        $display_lastname_checked_no = ( get_option('pmailer_sub_include_last_name') === 'no' ) ? 'checked="checked"' : '';
        ?>
        <td>Yes <input name="pmailer_sub_include_last_name" type="radio" value="yes" <?php echo $display_lastname_checked_yes; ?> />
        No <input name="pmailer_sub_include_last_name" type="radio" value="no" <?php echo $display_lastname_checked_no; ?> /> <i> - Displays the last name field on the subscription form.</i></td>
    </tr>
    <tr valign="top">
        <th scope="row">Last name title</th>
        <td><input name="pmailer_sub_last_name_title" type="text"
            value="<?php echo get_option('pmailer_sub_last_name_title'); ?>" /></td>
    </tr>
	<tr valign="top">
		<th scope="row">Content title</th>
		<td><input name="pmailer_sub_title_content" type="text"
			value="<?php echo get_option('pmailer_sub_title_content'); ?>" /></td>
	</tr>
	<tr valign="top">
		<th scope="row">Email address title</th>
		<td><input name="pmailer_sub_email_title" type="text"
			value="<?php echo get_option('pmailer_sub_email_title'); ?>" /></td>
	</tr>
	<tr valign="top">
		<th scope="row">Submit button text</th>
		<td><input name="pmailer_sub_submit_button_text" type="text"
			value="<?php echo get_option('pmailer_sub_submit_button_text'); ?>" /></td>
	</tr>
    <tr>
        <td colspan="2"><hr /></td>
    </tr>
</table>
<p class="submit"><input type="submit" class="button-primary"
	value="<?php _e('Save Changes') ?>" /></p>
</form>
</div>
	<?php
	endif;
	?>
</div>
<?php
}

/**
 * Widget settings and registration.
 */
function pmailer_sub_widget_subscription_form($args)
{
    extract($args, EXTR_SKIP);
    echo $before_widget;
    echo $before_title; echo esc_attr(get_option('pmailer_sub_title_content')); echo $after_title; ?>
<div>
	<form id="pmailer_subscription_form" method="post" action="#pmailer_subscription_form">
	<?php
	// warn if an error occurred working with the api
	if ( isset($_COOKIE['pmailer_setup_error']) === true ):
	?>
	<span class="pmailer_subscription_error"><?php echo $_COOKIE['pmailer_setup_error'];?></span><br />
	<?php
	endif;
	?>
	<?php
	// warn if an error was returned by pmailer api
	if ( isset($_COOKIE['pmailer_subscription_error']) === true ):
	?>
	<span class="pmailer_subscription_error"><?php echo $_COOKIE['pmailer_subscription_error'];?></span><br />
	<?php
	endif;

	// warn if an error was returned by pmailer api
	if ( isset($_COOKIE['pmailer_subscription_success']) === true ):
	?>
	<span class="pmailer_subscription_success"><?php echo $_COOKIE['pmailer_subscription_success'];?></span><br />
	<?php
	endif;

	unset($_COOKIE['pmailer_setup_error'],$_COOKIE['pmailer_subscription_error'],$_COOKIE['pmailer_subscription_success']);
	?>
	<span id="pmailer_sub_form_status">Subscribing...<br /></span>
	<span id="pmailer_sub_form_error" class="pmailer_subscription_error"></span>
	<span id="pmailer_sub_form_success" class="pmailer_subscription_success"></span>
	<label for="pmailer_subscriber_email" class="pmailer_subscription_titles"><?php echo esc_attr(get_option('pmailer_sub_email_title')); ?> *<small>required</small></label><br />
	<input name="pmailer_subscriber_email" type="text" value="" />
	<?php
	if ( get_option('pmailer_sub_include_first_name') == 'yes' ):
	?>
	<br /><?php echo get_option('pmailer_sub_first_name_title'); ?><br /><input name="pmailer_subscriber_firstname" type="text" value="" />
	<?php
	endif;
	?>
	<?php
    if ( get_option('pmailer_sub_include_last_name') == 'yes' ):
    ?>
    <br /><?php echo get_option('pmailer_sub_last_name_title'); ?><br /><input name="pmailer_subscriber_lastname" type="text" value="" />
    <?php
    endif;
    ?>
	<input type="hidden" name="pmailer_sub_form_submission" value="<?php echo uniqid(); ?>">
	<input type="hidden" name="pmailer_sub_ajax_form" id="pmailer_sub_ajax_form" value="<?php echo esc_attr(get_option('pmailer_sub_ajax_form')); ?>">
	<input type="hidden" name="pmailer_sub_ajax_url" id="pmailer_sub_ajax_url" value="<?php echo trailingslashit(home_url()); ?>">
	<input type="submit" name="pmailer_subscription_submit" id="pmailer_subscription_submit" value="<?php echo esc_attr(get_option('pmailer_sub_submit_button_text')); ?>" class="button" />
	</form>
</div>

<?php if( get_option('pmailer_sub_smart_from_enabled') === 'yes' ): ?>
<div id="pmailer_smart_form" style="display:none;">
    <form id="pmailer_subscription_smart_form" method="post" action="#pmailer_subscription_form">
    <?php
    unset($_COOKIE['pmailer_setup_error'],$_COOKIE['pmailer_subscription_error'],$_COOKIE['pmailer_subscription_success']);
    ?>
    <img id="pmailer_sub_smart_close" alt="close" title="close" src="<?php echo WP_PLUGIN_URL . '/pmailer-subscription/images/close.png'; ?>">
    <span id="pmailer_sub_smart_header"><?php echo esc_attr(get_option('pmailer_sub_title_content')); ?></span>
    <br />
    <span id="pmailer_sub_smart_form_error" class="pmailer_subscription_error"></span>
    <span id="pmailer_sub_smart_form_status" style="display:none;">Subscribing...<br /></span>
    <br />
    <label class="pmailer_subscription_titles"><?php echo (get_option('pmailer_sub_email_title')); ?> *<small>required</small></label>
    <input name="pmailer_subscriber_email" id="pmailer_smart_subscriber_email" type="text" value="" />
    <?php
    if ( get_option('pmailer_sub_include_first_name') == 'yes' ):
    ?>
    <br /><span><?php echo get_option('pmailer_sub_first_name_title'); ?></span><br /><input name="pmailer_subscriber_firstname" type="text" value="" />
    <?php
    endif;
    ?>
    <?php
    if ( get_option('pmailer_sub_include_last_name') == 'yes' ):
    ?>
    <br /><span><?php echo get_option('pmailer_sub_last_name_title'); ?></span><br /><input name="pmailer_subscriber_lastname" type="text" value="" />
    <?php
    endif;

    ?>

    <input type="hidden" name="pmailer_sub_ajax_form" value="yes">
    <input type="hidden" name="pmailer_sub_form_submission" value="<?php echo uniqid(); ?>">
    <input type="hidden" name="pmailer_sub_smart_ajax_url" id="pmailer_sub_ajax_url" value="<?php echo trailingslashit(home_url()); ?>">
    <input type="hidden" name="pmailer_sub_smart_form_enabled" id="pmailer_sub_smart_form_enabled" value="<?php echo esc_attr(get_option('pmailer_sub_smart_from_enabled')); ?>">
    <input type="hidden" name="pmailer_subscription_submit" id="pmailer_subscription_smart_submit" value="<?php echo esc_attr(get_option('pmailer_sub_submit_button_text')); ?>">
    <input type="hidden" name="pmailer_sub_smart_form_page_view_activation" id="pmailer_sub_smart_form_page_view_activation" value="<?php echo get_option('pmailer_sub_smart_form_page_view_activation'); ?>" />

    <br />
    <input type="submit" name="pmailer_smart_subscription_submit" id="pmailer_smart_subscription_submit" value="<?php echo esc_attr(get_option('pmailer_sub_submit_button_text')); ?>" class="button" />
    <br />
    <input type="button" id="pmailer_smart_subscription_dont_bugme" value="Do not show this again" class="button" />
    </form>
</div>
<?php
endif;
?>

<?php
    echo $after_widget;
}

function pmailer_sub_widget_coming_next_init()
{
    // if api details are invalid do nothing
    $valid = get_option('pmailer_sub_valid');
    if ( empty($valid) === false )
    {
        wp_register_sidebar_widget('pmailer_sub_subscription_widget',
            __('Pmailer subscription widget'), 'pmailer_sub_widget_subscription_form');
    }
}

// Register widget to WordPress
add_action("plugins_loaded", "pmailer_sub_widget_coming_next_init");

/**
 * Front-end widget and resources
 */
function pmailer_sub_submit_subscription_form()
{
	if ( isset($_POST['pmailer_sub_form_submission']) === true )
	{
		$subscribed = array();
        $successfull = true;
        $pmailerApi = new PMailerSubscriptionApiV1_0(get_option('pmailer_sub_url'), get_option('pmailer_sub_api_key'));
	    try
	    {
	    	$properties = array('contact_email' => $_POST['pmailer_subscriber_email']);

	    	// add contact first name if its set
	    	if ( isset($_POST['pmailer_subscriber_firstname']) === true )
	    	{
	    		$properties = $properties + array('contact_name' => $_POST['pmailer_subscriber_firstname']);
	    	}

            // add contact last name if its set
            if ( isset($_POST['pmailer_subscriber_lastname']) === true )
            {
                $properties = $properties + array('contact_lastname' => $_POST['pmailer_subscriber_lastname']);
            }

    	    // get lists to subscribe to
            $selected_lists = get_option('pmailer_sub_selected_lists');
            if ( is_array($selected_lists) === false )
            {
                $selected_lists = unserialize($selected_lists);
            }
            if ( empty($selected_lists) === true )
            {
            	$subscribed['status'] = 'error';
            	$subscribed['message'] = 'No lists were chosen.';
            }
            else
            {
            	$subscribed = $pmailerApi->subscribe($properties, $selected_lists, 'unconfirmed');
            }

		}
		catch ( PMailerSubscriptionException $e )
		{
			$successfull = false;
			$_COOKIE['pmailer_setup_error'] = 'An error occurred, please re-run the pmailer setup';
			$subscribed['status'] = 'error';
			$subscribed['message'] = $e->getMessage();
		}

		// check if api returned errors
		if ( $successfull === true && $subscribed['status'] === 'error' )
		{
			$message = '';
			// display a more user friendly version of api message
			if ( strpos($subscribed['message'], 'required') !== false
                && strpos($subscribed['message'], 'set') !== false )
            {
                $message = 'Email address was not filled in.';
            }

            if ( strpos($subscribed['message'], 'validating') !== false
                && strpos($subscribed['message'], 'valid email') !== false
                || strpos($subscribed['message'], 'valid hostname') !== false )
            {
                $message = 'Invalid email address.';
            }
            if ( strpos($subscribed['message'], 'lists') !== false
                && strpos($subscribed['message'], 'chosen') !== false )
            {
                $message = 'No lists were chosen.';
            }

			$_COOKIE['pmailer_subscription_error'] = $message;
		}

		// check if api subscribe was successful
		if ( $successfull === true && $subscribed['status'] === 'success' )
		{
			$_COOKIE['pmailer_subscription_success'] = 'Email address successfully subscribed.';
		}

		// check if this was an ajax request
		if ( $_POST['pmailer_sub_ajax_form'] == 'yes' )
		{
			die(json_encode($subscribed));
		}

	}
}

function pmailer_sub_enqueue_scripts()
{
    // include wordpresses jquery
    wp_enqueue_script('jquery');
    // include pmailer js
    wp_register_script('pmailer_subscription', WP_PLUGIN_URL . '/pmailer-subscription/js/pmailer_subscription.js');
    wp_enqueue_script('pmailer_subscription');
}

function pmailer_sub_enqueue_styles()
{
    // include form style sheet
    wp_register_style('pmailer_sub_form_css', WP_PLUGIN_URL . '/pmailer-subscription/css/subscription_form.css');
    wp_enqueue_style('pmailer_sub_form_css', WP_PLUGIN_URL . '/pmailer-subscription/css/subscription_form.css');
}

add_action('init', 'pmailer_sub_submit_subscription_form');
add_action('wp_enqueue_scripts', 'pmailer_sub_enqueue_scripts');
add_action('wp_print_styles', 'pmailer_sub_enqueue_styles');


?>
