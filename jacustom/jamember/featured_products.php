<?php
$page_name = 'Manage Featured Products';
$page_title = 'John Amico - ' . $page_name;

// Header already exists issue. This will keep all the output in Buffer but will not release it.
//ob_start();
require_once("../common_files/include/global.inc");
require_once("session_check.inc");
require_once("templates/header.php");
require_once("templates/sidebar.php");


$self_page = basename(__FILE__);
$page_url = base_member_url() . "/$self_page?1=1";
$action_page = $self_page;
$action_page_url = $page_url;
$main_member_id = $_SESSION['member']['ses_member_id'];
$maxSelectableProducts = 12;

if( !is_featured_enable() ) { exit; }

try {
    include_once(base_shop_path() . "/app/Mage.php");
    Mage::reset();
    $app = Mage::app();

    if(class_exists('Mvisolutions_Featuredproducts_Model_Products')) {
        $maxSelectableProducts = Mvisolutions_Featuredproducts_Model_Products::MAX_FEATURED_PRODUCTS;
    }
}
catch (Exception $e) {
    //echo $e->getMessage();
}


//echo '<pre>'; var_dump( $_POST ); die();

$error = false;
$choicesSaved = true;
$selectedProducts = array();

if( !empty($_POST['submit']) ) {

    //debug(false, true, $_POST, $autoship_id, $autoshipRequest);
    $selectedProducts = $_POST['selected_products'];
    $choicesSaved = false;

    if(empty($_POST['selected_products'])) {
        $error = true;
        $errorMessage['selected_products'] = "You need to select at least 1 product to feature.";
    }

    //echo '<pre>'; var_dump( $_POST['selected_products'] ); die();

    if(!is_array($selectedProducts)) {
        $error = true;
        $errorMessage['selected_products'] = "Invalid data!";
    }
    if(count($selectedProducts) > $maxSelectableProducts) {
        $error = true;
        $errorMessage['selected_products'] = "You cannot select more than {$maxSelectableProducts} products for featuring.";
    }

    if( !$error ) {
        //echo '<pre>'; var_dump( $prodEnables, $prodQtys, $request ); die();

        try {
            $choicesSaved = Mage::getModel('jafeaturedprods/products')->addFeaturedProducts($main_member_id, $selectedProducts);
            if(!$choicesSaved) {
                $error = true;
                $errorMessage['selected_products'] = "Something went wrong while saving your choices. Please try again.";
            } else {
                $successMessage['selected_products'] = "Choices are successfully saved.";
            }
        }
        catch (Exception $e) {
            //echo $e->getMessage();
        }

    }

}



try {

    $featuredData = Mage::getModel('jafeaturedprods/categories');
    $cats = $featuredData->getFeaturedCategoriesWithProductFilter();

    //if(  ) {}
} catch (Exception $e) {
    //echo $e->getMessage();
}
//echo '<pre>'; var_dump( $cats ); die();

