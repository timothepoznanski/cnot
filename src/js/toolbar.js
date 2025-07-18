// Toolbar functions and mobile toolbar behavior
// Combined from toolbar-functions.js and mobile-toolbar.js

// ==============================================
// TOOLBAR FUNCTIONS (formatage et utilitaires)
// ==============================================

function addLinkToNote() {
  const url = prompt('Entrer l\'URL du lien:', 'https://');
  if (url) document.execCommand('createLink', false, url);
}

function toggleRedColor() {
  document.execCommand('styleWithCSS', false, true);
  const sel = window.getSelection();
  if (sel.rangeCount > 0) {
    const range = sel.getRangeAt(0);
    let allRed = true, hasText = false;
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
    if (hasText && allRed) {
      document.execCommand('foreColor', false, 'black');
    } else {
      document.execCommand('foreColor', false, '#ff2222');
    }
  }
  document.execCommand('styleWithCSS', false, false);
}

function toggleYellowHighlight() {
  const sel = window.getSelection();
  if (sel.rangeCount > 0) {
    const range = sel.getRangeAt(0);
    let allYellow = true, hasText = false;
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
    document.execCommand('styleWithCSS', false, true);
    if (hasText && allYellow) {
      document.execCommand('hiliteColor', false, 'inherit');
    } else {
      document.execCommand('hiliteColor', false, '#ffe066');
    }
    document.execCommand('styleWithCSS', false, false);

    // Rétablir le style des blocs <pre> touchés par la sélection
    // On parcourt tous les <pre> dans la note courante
    const note = range.commonAncestorContainer.closest ? range.commonAncestorContainer.closest('.noteentry') : null;
    if (note) {
      const pres = note.querySelectorAll('pre');
      pres.forEach(pre => {
        pre.style.background = '#F7F6F3';
        pre.style.color = 'rgb(55, 53, 47)';
        pre.style.padding = '34px 16px 32px 32px';
        pre.style.borderRadius = '4px';
        pre.style.fontFamily = 'Consolas, monospace';
        pre.style.fontSize = '90%';
        pre.style.margin = '1em 0';
        pre.style.border = '1px solid #ddd';
      });
    }
  }
}

function changeFontSize() {
  const size = prompt('Taille de police (1-7):', '3');
  if (size) document.execCommand('fontSize', false, size);
}

function toggleCodeBlock() {
  const sel = window.getSelection();
  if (!sel.rangeCount) return;
  const range = sel.getRangeAt(0);
  let container = range.commonAncestorContainer;
  if (container.nodeType === 3) container = container.parentNode;
  
  // Si déjà dans un bloc code, on le retire
  if (container.closest && container.closest('pre')) {
    const pre = container.closest('pre');
    // Remplacer le <pre> par son contenu sous forme de <div> et <br>
    const text = pre.textContent;
    const div = document.createElement('div');
    const lines = text.split('\n');
    lines.forEach((line, index) => {
      if (index > 0) div.appendChild(document.createElement('br'));
      if (line.length > 0) div.appendChild(document.createTextNode(line));
    });
    pre.parentNode.replaceChild(div, pre);
    
    // Déclencher la détection de modification
    if (typeof update === 'function') {
      update();
    }
    return;
  }
  
  // Sinon, transformer la sélection en bloc code
  if (sel.isCollapsed) return; // rien à faire si pas de sélection
  
  // On clone le contenu sélectionné
  const fragment = range.cloneContents();
  if (!fragment.textContent.trim()) return;
  
  let content = '';
  // On récupère le texte avec sa structure
  const processNode = (node) => {
    if (node.nodeType === Node.TEXT_NODE) {
      content += node.textContent;
    } else if (node.nodeType === Node.ELEMENT_NODE) {
      if (node.nodeName ==='P' || node.nodeName === 'DIV') {
        if (content.length > 0 && !content.endsWith('\n')) content += '\n';
      } else if (node.nodeName === 'BR') {
        content += '\n';
      }
      for (const child of node.childNodes) processNode(child);
    }
  };
  
  for (const node of fragment.childNodes) processNode(node);
  content = content.replace(/\n{3,}/g, '\n\n');
  
  // Essayer d'abord avec execCommand pour les navigateurs qui le supportent encore
  const escapedContent = content
    .replace(/&/g, '&amp;')
    .replace(/</g, '&lt;')
    .replace(/>/g, '&gt;');
  
  const preHTML = `<pre style="background: #F7F6F3; color: rgb(55, 53, 47); padding: 34px 16px 32px 32px; border-radius: 4px; font-family: Consolas, monospace; font-size: 90%; margin: 1em 0; border: 1px solid #ddd;">${escapedContent}</pre>`;
  
  try {
    const success = document.execCommand('insertHTML', false, preHTML);
    if (success) {
      // Déclencher la détection de modification
      if (typeof update === 'function') {
        update();
      }
      return;
    }
  } catch (e) {
    // execCommand a échoué, utiliser l'approche manuelle
  }
  
  // Fallback : création manuelle avec support d'annulation
  const pre = document.createElement('pre');
  pre.textContent = content;
  pre.style.background = '#F7F6F3';
  pre.style.color = 'rgb(55, 53, 47)';
  pre.style.padding = '34px 16px 32px 32px';
  pre.style.borderRadius = '4px';
  pre.style.fontFamily = 'Consolas, monospace';
  pre.style.fontSize = '90%';
  pre.style.margin = '1em 0';
  pre.style.border = '1px solid #ddd';
  
  // Trouver la noteentry pour les événements
  let noteContainer = range.commonAncestorContainer;
  if (noteContainer.nodeType === 3) noteContainer = noteContainer.parentNode;
  const noteentry = noteContainer.closest && noteContainer.closest('.noteentry');
  
  if (noteentry) {
    // Déclencher un événement beforeinput pour l'historique d'annulation
    const beforeInputEvent = new InputEvent('beforeinput', {
      bubbles: true,
      cancelable: true,
      inputType: 'insertText',
      data: null
    });
    
    if (noteentry.dispatchEvent(beforeInputEvent)) {
      // Remplacer la sélection par le bloc code
      range.deleteContents();
      range.insertNode(pre);
      
      // Positionner le curseur après le bloc code
      sel.removeAllRanges();
      const newRange = document.createRange();
      newRange.setStartAfter(pre);
      newRange.setEndAfter(pre);
      sel.addRange(newRange);
      
      // Déclencher l'événement input
      const inputEvent = new InputEvent('input', {
        bubbles: true,
        inputType: 'insertText',
        data: null
      });
      noteentry.dispatchEvent(inputEvent);
    }
  }
  
  // Déclencher la détection de modification
  if (typeof update === 'function') {
    update();
  }
}

