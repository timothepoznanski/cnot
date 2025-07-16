/*
  popline.native.js - Version native JS de Popline (sans jQuery)
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
  static _selectionChangeHandler = null;
  static _selectionChangeTimeout = null;

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
          // Toggle red color: if all selection is red, remove only color; else, apply
          const sel = window.getSelection();
          if (sel.rangeCount > 0) {
            const range = sel.getRangeAt(0);
            let allRed = true;
            let hasText = false;
            const treeWalker = document.createTreeWalker(range.commonAncestorContainer, NodeFilter.SHOW_TEXT, {
              acceptNode: function(node) {
                if (!range.intersectsNode(node)) return NodeFilter.FILTER_REJECT;
                return NodeFilter.FILTER_ACCEPT;
              }
            });
            let node = treeWalker.currentNode;
            while(node) {
              if (node.nodeType === 3 && node.nodeValue.trim() !== '') {
                hasText = true;
                let parent = node.parentNode;
                let color = '';
                if (parent && parent.style && parent.style.color) color = parent.style.color.replace(/\s/g, '').toLowerCase();
                if (color !== '#ff2222' && color !== 'rgb(255,34,34)') allRed = false;
              }
              node = treeWalker.nextNode();
            }
            document.execCommand('styleWithCSS', false, true);
            if (hasText && allRed) {
              document.execCommand('foreColor', false, 'black');
            } else {
              document.execCommand('foreColor', false, '#ff2222');
            }
            document.execCommand('styleWithCSS', false, false);
          }
        }
      },
      { name: 'bgcolor', icon: '<i class="fas fa-fill-drip" style="color:#ffe066;" title="Background color"></i>', action: () => {
          // Toggle yellow highlight: if all selection is yellow, remove only highlight; else, apply
          const sel = window.getSelection();
          if (sel.rangeCount > 0) {
            const range = sel.getRangeAt(0);
            let allYellow = true;
            let hasText = false;
            const treeWalker = document.createTreeWalker(range.commonAncestorContainer, NodeFilter.SHOW_ELEMENT | NodeFilter.SHOW_TEXT, {
              acceptNode: function(node) {
                if (!range.intersectsNode(node)) return NodeFilter.FILTER_REJECT;
                return NodeFilter.FILTER_ACCEPT;
              }
            });
            let node = treeWalker.currentNode;
            while(node) {
              if (node.nodeType === 3 && node.nodeValue.trim() !== '') {
                hasText = true;
                let parent = node.parentNode;
                let bg = '';
                if (parent && parent.style && parent.style.backgroundColor) bg = parent.style.backgroundColor.replace(/\s/g, '').toLowerCase();
                if (bg !== '#ffe066' && bg !== 'rgb(255,224,102)') allYellow = false;
              }
              node = treeWalker.nextNode();
            }
            if (hasText && allYellow) {
              // On retire uniquement le surlignage (pas tout le format)
              document.execCommand('styleWithCSS', false, true);
              document.execCommand('hiliteColor', false, 'inherit');
              document.execCommand('styleWithCSS', false, false);
            } else {
              document.execCommand('hiliteColor', false, '#ffe066');
            }
          }
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
          const sel = window.getSelection();
          if (!sel.rangeCount) return;

          const range = sel.getRangeAt(0);
          let container = range.commonAncestorContainer;
          if (container.nodeType === 3) container = container.parentNode;

          //If we are already in code block, we remove it
          if (container.closest('pre')) {
            const pre = container.closest('pre');
            const text = pre.textContent;

            // Convertir les retours à la ligne en éléments <br> ou <div>
            const div = document.createElement('div');
            const lines = text.split('\n');
            lines.forEach((line, index) => {
              if (index > 0) {
                div.appendChild(document.createElement('br'));
              }
              if (line.length > 0) {
                div.appendChild(document.createTextNode(line));
              }
            });

            pre.parentNode.replaceChild(div, pre);
            return;
          }

          // Création d'un nouveau bloc de code
          const fragment = range.cloneContents();
          if (!fragment.textContent.trim()) return; // Ne rien faire si la sélection est vide

          // Convertir le fragement en texte en préservant la structure
          let content = '';
          const processNode = (node) => {
            if (node.nodeType === Node.TEXT_NODE) {
              content += node.textContent;
            } else if (node.nodeType === Node.ELEMENT_NODE) {
              // Ajouter un saut de ligne seulement pour les éléments de bloc
              if (node.nodeName ==='P' || node.nodeName === 'DIV') {
                if (!content.endsWith('\n')) content += '\n';
              } else if (node.nodeName === 'BR') {
                content += '\n';
              }

              for (const child of node.childNodes) {
                processNode(child);
              }
            }
          };

          for (const node of fragment.childNodes) {
            processNode(node);
          }

          // Préserver les doubles sauts de ligne intentionnels mais enlever les extras
          content = content.replace(/\n{3,}/g, '\n\n');  // Enlève les espaces au début et à la fin

          // Créer me bloc de code
          const pre = document.createElement('pre');
          pre.textContent = content;

          // Remplacer la sélection par le bloc de code
          range.deleteContents();
          range.insertNode(pre);

          // Nettoyer la sélection
          sel.removeAllRanges();
       }
      },    
      { name: 'removeFormat', icon: '<i class="fas fa-eraser" title="Remove format"></i>', action: () => document.execCommand('removeFormat') }
    ];
    buttons.forEach(btn => this.addButton(btn));
    // Gestion des événements natifs
    // On écoute mouseup sur tout le document pour gérer les sélections multi-lignes qui finissent hors de la zone
    if (!Popline._globalMouseupHandler) {
      Popline._globalMouseupHandler = (e) => {
        Popline.instances.forEach(instance => {
          const sel = window.getSelection();
          if (!sel.rangeCount) return;
          const range = sel.getRangeAt(0);
          if (instance.target.contains(range.commonAncestorContainer)) {
            instance.handleSelection(e);
          } else {
            instance.hide();
          }
        });
      };
      document.addEventListener('mouseup', Popline._globalMouseupHandler);
    }
    this.target.addEventListener('keyup', (e) => this.handleSelection(e));
    document.addEventListener('mousedown', (e) => {
      if (!this.bar.contains(e.target)) this.hide();
    });
    // On retire le listener selectionchange (plus de debounce, UX plus instantanée)
    if (Popline._selectionChangeHandler) {
      document.removeEventListener('selectionchange', Popline._selectionChangeHandler);
      Popline._selectionChangeHandler = null;
    }
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
      // Déclenche un événement input sur la noteentry active
      const sel = window.getSelection();
      if (sel.rangeCount > 0) {
        let container = sel.getRangeAt(0).commonAncestorContainer;
        if (container.nodeType === 3) container = container.parentNode;
        const noteentry = container.closest && container.closest('.noteentry');
        if (noteentry) noteentry.dispatchEvent(new Event('input', {bubbles:true}));
      }
      e.stopPropagation();
    });
    li.appendChild(btn);
    this.bar.appendChild(li);
  }

  handleSelection(e) {
    const selection = window.getSelection();
    if (selection && selection.toString().length > 0) {
      const rect = selection.getRangeAt(0).getBoundingClientRect();
      // Afficher la barre au-dessus de la sélection, mais si pas la place, la mettre juste en dessous
      let barHeight = this.bar.offsetHeight || 40;
      let desiredTop = window.scrollY + rect.top - barHeight - 10;
      let minTop = 8;
      if (desiredTop < minTop) {
        // Pas la place au-dessus, on la met en dessous
        desiredTop = window.scrollY + rect.bottom + 10;
      }
      this.show({
        top: desiredTop,
        left: window.scrollX + rect.left + rect.width / 2 - (this.bar.offsetWidth || 200) / 2
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

// Fonction globale pour insérer un séparateur à la position du curseur dans la note active
window.insertSeparator = function() {
  // Restaure la dernière sélection si elle a été sauvegardée
  if (window._lastNoteSelection && window._lastNoteSelection.range) {
    const sel = window.getSelection();
    sel.removeAllRanges();
    sel.addRange(window._lastNoteSelection.range);
  }
  const sel = window.getSelection();
  if (!sel.rangeCount) return;
  const range = sel.getRangeAt(0);
  // Crée un élément <hr>
  const hr = document.createElement('hr');
  hr.style.border = 'none';
  hr.style.borderTop = '1px solid #bbb'; // gris pour le séparateur
  hr.style.margin = '12px 0';
  // Insère le <hr> à la position du curseur d'édition
  if (!range.collapsed) {
    range.deleteContents();
  }
  range.insertNode(hr);
  // Place le curseur après le <hr>
  range.setStartAfter(hr);
  range.setEndAfter(hr);
  sel.removeAllRanges();
  sel.addRange(range);
  // Déclenche un événement input sur la noteentry active
  let container = range.commonAncestorContainer;
  if (container.nodeType === 3) container = container.parentNode;
  const noteentry = container.closest && container.closest('.noteentry');
  if (noteentry) noteentry.dispatchEvent(new Event('input', {bubbles:true}));
};
// Sauvegarde la sélection dans la note avant de cliquer sur le bouton séparateur
document.addEventListener('mousedown', function(e) {
  const sel = window.getSelection();
  if (sel.rangeCount > 0) {
    let container = sel.getRangeAt(0).commonAncestorContainer;
    if (container.nodeType === 3) container = container.parentNode;
    if (container.closest && container.closest('.noteentry')) {
      window._lastNoteSelection = { range: sel.getRangeAt(0).cloneRange() };
    }
  }
});
