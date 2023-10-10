'use strict';

/**
 *
 * @param contentEditable {HTMLElement}
 * @param apiProvider {TextCorrectionApi}
 * @param options {Object|*}
 * @constructor
 */
function TextEditor(contentEditable, apiProvider, options) {
    options = options || {}
    // Class attribute of highlighted error
    this.errorHighlightClass = options.errorHighlightClass;
    // Class attribute for error description popup
    this.errorPopupClass = options.errorPopupClass;
    // Div containing editable text, with "contenteditable" attribute
    this.contentEditable = contentEditable;
    // Set default state.
    // Next we'll check the state to prevent text checking while the previous one is still in progress.
    this.state = this.STATE_IDLE;
    // Text checking API provider
    this.apiProvider = apiProvider;
    // Text to be shown on connection errors and other unhandled exceptions
    this.defaultExceptionText = options.defaultExceptionText || "Runtime error happened :(";
    // Bear in mind that so many people write from right to left!
    this.rtl = options.rtl || false;
    this.contentEditable.style.direction = this.rtl ? "rtl" : "ltr";

    // Map of highlights by identifier
    this.errors = {};

    // Here you can set up input filtering
    var self = this; // Use let or short function syntax if you don't like old browsers
    this.contentEditable.addEventListener('paste', function () {
        self.polishContents()
    });
    this.contentEditable.addEventListener('input', function () {
        self.polishContents()
    });
}

/**
 * Basic component states
 * @type {string}
 */
TextEditor.prototype.STATE_IDLE = "idle";
TextEditor.prototype.STATE_LOADING = "loading";

/**
 * Here you can remove tags, styles or make any custom text formatting or preparations
 */
TextEditor.prototype.polishContents = function () {
    var highlights = this.contentEditable.getElementsByClassName(this.errorHighlightClass);
    // Remove highlights if content was changed
    for (var i = highlights.length - 1; i >= 0; --i) {
        // Iterate backwards because removing element decreases highlights.length
        if (highlights[i].dataset.errata != highlights[i].innerText) {
            highlights[i].classList.remove(this.errorHighlightClass);
            continue;
        }
        // Remove style after copy-paste of editor contexts
        highlights[i].removeAttribute("style")
    }
    // Merge neighbouring TextNodes
    this.contentEditable.normalize();

    // Remove pasted styles
    var stripStyles = function (rootNode, stripStyles) {
        rootNode.childNodes.forEach(function (node) {
            if (node.nodeType == Node.ELEMENT_NODE) {
                node.removeAttribute("style");
            }
            if (node.nodeName == "U") {
                node.parentNode.replaceChild(document.createTextNode(node.innerText), node);
                return
            }
            stripStyles(node, stripStyles)
        });
    };
    stripStyles(this.contentEditable, stripStyles)

    // Ctrl + A with DEL can leave <br> in contents
    if (!this.contentEditable.innerText.trim()) {
        this.clear()
    }
}

/**
 * Calculates current text area carel pos thru all the node elements
 *
 * @return {number|null}
 */
TextEditor.prototype.getCaretPos = function () {
    var selection = window.getSelection();
    if (!selection || !selection.focusNode) {
        return null;
    }

    // Feel free to use Node.contains for modern browsers
    var isParent = function (child, parent) {
        while ((child = child.parentNode) && child !== parent);
        return !!child;
    }

    if (!isParent(selection.focusNode, this.contentEditable)) {
        return null;
    }

    var caret = 0;
    var focusNodeFound = false;
    var iterateChildren = function (root) {
        if (focusNodeFound) {
            return;
        }
        if (root === selection.focusNode) {
            caret += selection.focusOffset;
            focusNodeFound = true;
            return
        }
        if (!root.hasChildNodes()) {
            caret += (root.innerText || root.nodeValue).length;
        } else {
            root.childNodes.forEach(iterateChildren);
        }
    };
    iterateChildren(this.contentEditable)

    return caret;
}

/**
 * Get value of text editor
 * @returns {*|string}
 */
TextEditor.prototype.getValue = function() {
    return this.contentEditable.innerText;
}

/**
 * Make a text checking and highlight errors
 */
TextEditor.prototype.check = function() {
    // Prevent simultaneous checking
    if (this.state != this.STATE_IDLE) {
        return;
    }
    // Change state
    this.state = this.STATE_LOADING;

    // Turn on readonly mode. Content must not be changed before the error highlighting
    this.contentEditable.contentEditable = false;

    var self = this; // Or just use () => {} in make for modern browsers only
    return this.apiProvider.callApi("/grammar", {
        text: this.getValue()
    })
        .then(function (backendResponse) {
            self.highlightErrors(backendResponse.response.errors || [])
        })
        .catch(function (errorResponse) {
            alert(self.defaultExceptionText)
        })
        .finally(function() {
            self.state = self.STATE_IDLE;
            self.contentEditable.contentEditable = true;
        })
}

