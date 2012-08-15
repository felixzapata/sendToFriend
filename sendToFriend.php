<?php
/*
Plugin Name: Enviar a un amigo
Plugin URI: 
Description: Realiza el envio de la pagina a un amigo indicado mediante email devolviendo una confirmacion de envio en la url
Author: Felix Zapata
Version: 0.1
*/
class sendToFriend {

/*--------------------------------------------*
* Constructor
*--------------------------------------------*/
/**
* Initializes the plugin by setting localization, filters, and administration functions.
*/
function __construct() {


register_activation_hook( __FILE__, array( &$this, 'activate' ) );
register_deactivation_hook( __FILE__, array( &$this, 'deactivate' ) );

/*
* Define the custom functionality for your plugin. The first parameter of the
* add_action/add_filter calls are the hooks into which your code should fire.
*
* The second parameter is the function name located within this class. See the stubs
* later in the file.
*
*/

add_action( 'init', array( $this, 'wp_sendToFriend' ) ); 
add_filter( 'query_vars', array( $this,'wp_add_queryvars' ) );  


} // end constructor

/**
* Fired when the plugin is activated.
*
* @params $network_wide True if WPMU superadmin uses "Network Activate" action, false if WPMU is disabled or plugin is activated on an individual blog
*/
function activate( $network_wide ) {
	global $wp_rewrite;
	add_rewrite_endpoint( 'send', EP_PAGES | EP_PERMALINK | EP_ROOT );  
	$wp_rewrite->flush_rules();
} 

/**
* Fired when the plugin is deactivated.
*
* @params $network_wide True if WPMU superadmin uses "Network Activate" action, false if WPMU is disabled or plugin is activated on an individual blog
*/
function deactivate( $network_wide ) {
	global $wp_rewrite;
	$wp_rewrite->flush_rules();	
} 

/*--------------------------------------------*
* Core Functions
*---------------------------------------------*/

    function wp_add_queryvars( $query_vars ) {  
        $query_vars[] = 'send';  
        return $query_vars;  
    }  
   

	function wp_sendToFriend(){

		/* add_rewrite_endpint() / WP_rewrite::add_endpoint don't seem to honour $places:
 			http://wordpress.org/support/topic/add_rewrite_endpint-wp_rewriteadd_endpoint-dont-seem-to-honour-places */
		global $wp_rewrite;
 		add_rewrite_endpoint( 'send', EP_PAGES | EP_PERMALINK | EP_ROOT );
 		/* *****************  */

		if( ! isset( $_POST['action'] ) || 'wp_sendFriend_message' != $_POST['action'] ) return;  
		else {

			if(!($_POST["forE"]) || !($_POST["nombre"]) || !($_POST["email"]) || !wp_verify_nonce($_POST['wp_sendFriend_nonce'],'wp_sendFriend_message')) {
				$error="false";
				
			}else{

				$emails = $_POST["forE"];
				$nombre = $_POST["nombre"];
				$email = $_POST["email"];
				$comment = $_POST["comment"];
				$id = $_POST["idPost"];
				$emailsDestino = preg_split("/[,]+/", $emails);
									
				foreach ($emailsDestino as $k => $destinatario) { 
					$subject = "Te recomiendan esta noticia:";
					$nombre = HTMLEntities($nombre, ENT_COMPAT, "UTF-8");		
					
					
					$cuerpo = "Hola: " . $email . "<br /><br />";
					$cuerpo .= $nombre . " ha visitado nuestra página y quiere recomendarte esta noticia: <a href='" . get_permalink( $id ) . "'>" . get_the_title( $id ) . "</a><br /><br />";

					if($comment != ""){	
						$cuerpo .= "Además, quiere comentarte lo siguiente: <br />";
						$cuerpo .= $comment . "<br />";
					}
					
					
					$headers = "MIME-Version: 1.0\r\n";
					$headers .= "Content-type: text/html; charset=UTF-8\r\n";
					$headers .= "From: ".$nombre." <".$email.">\r\n";
							
					$result = wp_mail($destinatario,$subject,$cuerpo,$headers);

					$error = "true";

				}
					
			}
			
			$location = $_POST["redirect"]."/send/".$error;

			wp_redirect($location);

			exit();
		}

	}
  
} 

new sendToFriend();
?>