function insertSeparator() {
  const sel = window.getSelection();
  if (!sel.rangeCount) return;
  
  const range = sel.getRangeAt(0);
  let container = range.commonAncestorContainer;
  if (container.nodeType === 3) container = container.parentNode;
  const noteentry = container.closest && container.closest('.noteentry');
  
  if (!noteentry) return;
  
  // Essayer d'abord avec execCommand pour les navigateurs qui le supportent encore
  try {
    const hrHTML = '<hr style="border: none; border-top: 1px solid #bbb; margin: 12px 0;">';
    const success = document.execCommand('insertHTML', false, hrHTML);
    
    if (success) {
      // Déclenche un événement input
      noteentry.dispatchEvent(new Event('input', {bubbles:true}));
      return;
    }
  } catch (e) {
    // execCommand a échoué, utiliser l'approche manuelle
  }
  
  // Fallback : insertion manuelle avec support d'annulation via l'API moderne
  const hr = document.createElement('hr');
  hr.style.border = 'none';
  hr.style.borderTop = '1px solid #bbb';
  hr.style.margin = '12px 0';
  
  // Déclencher un événement beforeinput pour l'historique d'annulation
  const beforeInputEvent = new InputEvent('beforeinput', {
    bubbles: true,
    cancelable: true,
    inputType: 'insertText',
    data: null
  });
  
  if (noteentry.dispatchEvent(beforeInputEvent)) {
    // Insérer l'élément
    if (!range.collapsed) {
      range.deleteContents();
    }
    range.insertNode(hr);
    
    // Positionner le curseur après le HR
    range.setStartAfter(hr);
    range.setEndAfter(hr);
    sel.removeAllRanges();
    sel.addRange(range);
    
    // Déclencher l'événement input
    const inputEvent = new InputEvent('input', {
      bubbles: true,
      inputType: 'insertText',
      data: null
    });
    noteentry.dispatchEvent(inputEvent);
  }
}

// ==============================================
// MOBILE TOOLBAR BEHAVIOR (affichage conditionnel)
// ==============================================

document.addEventListener('DOMContentLoaded', function() {
    // Vérifier si on est sur mobile
    const isMobile = window.innerWidth <= 800;
    
    if (!isMobile) return; // Ne pas exécuter ce script sur desktop
    
    let selectionTimer;
    
    // Fonction pour afficher/cacher les boutons de formatage
    function toggleFormatButtons() {
        const selection = window.getSelection();
        const toolbar = document.querySelector('.note-edit-toolbar');
        
        if (selection.toString().length > 0) {
            // Il y a du texte sélectionné, afficher les boutons de formatage
            if (toolbar) {
                toolbar.classList.add('show-format-buttons');
            }
        } else {
            // Pas de sélection, cacher les boutons de formatage
            if (toolbar) {
                toolbar.classList.remove('show-format-buttons');
            }
        }
    }
    
    // Écouter les événements de sélection
    document.addEventListener('selectionchange', function() {
        // Utiliser un timer pour éviter trop d'appels
        clearTimeout(selectionTimer);
        selectionTimer = setTimeout(toggleFormatButtons, 100);
    });
    
    // Écouter aussi les clics sur les éléments éditables
    document.addEventListener('click', function(e) {
        if (e.target.closest('.noteentry')) {
            setTimeout(toggleFormatButtons, 100);
        }
    });
    
    // Écouter les événements tactiles pour mobile
    document.addEventListener('touchend', function(e) {
        if (e.target.closest('.noteentry')) {
            setTimeout(toggleFormatButtons, 150);
        }
    });
    
    // Cacher les boutons quand on clique en dehors d'une note
    document.addEventListener('click', function(e) {
        if (!e.target.closest('.notecard')) {
            const toolbar = document.querySelector('.note-edit-toolbar');
            if (toolbar) {
                toolbar.classList.remove('show-format-buttons');
            }
        }
    });
});
