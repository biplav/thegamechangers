<?php
/*
Template Name: Service Suite Template
*/

get_header(); 
$suite_name = get_post_meta( get_the_ID(), 'suite_name', true );
$tagline = get_post_meta( get_the_ID(), 'tagline', true );
?>

<div class="banner <?php echo $suite_name ?>">
  	<div class="bannerinner">
    	<img src="<?php echo get_template_directory_uri(); ?>/images/banner-<?php echo $suite_name ?>.jpg">
        <h1><strong><?php the_title() ?></strong>
			<br><?php echo $tagline ?>
        </h1>
    </div>
  </div>

 <?php if (have_posts()) : while (have_posts()) : the_post();?>
	<?php the_content(); ?>
 <?php endwhile; endif; ?>

<script src="<?php echo get_template_directory_uri(); ?>/js/SpryAccordion.js	" type="text/javascript"></script>
<script src="<?php echo get_template_directory_uri(); ?>/js/SpryTabbedPanels.js" type="text/javascript"></script>
<link href="<?php echo get_template_directory_uri(); ?>/css/SpryTabbedPanels.css" rel="stylesheet" type="text/css">
<link href="<?php echo get_template_directory_uri(); ?>/css/SpryAccordion.css" rel="stylesheet" type="text/css">

<script type="text/javascript">
	var defaultTab = 0;
	function getParameterByName(name) {
    	name = name.replace(/[\[]/, "\\[").replace(/[\]]/, "\\]");
    	var regex = new RegExp("[\\?&]" + name + "=([^&#]*)"),
        	results = regex.exec(location.search);
    	return results == null ? "" : decodeURIComponent(results[1].replace(/\+/g, " "));
	}
	if(typeof panelMap !== 'undefined') {
		var panelName = getParameterByName('panel');
		console.log(panelName);
		if(panelName) {
			if(panelMap[panelName]) {
				defaultTab=panelMap[panelName];
			}
			console.log(defaultTab)
		}
	}	
	var TabbedPanels1 = new Spry.Widget.TabbedPanels("TabbedPanels1", {defaultTab: defaultTab});
</script>
<script>
var Accordion1 = new Spry.Widget.Accordion("Accordion1", { useFixedPanelHeights: false, defaultPanel: -1 });
</script>
</div>
<?php get_footer(); ?>
 
