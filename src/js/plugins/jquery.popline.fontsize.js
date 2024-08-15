
;(function($) {
 
  $.popline.addButton({
    justify: {
      iconClass: "fas fa-text-height",
      mode: "edit",
      buttons: {
          
        un: {
          text: "12",
          action: function(event) {
            document.execCommand('fontSize', false, "1");
          }
        },
          
        deux: {
          text: "16",
          action: function(event) {
            document.execCommand('fontSize', false, "2");
          }
        },

        trois: {
          text: "18",
          action: function(event) {
            document.execCommand('fontSize', false, "3");
          }
        },

        quatre: {
          text: "20",
          action: function(event) {
            document.execCommand('fontSize', false, "4");
          }
        },

        cinq: {
          text: "24",
          action: function(event) {
            document.execCommand('fontSize', false, "5");
          }
        },

        six: {
          text: "36",
          action: function(event) {
            document.execCommand('fontSize', false, "6");
          }
        },
        
        sept: {
          text: "48",
          action: function(event) {
            document.execCommand('fontSize', false, "7");
          }
        }
      }
    }
  });
})(jQuery);
