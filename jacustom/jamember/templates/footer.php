                <!-- end: page -->

            </div>
        </section>

        <!-- Vendor -->
        <link href="<?php echo base_theme_assets_url(); ?>/vendor/pnotify/pnotify.custom.css">

        <script src="<?php echo base_theme_assets_url(); ?>/vendor/jquery-browser-mobile/jquery.browser.mobile.js"></script>
        <script src="<?php echo base_theme_assets_url(); ?>/vendor/bootstrap/js/bootstrap.js"></script>
        <script src="<?php echo base_theme_assets_url(); ?>/vendor/nanoscroller/nanoscroller.js"></script>
        <script src="<?php echo base_theme_assets_url(); ?>/vendor/bootstrap-datepicker/js/bootstrap-datepicker.js"></script>
        <script src="<?php echo base_theme_assets_url(); ?>/vendor/magnific-popup/jquery.magnific-popup.js"></script>
        <script src="<?php echo base_theme_assets_url(); ?>/vendor/jquery-placeholder/jquery-placeholder.js"></script>

        <!-- Specific Page Vendor -->
        <script src="<?php echo base_theme_assets_url(); ?>/vendor/jquery-ui/jquery-ui.js"></script>
        <script src="<?php echo base_theme_assets_url(); ?>/vendor/jqueryui-touch-punch/jqueryui-touch-punch.js"></script>
        <script src="<?php echo base_theme_assets_url(); ?>/vendor/jquery-appear/jquery-appear.js"></script>
        <script src="<?php echo base_theme_assets_url(); ?>/vendor/bootstrap-multiselect/bootstrap-multiselect.js"></script>
        <script src="<?php echo base_theme_assets_url(); ?>/vendor/jquery.easy-pie-chart/jquery.easy-pie-chart.js"></script>
        <script src="<?php echo base_theme_assets_url(); ?>/vendor/flot/jquery.flot.js"></script>
        <script src="<?php echo base_theme_assets_url(); ?>/vendor/flot.tooltip/flot.tooltip.js"></script>
        <script src="<?php echo base_theme_assets_url(); ?>/vendor/flot/jquery.flot.pie.js"></script>
        <script src="<?php echo base_theme_assets_url(); ?>/vendor/flot/jquery.flot.categories.js"></script>
        <script src="<?php echo base_theme_assets_url(); ?>/vendor/flot/jquery.flot.resize.js"></script>
        <script src="<?php echo base_theme_assets_url(); ?>/vendor/jquery-sparkline/jquery-sparkline.js"></script>
        <script src="<?php echo base_theme_assets_url(); ?>/vendor/raphael/raphael.js"></script>
        <script src="<?php echo base_theme_assets_url(); ?>/vendor/morris.js/morris.js"></script>
        <script src="<?php echo base_theme_assets_url(); ?>/vendor/gauge/gauge.js"></script>
        <script src="<?php echo base_theme_assets_url(); ?>/vendor/snap.svg/snap.svg.js"></script>
        <script src="<?php echo base_theme_assets_url(); ?>/vendor/liquid-meter/liquid.meter.js"></script>
        <script src="<?php echo base_theme_assets_url(); ?>/vendor/jqvmap/jquery.vmap.js"></script>
        <script src="<?php echo base_theme_assets_url(); ?>/vendor/jqvmap/data/jquery.vmap.sampledata.js"></script>
        <script src="<?php echo base_theme_assets_url(); ?>/vendor/jqvmap/maps/jquery.vmap.world.js"></script>
        <script src="<?php echo base_theme_assets_url(); ?>/vendor/jqvmap/maps/continents/jquery.vmap.africa.js"></script>
        <script src="<?php echo base_theme_assets_url(); ?>/vendor/jqvmap/maps/continents/jquery.vmap.asia.js"></script>
        <script src="<?php echo base_theme_assets_url(); ?>/vendor/jqvmap/maps/continents/jquery.vmap.australia.js"></script>
        <script src="<?php echo base_theme_assets_url(); ?>/vendor/jqvmap/maps/continents/jquery.vmap.europe.js"></script>
        <script src="<?php echo base_theme_assets_url(); ?>/vendor/jqvmap/maps/continents/jquery.vmap.north-america.js"></script>
        <script src="<?php echo base_theme_assets_url(); ?>/vendor/jqvmap/maps/continents/jquery.vmap.south-america.js"></script>
        <script src="<?php echo base_theme_assets_url(); ?>/vendor/pnotify/pnotify.custom.js"></script>
        <script src="<?php echo base_theme_assets_url(); ?>/vendor/jquery-validation/jquery.validate.js"></script>
        <script src="<?php echo base_theme_assets_url(); ?>/vendor/ios7-switch/ios7-switch.js"></script>
        <script src="<?php echo base_theme_assets_url(); ?>/vendor/bootstrap-fileupload/bootstrap-fileupload.min.js"></script>

        <!-- Theme Base, Components and Settings -->
        <script src="<?php echo base_theme_assets_url(); ?>/javascripts/theme.js"></script>
        <script src="<?php echo base_theme_assets_url(); ?>/javascripts/ui-elements/examples.modals.js"></script>
        <!--<script src="<?php /*echo base_url(); */?>/theme_assets/javascripts/forms/examples.validation.js"></script>-->

        <!-- Theme Custom -->
        <script src="<?php echo base_theme_assets_url(); ?>/javascripts/theme.custom.js"></script>

        <!-- Theme Initialization Files -->
        <script src="<?php echo base_theme_assets_url(); ?>/javascripts/theme.init.js"></script>

        <script>
            $('#ec_custom_login_btn').removeAttr("disabled");
            jQuery(document).on("click", "#ec_custom_login_btn", function() {
                var ec_custom_login_key = jQuery("#ec_custom_login_key").val();
                window.location.href = "https://www.johnamico.com/jamember/contact_info2__ec_login_as_m.php?key="+ec_custom_login_key;
            });
        </script>

        <!-- Examples -->
        <!--<script src="<?php /*echo base_url(); */?>/theme_assets/javascripts/dashboard/examples.dashboard.js"></script>-->
    </body>
</html>
