/*
  jquery.popline.justify.js 1.0.0

  Version: 1.0.0
  Updated: Sep 10th, 2014

  (c) 2014 by kenshin54
*/
;(function($) {

  var removeRedundantParagraphTag = function(popline, align) {
    if ($.popline.utils.browser.ie) {
      $paragraphs = popline.target.find("p[align=" + align + "]");
      $paragraphs.each(function(i, obj){
        if (obj.childNodes.length === 1 && obj.childNodes[0].nodeType === 3 && !/\S/.test(obj.childNodes[0].nodeValue)) {
          $(obj).remove();
        }
      })
    }
  }

  $.popline.addButton({
    justify: {
      iconClass: "fas fa-align-justify",
      mode: "edit",
      buttons: {
        justifyLeft: {
          iconClass: "fas fa-align-left",
          action: function(event, popline) {
            document.execCommand("JustifyLeft");
            removeRedundantParagraphTag(popline, "left");
          }
        },

        justifyCenter: {
          iconClass: "fas fa-align-center",
          action: function(event, popline) {
            document.execCommand("JustifyCenter");
            removeRedundantParagraphTag(popline, "center");
          }
        },

        justifyRight: {
          iconClass: "fas fa-align-right",
          action: function(event, popline) {
            document.execCommand("JustifyRight");
            removeRedundantParagraphTag(popline, "right");
          }
        },

        indent: {
          iconClass: "fas fa-indent",
          action: function(event) {
            document.execCommand("indent");
          }
        },

        outdent: {
          iconClass: "fas fa-dedent",
          action: function(event) {
            document.execCommand("outdent");
          }
        }
      }
    }
  });
})(jQuery);
