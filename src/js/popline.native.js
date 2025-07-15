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
      { name: 'bold', icon: '<b>B</b>', action: () => document.execCommand('bold') },
      { name: 'italic', icon: '<i>I</i>', action: () => document.execCommand('italic') },
      { name: 'underline', icon: '<u>U</u>', action: () => document.execCommand('underline') },
      { name: 'strike', icon: '<s>S</s>', action: () => document.execCommand('strikeThrough') },
      { name: 'link', icon: '<span style="text-decoration:underline;">🔗</span>', action: () => {
          const url = prompt('Entrez l’URL du lien :', 'https://');
          if (url) document.execCommand('createLink', false, url);
        }
      },
      { name: 'unlink', icon: '<span style="text-decoration:line-through;">⛔</span>', action: () => document.execCommand('unlink') },
      { name: 'color', icon: '<span style="color:#e74c3c;">A</span>', action: () => {
          const color = prompt('Couleur du texte (nom ou #hex) :', '#e74c3c');
          if (color) document.execCommand('foreColor', false, color);
        }
      },
      { name: 'bgcolor', icon: '<span style="background:#ffe066;">A</span>', action: () => {
          const color = prompt('Couleur de fond (nom ou #hex) :', '#ffe066');
          if (color) document.execCommand('hiliteColor', false, color);
        }
      },
      { name: 'ul', icon: '• Liste', action: () => document.execCommand('insertUnorderedList') },
      { name: 'ol', icon: '1. Liste', action: () => document.execCommand('insertOrderedList') },
      { name: 'fontsize', icon: 'A+', action: () => {
          const size = prompt('Taille de police (1-7) :', '3');
          if (size) document.execCommand('fontSize', false, size);
        }
      },
      // boutons d'alignement supprimés
      { name: 'removeFormat', icon: '⎚', action: () => document.execCommand('removeFormat') }
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
    this.bar.style.top = options.top + 'px';
    this.bar.style.left = options.left + 'px';
    this.bar.style.display = 'block';
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
