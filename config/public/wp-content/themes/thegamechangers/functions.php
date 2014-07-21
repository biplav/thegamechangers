<?php 
function register_my_menus() {
  register_nav_menus(
    array(
      'header-menu' => __( 'Header Menu' ),
    )
  );
}
add_action( 'init', 'register_my_menus' );
add_filter('nav_menu_css_class' , 'special_nav_class' , 10 , 2);
function special_nav_class($classes, $item){
     if( in_array('current-menu-item', $classes) && !in_array('subMenu',$classes)){
             $classes[] = 'current ';
     }
     return $classes;
}

function newautop($text)
{
    $newtext = "";
    $pos = 0;

    $tags = array('<!-- noformat on -->', '<!-- noformat off -->');
    $status = 0;

    while (!(($newpos = strpos($text, $tags[$status], $pos)) === FALSE))
    {
        $sub = substr($text, $pos, $newpos-$pos);

        if ($status)
            $newtext .= $sub;
        else
            $newtext .= convert_chars(wptexturize(wpautop($sub)));      //Apply both functions (faster)

        $pos = $newpos+strlen($tags[$status]);

        $status = $status?0:1;
    }

    $sub = substr($text, $pos, strlen($text)-$pos);

    if ($status)
        $newtext .= $sub;
    else
        $newtext .= convert_chars(wptexturize(wpautop($sub)));      //Apply both functions (faster)

    //To remove the tags
    $newtext = str_replace($tags[0], "", $newtext);
    $newtext = str_replace($tags[1], "", $newtext);

    return $newtext;
}

function newtexturize($text)
{
    return $text;   
}

function new_convert_chars($text)
{
    return $text;   
}

remove_filter('the_content', 'wpautop');
add_filter('the_content', 'newautop');

remove_filter('the_content', 'wptexturize');
add_filter('the_content', 'newtexturize');

remove_filter('the_content', 'convert_chars');
add_filter('the_content', 'new_convert_chars');
?>