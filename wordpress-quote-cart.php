<?php
session_start();
/* 
Plugin Name: Digit Quote Cart
Plugin URI: https://github.com/aymencis/wordpress-quote-cart
Description: Add Quote Cart to WP
Version: 1.0
Author: Aymencis
Author URI: https://github.com/aymencis
License: GPLv2

 */

if (!function_exists('http_build_url'))
{
    define('HTTP_URL_REPLACE', 1);              // Replace every part of the first URL when there's one of the second URL
    define('HTTP_URL_JOIN_PATH', 2);            // Join relative paths
    define('HTTP_URL_JOIN_QUERY', 4);           // Join query strings
    define('HTTP_URL_STRIP_USER', 8);           // Strip any user authentication information
    define('HTTP_URL_STRIP_PASS', 16);          // Strip any password authentication information
    define('HTTP_URL_STRIP_AUTH', 32);          // Strip any authentication information
    define('HTTP_URL_STRIP_PORT', 64);          // Strip explicit port numbers
    define('HTTP_URL_STRIP_PATH', 128);         // Strip complete path
    define('HTTP_URL_STRIP_QUERY', 256);        // Strip query string
    define('HTTP_URL_STRIP_FRAGMENT', 512);     // Strip any fragments (#identifier)
    define('HTTP_URL_STRIP_ALL', 1024);         // Strip anything but scheme and host

    // Build an URL
    // The parts of the second URL will be merged into the first according to the flags argument. 
    // 
    // @param   mixed           (Part(s) of) an URL in form of a string or associative array like parse_url() returns
    // @param   mixed           Same as the first argument
    // @param   int             A bitmask of binary or'ed HTTP_URL constants (Optional)HTTP_URL_REPLACE is the default
    // @param   array           If set, it will be filled with the parts of the composed url like parse_url() would return 
    function http_build_url($url, $parts=array(), $flags=HTTP_URL_REPLACE, &$new_url=false)
    {
        $keys = array('user','pass','port','path','query','fragment');

        // HTTP_URL_STRIP_ALL becomes all the HTTP_URL_STRIP_Xs
        if ($flags & HTTP_URL_STRIP_ALL)
        {
            $flags |= HTTP_URL_STRIP_USER;
            $flags |= HTTP_URL_STRIP_PASS;
            $flags |= HTTP_URL_STRIP_PORT;
            $flags |= HTTP_URL_STRIP_PATH;
            $flags |= HTTP_URL_STRIP_QUERY;
            $flags |= HTTP_URL_STRIP_FRAGMENT;
        }
        // HTTP_URL_STRIP_AUTH becomes HTTP_URL_STRIP_USER and HTTP_URL_STRIP_PASS
        else if ($flags & HTTP_URL_STRIP_AUTH)
        {
            $flags |= HTTP_URL_STRIP_USER;
            $flags |= HTTP_URL_STRIP_PASS;
        }

        // Parse the original URL
        $parse_url = parse_url($url);

        // Scheme and Host are always replaced
        if (isset($parts['scheme']))
            $parse_url['scheme'] = $parts['scheme'];
        if (isset($parts['host']))
            $parse_url['host'] = $parts['host'];

        // (If applicable) Replace the original URL with it's new parts
        if ($flags & HTTP_URL_REPLACE)
        {
            foreach ($keys as $key)
            {
                if (isset($parts[$key]))
                    $parse_url[$key] = $parts[$key];
            }
        }
        else
        {
            // Join the original URL path with the new path
            if (isset($parts['path']) && ($flags & HTTP_URL_JOIN_PATH))
            {
                if (isset($parse_url['path']))
                    $parse_url['path'] = rtrim(str_replace(basename($parse_url['path']), '', $parse_url['path']), '/') . '/' . ltrim($parts['path'], '/');
                else
                    $parse_url['path'] = $parts['path'];
            }

            // Join the original query string with the new query string
            if (isset($parts['query']) && ($flags & HTTP_URL_JOIN_QUERY))
            {
                if (isset($parse_url['query']))
                    $parse_url['query'] .= '&' . $parts['query'];
                else
                    $parse_url['query'] = $parts['query'];
            }
        }

        // Strips all the applicable sections of the URL
        // Note: Scheme and Host are never stripped
        foreach ($keys as $key)
        {
            if ($flags & (int)constant('HTTP_URL_STRIP_' . strtoupper($key)))
                unset($parse_url[$key]);
        }


        $new_url = $parse_url;

        return 
             ((isset($parse_url['scheme'])) ? $parse_url['scheme'] . '://' : '')
            .((isset($parse_url['user'])) ? $parse_url['user'] . ((isset($parse_url['pass'])) ? ':' . $parse_url['pass'] : '') .'@' : '')
            .((isset($parse_url['host'])) ? $parse_url['host'] : '')
            .((isset($parse_url['port'])) ? ':' . $parse_url['port'] : '')
            .((isset($parse_url['path'])) ? $parse_url['path'] : '')
            .((isset($parse_url['query'])) ? '?' . $parse_url['query'] : '')
            .((isset($parse_url['fragment'])) ? '#' . $parse_url['fragment'] : '')
        ;
    }
}

