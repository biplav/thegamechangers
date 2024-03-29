<?php
	class WpPollsForWpFc{
		public function __construct(){
			add_action( 'wp_ajax_nopriv_example_ajax_request', array($this, "example_ajax_request"));
		}

		public function execute(){
			add_action( 'wp_footer', array($this, "wpfc_wp_polls") );
		}

		public function wpfc_wp_polls() { ?>
			<script type="text/javascript">
				jQuery(document).ready(function(){
					var poll = jQuery('div[id^=\"polls-\"][id$=\"-loading\"]');
					if(poll.length > 0){
						poll_id = poll.attr('id').match(/\d+/)[0];
						poll_nonce = jQuery('#poll_' + poll_id + '_nonce').val();

						jQuery.ajax({
							type: 'POST', 
							url: pollsL10n.ajax_url,
							dataType : "json",
							data : {"action": "example_ajax_request", "poll_id": poll_id},
							cache: false, 
							success: function(data){
								if(data === true){
									poll_result(poll_id);
								}else if(data === false){
									poll_booth(poll_id);
								}
							}
						});
					}
				});
			</script><?php
		}

		public function example_ajax_request() {
			$id = strip_tags($_POST["poll_id"]);
			$id = mysql_real_escape_string($id);

			$result = check_voted($id);

			if($result){
				echo "true";
			}else{
				echo "false";
			}
			die();
		}
	}
?>