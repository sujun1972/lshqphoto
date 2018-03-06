(function ($) {
    Drupal.behaviors.addLinkToColorboxCaption =  {
        attach: function(context, settings) {
            var imgTitle = $('#cboxTitle').text();
            var titleArray = imgTitle.split("|||");
            var url = $("a[title='"+imgTitle+"']").attr("url");
            $('#cboxTitle').html('<a href="'+url+'" target="_blank" title="查看作品详情">'+titleArray[0]+'</a>');
        }
    };
})(jQuery);