/**
 * Clear text editing area
 */
TextEditor.prototype.clear = function () {
    this.contentEditable.innerHTML = "";
}

/**
 * Smart wrap text with tags to highlight errors
 * @param errors {Array<Object>}
 */
TextEditor.prototype.highlightErrors = function (errors) {
    // Remove old checker spans from div
    var oldErrors = this.contentEditable.getElementsByTagName('ins');
    for (var j = 0; j < oldErrors.length;) {
        oldErrors[j].parentNode.replaceChild(
            document.createTextNode(oldErrors[j].innerText),
            oldErrors[j]
        )
    }
    // Merge neighbouring TextNodes
    this.contentEditable.normalize();

    this.errors = {}

    if (!errors || !errors.length) {
        return;
    }

    // Wrap text with <span class="...">
    for (var i = 0; i < errors.length; ++i) {
        this.errors[errors[i].id] = errors[i];
        this.highlightOne(
            this.contentEditable,
            errors[i].offset,
            errors[i].length,
            errors[i].id
        );
    }
}


/**
 * Highlight a single piece of text.
 * The smart algorithm for wrapping text with tags through other tags(!)
 *
 * @param rootElement {HTMLElement}
 * @param offset {number}    Mistake offset (in symbols)
 * @param length {number}   Mistake length
 * @param id {string}       Mistake id
 * @param childFlag {boolean|*}
 */
TextEditor.prototype.highlightOne = function (rootElement, offset, length, id, childFlag) {
    this.contentProcessingCursor = this.contentProcessingCursor || 0;
    this.highlightingFinished = this.highlightingFinished || false;

    childFlag = childFlag || false;
    if (!childFlag) {
        this.highlightingFinished = false;
        this.contentProcessingCursor = 0;
    }
    if (this.highlightingFinished) {
        return;
    }
    var className = "highlight-" + id;
    if (rootElement.classList && rootElement.classList.contains(className)) {
        return;
    }

    if (rootElement.childNodes.length) {
        // Recursively highlight text inside children
        for (var j = 0; j < rootElement.childNodes.length; ++j) {
            this.highlightOne(rootElement.childNodes[j], offset, length, id, true);
        }
    } else {
        // If not is text node (nodeType === 3)
        if (rootElement.nodeType === Text.TEXT_NODE) {
            /*
             Now we divide one TEXT_NODE info 1-3 nodes.
             Before:
                TEXT_NODE: "word1 word2 word3"

             After:
                TEXT_NODE: "word1"
                ELEMENT_NODE: "span"
                    TEXT_NODE: "word2"
                TEXT_NODE: "word3"
              */

            if (this.contentProcessingCursor + rootElement.nodeValue.length > offset) {
                var textBeforeHighlighting = "",
                    highlightedText = "",
                    textAfterHighlighting = "";

                if (offset > this.contentProcessingCursor) {
                    textBeforeHighlighting = rootElement.nodeValue.substr(0, offset - this.contentProcessingCursor);
                }
                if (offset >= this.contentProcessingCursor) {
                    highlightedText = rootElement.nodeValue.substr(offset - this.contentProcessingCursor, length);
                } else {
                    highlightedText = rootElement.nodeValue.substr(0, offset + length - this.contentProcessingCursor);
                }

                if (offset - this.contentProcessingCursor + length < rootElement.nodeValue.length) {
                    textAfterHighlighting = rootElement.nodeValue.substr(offset - this.contentProcessingCursor + length);
                }

                var fixElement = document.createElement("ins");
                fixElement.className = this.errorHighlightClass + ' ' + className;
                var self = this; // Or just use short syntax () => {} for modern browsers only
                fixElement.onclick = function (event) {
                    if (!fixElement.classList.contains(self.errorHighlightClass)) {
                        // Do not show explanations on
                        return;
                    }
                    self.showExplanation(id)
                };
                // You can use it to compare with innerText to detect any changes
                fixElement.dataset.errata = highlightedText;
                fixElement.innerText = highlightedText;
                rootElement.parentNode.replaceChild(fixElement, rootElement);

                // Insert before
                if (textBeforeHighlighting.length) {
                    var textNodeBeforeHighlighting = document.createTextNode(textBeforeHighlighting);
                    fixElement.parentNode.insertBefore(textNodeBeforeHighlighting, fixElement);
                }

                // Insert after
                if (textAfterHighlighting.length) {
                    var textNodeAfterHighlighting = document.createTextNode(textAfterHighlighting);
                    if (fixElement.nextSibling) {
                        fixElement.parentNode.insertBefore(textNodeAfterHighlighting, fixElement.nextSibling);
                    } else {
                        fixElement.parentNode.appendChild(textNodeAfterHighlighting);
                    }
                }

                if (offset - this.contentProcessingCursor + length <= rootElement.nodeValue.length) {
                    // Process finished
                    this.highlightingFinished = true;
                    return;
                }
            }

            this.contentProcessingCursor += rootElement.nodeValue.length;
        }
    }
}


