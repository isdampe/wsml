<?php 

function wsml_enqueue_assets( $hook_suffix ) {
  
  if ( $hook_suffix == 'tools_page_wsml_stock_manager' ) {
    wp_enqueue_style('wsml-bootstrap', 'https://maxcdn.bootstrapcdn.com/bootstrap/3.3.6/css/bootstrap.min.css', array(), '1.0');
  	wp_enqueue_script('wsml-bootstrap-js', 'https://maxcdn.bootstrapcdn.com/bootstrap/3.3.6/js/bootstrap.min.js', array('jquery'), '1.0');
    wp_enqueue_style('wsml-manager', plugins_url('/woo-stock-manager-light/assets/css/wsml.css'), array(), '1.0');
    wp_enqueue_script('wsml-manager-js', plugins_url('/woo-stock-manager-light/assets/js/wsml.js'), array('jquery'), '1.0');
  }
  
}
add_action('admin_enqueue_scripts', 'wsml_enqueue_assets');

function wsml_add_admin_page() {
  add_submenu_page(
        'tools.php',
        'Stock Manager',
        'Stock Manager',
        'manage_options',
        'wsml_stock_manager',
        'wsml_load_stock_manager' );
}
add_action('admin_menu', 'wsml_add_admin_page');

function wsml_load_stock_manager() {
  
  wsml_stock_manager_controller();
  
  include dirname(__FILE__) . '/../stock_manager.php';
  
}

function wsml_stock_manager_controller() {
  
  global $wsml, $product, $post;
  
  if ( ! empty($_GET['fullscreen']) ) {
    $wsml['fullscreen'] = true;
  }
  
  $products = array();
  $args = array( 'orderby' => 'post_title', 'order' => 'ASC', 'post_type' => 'product', 'posts_per_page' => WSML_POSTS_PER_PAGE );
  
  $_pf = new WC_Product_Factory();
  $loop = new WP_Query( $args );
  if ( $loop->have_posts() ) {
    while ( $loop->have_posts() ) {
      $loop->the_post();
      $product = new WC_Product(get_the_ID());
      $product->stock = $product->get_stock_quantity();
      if ( has_post_thumbnail() ) {
        $thumb = wp_get_attachment_image_src( get_post_thumbnail_id($post->ID), 'thumbnail' );
        $product->thumbnail = $thumb[0];
      } else {
        $product->thumbnail = false;
      }
      $product->sku = $product->get_sku();
      $product->regular_price = $product->get_regular_price();
      $product->sale_price = $product->get_sale_price();
      $product->default_price = $product->get_price();
      
      $products[] = $product;
    }
  }
  
  wp_reset_postdata();
  $wsml['products'] = $products;
  
}

function wsml_process_ajax() {
  	
  //Admin only.
  if (! is_admin() ) {
    http_response_code(403);
    exit;
  }
  
  if ( empty($_POST['ID']) || empty($_POST['field']) ) {
    http_response_code(400);
    exit;
  }
  
  $ID = intval( $_POST['ID'] );
  $field = stripslashes($_POST['field']);
  $value = intval( (empty($_POST['value']) ? 0 : $_POST['value']) );
  
  wsml_update_product_by_field($ID,$field,$value);
  echo json_encode(array('status' => 'OK'));
  
  wp_die();
  
}
add_action( 'wp_ajax_wsml_update_product', 'wsml_process_ajax' );

function wsml_update_product_by_field($ID,$field,$value) {
  
  global $wpdb;
  
  $meta_key = false;
  
  switch ( $field ) {
    case "regular_price":
      $meta_key = "_regular_price";
      if ( $value == 0 ) {
        $value = "";
      }
      break;
    case "sale_price":
      $meta_key = "_sale_price";
      if ( $value == 0 ) {
        $value = "";
      }
      break;
    case "stock":
      $meta_key = "_stock";
      break;
  }
  
  update_post_meta($ID,$meta_key,$value);
  
  //Get meta.
  $reg = (int)get_post_meta($ID,'_regular_price',true);
  $sale = (int)get_post_meta($ID,'_sale_price',true);
  if ( $sale > 0 ) {
    update_post_meta($ID,'_price', $sale);
  } else {
    update_post_meta($ID,'_price', $reg);
  }
  
}