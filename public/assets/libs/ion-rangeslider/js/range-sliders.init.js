$(document).ready(function () {
    $("#property_size_range").ionRangeSlider({
        skin: "square",
        type: "double",
        grid: !0,
        min: 1,
        max: 1e4,
        from: 1,
        to: 10000,
        prefix: "",
    }),
    $("#price_range").ionRangeSlider({
        skin: "square",
        type: "double",
        grid: !0,
        min: 1,
        max: 1e8,
        from: 1,
        to: 100000000,
        prefix: "",
    });
});
