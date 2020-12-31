CKEDITOR.plugins.add('simplecodetag', {
  // TODO: add icon file
  icons: 'simpleCodeTag',
  init(editor) {
    const CODE_TAG_NAME = 'code';

    editor.addCommand('toggleSimpleCodeTagInsertion', {
      exec(editor) {
        const editorSelection = editor.getSelection();
        const selectedElement =
            editorSelection.getSelectedElement() || /* fallback as `.getSelectedElement()` might return null: https://dev.ckeditor.com/ticket/7928 */
            editorSelection.getStartElement();
        const isCodeElementInserted = selectedElement && selectedElement.$.tagName.toLowerCase() === CODE_TAG_NAME;

        if (isCodeElementInserted) {
          unWrapTextFromCodeTag(selectedElement, editorSelection);
        } else {
          wrapTextInCodeTag(editorSelection);
        }
      },
    });

    const CTRL_K = CKEDITOR.CTRL + 75;
    editor.setKeystroke(CTRL_K, 'toggleSimpleCodeTagInsertion');

    editor.ui.addButton('SimpleCodeTag', {
      label: 'Insert Code Tag',
      command: 'toggleSimpleCodeTagInsertion',
    });

    function unWrapTextFromCodeTag(selectedElement, editorSelection) {
      const elementContent = new CKEDITOR.dom.element.get(selectedElement.$.childNodes[0]);

      selectedElement.insertBeforeMe(elementContent);
      selectedElement.remove();

      editorSelection.selectElement(elementContent);
    }

    function wrapTextInCodeTag(editorSelection) {
      const htmlElement = new CKEDITOR.dom.element(CODE_TAG_NAME);
      const selectedText = editorSelection.getSelectedText() || /* if nothing is selected then prepare empty space */ ' ';

      // set content as text to avoid XSS
      htmlElement.setText(selectedText);

      // use inline styling, because CKEditor is loaded inside iframe,
      // so styles declared outside it (like qa-styles.css) cannot be applied
      htmlElement.setStyles({
        color: 'white',
        background: '#2c3e50',
        padding: '2px 4px',
        borderRadius: '2px',
      });

      editor.editable().insertElementIntoSelection(htmlElement);
      editorSelection.selectElement(htmlElement);
    }
  }
});