add_action( 'init', 'digit_quote_register_my_post_types' );
function digit_quote_register_my_post_types() {
    register_post_type( 'devis',
    array(
    'labels' => array( 'name' => 'Devis' ),
    'publicly _ queryable' => false,
    'exclude_from_search' => false,
    'show_in_nav_menus' => false,
    'public' => true,
    )
    );
}


function set_html_content_type() {

	return 'text/html';
}

add_action('get_header', 'digit_quote_process_post');

function digit_quote_process_post(){
    global $post;
    if(!isset($_SESSION["ids"])) {
        $_SESSION["ids"]="";
    }
 if(isset($_POST['dqc_submit']) && !empty($_SESSION["ids"])) {
   // process $_POST data here
     $emptyField = (empty($_POST['clientname']) || empty($_POST['clientemail']) || empty($_POST['clienttelephone']));
     if($emptyField) {
         $_SESSION['error'] = "Veuillez remplir les champs obligatoires";
     } else {
   $html = <<<XML
<html>
<head><title>Demande de devis</title></head>
<body>
<table align="center" width="800px;" cellpadding="1" cellspacing="1" style="font-size:12px; font-family:Verdana, Geneva, sans-serif;border:1px solid #656565;">
<tr>
	<td colspan="2" align="center"><span style="color:#C93838;font-weight:bold;">Nouvelle demande de devis</span></td>
</tr>
<tr>
	<td colspan="2" height="10" style="text-align:left;">
		&nbsp;
	</td>
</tr>
<tr>
	<td colspan="2" height="10" style="text-align:left;">
		Bonjour, vous avez recu une nouvelle demande de devis, dont les détails sont ci dessous :
	</td>
</tr>
<tr>
	<td colspan="2" height="10" style="text-align:left;">
		&nbsp;
	</td>
</tr>
<tr>
	<td width="300px" align="left" style="text-align:left;">
		<div style="background-color:#C93838; color:#FFF;">Nom :</div>
	</td>
	<td width="500px" align="left" style="text-align:left; border:1px dotted #C93838;">
		%1\$s
	</td>
</tr>
<tr>
	<td colspan="2" height="10" style="text-align:left;">
		&nbsp;
	</td>
</tr>
<tr>
	<td width="300px" align="left" style="text-align:left;">
		<div style="background-color:#C93838; color:#FFF;">Email :</div>
	</td>
	<td width="500px" align="left" style="text-align:left; border:1px dotted #C93838;">
		%2\$s
	</td>
</tr>
<tr>
	<td colspan="2" height="10" style="text-align:left;">
		&nbsp;
	</td>
</tr>
<tr>
	<td width="300px" align="left" style="text-align:left;">
		<div style="background-color:#C93838; color:#FFF;">Telephone :</div>
	</td>
	<td width="500px" align="left" style="text-align:left; border:1px dotted #C93838;">
		%3\$s
	</td>
</tr>
<tr>
	<td colspan="2" height="10" style="text-align:left;">
		&nbsp;
	</td>
</tr>
<tr>
	<td width="300px" align="left" valign="top" style="text-align:left;">
		<div style="background-color:#C93838; color:#FFF;">Liste des produits :</div>
	</td>
	<td width="500px" align="left" style="text-align:left; border:1px dotted #C93838;">
		%4\$s
	</td>
</tr>
<tr>
	<td colspan="2" height="10" style="text-align:left;">
		&nbsp;
	</td>
</tr>
<tr>
	<td width="300px" align="left" valign="top" style="text-align:left;">
		<div style="background-color:#C93838; color:#FFF;">Message :</div>
	</td>
	<td width="500px" align="left" style="text-align:left; border:1px dotted #C93838;">
		%5\$s
	</td>
</tr>
<tr>
	<td colspan="2" height="10" style="text-align:left;">
		&nbsp;
	</td>
</tr>
</table>
</body>
</html>
XML;
   $ids = explode(",", $_SESSION["ids"]);
   $prods = "<ul>";
   foreach ($ids as $postID) {
       if((int)$postID>0) {
           $title = get_the_title( $postID );
           $permalink = get_permalink( $postID );
           $quantite = (isset($_POST['qte-'.$postID]) && ((int)$_POST['qte-'.$postID]>0))? $_POST['qte-'.$postID]." X ":"1 X ";
           $prods.= sprintf('<li>%s<a href="%s" target="_blank">%s</a></li>',$quantite, $permalink,$title);
       }
    }
    $prods .= "</ul>";
    $resultHtml = sprintf($html, esc_html($_POST['clientname']),esc_html($_POST['clientemail']),esc_html($_POST['clienttelephone']),$prods,esc_html($_POST['clientdescription']));
     //load the plugin options array
    $digit_quote_cart_options_arr = get_option( 'digit_quote_cart_options' );
     $multiple_to_recipients = array(
        $digit_quote_cart_options_arr['email'],
    );
    $headers[] = 'From: '.esc_html($_POST['clientname']).' <'. esc_html($_POST['clientemail']).'>';
    $headers[] = 'Bcc: John Q <jhon@doe.com>';
    $headers[] = 'To: '.$digit_quote_cart_options_arr['name'].' <'.$digit_quote_cart_options_arr['email'].'>';
    add_filter( 'wp_mail_content_type', 'set_html_content_type' );
    $subject = $digit_quote_cart_options_arr['subject']." :: ". esc_html($_POST['clientname'])." - ". esc_html($_POST['clientemail'])." - ". esc_html($_POST['clienttelephone']);
    wp_mail( $multiple_to_recipients, $subject, $resultHtml,$headers );
    
    remove_filter( 'wp_mail_content_type', 'set_html_content_type' );
      // insert in db
    if( post_type_exists( 'devis' ) ) {
        // Create post object
        $devis = array();
        $devis['post_title'] = $subject;
        $devis['post_content'] = $resultHtml;
        $devis['post_type'] = 'devis';
        $devisId = wp_insert_post( $devis );
        if($devisId) {
            $_SESSION["ids"] ="";
            $_SESSION["message"] ="Votre devis a bien ete envoye";
        }
    }
 }
 } else {
     if(isset($_GET['addquote']) && (int)$_GET['addquote']==1) {
         $ids = explode(",", $_SESSION["ids"]);
         if(!in_array($post->ID, $ids)) {
             $ids[] = $post->ID;
             $_SESSION["ids"] = join(",", $ids);
         }
     } else if(isset($_GET['remquote']) && (int)$_GET['remquote']>0) {
         $ids = explode(",", $_SESSION["ids"]);
         $postIDR = (int)$_GET['remquote'];
         if(in_array($postIDR, $ids)) {
             $k = array_search($postIDR, $ids);
             unset($ids[$k]);
             $_SESSION["ids"] = join(",", $ids);
         }
     }
 }
}
// Action hook to add the quote menu item
add_action( 'admin_menu', 'digit_quote_menu' );

