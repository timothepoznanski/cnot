/*
  jquery.popline.decoration.js 1.0.0

  Version: 1.0.0
  Updated: Sep 10th, 2014

  (c) 2014 by kenshin54
*/
;(function($) {

  $.popline.addButton({
    bold: {
      iconClass: "fas fa-bold",
      mode: "edit",
      action: function(event) {
        document.execCommand("bold");
      }
    },

    italic: {
      iconClass: "fas fa-italic",
      mode: "edit",
      action: function(event) {
        document.execCommand("italic");
      }
    },

    strikethrough: {
      iconClass: "fas fa-strikethrough",
      mode: "edit",
      action: function(event) {
        document.execCommand("strikethrough");
      }
    },

    underline: {
      iconClass: "fas fa-underline",
      mode: "edit",
      action: function(event) {
        document.execCommand("underline");
      }
    },
    
    replacebyhorizontal: {
      iconClass: "fas fa-minus",
      mode: "edit",
      action: function(event) {
        // document.execCommand('insertHorizontalRule');
        document.execCommand('insertHTML', false, "<br><hr><br>"); // This may not be supported by Internet Explorer.
      }
    },
    
    
    simplifytext: {
      iconClass: "fas fa-won-sign",
      mode: "edit",
      action: function(event) {
        document.execCommand('removeFormat');
      }
    },

menlo: {
  iconClass: "fas fa-code",
  mode: "edit",
  action: function(event) {
  document.execCommand('fontName', false, "menlo"); // The file is located in the fonts folder + add a css @font-face in style.css
  document.execCommand('ForeColor', false, "#323095");
  //document.execCommand('fontSize', false, "2");
  //document.execCommand('BackColor',false, "#fbfaf8");
  }
},

    roboto: {
      iconClass: "fas fa-font",
      mode: "edit",
      action: function(event) {
        document.execCommand('fontName', false, "Roboto");
      }
    }

  });
})(jQuery);
