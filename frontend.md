# Frontend

[&larr; Back](./README.md)

In order to make the frontend code as convenient as possible for use in other projects
I didn't use any framework, just pure Javascript with support for older browsers.
In the code, comments mark places that can be simplified if you do not need support for older browsers.

The only third party library I use is `axios` to make HTTP requests to the backend.
It is used only in one place and you can stick any other there.

## Contents

- [Initializing the application](#init)
- [Contenteditable=true](#contenteditable)
- [Internationalization or i18n](#i18n)
- [Text editor component](#editor)

### <a name="init"></a>Initializing the application

What should a web application for Telegram do at the very beginning?

- Collect all application configuration information in one object. It’s convenient to have all the settings in one place and use them later.
- Customize application styles according to the theme settings sent by Telegram. It is important that the application elements are a visual continuation of the Telegram.
- Customize the arrangement of elements on the page based on the size of the window. The size and arrangement of elements should, if possible, adapt automatically.
- Set up a function to update the theme and location on Telegram events.
- Determine the user's language and select a dictionary with translations.

All this can be seen in the main file `public/app.htm`. Obviously, if you are using a framework,
then you won’t need some of the code from there - you won’t have to find “manually” the necessary elements on the page, for example.

### <a name="contenteditable"></a>Contenteditable=true

If the functionality of `<textarea />` is not enough, the property comes to the rescue
`contenteditable`, which turns any element into a text editor.

I have collected here the important features of working with such elements. So let's see:

```html
<div id="editor"
         spellcheck="false"
         placeholder="Enter some text here"
         contenteditable="true"></div>
```

Let's specify `spellcheck="false"` so that the browser does not underline text that it thinks is
contains typos.

Unfortunately, `contenteditable` cannot have a placeholder (text shown when the content is empty).
But we'll get around this with CSS:

```css
/* Placeholder trick */
[contenteditable=true]:empty:not(:focus):before {
    content: attr(placeholder);
    display: block;
    color:#AAA;
}
```

### <a name="i18n"></a>Мультиязычность

We should also talk about internationalization (i18n). As practice shows, there are few developers
in their lives they are faced with the need to develop and support applications in several languages.

I am not going to tell you about techniques for storing and maintaining dictionaries. I just want to remind you something else:

**A huge number of people in the world write from right to left**. And if you are developing a truly multilingual application,
this needs to be taken into account. But how?

- Make a dynamic change to the page property `direction` from `ltr` to `rtl` depending on the language.
- Avoid `text-align: left`, the browser should take the direction from the `direction` property.
- Remember that if `direction: rtl`, then child `inline` and `inline-block` elements are displayed from right to left.  

### <a name="editor"></a>Text editor component

The key element of the application is the text editing component `TextEditor`.

```javascript
var editor = new TextEditor(editorDiv, api, {
    errorHighlightClass: "editor-highlight",
    errorPopupClass: "editor-error-container",
    defaultExceptionText: TextCheckerPhrases.text.error,
    // Bear in mind that so many people write from right to left!
    rtl: ["ar", "fa", "he", "ku", "ps", "ur", "yi"].indexOf(appConfig.language.substring(0, 2)) != -1
});
```

It is designed to make it easier to manipulate `div[contenteditable]`.
Its main advantage is the implementation of methods for wrapping the necessary parts of the text with given tags.
Unlike the vast majority of available code examples, the component works correctly with internal
layout of any degree of nesting.

#### Basic methods:

```javascript
editor.polishContents();
```

When inserting text into `div[contenteditable]` you may experience style wrapping failure.
This method forces the inserted elements to match the style of the entire page. Removes unnecessary tags.
Ensures that when all content is deleted, no empty tags remain.


```javascript
editor.getCaretPos();
```

Getting the cursor position in the field, taking into account all possible nesting of elements.
If the cursor is not inside an editor element, returns null.


```javascript
editor.getValue();
```

Returns the text content of the editor as a string without tags.

```javascript
editor.check();
```

If the text is not being checked at the moment, it makes a request to the backend for
getting a list of errors. Next, for each error, wrapping tags are created for
highlighting detected errors.

```javascript
editor.clear();
```

Clears the editor text field.

```javascript
editor.highlightOne(el, offset, length, id);
```

Wraps the desired portion of the editor content for highlighting.
Also, when you click on the selection, an information block with an explanation will be shown.

```javascript
editor.copyText();
```

Copies text content to the clipboard. The method code contains different options for different browsers.
If you don't need support for older browsers, the method can be greatly simplified.
