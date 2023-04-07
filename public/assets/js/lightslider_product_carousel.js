$(document).ready(function() {
    $("#lightSlider").lightSlider({
        autoWidth: false,
        loop: true,
        auto: true,
        pause: 3000,
        item: 4,
        pager: false,
        slideMove: 1,
        slideMargin: 5,
        responsive : [
            {
                breakpoint: 992,
                settings: {
                    item: 3,
                    slideMove: 1,
                    slideMargin: 4,
                }
            },
            {
                breakpoint: 768,
                settings: {
                    item: 2,
                    slideMove: 1,
                    slideMargin: 1,
                }
            },
            {
                breakpoint:420,
                settings: {
                    item: 1,
                    slideMove: 1
                }
            }
        ]
    });
});
