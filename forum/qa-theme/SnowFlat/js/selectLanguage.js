document.addEventListener("DOMContentLoaded", () => {
    if (typeof CKEDITOR != 'undefined') {
        CKEDITOR.on('instanceReady', function(event) {
            const categories = [
                {category: 'HTML i CSS', language: 'xml'},
                {category: 'C i C++', language: 'cpp'},
                {category: 'JavaScript', language: 'jscript'},
                {category: 'PHP', language: 'php'},
                {category: 'SQL, bazy danych', language: 'sql'},
                {category: 'C#', language: 'csharp'},
                {category: 'Java', language: 'java'},
                {category: 'Python', language: 'python'},
                {category: 'SPOJ', language: 'cpp'},
                {category: 'Ruby', language: 'ruby'},
                {category: 'Visual Basic', language: 'vb'},
                {category: 'Android, Swift, Symbian', language: 'java'},
                {category: 'OpenGL, Unity', language: 'cpp'},
                {category: 'Mikrokontrolery', language: 'cpp'},
                {category: 'Systemy CMS', language: 'php'}
            ];

            let clickHandler;
            if (location.href.includes('/ask')) {
                clickHandler = () => {
                    const firstCategory = document.querySelector('#category_1');
                    const selectedFirstOption = firstCategory.children[firstCategory.selectedIndex];
                    if (selectedFirstOption.textContent === 'Programowanie') {
                        const secondCategory = document.querySelector('#category_2');
                        const selectedSecondOption = secondCategory.children[secondCategory.selectedIndex];
                        const findCategory = categories.find(function(object) {
                            return object.category == selectedSecondOption.textContent;
                        });

                        CKEDITOR.config.syntaxhighlight_lang = findCategory ? findCategory.language : SyntaxHighlighter.defaults['code-language'].alias;
                    } else {
                        CKEDITOR.config.syntaxhighlight_lang = SyntaxHighlighter.defaults['code-language'].alias;
                    }
                };
            } else if (location.href.includes('edit')) {
                clickHandler = () => {
                    let findCategory;
                    const firstCategory = document.querySelector('#q_category_1');

                    if (firstCategory) {
                        const selectedFirstOption = firstCategory.children[firstCategory.selectedIndex];
                        if (selectedFirstOption.textContent === 'Programowanie') {
                            const secondCategory = document.querySelector('#q_category_2');
                            const selectedSecondOption = secondCategory.children[secondCategory.selectedIndex];

                            findCategory = categories.find((object) => {
                                return object.category == selectedSecondOption.textContent;
                            });
                        }
                    } else {
                       const category = document.querySelector('.qa-q-view-where-data');
                       findCategory = categories.find((object) => {
                            return object.category == category.textContent;
                        });
                    }

                    CKEDITOR.config.syntaxhighlight_lang = findCategory ? findCategory.language : SyntaxHighlighter.defaults['code-language'].alias;
                };

            } else {
                clickHandler = () => {
                    const category = document.querySelector('.qa-q-view-where-data');
                    const findCategory = categories.find(function(object) {
                        return object.category == category.textContent;
                    });

                    CKEDITOR.config.syntaxhighlight_lang = findCategory ? findCategory.language : SyntaxHighlighter.defaults['code-language'].alias;
                };
            }

            const postContentTextarea = document.getElementsByName(event.editor.name)[0];
            const editorParent = postContentTextarea.parentNode;

            const codeBlock = editorParent.querySelector('.cke_button__syntaxhighlight');
            codeBlock.addEventListener('click', clickHandler);
        });
    }
});
