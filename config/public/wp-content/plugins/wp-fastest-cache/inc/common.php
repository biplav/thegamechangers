<?php
	class WpFastestCache{
		private $menuTitle = "WP Fastest Cache";
		private $pageTitle = "WP Fastest Cache Settings";
		private $slug = "wp_fastest_cache";
		private $adminPageUrl = "wp-fastest-cache/admin/index.php";
		private $wpContentDir = "";
		private $systemMessage = "";
		private $options = array();
		private $cronJobSettings;
		private $startTime;
		private $blockCache = false;

		public function __construct(){
			$this->setWpContentDir();
			$this->setOptions();
			$this->detectNewPost();
			$this->checkCronTime();

			$this->checkActivePlugins();

			if(is_admin()){
				$this->optionsPageRequest();
				$this->iconUrl = plugins_url("wp-fastest-cache/images/icon-left.png");
				$this->setCronJobSettings();
				$this->addButtonOnEditor();
				add_action('admin_enqueue_scripts', array($this, 'addJavaScript'));
			}
		}

		public function checkActivePlugins(){
			include_once( ABSPATH . 'wp-admin/includes/plugin.php' );

			//for WP-Polls
			if(is_plugin_active('wp-polls/wp-polls.php')){ 
				require_once "wp-polls.php";
				$wp_polls = new WpPollsForWpFc();
				$wp_polls->execute();
			}
		}

		public function addButtonOnEditor(){
			add_action('admin_print_footer_scripts', array($this, 'addButtonOnQuicktagsEditor'));
			add_action('init', array($this, 'myplugin_buttonhooks'));
		}

		public function checkShortCode($content){
			preg_match("/\[wpfcNOT\]/", $content, $wpfcNOT);
			if(count($wpfcNOT) > 0){
				if(is_single() || is_page()){
					$this->blockCache = true;
				}
				$content = str_replace("[wpfcNOT]", "", $content);
			}
			return $content;
		}

		public function myplugin_buttonhooks() {
		   // Only add hooks when the current user has permissions AND is in Rich Text editor mode
		   if ( ( current_user_can('edit_posts') || current_user_can('edit_pages') ) && get_user_option('rich_editing') ) {
		     add_filter("mce_external_plugins", array($this, "myplugin_register_tinymce_javascript"));
		     add_filter('mce_buttons', array($this, 'myplugin_register_buttons'));
		   }
		}
		// Load the TinyMCE plugin : editor_plugin.js (wp2.5)
		public function myplugin_register_tinymce_javascript($plugin_array) {
		   $plugin_array['wpfc'] = plugins_url('../js/button.js?v='.time(),__file__);
		   return $plugin_array;
		}

		public function myplugin_register_buttons($buttons) {
		   array_push($buttons, 'wpfc');
		   return $buttons;
		}

		public function addButtonOnQuicktagsEditor(){
			if (wp_script_is('quicktags')){ ?>
				<script type="text/javascript">
				    QTags.addButton('wpfc_not', 'wpfcNOT', '<!--[wpfcNOT]-->', '', '', 'Block caching for this page');
			    </script>
		    <?php }
		}

		public function deactivate(){
			$wpfc = new WpFastestCache();
			$path = ABSPATH;
			if($wpfc->is_subdirectory_install()){
				$path = $wpfc->getABSPATH();
			}

			if(is_file($path.".htaccess") && is_writable($path.".htaccess")){
				$htaccess = file_get_contents($path.".htaccess");
				$htaccess = preg_replace("/#\s?BEGIN\s?WpFastestCache.*?#\s?END\s?WpFastestCache/s", "", $htaccess);
				$htaccess = preg_replace("/#\s?BEGIN\s?GzipWpFastestCache.*?#\s?END\s?GzipWpFastestCache/s", "", $htaccess);
				file_put_contents($path.".htaccess", $htaccess);
			}

			wp_clear_scheduled_hook("wp_fastest_cache");
			delete_option("WpFastestCache");
			$wpfc->deleteCache();
		}

		public function optionsPageRequest(){
			if(!empty($_POST)){
				if(isset($_POST["wpFastestCachePage"])){
					if($_POST["wpFastestCachePage"] == "options"){
						$this->saveOption();
					}else if($_POST["wpFastestCachePage"] == "deleteCache"){
						$this->deleteCache();
					}else if($_POST["wpFastestCachePage"] == "deleteCssAndJsCache"){
						$this->deleteCssAndJsCache();
					}else if($_POST["wpFastestCachePage"] == "cacheTimeout"){
						$this->addCacheTimeout();
					}
				}
			}
		}

		public function setWpContentDir(){
			$this->wpContentDir = ABSPATH."wp-content";
		}

		public function addMenuPage(){
			add_action('admin_menu', array($this, 'register_my_custom_menu_page'));
		}

		public function addJavaScript(){
			wp_enqueue_script("language", plugins_url("wp-fastest-cache/js/language.js"), array(), time(), false);
			wp_enqueue_script("info", plugins_url("wp-fastest-cache/js/info.js"), array(), time(), true);
			if(isset($this->options->wpFastestCacheLanguage) && $this->options->wpFastestCacheLanguage != "eng"){
				wp_enqueue_script("dictionary", plugins_url("wp-fastest-cache/js/lang/".$this->options->wpFastestCacheLanguage.".js"), array(), time(), false);
			}
		}

		public function register_my_custom_menu_page(){
			if(function_exists('add_menu_page')){ 
				add_menu_page($this->pageTitle, $this->menuTitle, 'manage_options', "WpFastestCacheOptions", array($this, 'optionsPage'), $this->iconUrl, 99 );
				wp_enqueue_style("wp-fastest-cache", plugins_url("wp-fastest-cache/css/style.css"), array(), time(), "all");
			}
		}

		public function optionsPage(){
			$wpFastestCacheStatus = "";
			$wpFastestCacheNewPost = "";
			$wpFastestCacheLanguage = "";
			$wpFastestCacheTimeOut = "";
			$wpFastestCacheStatus = isset($this->options->wpFastestCacheStatus) ? 'checked="checked"' : "";
			$wpFastestCacheMobile = isset($this->options->wpFastestCacheMobile) ? 'checked="checked"' : "";
			$wpFastestCacheNewPost = isset($this->options->wpFastestCacheNewPost) ? 'checked="checked"' : "";
			$wpFastestCacheMinifyHtml = isset($this->options->wpFastestCacheMinifyHtml) ? 'checked="checked"' : "";
			$wpFastestCacheMinifyCss = isset($this->options->wpFastestCacheMinifyCss) ? 'checked="checked"' : "";
			$wpFastestCacheGzip = isset($this->options->wpFastestCacheGzip) ? 'checked="checked"' : "";
			$wpFastestCacheLanguage = isset($this->options->wpFastestCacheLanguage) ? $this->options->wpFastestCacheLanguage : "eng";
			$wpFastestCacheTimeOut = isset($this->cronJobSettings["period"]) ? $this->cronJobSettings["period"] : "";
			?>
			<div class="wrap">
				<h2>WP Fastest Cache Options</h2>
				<?php if($this->systemMessage){ ?>
					<div class="updated <?php echo $this->systemMessage[1]; ?>" id="message"><p><?php echo $this->systemMessage[0]; ?></p></div>
				<?php } ?>
				<div class="tabGroup">
					<?php
						$tabs = array(array("id"=>"wpfc-options","title"=>"Settings"),
									  array("id"=>"wpfc-deleteCache","title"=>"Delete Cache"),
									  array("id"=>"wpfc-deleteCssAndJsCache","title"=>"Delete Minified Css & Js"),
									  array("id"=>"wpfc-cacheTimeout","title"=>"Cache Timeout"));

						foreach ($tabs as $key => $value){
							$checked = "";
							if(!isset($_POST["wpFastestCachePage"]) && $value["id"] == "wpfc-options"){
								$checked = ' checked="checked" ';
							}else if((isset($_POST["wpFastestCachePage"])) && ("wpfc-".$_POST["wpFastestCachePage"] == $value["id"])){
								$checked = ' checked="checked" ';
							}
							echo '<input '.$checked.' type="radio" id="'.$value["id"].'" name="tabGroup1">'."\n";
							echo '<label for="'.$value["id"].'">'.$value["title"].'</label>'."\n";
						}
					?>
				    <br>
				    <div class="tab1">
						<form method="post" name="wp_manager">
							<input type="hidden" value="options" name="wpFastestCachePage">
							<div class="questionCon">
								<div class="question">Cache System</div>
								<div class="inputCon"><input type="checkbox" <?php echo $wpFastestCacheStatus; ?> id="wpFastestCacheStatus" name="wpFastestCacheStatus"><label for="wpFastestCacheStatus">Enable</label></div>
							</div>

							<div class="questionCon">
								<div class="question">Mobile</div>
								<div class="inputCon"><input type="checkbox" <?php echo $wpFastestCacheMobile; ?> id="wpFastestCacheMobile" name="wpFastestCacheMobile"><label for="wpFastestCacheMobile">Don't show the cached version for mobile devices</label></div>
							</div>


							<div class="questionCon">
								<div class="question">New Post</div>
								<div class="inputCon"><input type="checkbox" <?php echo $wpFastestCacheNewPost; ?> id="wpFastestCacheNewPost" name="wpFastestCacheNewPost"><label for="wpFastestCacheNewPost">Clear all cache files when a post or page is published</label></div>
							</div>
							<div class="questionCon">
								<div class="question">Minify HTML</div>
								<div class="inputCon"><input type="checkbox" <?php echo $wpFastestCacheMinifyHtml; ?> id="wpFastestCacheMinifyHtml" name="wpFastestCacheMinifyHtml"><label for="wpFastestCacheMinifyHtml">You can decrease the size of page</label></div>
								<div class="get-info"><img src="<?php echo plugins_url("wp-fastest-cache/images/info.png"); ?>" /></div>
							</div>

							<div class="questionCon">
								<div class="question">Minify Css</div>
								<div class="inputCon"><input type="checkbox" <?php echo $wpFastestCacheMinifyCss; ?> id="wpFastestCacheMinifyCss" name="wpFastestCacheMinifyCss"><label for="wpFastestCacheMinifyCss">You can decrease the size of css files</label></div>
								<div class="get-info"><img src="<?php echo plugins_url("wp-fastest-cache/images/info.png"); ?>" /></div>
							</div>

							<div class="questionCon">
								<div class="question">Gzip</div>
								<div class="inputCon"><input type="checkbox" <?php echo $wpFastestCacheGzip; ?> id="wpFastestCacheGzip" name="wpFastestCacheGzip"><label for="wpFastestCacheGzip">Reduce the size of files sent from your server</label></div>
								<div class="get-info"><img src="<?php echo plugins_url("wp-fastest-cache/images/info.png"); ?>" /></div>
							</div>


							<div class="questionCon">
								<div class="question">Language</div>
								<div class="inputCon">
									<select id="wpFastestCacheLanguage" name="wpFastestCacheLanguage">
									  <option value="de">Deutsch</option>
									  <option value="eng">English</option>
									  <option value="es">Español</option>
									  <option value="pt">Português</option>
									  <option value="ru">Русский</option>
									  <option value="tr">Türkçe</option>
									  <option value="ukr">Українська</option>
									</select> 
								</div>
							</div>
							<div class="questionCon qsubmit">
								<div class="submit"><input type="submit" value="Submit" class="button-primary"></div>
							</div>
						</form>
				    </div>
				    <div class="tab2">
				    	<form method="post" name="wp_manager">
				    		<input type="hidden" value="deleteCache" name="wpFastestCachePage">
				    		<div class="questionCon">
				    			<div style="padding-left:11px;">
				    			<label>You can delete all cache files</label><br>
				    			<label>Target folder</label> <b><?php echo $this->wpContentDir; ?>/cache/all</b>
				    			</div>
				    		</div>
				    		<div class="questionCon qsubmit">
				    			<div class="submit"><input type="submit" value="Delete Now" class="button-primary"></div>
				    		</div>
				   		</form>
				    </div>



				    <div class="tab3">
				    	<form method="post" name="wp_manager">
				    		<input type="hidden" value="deleteCssAndJsCache" name="wpFastestCachePage">
				    		<div class="questionCon">
				    			<div style="padding-left:11px;">
				    			<label>If you modify any css or js file, you have to delete minified js and css files</label><br>
				    			<label>All cache files will be removed as well</label><br>
				    			<label>Target folder</label> <b><?php echo $this->wpContentDir; ?>/cache/wpfc-minified</b><br>
				    			<label>Target folder</label> <b><?php echo $this->wpContentDir; ?>/cache/all</b>
				    			</div>
				    		</div>
				    		<div class="questionCon qsubmit">
				    			<div class="submit"><input type="submit" value="Delete Now" class="button-primary"></div>
				    		</div>
				   		</form>
				    </div>


				    <div class="tab4">
				    	<form method="post" name="wp_manager">
				    		<input type="hidden" value="cacheTimeout" name="wpFastestCachePage">
				    		<div class="questionCon">
				    			<label style="padding-left:11px;">All cached files are deleted at the determinated time.</label>
				    		</div>
				    		<div class="questionCon" style="text-align: center;padding-top: 10px;">
									<select id="wpFastestCacheTimeOut" name="wpFastestCacheTimeOut">
										<?php
											$arrSettings = array(array("value" => "", "text" => "Choose One"),
																array("value" => "hourly", "text" => "Once an hour"),
																array("value" => "daily", "text" => "Once a day"),
																array("value" => "twicedaily", "text" => "Twice a day"));

											foreach ($arrSettings as $key => $value) {
												$checked = $value["value"] == $wpFastestCacheTimeOut ? 'selected=""' : "";
												echo "<option {$checked} value='{$value["value"]}'>{$value["text"]}</option>";
											}
										?>
									</select> 
							</div>
							<?php if($wpFastestCacheTimeOut){ ?>
								<div class="questionCon">
									<table class="widefat fixed" style="border:0;border-top:1px solid #DEDBD1;border-radius:0;margin: 5px 4% 0 4%;width: 92%;">
										<thead>
											<tr>
												<th scope="col" style="border-left:1px solid #DEDBD1;border-top-left-radius:0;">Next due</th>
												<th scope="col" style="border-right:1px solid #DEDBD1;border-top-right-radius:0;">Schedule</th>
											</tr>
										</thead>
											<tbody>
												<tr>
													<th scope="row" style="border-left:1px solid #DEDBD1;"><?php echo date("d-m-Y @ H:i", $this->cronJobSettings["time"]); ?></th>
													<td style="border-right:1px solid #DEDBD1;"><?php echo $this->cronJobSettings["period"]; ?>
														<label id="deleteCron" style="float:right;padding-right:5px;">[ x ]</label>
														<script>
															jQuery("#deleteCron").click(function(){
																var selectPeriod = jQuery("#wpFastestCacheTimeOut");
																selectPeriod.val("");
																var submit = selectPeriod.closest("form").find('input[type="submit"]');
																submit.click();
															})
														</script>
													</td>
												</tr>
											</tbody>
									</table>
					    		</div>
				    		<?php } ?>
				    		<div class="questionCon" style="text-align: center;padding-top: 10px;">
				    			<strong><label>Server time</label>: <?php echo date("Y-m-d H:i:s"); ?></strong>
				    		</div>
				    		<div class="questionCon qsubmit">
				    			<div class="submit"><input type="submit" value="Submit" class="button-primary"></div>
				    		</div>
				   		</form>
				    </div>
				</div>
				<div class="omni_admin_sidebar">
				<div class="omni_admin_sidebar_section">
				  <h3>Having Issues?</h3>
				  <ul>
				    <li><label>You can create a ticket</label> <a target="_blank" href="http://wordpress.org/support/plugin/wp-fastest-cache"><label>WordPress support forum</label></a></li>
				  <?
				  	if(isset($this->options->wpFastestCacheLanguage) && $this->options->wpFastestCacheLanguage == "tr"){
				  		?>
				  		<li><label>R10 Üzerinden Sorabilirsiniz</label> <a target="_blank" href="http://www.r10.net/wordpress/1096868-wp-fastest-cache-wp-en-hizli-ve-en-basit-cache-sistemi.html"><label>R10.net destek başlığı</label></a></li>
				  		<?
				  	}
				  ?>
				  </ul>
				  </div>
				</div>
			</div>
			<script>Wpfclang.init("<?php echo $wpFastestCacheLanguage; ?>");</script>
			<?php
		}

		public function checkCronTime(){
			add_action($this->slug,  array($this, 'setSchedule'));
			add_action($this->slug."TmpDelete",  array($this, 'actionDelete'));
		}

		public function detectNewPost(){
			if(isset($this->options->wpFastestCacheNewPost) && isset($this->options->wpFastestCacheStatus)){
				add_filter ('publish_post', array($this, 'deleteCache'));
				add_filter ('delete_post', array($this, 'deleteCache'));
			}
		}

		public function deleteCache(){
			if(is_dir($this->wpContentDir."/cache/all")){
				//$this->rm_folder_recursively($this->wpContentDir."/cache/all");
				if(is_dir($this->wpContentDir."/cache/tmpWpfc")){
					rename($this->wpContentDir."/cache/all", $this->wpContentDir."/cache/tmpWpfc/".time());
					wp_schedule_single_event(time() + 60, $this->slug."TmpDelete");
					$this->systemMessage = array("All cache files have been deleted","success");
				}else if(@mkdir($this->wpContentDir."/cache/tmpWpfc", 0755, true)){
					rename($this->wpContentDir."/cache/all", $this->wpContentDir."/cache/tmpWpfc/".time());
					wp_schedule_single_event(time() + 60, $this->slug."TmpDelete");
					$this->systemMessage = array("All cache files have been deleted","success");
				}else{
					$this->systemMessage = array("Permission of <strong>/wp-content/cache</strong> must be <strong>755</strong>", "error");
				}
			}else{
				$this->systemMessage = array("Already deleted","success");
			}
		}

		public function deleteCssAndJsCache(){
			if(is_dir($this->wpContentDir."/cache/wpfc-minified")){
				$this->rm_folder_recursively($this->wpContentDir."/cache/wpfc-minified");
				$this->deleteCache();
			}else{
				$this->systemMessage = array("Already deleted","success");
			}
		}

		public function actionDelete(){
			if(is_dir($this->wpContentDir."/cache/tmpWpfc")){
				$this->rm_folder_recursively($this->wpContentDir."/cache/tmpWpfc");
				if(is_dir($this->wpContentDir."/cache/tmpWpfc")){
					wp_schedule_single_event(time() + 60, $this->slug."TmpDelete");
				}
			}
		}

		public function addCacheTimeout(){
			if(isset($_POST["wpFastestCacheTimeOut"])){
				if($_POST["wpFastestCacheTimeOut"]){
					wp_clear_scheduled_hook($this->slug);
					wp_schedule_event(time() + 120, $_POST["wpFastestCacheTimeOut"], $this->slug);
				}else{
					wp_clear_scheduled_hook($this->slug);
				}
			}
		}

		public function setSchedule(){
			$this->deleteCache();
		}

		public function setCronJobSettings(){
			if(wp_next_scheduled($this->slug)){
				$this->cronJobSettings["period"] = wp_get_schedule($this->slug);
				$this->cronJobSettings["time"] = wp_next_scheduled($this->slug);
			}
		}

		public function rm_folder_recursively($dir, $i = 1) {
		    foreach(scandir($dir) as $file) {
		    	if($i > 500){
		    		return true;
		    	}else{
		    		$i++;
		    	}
		        if ('.' === $file || '..' === $file) continue;
		        if (is_dir("$dir/$file")) $this->rm_folder_recursively("$dir/$file", $i);
		        else @unlink("$dir/$file");
		    }
		    
		    @rmdir($dir);
		    return true;
		}

		public function saveOption(){
			unset($_POST["wpFastestCachePage"]);
			$data = json_encode($_POST);
			//for optionsPage() $_POST is array and json_decode() converts to stdObj
			$this->options = json_decode($data);

			$this->systemMessage = $this->modifyHtaccess($_POST);

			if(isset($this->systemMessage[1]) && $this->systemMessage[1] != "error"){

				if($message = $this->checkCachePathWriteable()){


					if(is_array($message)){
						$this->systemMessage = $message;
					}else{
						if(get_option("WpFastestCache")){
							update_option("WpFastestCache", $data);
						}else{
							add_option("WpFastestCache", $data, null, "yes");
						}
					}
				}
			}
		}

		public function checkCachePathWriteable(){
			$message = array();

			if(!is_dir($this->wpContentDir."/cache/")){
				if (@mkdir($this->wpContentDir."/cache/", 0755, true)){
					//
				}else{
					array_push($message, "- /wp-content/cache/ is needed to be created");
				}
			}else{
				if (@mkdir($this->wpContentDir."/cache/testWpFc/", 0755, true)){
					rmdir($this->wpContentDir."/cache/testWpFc/");
				}else{
					array_push($message, "- /wp-content/cache/ permission has to be 755");
				}
			}

			if(!is_dir($this->wpContentDir."/cache/all/")){
				if (@mkdir($this->wpContentDir."/cache/all/", 0755, true)){
					//
				}else{
					array_push($message, "- /wp-content/cache/all/ is needed to be created");
				}
			}else{
				if (@mkdir($this->wpContentDir."/cache/all/testWpFc/", 0755, true)){
					rmdir($this->wpContentDir."/cache/all/testWpFc/");
				}else{
					array_push($message, "- /wp-content/cache/all/ permission has to be 755");
				}	
			}

			if(count($message) > 0){
				return array(implode("<br>", $message), "error");
			}else{
				return true;
			}
		}

		public function setOptions(){
			if($data = get_option("WpFastestCache")){
				$this->options = json_decode($data);
			}
		}

		public function is_subdirectory_install(){
			if(strlen(site_url()) > strlen(home_url())){
				return true;
			}
			return false;
		}

		public function getABSPATH(){
			$path = ABSPATH;
			$siteUrl = site_url();
			$homeUrl = home_url();
			$diff = str_replace($homeUrl, "", $siteUrl);
			$diff = trim($diff,"/");

		    $pos = strrpos($path, $diff);

		    if($pos !== false){
		    	$path = substr_replace($path, "", $pos, strlen($diff));
		    	$path = trim($path,"/");
		    	$path = "/".$path."/";
		    }
		    return $path;
		}

		public function modifyHtaccess($post){
			$path = ABSPATH;
			if($this->is_subdirectory_install()){
				$path = $this->getABSPATH();
			}

			if((isset($post["wpFastestCacheStatus"]) && $post["wpFastestCacheStatus"] == "on") || (isset($post["wpFastestCacheGzip"]) && $post["wpFastestCacheGzip"] == "on")){
				if(!is_file($path.".htaccess")){
					return array(".htaccess was not found", "error");
				}else if(is_writable($path.".htaccess")){
					$htaccess = file_get_contents($path.".htaccess");
					$htaccess = $this->insertRewriteRule($htaccess);
					$htaccess = $this->insertGzipRule($htaccess, $post);
					file_put_contents($path.".htaccess", $htaccess);
				}else{
					return array(".htaccess is not writable", "error");
				}
				return array("Options have been saved", "success");
			}else{
				//disable
				$this->deleteCache();
				return array("Options have been saved", "success");
			}
		}

		public function insertGzipRule($htaccess, $post){
			if(isset($post["wpFastestCacheGzip"]) && $post["wpFastestCacheGzip"] == "on"){
		    	$data = "# BEGIN GzipWpFastestCache"."\n".
		          		"<IfModule mod_deflate.c>"."\n".
		  				"AddOutputFilterByType DEFLATE text/plain"."\n".
		  				"AddOutputFilterByType DEFLATE text/html"."\n".
		  				"AddOutputFilterByType DEFLATE text/xml"."\n".
		  				"AddOutputFilterByType DEFLATE text/css"."\n".
		  				"AddOutputFilterByType DEFLATE application/xml"."\n".
		  				"AddOutputFilterByType DEFLATE application/xhtml+xml"."\n".
		  				"AddOutputFilterByType DEFLATE application/rss+xml"."\n".
		  				"AddOutputFilterByType DEFLATE application/javascript"."\n".
		  				"AddOutputFilterByType DEFLATE application/x-javascript"."\n".
		  				"</IfModule>"."\n".
						"# END GzipWpFastestCache"."\n\n";

				preg_match("/BEGIN GzipWpFastestCache/", $htaccess, $check);
				if(count($check) === 0){
					return $data.$htaccess;
				}else{
					return $htaccess;
				}	
			}else{
				//delete gzip rules
				$htaccess = preg_replace("/#\s?BEGIN\s?GzipWpFastestCache.*?#\s?END\s?GzipWpFastestCache/s", "", $htaccess);
				//echo $htaccess;
				//file_put_contents(ABSPATH.".htaccess", $htaccess);
				return $htaccess;
			}
		}

		public function insertRewriteRule($htaccess){
			$htaccess = preg_replace("/#\s?BEGIN\s?WpFastestCache.*?#\s?END\s?WpFastestCache/s", "", $htaccess);
			$htaccess = $this->getHtaccess().$htaccess;

			return $htaccess;
		}

		public function prefixRedirect(){
			$forceTo = "";

			if(preg_match("/^https:\/\//", home_url())){
				if(preg_match("/^https:\/\/www\./", home_url())){
					$forceTo = "\nRewriteCond %{HTTPS} !=on"."\n".
							   "RewriteCond %{HTTP_HOST} !^www\."."\n".
							   "RewriteCond %{REQUEST_URI} !^/wp-login.php"."\n".
							   "RewriteCond %{REQUEST_URI} !^/wp-admin"."\n".
							   "RewriteCond %{REQUEST_URI} !^/wp-content"."\n".
							   "RewriteRule ^(.*)$ http://www.%{HTTP_HOST}/$1 [R=301,L]"."\n\n".

							   "RewriteCond %{HTTPS} !=on"."\n".
							   "RewriteCond %{HTTP_HOST} ^www\.(.*)$ [NC]"."\n".
							   "RewriteCond %{REQUEST_URI} !^/wp-login.php"."\n".
							   "RewriteCond %{REQUEST_URI} !^/wp-admin"."\n".
							   "RewriteCond %{REQUEST_URI} !^/wp-content"."\n".
							   "RewriteRule ^(.*)$ https://www.%1/$1 [R=301,L]"."\n\n";
				}else{
					$forceTo = "\nRewriteCond %{HTTPS} !=on"."\n".
							   "RewriteCond %{HTTP_HOST} ^www\.(.*)$ [NC]"."\n".
							   "RewriteCond %{REQUEST_URI} !^/wp-login.php"."\n".
							   "RewriteCond %{REQUEST_URI} !^/wp-admin"."\n".
							   "RewriteCond %{REQUEST_URI} !^/wp-content"."\n".
							   "RewriteRule ^(.*)$ http://%1/$1 [R=301,L]"."\n\n".

							   "RewriteCond %{HTTPS} !=on"."\n".
							   "RewriteCond %{HTTP_HOST} !^www\."."\n".
							   "RewriteCond %{REQUEST_URI} !^/wp-login.php"."\n".
							   "RewriteCond %{REQUEST_URI} !^/wp-admin"."\n".
							   "RewriteCond %{REQUEST_URI} !^/wp-content"."\n".
							   "RewriteRule ^(.*)$ https://%{HTTP_HOST}/$1 [R=301,L]"."\n\n";
				}
			}else{
				if(preg_match("/^http:\/\/www\./", home_url())){
					$forceTo = "\nRewriteCond %{HTTP_HOST} !^www\."."\n".
							   "RewriteCond %{REQUEST_URI} !^/wp-login.php"."\n".
							   "RewriteCond %{REQUEST_URI} !^/wp-admin"."\n".
							   "RewriteCond %{REQUEST_URI} !^/wp-content"."\n".
							   "RewriteRule ^(.*)$ http://www.%{HTTP_HOST}/$1 [R=301,L]"."\n\n";
				}else{
					$forceTo = "\nRewriteCond %{HTTP_HOST} ^www\.(.*)$ [NC]"."\n".
							   "RewriteCond %{REQUEST_URI} !^/wp-login.php"."\n".
							   "RewriteCond %{REQUEST_URI} !^/wp-admin"."\n".
							   "RewriteCond %{REQUEST_URI} !^/wp-content"."\n".
							   "RewriteRule ^(.*)$ http://%1/$1 [R=301,L]"."\n\n";
				}
			}
			return $forceTo;
		}

		public function getHtaccess(){
			$mobile = "";

			if(isset($_POST["wpFastestCacheMobile"]) && $_POST["wpFastestCacheMobile"] == "on"){
				$mobile = "RewriteCond %{HTTP_USER_AGENT} !^.*(iphone|sony|symbos|nokia|samsung|mobile|epoc|ericsson|panasonic|philips|sanyo|sharp|sie-|portalmmm|blazer|avantgo|danger|palm|series60|palmsource|pocketpc|android|blackberry|playbook|iphone|ipod|iemobile|palmos|webos|googlebot-mobile).*$ [NC]"."\n";
			}

			$data = "# BEGIN WpFastestCache"."\n".
					"<IfModule mod_rewrite.c>"."\n".
					"RewriteEngine On"."\n".
					"RewriteBase /"."\n".$this->prefixRedirect().
					"RewriteCond %{REQUEST_METHOD} !POST"."\n".
					"RewriteCond %{QUERY_STRING} !.*=.*"."\n".
					"RewriteCond %{HTTP:Cookie} !^.*(comment_author_|wordpress_logged_in|wp-postpass_).*$"."\n".
					'RewriteCond %{HTTP:X-Wap-Profile} !^[a-z0-9\"]+ [NC]'."\n".
					'RewriteCond %{HTTP:Profile} !^[a-z0-9\"]+ [NC]'."\n".$mobile.
					"RewriteCond %{DOCUMENT_ROOT}/".$this->getRewriteBase()."wp-content/cache/all/".$this->getRewriteBase(true)."$1/index.html -f"."\n".
					'RewriteRule ^(.*) "/'.$this->getRewriteBase().'wp-content/cache/all/'.$this->getRewriteBase(true).'$1/index.html" [L]'."\n".
					"</IfModule>"."\n".
					"# END WpFastestCache"."\n";
			return $data;
		}

		public function getRewriteBase($sub = ""){
			if($sub && $this->is_subdirectory_install()){
				return "";
			}
			
			$tmp = str_replace($_SERVER['DOCUMENT_ROOT']."/", "", ABSPATH);
			$tmp = str_replace("/", "", $tmp);
			$tmp = $tmp ? $tmp."/" : "";
			return $tmp;
		}

		public function createCache(){
			if(isset($this->options->wpFastestCacheStatus)){
				$this->startTime = microtime(true);
				ob_start(array($this, "callback"));
			}
		}

		public function ignored(){
			$ignored = array("robots.txt", "wp-login.php", "wp-cron.php", "wp-content", "wp-admin", "wp-includes");
			foreach ($ignored as $key => $value) {
				if (strpos($_SERVER["REQUEST_URI"], $value) === false) {
				}else{
					return true;
				}
			}
			return false;
		}

		public function callback($buffer){
			$buffer = $this->checkShortCode($buffer);

			if(defined('DONOTCACHEPAGE')){ // for Wordfence: not to cache 503 pages
				return $buffer;
			}else if(is_404()){
				return $buffer;
			}else if($this->ignored()){
				return $buffer;
			}else if($this->blockCache === true){
				return $buffer."<!-- not cached -->";
			}else if(isset($_GET["preview"])){
				return $buffer."<!-- not cached -->";
			}else if($this->checkHtml($buffer)){
				return $buffer;
			}else{
				$cachFilePath = $this->wpContentDir."/cache/all".$_SERVER["REQUEST_URI"];

				$content = $this->cacheDate($buffer);
				$content = $this->minify($content);
				$content = $this->minifyCss($content);

				$this->createFolder($cachFilePath, $content);

				return $buffer;
			}
		}

		public function minify($content){
			return isset($this->options->wpFastestCacheMinifyHtml) ? preg_replace("/^\s+/m", "", ((string) $content)) : $content;
		}

		public function checkHtml($buffer){
			preg_match('/<\/html>/', $buffer, $htmlTag);
			preg_match('/<\/body>/', $buffer, $bodyTag);
			if(count($htmlTag) > 0 && count($bodyTag) > 0){
				return 0;
			}else{
				return 1;
			}
		}

		public function cacheDate($buffer){
			return $buffer."<!-- WP Fastest Cache file was created in ".$this->creationTime()." seconds, on ".date("d-m-y G:i:s")." -->";
		}

		public function creationTime(){
			return microtime(true) - $this->startTime;
		}

		public function isCommenter(){
			$commenter = wp_get_current_commenter();
			return isset($commenter["comment_author_email"]) && $commenter["comment_author_email"] ? false : true;
		}

		public function createFolder($cachFilePath, $buffer, $extension = "html", $prefix = ""){
			if($buffer && strlen($buffer) > 100){
				if (!is_user_logged_in() && $this->isCommenter()){
					if(!is_dir($cachFilePath)){
						if(is_writable($this->wpContentDir) || ((is_dir($this->wpContentDir."/cache")) && (is_writable($this->wpContentDir."/cache")))){
							if (@mkdir($cachFilePath, 0755, true)){
								file_put_contents($cachFilePath."/".$prefix."index.".$extension, $buffer);
							}else{
							}
						}else{

						}
					}else{
						if(file_exists($cachFilePath."/".$prefix."index.".$extension)){

						}else{
							file_put_contents($cachFilePath."/".$prefix."index.".$extension, $buffer);
						}
					}
				}
			}
		}

		public function minifyCss($content){
			if(isset($this->options->wpFastestCacheMinifyCss)){
				require_once "css-utilities.php";
				$css = new CssUtilities($content);

				if(count($css->getCssLinks()) > 0){
					foreach ($css->getCssLinks() as $key => $value) {
						if($href = $css->checkInternal($value)){
							$minifiedCss = $css->minify($href);

							if($minifiedCss){
								if(!is_dir($minifiedCss["cachFilePath"])){
									$prefix = time();
									$this->createFolder($minifiedCss["cachFilePath"], $minifiedCss["cssContent"], "css", $prefix);
								}

								if($cssFiles = @scandir($minifiedCss["cachFilePath"], 1)){
									$content = str_replace($href, $minifiedCss["url"]."/".$cssFiles[0], $content);	
								}
							}
						}
					}
				}
			}
			return $content;
		}
	}
?>