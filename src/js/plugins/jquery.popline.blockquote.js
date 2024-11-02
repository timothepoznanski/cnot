;(function($) {

  var quoteUtils = function() {
    return {
      quote: function() {
        var selection = window.getSelection();
        if (selection.rangeCount > 0) {
          var range = selection.getRangeAt(0);
          var span = document.createElement("span");

          // Appliquer les styles pour le formatage de code
          span.style.fontFamily = "Consolas, monospace";
          span.style.backgroundColor = "#f9f9f9"; // Gris très clair
          span.style.border = "1px solid #ddd";
          span.style.padding = "10px";
          span.style.display = "block";
          span.style.borderRadius = "4px";
          span.style.whiteSpace = "pre-wrap"; // Conserve les sauts de ligne et espaces

          // Insérer le texte sélectionné dans le <span>
          span.textContent = selection.toString();
          
          // Remplacer le texte sélectionné par le <span>
          range.deleteContents();
          range.insertNode(span);
        }
      },
      unquote: function() {
        var selection = window.getSelection();
        if (selection.rangeCount > 0) {
          var range = selection.getRangeAt(0);
          var node = range.commonAncestorContainer;

          // Vérifier si le noeud parent est un <span> avec les styles appliqués
          if (node.nodeType === Node.TEXT_NODE) {
            node = node.parentNode;
          }
          if (node.tagName === "SPAN" && node.style.fontFamily === "Consolas, monospace") {
            // Retirer les styles sans toucher au contenu ou structure
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
