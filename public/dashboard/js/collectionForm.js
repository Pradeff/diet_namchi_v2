(function ($){
    jQuery(document).ready(function (){

        var $itinerarydetailswapper = $('.js_itinerarydetails_wrapper');
        var $wrapper_homestay = $('.js-homestay-wrapper');
        var $trekkingdetailswapper = $('.js_trekkingdetails_wrapper');
        var $gallerydetailswapper = $('.js_gallerydetails_wrapper');

        /* Day-Wise Trekking */
        $gallerydetailswapper.on('click','.js_add_vgallery_itinerary_arr',function (e){
            e.preventDefault();
            var prototype = $gallerydetailswapper.data('prototype');
            var index = $gallerydetailswapper.data('index');
            var newForm = prototype;
            newForm = newForm.replace(/__name__/g, index);
            $gallerydetailswapper.data('index', index + 1);
            $(this).before(newForm);

        })
        $gallerydetailswapper.on('click', '.js_remove_vgallery_itinerary_arr', function(e) {
            e.preventDefault();
            $(this).closest('.js-form-item')
                .fadeOut()
                .remove();
        });


        /* Day-Wise Itinerary */
        $itinerarydetailswapper.on('click','.js_add_touritinerary_arr',function (e){
            e.preventDefault();
            var prototype = $itinerarydetailswapper.data('prototype');
            var index = $itinerarydetailswapper.data('index');
            var newForm = prototype;
            newForm = newForm.replace(/__name__/g, index);
            $itinerarydetailswapper.data('index', index + 1);
            $(this).before(newForm);

        })
        $itinerarydetailswapper.on('click', '.js_remove_touritinerary_arr', function(e) {
            e.preventDefault();
            $(this).closest('.js-form-item')
                .fadeOut()
                .remove();
        });

        // Homestay Images Collection
        $wrapper_homestay.on('click', '.js_homestay_image_remove', function (e) {
            e.preventDefault();
            $(this).closest('.js-form-item')
                .fadeOut()
                .remove();
        });
        $wrapper_homestay.on('click', '.js_homestay_image_add', function (e) {
            e.preventDefault();
            var prototype = $wrapper_homestay.data('prototype');
            var index = $wrapper_homestay.data('index');
            var newForm = prototype;
            newForm = newForm.replace(/__name__/g, index);
            $wrapper_homestay.data('index', index + 1);
            $(this).before(newForm);
        })

        /* Day-Wise Trekking */
        $trekkingdetailswapper.on('click','.js_add_trekitinerary_arr',function (e){
            e.preventDefault();
            var prototype = $trekkingdetailswapper.data('prototype');
            var index = $trekkingdetailswapper.data('index');
            var newForm = prototype;
            newForm = newForm.replace(/__name__/g, index);
            $trekkingdetailswapper.data('index', index + 1);
            $(this).before(newForm);

        })
        $trekkingdetailswapper.on('click', '.js_remove_trekitinerary_arr', function(e) {
            e.preventDefault();
            $(this).closest('.js-form-item')
                .fadeOut()
                .remove();
        });


    });
})(jQuery);
