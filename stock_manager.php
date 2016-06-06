<?php global $wsml; ?>
<div class="wrap<?php if ( $wsml['fullscreen'] == true ) { ?> wrap-fullscreen<?php } ?>">
  <?php if ( $wsml['fullscreen'] !== true ) { ?>
  <h2>Stock manager</h2>
  <?php } ?>
  <input type="text" class="form-control" data-filter="stock" id="stock-filer" placeholder="Search for product" value="" />
  
  <table class="table table-striped table-hover stock-table" id="stock-table">
    <thead>
      <tr>
        <th class="image">Image</th>
        <th>Product name</th>
        <th class="status">Status</th>
        <th class="price">Price</th>
        <th class="price">Sale Price</th>
        <th class="stock">Stock</th>
      </tr>
    </thead>
    <tbody>
      
        <?php
        if ( $wsml && ! empty($wsml['products']) ) {
          foreach ( $wsml['products'] as $product ) {
            
            //print_r($product);
            
            $status = "";
            if ( $product->stock > 0 ) {
              $status = '<label class="label label-success">In stock</label>';
            } else {
              $status = '<label class="label label-danger">Out of stock</label>';
            }
            
            $img = "";
            if ( $product->thumbnail ) {
              $img = sprintf('<img width="50" height="50" src="%s" alt="%s" />', $product->thumbnail, $product->post->post_title);
            }
            $href = get_permalink($product->post->ID);
            
            echo sprintf('<tr data-id="%s">
              <td class="image">
				%s
              </td>
              <td class="product_name"><a target="_blank" href="%s">%s <span class="label label-primary">%s</span></a><br /><p class="excerpt">%s</p></td>
              <td class="status">
                %s
              </td>
              <td class="price">
                <div class="input-group">
                  <span class="input-group-addon">$</span>
                  <input type="text" class="form-control" data-ajax-update data-id="%s" data-field="regular_price" aria-label="Product price" value="%s">
                </div>
              </td>
              <td class="price">
                <div class="input-group">
                  <span class="input-group-addon">$</span>
                  <input type="text" class="form-control" data-ajax-update data-id="%s" data-field="sale_price" aria-label="Sale price" value="%s">
                </div>
              </td>
              <td class="stock"><input type="number" class="form-control" data-ajax-update data-id="%s" data-field="stock" value="%s"></td>
            </tr>',
                        $product->id,
                        $img,
                        $href,
                        $product->post->post_title,
                        $product->sku,
                        strip_tags($product->post->post_excerpt),
                        $status,
                        $product->id,
                        $product->regular_price,
                        $product->id,
                        $product->sale_price,
                        $product->id,
                        $product->stock);
          }
        }
      	?>
    </tbody>
  </table>
</div>
<script>
  window.stocklist = <?php echo json_encode($wsml['products']); ?>;
</script>