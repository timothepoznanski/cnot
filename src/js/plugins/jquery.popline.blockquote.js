;(function($) {

  var quoteUtils = function() {
    return {
      quote: function() {
        var selection = window.getSelection();
        if (selection.rangeCount > 0) {
          var range = selection.getRangeAt(0);
          var span = document.createElement("span");

          span.style.fontFamily = "Consolas, monospace";
          span.style.backgroundColor = "#f9f9f9";
          span.style.fontSize = "12px";
          span.style.border = "1px solid #ddd";
          span.style.padding = "10px";
          span.style.display = "block";
          span.style.borderRadius = "4px";
          span.style.whiteSpace = "pre-wrap";

          span.textContent = selection.toString();
          
          range.deleteContents();
          range.insertNode(span);
        }
      },
      unquote: function() {
        var selection = window.getSelection();
        if (selection.rangeCount > 0) {
          var range = selection.getRangeAt(0);
          var node = range.commonAncestorContainer;

          if (node.nodeType === Node.TEXT_NODE) {
            node = node.parentNode;
          }
          if (node.tagName === "SPAN" && node.style.fontFamily === "Consolas, monospace") {
            node.style.fontFamily = "";
            node.style.backgroundColor = "";
            node.style.border = "";
            node.style.padding = "";
            node.style.display = "";
            node.style.borderRadius = "";
          }
        }
      }
    };
  };

  $.popline.addButton({
    blockquote: {
      iconClass: "fas fa-code",
      mode: "edit",
      action: function(event, popline) {
        var focusNode = $.popline.utils.selection().focusNode();
        var isStyled = focusNode && focusNode.parentNode && focusNode.parentNode.tagName === "SPAN" && focusNode.parentNode.style.fontFamily === "Consolas, monospace";
        if (isStyled) {
          quoteUtils().unquote();
        } else {
          quoteUtils().quote();
        }
      }
    }
  });

})(jQuery);
