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
          span.style.position = "relative"; // Make relative for button positioning

          // Set text content of span
          span.textContent = selection.toString();

          // Insert the formatted span in place of the selection
          range.deleteContents();
          range.insertNode(span);

          // Create a blank line (br) element after the span
          var br = document.createElement("br");
          
          // Insert the <br> after the span
          span.parentNode.insertBefore(br, span.nextSibling);

          // Add the copy button
          addCopyButton(span);

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

            // Remove the copy button when unquoting
            var copyButton = node.querySelector(".copy-button");
            if (copyButton) {
              copyButton.remove();
            }

            // After resetting, ensure the span is not an empty tag and remove it if it's empty
            if (node.textContent.trim() === "") {
              node.remove();
            }
          }
        }
      }
    };
  };

  // Function to add the copy button to the code block
  function addCopyButton(span) {
    var copyButton = document.createElement("button");
    copyButton.textContent = "Copy";
    copyButton.classList.add("copy-button"); // Add a class for easy selection
    copyButton.style.position = "absolute";
    copyButton.style.top = "8px";
    copyButton.style.right = "8px";
    copyButton.style.fontSize = "12px";
    copyButton.style.padding = "4px 8px";
    copyButton.style.cursor = "pointer";
    copyButton.style.border = "1px solid #ccc";
    copyButton.style.borderRadius = "4px";
    copyButton.style.backgroundColor = "#fff";
    copyButton.style.color = "#333";

    // Prevent the cursor from appearing inside the button
    copyButton.style.userSelect = "none";  // Prevent text selection
    copyButton.style.outline = "none";     // Remove focus outline
    copyButton.style.pointerEvents = "auto"; // Ensure button is clickable

    span.appendChild(copyButton);

    // Add the copy functionality using Clipboard API
    copyButton.addEventListener("click", function() {
      // Get the text content and remove "Copy" using replace
      var textToCopy = span.textContent.replace("Copy", "");

      if (navigator.clipboard) {
        // Use Clipboard API to copy the modified text
        navigator.clipboard.writeText(textToCopy).then(function() {
          // Optionally, give feedback to the user, e.g., change button text or style
          copyButton.textContent = "Copied!";
          setTimeout(function() {
            copyButton.textContent = "Copy"; // Reset the button text after a delay
          }, 1500);
        }).catch(function(err) {
          console.error("Clipboard write failed", err);
        });
      }
    });
  }

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
