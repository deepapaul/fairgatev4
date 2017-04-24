/*
 ================================================================================================ 
 * Custom Plugin for extend gallery plugin
 * Function - FgGallery - to configure items to display or not 
 * Author : Sebin
 ================================================================================================ 
 */

/*
 ================================================================================================ 
 *
 *   FgPageTitlebar Used to configure the items in page title bar
 *         
 ================================================================================================ 
 */
FgGallery = function () {
    var settings;
    var $object;
    var fgUniteGalleryObj;

    var defaultSettings = {
        selector: '#gallery',
        /*unite gallery plugin confg begins here---*/
        tiles_type: 'justified', //grid / justified - tiles layout type
        tiles_col_width: 250, //column width - exact or base according the settings
        tiles_align: "center", //align of the tiles in the space
        tiles_exact_width: false, //exact width of column - disables the min and max columns
        tiles_space_between_cols: 3, //space between images
        tiles_space_between_cols_mobile: 3, //space between cols for mobile type
        tiles_include_padding: true, //include padding at the sides of the columns, equal to current space between cols
        tiles_min_columns: 2, //min columns
        tiles_max_columns: 0, //max columns (0 for unlimited)
        tiles_keep_order: false, //keep order - slower algorytm
        tiles_set_initial_height: true, //set some estimated height before images show
        tiles_justified_row_height: 150, //base row height of the justified type
        tiles_justified_space_between: 3, //space between the tiles justified type
        tiles_nested_optimal_tile_width: 250, // tiles optimal width
        tiles_nested_col_width: null, // nested tiles column width
        tiles_nested_debug: false,
        tiles_enable_transition: true,
        /*unite gallery plugin confg ends here---*/


        initCompleteCallback: function ($object) {
        }
    };
    // extends the initial configuration on method init		
    var initSettings = function (options) {
        settings = $.extend(true, {}, defaultSettings, options);

        //  get theme file js(); 
        getThemeUrl();
        fgUniteGalleryObj = $(settings.selector).unitegallery({
            tiles_type: settings.tiles_type
        });
        settings.initCompleteCallback.call();
        
        return fgUniteGalleryObj;
    };


    /*
     ================================================================================================ 
     *  Function to get js library for current tiles theme
     ================================================================================================ 
     */

    var getThemeUrl = function () {

        //newscript for adding script depening on gallery theme
        var baseUrl = '../../../fgassets/global/js/unitegallery-master/package/unitegallery/themes/';
        var src = baseUrl + 'tiles/ug-theme-tiles.js';  //default theme file title justified
        var newScript = document.createElement("script");
        newScript.type = "text/javascript";

        if (settings.tiles_type == 'justified') {
            src = baseUrl + 'tiles/ug-theme-tiles.js';
            if(typeof UGTheme_tiles == 'function'){
                src = '#'
            }
        } else if (settings.tiles_type == 'grid') {
            src = baseUrl + 'tilesgrid/ug-theme-tilesgrid.js';
           // settings.tiles_type = 'justified';
        }
        
        if(src != '#'){
            newScript.setAttribute("src", src);
            $("body").append(newScript);
        }
    };

    /*
     ================================================================================================ 
     *  Public functions that can access from anywhere from the project 
     ================================================================================================ 
     */
    return {
        // initialize the page titlebar plugin
        init: function (options) {
            return initSettings(options);
        }
    };




}();


(function ($) {

}(jQuery));
