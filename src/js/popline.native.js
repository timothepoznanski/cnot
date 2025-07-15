/*
  popline.native.js - Version native JS de Popline (sans jQuery)
  Basé sur jquery.popline.js 1.0.0
  (c) 2025 migration par GitHub Copilot
*/

class Popline {
  static instances = [];
  static current = null;
  static defaults = {
    zIndex: 9999,
    mode: "edit",
    enable: null,
    disable: null,
    position: "fixed",
    keepSlientWhenBlankSelected: true
  };

  constructor(target, options = {}) {
    this.settings = Object.assign({}, Popline.defaults, options);
    this.setPosition(this.settings.position);
    this.target = target;
    this.beforeShowCallbacks = [];
    this.afterHideCallbacks = [];
    this.init();
    Popline.instances.push(this);
  }

  setPosition(position) {
    this.settings.position = position === "relative" ? "relative" : "fixed";
  }

  init() {
    // Crée la barre d'outils
    this.bar = document.createElement('ul');
    this.bar.className = 'popline';
    this.bar.style.zIndex = this.settings.zIndex;
    this.bar.style.position = 'absolute';
    this.bar.style.display = 'none';
    document.body.appendChild(this.bar);
    this.target.popline = this;
    // Ajout de tous les boutons principaux
    const buttons = [
      { name: 'bold', icon: '<i class="fas fa-bold" title="Bold"></i>', action: () => document.execCommand('bold') },
      { name: 'italic', icon: '<i class="fas fa-italic" title="Italic"></i>', action: () => document.execCommand('italic') },
      { name: 'underline', icon: '<i class="fas fa-underline" title="Underline"></i>', action: () => document.execCommand('underline') },
      { name: 'strike', icon: '<i class="fas fa-strikethrough" title="Strikethrough"></i>', action: () => document.execCommand('strikeThrough') },
      { name: 'link', icon: '<i class="fas fa-link" title="Link"></i>', action: () => {
          const url = prompt('Enter link URL:', 'https://');
          if (url) document.execCommand('createLink', false, url);
        }
      },
      { name: 'unlink', icon: '<i class="fas fa-unlink" title="Remove link"></i>', action: () => document.execCommand('unlink') },
      { name: 'color', icon: '<i class="fas fa-palette" style="color:#ff2222;" title="Text color"></i>', action: () => {
          const color = prompt('Text color (name or #hex):', '#ff2222');
          if (color) document.execCommand('foreColor', false, color);
        }
      },
      { name: 'bgcolor', icon: '<i class="fas fa-fill-drip" style="color:#ffe066;" title="Background color"></i>', action: () => {
          const color = prompt('Background color (name or #hex):', '#ffe066');
          if (color) document.execCommand('hiliteColor', false, color);
        }
      },
      { name: 'ul', icon: '<i class="fas fa-list-ul" title="Bullet list"></i>', action: () => document.execCommand('insertUnorderedList') },
      { name: 'ol', icon: '<i class="fas fa-list-ol" title="Numbered list"></i>', action: () => document.execCommand('insertOrderedList') },
      { name: 'fontsize', icon: '<i class="fas fa-text-height" title="Font size"></i>', action: () => {
          const size = prompt('Font size (1-7):', '3');
          if (size) document.execCommand('fontSize', false, size);
        }
      },
      { name: 'codeblock', icon: '<i class="fas fa-code" title="Code block"></i>', action: () => {
          document.execCommand('formatBlock', false, 'pre');
        }
      },
      { name: 'removeColor', icon: '<i class="fas fa-tint-slash" title="Remove color/highlight"></i>', action: () => {
          document.execCommand('removeFormat');
          document.execCommand('foreColor', false, '');
          document.execCommand('hiliteColor', false, '');
        }
      },
      { name: 'removeFormat', icon: '<i class="fas fa-eraser" title="Remove format"></i>', action: () => document.execCommand('removeFormat') }
    ];
    buttons.forEach(btn => this.addButton(btn));
    // Gestion des événements natifs
    this.target.addEventListener('mouseup', (e) => this.handleSelection(e));
    this.target.addEventListener('keyup', (e) => this.handleSelection(e));
    document.addEventListener('mousedown', (e) => {
      if (!this.bar.contains(e.target)) this.hide();
    });
  }

  addButton({name, icon, action}) {
    const li = document.createElement('li');
    li.className = `popline-button popline-${name}-button`;
    const btn = document.createElement('span');
    btn.className = 'popline-btn';
    btn.innerHTML = icon;
    btn.addEventListener('mousedown', e => e.preventDefault());
    btn.addEventListener('click', e => {
      action();
      this.hide();
      e.stopPropagation();
    });
    li.appendChild(btn);
    this.bar.appendChild(li);
  }

  handleSelection(e) {
    const selection = window.getSelection();
    if (selection && selection.toString().length > 0) {
      const rect = selection.getRangeAt(0).getBoundingClientRect();
      this.show({
        top: window.scrollY + rect.top - this.bar.offsetHeight - 10,
        left: window.scrollX + rect.left + rect.width / 2 - this.bar.offsetWidth / 2
      });
    } else {
      this.hide();
    }
  }

  show(options) {
    // Affiche la barre et ajuste sa position pour rester dans la fenêtre
    this.bar.style.display = 'block';
    // Calculer la position souhaitée
    let top = options.top;
    let left = options.left;
    const barRect = this.bar.getBoundingClientRect();
    const winWidth = window.innerWidth;
    const winHeight = window.innerHeight;
    // Ajuster à droite si dépassement
    if (left < 0) left = 8;
    if (left + barRect.width > winWidth) left = winWidth - barRect.width - 8;
    // Ajuster en haut si dépassement
    if (top < 0) top = 8;
    if (top + barRect.height > winHeight) top = winHeight - barRect.height - 8;
    this.bar.style.top = top + 'px';
    this.bar.style.left = left + 'px';
  }

  hide() {
    this.bar.style.display = 'none';
  }

  destroy() {
    this.bar.remove();
    delete this.target.popline;
  }
}

// Initialisation globale
Popline.init = function(selector, options) {
  const elements = typeof selector === 'string' ? document.querySelectorAll(selector) : [selector];
  elements.forEach(el => new Popline(el, options));
};

window.Popline = Popline;