//create the Quote settings sub-menu
function digit_quote_menu() {
    add_options_page( __( 'Digit Quote Cart Settings Page',
'digit-quote-cart-plugin' ), __( 'Quote Cart Settings',
'digit-quote-cart-plugin' ), 'manage_options', 'digit-quote-cart-settings',
'digit_quote_cart_settings_page' );

}

//build the plugin settings page
function digit_quote_cart_settings_page() {
//load the plugin options array
$digit_quote_cart_options_arr = get_option( 'digit_quote_cart_options' );
 
//set the option array values to variables
$subject = $digit_quote_cart_options_arr['subject'];
$name = $digit_quote_cart_options_arr['name'];
$email = $digit_quote_cart_options_arr['email'];
?>
<div class="wrap">
<h2><?php _e( 'Quote Cart Mail Options', 'digit-quote-cart-plugin' ) ?></h2>
<form method="post" action="options.php">
<?php settings_fields( 'digit-quote-cart-settings-group' ); ?>
<table class="form-table">
<tr valign="top">
<th scope="row"><?php _e( 'Subject', 'digit-quote-cart-plugin' ) ?></th>
<td><input type="text" name="digit_quote_cart_options[subject]"
value="<?php echo esc_attr( $subject ); ?>"
size="30" maxlength="50" /></td>
</tr>
<tr valign="top">
<th scope="row"><?php _e( 'Name', 'digit-quote-cart-plugin' ) ?></th>
<td><input type="text" name="digit_quote_cart_options[name]"
value="<?php echo esc_attr( $name ); ?>"
size="30" maxlength="50" /></td>
</tr>
<tr valign="top">
<th scope="row"><?php _e( 'Email', 'digit-quote-cart-plugin' ) ?></th>
<td><input type="text" name="digit_quote_cart_options[email]"
value="<?php echo esc_attr( $email ); ?>"
size="30" maxlength="50" /></td>
</tr>
</table>
<p class="submit">
<input type="submit" class="button-primary"
value="<?php _e( 'Save Changes', 'digit-quote-cart-plugin' ); ?>" />
</p>
</form>
</div>
<?php
}

