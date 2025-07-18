// Fonctions pour les boutons de la barre d'outils

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
        if (!content.endsWith('\n')) content += '\n';
      } else if (node.nodeName === 'BR') {
        content += '\n';
      }
      for (const child of node.childNodes) processNode(child);
    }
  };
  
  for (const node of fragment.childNodes) processNode(node);
  content = content.replace(/\n{3,}/g, '\n\n');
  
  // Crée le bloc <pre> avec style inline pour garantir l'apparence
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
  
  // Remplace la sélection par le bloc code
  range.deleteContents();
  range.insertNode(pre);
  
  // Place le curseur après le bloc code
  sel.removeAllRanges();
  const newRange = document.createRange();
  newRange.setStartAfter(pre);
  newRange.setEndAfter(pre);
  sel.addRange(newRange);
  
  // Déclencher la détection de modification
  if (typeof update === 'function') {
    update();
  }
}

function insertSeparator() {
  const sel = window.getSelection();
  if (!sel.rangeCount) return;
  const range = sel.getRangeAt(0);
  
  // Crée un élément <hr>
  const hr = document.createElement('hr');
  hr.style.border = 'none';
  hr.style.borderTop = '1px solid #bbb';
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
}