/**
 * Get the first existing popup or create one
 * @returns {HTMLElement}
 */
TextEditor.prototype.getPopup = function () {
    var found = document.getElementsByClassName(this.errorPopupClass);
    if (found.length) {
        return found.item(0);
    }

    var errorContainer = document.createElement("div");
    errorContainer.className = this.errorPopupClass;
    document.body.appendChild(errorContainer);
    return errorContainer;
}

/**
 * Build a popup containing the error info and possible fix suggestions
 * @param id {string} Highlight id
 */
TextEditor.prototype.showExplanation = function (id) {
    var errorInfo = this.errors[id] || null
    if (!errorInfo) {
        return;
    }

    var errorContainer = this.getPopup();
    errorContainer.innerHTML = "";

    var close = document.createElement("div");
    close.innerHTML = "╳"
    close.className = "close"
    close.onclick = function () {
        errorContainer.remove();
    };
    errorContainer.append(close);

    var title = document.createElement("h4");
    title.innerText = TextCheckerPhrases.text[errorInfo.type] || TextCheckerPhrases.text.spelling;
    errorContainer.append(title);

    var description = document.createElement("p");
    description.innerText = errorInfo.description.en || '';
    if (description.innerText) {
        errorContainer.append(description);
    }

    var self = this;
    for (var i = 0; i < errorInfo.better.length; ++i) {
        var suggestion = document.createElement("div");
        suggestion.className = 'button';
        suggestion.innerText = errorInfo.better[i] || TextCheckerPhrases.text.remove
        suggestion.style.backgroundColor = checkButton.style.backgroundColor;
        suggestion.style.color = checkButton.style.color;
        var _i = i; // feel free to use let or short function syntax
        suggestion.addEventListener("click", function () {
            self.fixError(errorInfo.id, errorInfo.better[_i])
        })
        errorContainer.append(suggestion);
    }

    this.contentEditable.blur();
}

/**
 * Insert new text node before the very beginning of highlighted error.
 * Remove highlighted text next.
 *
 * @param id {string} Highlight id
 * @param newText {string} Text replacement
 */
TextEditor.prototype.fixError = function (id, newText) {
    var highlightElements = this.contentEditable.getElementsByClassName("highlight-" + id);
    if (newText) {
        if (highlightElements.length) {
            var firstHighlight = highlightElements.item(0)
            var newElement = document.createTextNode(newText);
            firstHighlight.parentNode.insertBefore(newElement, firstHighlight);
            // Merge text nodes and remove ampty elements
            firstHighlight.parentNode.normalize()
        }
    }

    // Remove the highlight
    for (var i = 0; i < highlightElements.length;) {
        highlightElements[i].remove();
    }

    // Merge neighbouring TextNodes
    this.contentEditable.normalize();
    // Remove popup
    this.getPopup().remove();
}

/**
 * Sample crossbrowser code to copy text to clipboard.
 * I think you can find a better and a shorter way to do it.
 *
 * @param html
 */
TextEditor.prototype.copyText = function () {
    var html = this.getValue();
    // Uncomment if you need to copy HTML
    // html = html.replace(/<\/?[^>]+(>|$)/g, "")
    // html = html.replace(/\n/g, "<br>");

    // Create container for the HTML
    // [1]
    var container = document.createElement("div");
    container.innerHTML = html;

    // Hide element
    // [2]
    container.style.position = 'fixed';
    container.style.pointerEvents = 'none';
    container.style.color = 'black';
    container.style.background = 'white';
    container.style.opacity = "0";

    // Detect all style sheets of the page
    // let activeSheets = Array.prototype.slice.call(document.styleSheets)
    //     .filter(function (sheet: any) {
    //         return !sheet.disabled
    //     });

    // Mount the iframe to the DOM to make `contentWindow` available
    // [3]
    document.body.appendChild(container);

    // Copy to clipboard
    // [4]
    window?.getSelection()?.removeAllRanges();

    var range = document.createRange();
    range.selectNode(container);
    window?.getSelection()?.addRange(range);

    // [5.1]
    document.execCommand("copy");

    // Далее все стили удаляются и возвращаются заново
    // что выглядит как мерцание страницы
    // После комментирования кода ниже корректно работает в IE, FireFox, Edge

    // [5.2]
    // for (i = 0; i < activeSheets.length; i++) activeSheets[i].disabled = true;

    // [5.3]
    // document.execCommand('copy');

    // [5.4]
    // for (i = 0; i < activeSheets.length; i++) activeSheets[i].disabled = false;

    // Remove the iframe
    // [6]
    document.body.removeChild(container);
}
