<div class="wrap">
    <h2>WooCommerce Adobe Connect CSV export</h2>
    
    <?php if(iwcace_get_woocommerce_status()) : ?>
    
        <?php $action = isset($_GET['action']) ? $_GET['action'] : false; ?>
    
        <?php if($action == 'list_orders') : ?>
        
            <?php $productId = isset($_GET['productId']) ? $_GET['productId'] : false; ?>
            
            <?php if($productId) : ?>
            
                <?php iwcace_list_orders($productId); ?>
                
            <?php else : ?>
            
                <p>No product ID given.</p>
                
            <?php endif; ?>
            
        <?php elseif($action == 'create_csv') : ?>
            
            <?php $productId = isset($_GET['productId']) ? $_GET['productId'] : false; ?>
            
            <?php if($productId) : ?>
            
                <?php iwcace_create_csv($productId); ?>
                
            <?php else : ?>
            
                <p>No product ID given.</p>
                
            <?php endif; ?>
                
        <?php else : ?>
        
            <?php iwcace_list_products(); ?>
            
        <?php endif; ?>
        
    <?php else : ?>
        <div id="message" class="error"><p>The WooCommerce plugin is not installed or activated.</p></div>
    <?php endif; ?>
</div>