?>

    <div role="main" class="content-body">
        <header class="page-header">
            <h2><?php echo $page_name; ?></h2>

            <div class="right-wrapper pull-right">
                <ol class="breadcrumbs">
                    <li>
                        <a href="<?php echo base_admin_url(); ?>">
                            <i class="fa fa-home"></i>
                        </a>
                    </li>
                    <li><span><?php echo $page_name; ?></span></li>
                </ol>


                <a class="sidebar-right-toggle"></a>
            </div>
        </header>

        <div class="row ">
            <?php if(!empty($successMessage) || !empty($errorMessage)): ?>

                <div class="row">
                    <?php if(!empty($successMessage)): ?>
                        <div class="col-sm-10 centering">
                            <div class="alert alert-success">
                                <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
                                <?php if(is_array($successMessage)) {
                                    echo "<ul style='list-style: none'><li>".implode('</li><li>', $successMessage) . "</li></ul>";
                                } else {
                                    echo $successMessage;
                                }
                                ?>
                            </div>
                        </div>
                    <?php endif; ?>
                    <?php if(!empty($errorMessage)): ?>
                        <div class="col-sm-10 centering">
                            <div class="alert alert-danger">
                                <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
                                <?php if(is_array($errorMessage)) {
                                    echo "<ul style='list-style: none'><li>".implode('</li><li>', $errorMessage) . "</li></ul>";
                                } else {
                                    echo $errorMessage;
                                }
                                ?>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>

            <?php endif; ?>

            <script src="//cdnjs.cloudflare.com/ajax/libs/vue/2.4.2/vue.min.js"></script>
            <script src="//cdnjs.cloudflare.com/ajax/libs/axios/0.16.2/axios.min.js"></script>

            <form  id="featured_products" action="<?php echo $action_page_url; ?>" method="post" class="">
                <div class="row">
                    <div class="col-sm-10">
                        <div id="categories" class="categories">
                            <div class="clearfix"></div>
                            <div class="row">
                                <div class="category_wrapper col-sm-6" v-for="cat in categories" v-bind:data-catid="cat.id" v-if="cat.id > 0">
                                    <div class="category">
                                        <div class="category-details">
                                            <div class="block-title">
                                                <h3>{{ cat.name }}</h3>
                                            </div>
                                        </div>
                                        <div class="clearfix"></div>
                                        <div class="col-xs-12">
                                            <div class="category-products" v-bind:id="getClass('category-products-', cat.id)">
                                                <div class="loader">
                                                    <div class="loader_icon"><img src="<?php echo base_shop_url(); ?>/images/icons/ajax-loader_lg.gif" class=""/></div>
                                                </div>
                                                <div id="product-list">
                                                    <div class="">
                                                        <div class="product_wrapper col-sm-4" v-for="product in products[cat.id]" v-if="product.id > 0" @click="productSelectHandle">
                                                            <div class="product" v-bind:class="{ selected: product.selected }" v-bind:data-prod-id="product.id" v-bind:title="lowerCase(product.title)">
                                                                <div  class="product_image_wrapper">
                                                                    <img class="product_image img-responsive" v-bind:src="product.small_image" width="100" height="100" v-bind:alt="product.small_image_title" />
                                                                </div>
                                                                <div class="product_details">
                                                                    <h3 class="product_name ellipsis">{{ lowerCase(product.title) }}</h3>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="clearfix"></div>
                        </div>
                        <div class="clearfix"></div>
                    </div>
                    <div class="col-sm-2">
                        <div id="selected_products" class="selected_products">
                            <div class="block-title">
                                <h3>Selected Products</h3>
                            </div>
                            <div class="clearfix"></div>
                            <div class="panel-heading">
                                <h5 class="text-center"><span class="selectedCount">{{ countText(selectedProducts) }}</span> out of <?php echo ($maxSelectableProducts); ?> selected</h5>
                                <div class="clearfix"></div>
                            </div>
                            <div class="products_wrapper">
                                <div class="loader">
                                    <div class="loader_icon"><img src="<?php echo base_shop_url(); ?>/images/icons/ajax-loader_lg.gif" class=""/></div>
                                </div>
                                <div class="message hide">
                                    <p>You haven't selected any products to feature them. Please select maximum 12 products from left side.</p>
                                </div>
                                <div class="products hide">
                                    <div class="product_wrapper col-xs-12" v-for="product in selectedProducts" v-if="product.id" @click="selectProductsProductSelectHandle">
                                        <div class="product text-center" v-bind:data-prod-id="product.id">
                                            <div class="wrapper">
                                                <div  class="product_image_wrapper">
                                                    <img class="product_image img-responsive" v-bind:src="product.small_image" width="100" height="100" v-bind:alt="product.small_image_title" />
                                                </div>
                                                <div class="product_details">
                                                    <input type="hidden" name="selected_products[]" v-bind:value="product.id" />
                                                    <h3 class="product_name ellipsis" v-bind:title="lowerCase(product.title)">{{ truncate(lowerCase(product.title), 15) }}</h3>
                                                </div>
                                                <div class="unselect_button_wrapper">
                                                    <i class="unselect_button fa fa-times-circle-o" aria-hidden="true"></i>
                                                    <!--<input type="button" class="btn btn-danger full-width unselect_button" value="Remove" />-->
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="clearfix"></div>
                                </div>
                            </div>
                            <div class="clearfix"></div>
                            <div class="panel-footer hide">
                                <div class="buttons_wrapper col-xs-12">
                                    <input type="submit" name="submit" class="btn btn-success full-width" value="Save Choices" />
                                </div>
                                <div class="clearfix"></div>
                            </div>
                        </div>
                        <div class="clearfix"></div>
                    </div>
                </div>
                <div class="form_extra"></div>
                <div class="clearfix"></div>

            </form>

            <script type="text/javascript">
                var prodQCounter = 0, totalAjaxCalls=0, clonedTotalAjaxCalls=0, catIds = {};
                jQuery(document).ready(function($){

                    var productAjaxurl = '<?php echo base_shop_url(); ?>featprods/index/ajaxLoadProducts/';
                    var categoryAjaxurl = '<?php echo base_shop_url(); ?>quick-order/index/ajaxLoadCategories/';
                    var selectedAjaxurl = '<?php echo base_shop_url(); ?>featprods/index/products/';

                    // register the grid component
                    var featuredProductsApp = new Vue({
                        el: '#featured_products',
                        data: {
                            products: {},
                            productsRaw: {},
                            productsRawCats: {},
                            categories: <?php echo json_encode($cats); ?>,
                            //categories: {0:{id:0, name:'', products: {}}},
                            //categories: [],
                            prodQCounter: 0,
                            prodSelectCounter: 0,
                            totalAjaxCalls: 0,
                            selectedProductsCollection: <?php echo ($choicesSaved ? json_encode((object) array()) : json_encode((object)$selectedProducts) ); ?>,
                            selectedProducts: {},
                            catIds: {}
                        },
                        methods: {
                            onSubmit: function(e) {
                                var form = e[0] ? e[0] :  e.srcElement;
                                console.log(form);
                            },
                            makeCategoriesAjaxCall: function() {

                                if( Object.keys(this.selectedProductsCollection).length ) {
                                    for(var i in this.selectedProductsCollection) {
                                        this.selectedProductsCollection[this.selectedProductsCollection[i]] = this.selectedProductsCollection[i];
                                    }
                                }

                                this.makeProductsAjaxCalls();
                                /*var that = this;
                                jQuery.ajax({
                                    type: 'POST',
                                    url: categoryAjaxurl,
                                    success: function (returnData) {
                                        //console.log(returnData);
                                        that.loadCategories(returnData);
                                    }
                                });*/
                            },
                            makeProductsAjaxCalls: function() {
                                if(this.categories) {
                                    for(var i in this.categories) {
                                        if( !this.catIds[this.categories[i].id] ) {
                                            this.catIds[this.categories[i].id] = this.categories[i].id;
                                            totalAjaxCalls++;
                                        }
                                    }
                                }
                                clonedTotalAjaxCalls = totalAjaxCalls;

                                if(totalAjaxCalls) {
                                    this.makeProductCall(this.catIds[ Object.keys(this.catIds)[0] ]);
                                }
                            },
                            makeSelectedProductsAjaxCalls: function() {
                                var that = this;
                                jQuery.ajax({
                                    type: 'POST',
                                    url: selectedAjaxurl,
                                    data: { member_id: <?php echo $main_member_id; ?> },
                                    success: function(returnData) {
                                        if(returnData) {
                                            if( Object.keys(returnData).length > 0 ) {

                                                jQuery('#selected_products .loader').removeClass('hide');
                                                jQuery('#selected_products .products').removeClass('hide');
                                                jQuery('#selected_products .panel-footer').removeClass('hide');
                                                jQuery('#selected_products .message').addClass('hide');

                                                for(var i in returnData) {
                                                    that.selectedProductsCollection[returnData[i]] = returnData[i];
                                                }
                                            } else {
                                                jQuery('#selected_products .loader').addClass('hide');
                                                jQuery('#selected_products .products').addClass('hide');
                                                jQuery('#selected_products .panel-footer').addClass('hide');
                                                jQuery('#selected_products .message').removeClass('hide');
                                            }
                                        }
                                        that.makeProductsAjaxCalls();
                                    }
                                });
                            },
                            makeProductCall: function(catId) {
                                var that = this;
                                jQuery.ajax({
                                    type: 'POST',
                                    url: productAjaxurl,
                                    data: { catid: catId, cleanMode:1 },
                                    //async: false,
                                    success: function(returnData) {
                                        //console.log(returnData);
                                        that.loadProducts(catId, returnData);
                                        jQuery('#category-products-'+catId+' .loader').hide();

                                        delete that.catIds[ catId ];

                                        if(Object.keys(that.catIds).length) {
                                            that.makeProductCall(Object.keys(that.catIds)[0]);
                                        }
                                    }
                                });
                            },
                            loadCategories: function(categories) {
                                if(categories) {
                                    for (var i in categories) {
                                        if (categories[i].id) {
                                            var category = categories[i];
                                            this.categories.push(category);
                                        }
                                    }

                                    //this.makeProductsAjaxCalls();
                                }
                            },
                            loadProducts: function(catId, prods) {
                                if(catId) {
                                    if (!this.products[catId]) {
                                        this.$set(this.products, catId, [])
                                    }
                                    for (var i in prods) {
                                        if (prods[i].id && !this.productsRaw[prods[i].id] )
                                        {
                                            prods[i]['selected'] = 0;

                                            this.productsRawCats[prods[i].id] = {};
                                            this.productsRawCats[prods[i].id]['categoryId'] = catId;
                                            this.productsRawCats[prods[i].id]['counter'] = this.prodQCounter;

                                            if( this.selectedProductsCollection[ prods[i].id ] ) {
                                                prods[i]['selected'] = 1;
                                                this.$set(this.selectedProducts, prods[i].id, prods[i]);
                                                this.productsRawCats[prods[i].id]['selectedCounter'] = this.prodSelectCounter;

                                                this.prodSelectCounter++;

                                                delete this.selectedProductsCollection[prods[i].id];

                                                jQuery('#selected_products .loader').addClass('hide');
                                                jQuery('#selected_products .message').addClass('hide').removeClass('show');
                                                jQuery('#selected_products .products').removeClass('hide');
                                                jQuery('#selected_products .panel-footer').removeClass('hide');
                                            }

                                            this.products[catId].push(prods[i]);
                                            this.productsRaw[prods[i].id] = prods[i];

                                            this.prodQCounter++;
                                            //jQuery('.counting_text .total').html( this.prodQCounter );

                                            if( !this.selectedProductsCollection.length) {
                                                jQuery('#selected_products .loader').hide();
                                            }
                                        }
                                    }
                                }
                            },
                            lowerCase: function(text) {
                                return text.toLowerCase();
                            },
                            getClass: function(text, id) {
                                return text + id;
                            },
                            selectProductsProductSelectHandle: function(e) {
                                var element = e[0] ? e[0] :  e.target;
                                var prodElem = ( $(element).hasClass('product') ? $(element) : $(element).parents('.product') )

                                var prodId = $(prodElem).attr('data-prod-id');

                                if(prodId) {
                                    //console.log(prodId, prodElem);
                                    this.removeProductFromSelection(prodId);
                                }
                            },
                            productSelectHandle: function(e) {
                                //console.log(e);
                                var element = e[0] ? e[0] :  e.target;
                                var prodElem = ( $(element).hasClass('product') ? $(element) : $(element).parents('.product') )

                                var prodId = $(prodElem).attr('data-prod-id');

                                if(prodId) {

                                    if(this.productsRaw[prodId].selected === 1) {
                                        this.removeProductFromSelection(prodId);
                                    } else {
                                        this.addProductToSelection(prodId);
                                    }

                                    jQuery('#selected_products .loader').removeClass('hide');
                                    jQuery('#selected_products .products').removeClass('hide');
                                    jQuery('#selected_products .panel-footer').removeClass('hide');
                                    jQuery('#selected_products .message').addClass('hide');

                                }
                            },
                            truncate: function( string, n, useWordBoundary ){
                                if (string.length <= n) { return string; }
                                var subString = string.substr(0, n-1);
                                return (useWordBoundary
                                    ? subString.substr(0, subString.lastIndexOf(' '))
                                    : subString) + "...";
                            },
                            countText: function(object) {
                                return String( Object.keys(object).length );
                            },
                            removeProductFromSelection: function (productId) {
                                if(productId) {
                                    this.productsRaw[productId].selected = 0;

                                    if( this.selectedProducts[productId] ) {
                                        //this.selectedProducts.splice(this.productsRawCats[productId]['selectedCounter'], 1);
                                        delete this.selectedProducts[productId];
                                        this.prodSelectCounter--;

                                        if(Object.keys(this.selectedProducts).length < 1) {
                                            jQuery('#selected_products .products').addClass('hide');
                                            jQuery('#selected_products .panel-footer').addClass('hide');
                                            jQuery('#selected_products .message').removeClass('hide').addClass('show');
                                        }
                                    }

                                    return 0;
                                }
                            },
                            addProductToSelection: function (productId) {
                                if(productId) {

                                    if (Object.keys(this.selectedProducts).length <= '<?php echo ($maxSelectableProducts-1); ?>' )
                                    {
                                        this.productsRaw[productId].selected = 1;
                                        this.$set(this.selectedProducts, productId, this.productsRaw[productId]);
                                        this.prodSelectCounter++;

                                        jQuery('#selected_products .products').removeClass('hide');
                                        jQuery('#selected_products .panel-footer').removeClass('hide');
                                        jQuery('#selected_products .message').addClass('hide').removeClass('show');

                                        return 1; // Product Selected
                                    } else {
                                        alert('You have already reached your maximum <?php echo ($maxSelectableProducts); ?> Products selection. Please remove an item to select this one.')
                                    }
                                }
                            },
                        }
                    });

                    <?php echo ( ($choicesSaved) ? 'featuredProductsApp.makeSelectedProductsAjaxCalls();' : 'featuredProductsApp.makeCategoriesAjaxCall();' ) ; ?>

                    /*// Auto load content based on scrollbar position near bottom.
                    $(window).scroll(function () {
                        if ($(window).scrollTop() + $(window).height() > $(document).height() - 350) {
                            eventListApp.loadEvents();
                        }
                    });*/
                });
            </script>

        </div>
    </div>


<?php
require_once("templates/footer.php");