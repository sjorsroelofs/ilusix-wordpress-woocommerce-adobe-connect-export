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
            
        <?php elseif($action == 'list_columns') : ?>
            
            <?php $productId = isset($_GET['productId']) ? $_GET['productId'] : false; ?>
            
            <?php if($productId) : ?>

                <?php iwcace_list_columns($_POST, $productId); ?>
                
            <?php else : ?>
            
                <p>No product ID given.</p>
                
            <?php endif; ?>
            
        <?php elseif($action == 'create_csv') : ?>

            <?php $productId = isset($_GET['productId']) ? $_GET['productId'] : false; ?>
            
            <?php if($productId && isset($_POST['orders-post-result'])) : ?>
            
                <?php if($fileUrl = iwcace_create_csv($_POST['orders-post-result'], $productId, $_POST)) : ?>
                    <br/>
                    <a class="button button-primary" href="<?php echo $fileUrl; ?>">Download CSV</a>
                <?php else : ?>
                    <p>You haven't selected any users.</p>
                <?php endif; ?>
                
            <?php else : ?>
            
                <p>No product ID given of users selected.</p>
                
            <?php endif; ?>
                
        <?php else : ?>
        
            <?php iwcace_list_products(); ?>
            
        <?php endif; ?>
        
    <?php else : ?>
        <div id="message" class="error"><p>The WooCommerce plugin is not installed or activated.</p></div>
    <?php endif; ?>
</div>