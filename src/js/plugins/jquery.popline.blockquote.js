/*
  jquery.popline.blockquote.js 1.0.0

  Version: 1.0.0
  Updated: Sep 10th, 2014

  (c) 2014 by kenshin54
  
  TPO : I use the blockquote to have background color for code format
  
*/
;(function($) {

  var quoteUtils = function() {
    return {
        quote: function() {          
          document.execCommand('formatblock', false, 'BLOCKQUOTE');
          document.execCommand('fontName', false, "Inter");
        },
        unquote: function() {
            document.execCommand('formatblock', false, 'P');
            document.execCommand('removeFormat');
        }
    }
  }

  $.popline.addButton({
    blockquote: {
      iconClass: "fas fa-quote-left",
      mode: "edit",
      action: function(event, popline) {
        var focusNode = $.popline.utils.selection().focusNode();
        var node = $.popline.utils.findNodeWithTags(focusNode, 'BLOCKQUOTE');
        if (node) {
          quoteUtils().unquote();
        }else {
          quoteUtils().quote();
        }
      }
    }
  });
})(jQuery);