// Action hook to register the plugin option settings
add_action( 'admin_init', 'digit_quote_cart_register_settings' );
function digit_quote_cart_register_settings() {
//register the array of settings
register_setting( 'digit-quote-cart-settings-group',
'digit_quote_cart_options', 'digit_quote_cart_sanitize_options' );
}
function digit_quote_cart_sanitize_options( $options ) {
$options['subject'] = ( ! empty( $options['subject'] ) ) ?
sanitize_text_field( $options['subject'] ) : '';
$options['name'] = ( ! empty( $options['name'] ) ) ?
sanitize_text_field( $options['name'] ) : '';
$options['email'] = ( ! empty( $options['email'] ) ) ?
sanitize_text_field( $options['email'] ) : '';
return $options;
}

// Action hook to create the products shortcode
add_shortcode( 'dqclink', 'digit_quote_cart_shortcode' );

//create shortcode
function digit_quote_cart_shortcode( $atts, $content = null ) {
global $post;
$ids = explode(",", $_SESSION['ids']);
extract( shortcode_atts( array(
"show" => ''
), $atts ) );
$querystr = $_GET;
$linkTitle ="";
unset($querystr["addquote"]);
unset($querystr["remquote"]);
if(in_array($post->ID, $ids)) {
    $querystr["remquote"]=$post->ID;
    $linkClass ="remquote";
    $linkTitle =__( 'Rmove from quote', 'digit-quote-cart-plugin' );
} else {
    $querystr["addquote"]=1;
    $linkClass ="addquote";
    $linkTitle =__( 'Add to quote', 'digit-quote-cart-plugin' );
}

$url = http_build_url('',array("query"=>http_build_query($querystr)));
$hs_show = sprintf('<a href="%1$s" rel="nofollow" class="%2$s">%3$s</a>', $url, $linkClass,$linkTitle);
//return the shortcode value to display
return $hs_show;
}

// Action hook to create the products shortcode
add_shortcode( 'dqclinkup', 'digit_quote_cart_shortcode_up' );

//create shortcode
function digit_quote_cart_shortcode_up( $atts, $content = null ) {
global $post;
$ids = explode(",", $_SESSION['ids']);
extract( shortcode_atts( array(
"show" => ''
), $atts ) );
if(!empty($_SESSION['ids'])) {
    $linkImg = '<a href="#" class="big-link" data-reveal-id="myModal" data-ids="'.  $_SESSION['ids'].'">
			   <img class="send-devis" alt="envoyer le devis" src="'. get_template_directory_uri().'/images/send-devis.png"> 
		     </a>';
} else {
    $linkImg = "";
}


//return the shortcode value to display
return $linkImg;
}

//reaveal sc 

// Action hook to create the products shortcode
add_shortcode( 'dqcreveal', 'digit_quote_cart_reveal_shortcode_up' );

//create shortcode
function digit_quote_cart_reveal_shortcode_up( $atts, $content = null ) {
global $post;
$linkImg = "<!-- No Error OR Message -->";
if((isset($_SESSION["error"]) && !empty($_SESSION["error"])) || (isset($_SESSION["message"]) && !empty($_SESSION["message"]))) {
			$linkImg = "<script>$( document ).ready(function() {
    $('#myModal').reveal($(this).data());
});  </script>";
unset($_SESSION["error"]);
unset($_SESSION["message"]);
        }

//return the shortcode value to display
return $linkImg;
}

//end reveal sc 

// Action hook to create plugin widget
add_action( 'widgets_init', 'digit_quote_cart_register_widgets' );
//register the widget
function digit_quote_cart_register_widgets() {
register_widget( 'dqc_widget' );
}

