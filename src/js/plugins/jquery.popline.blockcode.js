(function($) {

  var quoteUtils = function() {
    return {
      quote: function() {
        var selection = window.getSelection();
        if (selection.rangeCount > 0) {
          var range = selection.getRangeAt(0);

          // Create the span for the code formatting
          var span = document.createElement("span");
          span.style.fontFamily = "Consolas, monospace";
          span.style.backgroundColor = "#F7F6F3";
          span.style.fontSize = "90%";
          span.style.border = "0px solid #ddd";
          span.style.padding = "34px 16px 32px 32px";
          span.style.display = "block";
          span.style.borderRadius = "4px";
          span.style.whiteSpace = "pre-wrap";
          span.style.minHeight = "1em";
          span.style.color = "rgb(55, 53, 47)";
 
          // Set text content of span
          span.textContent = selection.toString();

          // Insert the formatted span in place of the selection
          range.deleteContents();
          range.insertNode(span);

          // Create a blank line (br) element after the span
          // var br = document.createElement("br");
          
          // Insert the <br> after the span
          // span.parentNode.insertBefore(br, span.nextSibling);

          // Clear selection after transformation
          window.getSelection().removeAllRanges();
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
            // Reset all the inline styles
            node.style.fontFamily = "";
            node.style.backgroundColor = "";
            node.style.fontSize = "";  // Ensure font-size is reset to default
            node.style.border = "";
            node.style.padding = "";
            node.style.display = "";
            node.style.borderRadius = "";
            node.style.minHeight = "";
            node.style.color = "";

            // After resetting, ensure the span is not an empty tag and remove it if it's empty
            if (node.textContent.trim() === "") {
              node.remove();
            }
          }
        }
      }
    };
  };

  $.popline.addButton({
    blockcode: {
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
        // Hide the popline menu after the action
        popline.hide();
      }
    }
  });

})(jQuery);
