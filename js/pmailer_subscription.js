var PmailerUtilities = {};

PmailerUtilities.Cookie = 
{
	readCookie: function( name )
	{
		var value = document.cookie.match('(?:^|;)\\s*' + name.replace(/([-.*+?^${}()|[\]\/\\])/g, '\\$1') + '=([^;]*)');
	    return (value) ? decodeURIComponent(value[1]) : null;
	},
	delCookie: function( name )
	{
		return PmailerUtilities.Cookie.setCookie( name, '', { duration: -1 } );
	},
	setCookie: function( name, value, options )
	{
		if( typeof name === 'undefined' || typeof value === 'undefined' )
		{
			return false;
		}
		
		var str = name + '=' + encodeURIComponent(value);
		
		if( options.domain ) str += '; domain=' + options.domain;
		if( options.path ) 
		{
			str += '; path=' + options.path;
		}
		else
		{
			str += '; path=/';
		}
		if( options.duration )
		{
			var date = new Date();
			date.setTime( date.getTime() + options.duration * 24 * 60 * 60 * 1000 );
			str += '; expires=' + date.toGMTString();
		}
		if( options.secure ) str += '; secure';
		
		return document.cookie = str;
	}
};

PmailerUtilities.Form =
{
	subscribe: function(event)
	{
		event.preventDefault();
		jQuery('#pmailer_sub_form_status').show();
		// dont allow form to be submitted mutliple times
		jQuery('#pmailer_subscription_submit').attr('disabled', 'disabled');
		jQuery('#pmailer_sub_form_error, #pmailer_sub_form_success').html('');
		
		// submit form
		var pmailer_sub_url = jQuery('#pmailer_sub_ajax_url').val();
		var params = jQuery('#pmailer_subscription_form').serialize();
		jQuery.post(pmailer_sub_url, params, function(data) 
		{
			// clear previous messages
			jQuery('#pmailer_sub_form_success, #pmailer_sub_form_error').html('').hide();
			
			// check if subscribe was succesfull
			var response = eval("(" + data + ")");
			if ( response.status == 'success' )
			{
				jQuery('#pmailer_subscription_submit').removeAttr('disabled');
				jQuery('#pmailer_sub_smart_form_status').hide();
				jQuery('#pmailer_sub_form_success').html('Email address successfully subscribed.<br />').show();
				jQuery('#pmailer_sub_form_status').hide();
				jQuery('#pmailer_subscription_form input[type|="text"]').val(''); // clear form
				jQuery('#pmailer_subscription_form input[type|="checkbox"]').removeAttr('checked'); 
				
			}
			
			// check if errors occurred
			if ( response.status == 'error' )
			{
				jQuery('#pmailer_sub_smart_form_status').hide();
				jQuery('#pmailer_subscription_submit').removeAttr('disabled');
				// warn if email address was not filled in
				if ( response.message.indexOf('required') > 1 && response.message.indexOf('set') > 1 )
				{
					jQuery('#pmailer_sub_form_error').html('Email address was not filled in.<br />').show();
				}
				
				// warn if email address was not valid
				if ( response.message.indexOf('validating') > 1 && response.message.indexOf('valid email') > 1 || response.message.indexOf('valid hostname') > 1 )
				{
					jQuery('#pmailer_sub_form_error').html('Invalid email address.<br />').show();
				}
				
				// warn no lists were selected
				if ( response.message.indexOf('lists') > 1 && response.message.indexOf('chosen') > 1 )
				{
					jQuery('#pmailer_sub_form_error').html('No lists were chosen.<br />').show();
				}
				
				jQuery('#pmailer_sub_form_status').hide();
			}
		});
		
	}
};

/* pmailer submission form logic */
(function($)
{
	// do nothing if jquery does not exist
	if (!jQuery)
	{
		return;
	}
	$(document).ready(function() 
    {
		// check if pmailer form widget is to be ajaxed and bind appropriate events
		if ( jQuery('#pmailer_sub_ajax_form').val() == 'yes' )
		{
			jQuery('#pmailer_subscription_submit').bind('click', PmailerUtilities.Form.subscribe);
		}
		
		// check if smart forms is enabled
		if( jQuery('#pmailer_sub_smart_form_enabled').val() != 'yes' )
		{
			return;
		}
		
		// check if user has chosen not to see smart form
		if ( PmailerUtilities.Cookie.readCookie('pmailer_show_smart_form') == 'false' )
		{
			return;
		}
		
		// create cookie if it does not exist
		if ( PmailerUtilities.Cookie.readCookie('pmailer_page_views') == null )
		{
			PmailerUtilities.Cookie.setCookie( 'pmailer_page_views', '1', 
			{
			    duration : 60 // In days
			});
		}
		else // increment page view 
		{
			var page_views = parseInt(PmailerUtilities.Cookie.readCookie('pmailer_page_views'));
			page_views = page_views + 1;
			
			PmailerUtilities.Cookie.setCookie('pmailer_page_views', page_views, 
			{
			    duration : 365 // In days
			});
			
			// display smart form if page views is over 5
			var show_smart_form = PmailerUtilities.Cookie.readCookie('pmailer_show_smart_form');
			if ( show_smart_form != 'false' && page_views > parseInt($('#pmailer_sub_smart_form_page_view_activation').val()) )
			{
				PmailerUtilities.Dialog.open();
			}
		}
	
    });
	
})(jQuery);