//dqc_widget class
class dqc_widget extends WP_Widget {
    //process our new widget
    function __construct() {
        $widget_ops = array(
        'classname'   => 'dqc-widget-class',
        'description' => __( 'Display Quote Form',
        'digit-quote-cart-plugin' ) );
        parent::__construct( 'dqc_widget', __( 'Quote Widget','digit-quote-cart-plugin')
        , $widget_ops );
    }
    //build our widget settings form
    function form( $instance ) {
        $defaults = array(
            'title' => __( 'Quote Form', 'digit-quote-cart-plugin' ),

            );
        $instance = wp_parse_args( (array) $instance, $defaults );
        $title = $instance['title'];
        
        ?>
        <p><?php _e('Title', 'digit-quote-cart-plugin') ?>:
        <input class="widefat"
        name="<?php echo $this->get_field_name( 'title' ); ?>"
        type="text" value="<?php echo esc_attr( $title ); ?>" /></p>

<?php
    }
    
    //save our widget settings
    function update( $new_instance, $old_instance ) { 
        $instance = $old_instance;
        $instance['title'] = sanitize_text_field( $new_instance['title'] );
        return $instance;
    }

    //display our widget
    function widget( $args, $instance ) {
        global $post;
 
        extract( $args );
        if(isset($_SESSION["ids"]) && !empty($_SESSION["ids"])) {
        echo $before_widget;
        $title = apply_filters( 'widget_title', $instance['title'] );
        if ( ! empty( $title ) ) { echo $before_title
        . esc_html( $title ) . $after_title; };
        
        $formStr = $_GET;
        unset($formStr["addquote"]);
        $formUrl = http_build_url('',array("query"=>http_build_query($formStr)));
        if(isset($_SESSION["error"]) && !empty($_SESSION["error"])) {
            $error = $_SESSION["error"];
            echo sprintf('<span class="deviserror">%s</span>', $error);
            unset($_SESSION["message"]);
			
        }       
        ?>
        <form id="quoteform" method="post" style="z-index: 99999;" action="<?php echo $formUrl ?>">
            <div class="control-section">
                <label><?php _e('Nom & prénom', 'digit-quote-cart-plugin') ?> *</label>
                <input type="text" name="clientname">
            </div>
            <div class="control-section">
                <label><?php _e('Email', 'digit-quote-cart-plugin') ?> *</label>
                <input type="text" name="clientemail">
            </div>
            <div class="control-section">
                <label><?php _e('Téléphone', 'digit-quote-cart-plugin') ?> *</label>
                <input type="text" name="clienttelephone">
            </div>
            <div class="control-section">
                <label><?php _e('Message', 'digit-quote-cart-plugin') ?></label>
                <textarea name="clientdescription" rows="4" cols="50"></textarea>
            </div>
            <div class="control-section">
                <div class="pajoutertitle"><?php _e('Produits ajouté', 'digit-quote-cart-plugin') ?></div>
                <table cellpadding="1" cellspacing="1" style="width:450px;margin-top:20px;" class="liste-produits-ajouter">
                    <tr><th>Produit</th><th>Qté</th><th>Retirer du devis</th></tr>
                    <?php $ids = explode(",", $_SESSION["ids"]); ?>
                    <?php foreach ($ids as $postID):?>
                    <?php if((int)$postID>0): ?>
                    <?php
                        $title = get_the_title( $postID );
                        $querystr = $_GET;
                        unset($querystr["addquote"]);
                        unset($querystr["remquote"]);
                        $querystr["remquote"]=$postID;
                        $linkTitle =__( 'Retirer', 'digit-quote-cart-plugin' );
                        $url = http_build_url('',array("query"=>http_build_query($querystr)));
                        $link = sprintf('<a href="%s" class="quotelink">%s</a>', $url,$linkTitle);

                    ?>
                    <tr>
                        <td align="center"><?php echo $title; ?></td>
                        <td align="center"><input type="text" size="2" style="width:35px;height:35px" name="qte-<?php echo $postID ?>"></td>
                        <td align="center"><?php echo $link; ?></td>
                    </tr>
                    <?php endif;?>
                    <?php endforeach;?>
                </table>
                <input type="submit" name="dqc_submit" value="Envoyer">
            </div>
            <small>* Obligatoire</small>
        </form>
        <?php
        echo $after_widget;

    } else if(isset($_SESSION["message"]) && !empty($_SESSION["message"])) {
        $message = $_SESSION["message"];
		unset($_SESSION["error"]);
        echo sprintf('<span class="devissent">%s</span>', $message);
		
        
    }
    
    }
}