PmailerUtilities.Dialog =
{
	modalOverlay: null,
	open: function()
	{
		// create overlay if it does not exist
		if ( this.modalOverlay == null )
		{
			// create the overlay div
			var html = '<div id="pmailer_modal_over_lay"></div>';
			jQuery('body').append(html);
			this.modalOverlay = jQuery('#pmailer_modal_over_lay');
			// style the overlay
			var style = 
			{
				'top': 0, 
				'left': 0, 
				'right': 0, 
				'bottom': 0, 
				'background': '#000000', 
				'opacity': 0.5, 
				'position': 'fixed', 
				'z-index': 999999
			};
			this.modalOverlay.css(style);
			
			// create a container for the smart form
			html = '<div id="pmailer_modal_smart_container"></div>';
			jQuery('body').append(html);
			jQuery('#pmailer_modal_smart_container').css({'margin': 'auto'});
			
			
		}
		
		// now display smart form:
		var $smartForm = jQuery('#pmailer_smart_form');
		var form_width = form_height = 0;
		form_width = $smartForm.width();
		form_height = $smartForm.height();
		$smartForm = $smartForm.remove();
		
		jQuery('#pmailer_modal_smart_container').html($smartForm).width(form_width).css({'z-index': 998});
		$smartForm.show().css({
			'position': 'fixed', 
			'top': '300px', 
			'z-index': 999999
		});
		
		// IE7 Fix
		if ( jQuery.browser.msie && parseInt(jQuery.browser.version) == 7 )
		{
			$smartForm.css({'width': form_width + 'px'});
		}
		
		// focus on email address field
		jQuery('#pmailer_smart_subscriber_email').focus();
		
		// bind btn events
		jQuery('#pmailer_smart_subscription_submit').bind('click', PmailerUtilities.Dialog.subscribe);
		jQuery('#pmailer_smart_subscription_dont_bugme').bind('click', PmailerUtilities.Dialog.stayClosed);
		jQuery('#pmailer_sub_smart_close').bind('click', PmailerUtilities.Dialog.close);
		
		
	},
	
	close: function(event)
	{
		// remove form
		jQuery('#pmailer_smart_form').remove();
		// fade out overlay
		PmailerUtilities.Dialog.modalOverlay.remove();
		
		PmailerUtilities.Cookie.setCookie('pmailer_page_views', 1, 
		{
		    duration : 60 // In days
		});
		
	},
	
	stayClosed: function()
	{
		// remove form
		jQuery('#pmailer_smart_form').remove();
		// fade out overlay
		PmailerUtilities.Dialog.modalOverlay.remove();
		
		PmailerUtilities.Cookie.setCookie('pmailer_show_smart_form', 'false', 
		{
			duration : 365 // In days
		});
		// cleanup un-needed cookie
		PmailerUtilities.Cookie.delCookie('pmailer_page_views');
	},
	
	subscribe: function(event)
	{
		event.preventDefault();
		// hide previous error message
		jQuery('#pmailer_sub_smart_form_error').hide();
		// display subscribing text while ajax request is being made.
		jQuery('#pmailer_sub_smart_form_status').show();
		// disable subscribe btn
		jQuery('#pmailer_sub_smart_subscribe').attr('disabled', 'disabled');
		var pmailer_sub_url = jQuery('#pmailer_sub_ajax_url').val();
		var params = jQuery('#pmailer_subscription_smart_form').serialize();
		jQuery.post(pmailer_sub_url, params, function(data) 
		{
			// check if subscribe was succesfull
			var response = eval("(" + data + ")");
			if ( response.status == 'success' )
			{
				jQuery('#pmailer_sub_smart_form_status').hide();
				jQuery('#pmailer_subscription_smart_form').html('<span class="pmailer_subscription_success">Email address successfully subscribed.</span>').show();
				window.setTimeout('PmailerUtilities.Dialog.stayClosed();', 3000);
				// do not display the smart form again
				PmailerUtilities.Cookie.setCookie('pmailer_show_smart_form', 'false', 
				{
					duration : 365 // In days
				});
			}
			
			// check if errors occurred
			if ( response.status == 'error' )
			{
				jQuery('#pmailer_sub_smart_form_status').hide();
				jQuery('#pmailer_sub_smart_subscribe').removeAttr('disabled');
				// warn if email address was not filled in
				if ( response.message.indexOf('required') > 1 && response.message.indexOf('set') > 1 )
				{
					jQuery('#pmailer_sub_smart_form_error').html('Email address was not filled in.<br />').show();
				}
				
				// warn if email address was not valid
				if ( response.message.indexOf('validating') > 1 && response.message.indexOf('valid email') > 1 || response.message.indexOf('valid hostname') > 1 )
				{
					jQuery('#pmailer_sub_smart_form_error').html('Invalid email address.<br />').show();
				}
				
				// warn no lists were selected
				if ( response.message.indexOf('lists') > 1 && response.message.indexOf('chosen') > 1 )
				{
					jQuery('#pmailer_sub_smart_form_error').html('No lists were chosen.<br />').show();
				}
			}
			
		});
	}
};

