# Фронтенд

[&larr; Назад](./README.md)

Доступные языки: **RU** [EN](./../../frontend.md)

Для того чтобы сделать код фронтенда максимально удобным для использования в других проектах 
я не использовал никакой фреймворк, только чистый Javascript с поддержкой старых браузеров.
В коде комментариями отмечены места, которые можно упростить если поддержка старых браузеров вам не нужна.

Единственная сторонная библиотека, которую я использую - `axios` чтобы делать HTTP-запросы к бэкенду.
Она используется только в одном месте и вы сможете воткнуть туда любую другую.

## Содержание

- [Инициализация приложения](#init)
- [Contenteditable=true](#contenteditable)
- [Мультиязычность](#i18n)
- [Компонент редактирования контента](#editor)

### <a name="init"></a>Инициализация приложения

Что должно сделать веб-приложение для Телеграма в самом начале?

- Собрать в одном объекте всю информацию о конфигурации приложения. Удобно иметь все настройки в одном месте, а дальше использовать их.
- Настроить стили приложения согласно настройкам темы, переданной Телеграммом. Важно чтобы элементы приложения были тематическим продолжением Телеграмма.
- Настроить расположение элементов на странице исходя из размеров окна. Размер и расположение элементов должно по возможности адаптироваться автоматически. 
- Настроить вызов обновления тема и расположения при соответствующих событиях Телеграмма.
- Определить язык пользователя и выбрать словарь с переводами.

Все это можно увидеть в основном файле `public/app.htm`. Очевидно, что если вы используете фреймфорк, 
то часть коду оттуда вам не понадобится - вам на надо будет "руками" находить нужные элементы на странице, например.

### <a name="contenteditable"></a>Contenteditable=true

Если функциональности `<textarea />` недостаточно, на помощь приходит свойство
`contenteditable`, которое превращает любой элемент в текстовый редактор.

Я собрал здесь важные особенности работы с такими элементами. Итак, давайте посмотрим:

```html
<div id="editor"
         spellcheck="false"
         placeholder="Enter some text here"
         contenteditable="true"></div>
```

Укажем `spellcheck="false"` чтобы браузер не подчеркивал текст, который по его мнению
содержит опечатки. 

К сожалению, у `contenteditable` нельзя указать плейсхолдер (текст, показываемый когда содержимое пусто).
Но мы обойдем это с помощью CSS:

```css
/* Placeholder trick */
[contenteditable=true]:empty:not(:focus):before {
    content: attr(placeholder);
    display: block;
    color:#AAA;
}
```

### <a name="i18n"></a>Мультиязычность

Отдельно стоит поговорить об интернационализации (i18n). Как показывает практика, немного разработчиков
в своей жизни сталкиваются с необходимость разработки и поддержки приложения на нескольких языках.

Я не буду здесь рассказывать о техниках хранения и ведения словарей. Хочу напомнить другое:

**Огромное количесто людей в мире пишет справа налево**. И если вы разрабатываете действительно мультиязычное приложение,
это нужно учитывать. Как?

- Сделайте динамическое изменение свойства страницы `direction` с `ltr` на `rtl` в зависимости от языка.
- Избегайте `text-align: left`, браузер должен брать направление из свойства `direction`.
- Помнить, что если `direction: rtl`, то дочерние `inline` и `inline-block` элементы выводятся справа налево.  

### <a name="editor"></a>Компонент редактирования контента 

Ключевым элементом приложения является компонент редактирования текста `TextEditor`.

```javascript
var editor = new TextEditor(editorDiv, api, {
    errorHighlightClass: "editor-highlight",
    errorPopupClass: "editor-error-container",
    defaultExceptionText: TextCheckerPhrases.text.error,
    // Bear in mind that so many people write from right to left!
    rtl: ["ar", "fa", "he", "ku", "ps", "ur", "yi"].indexOf(appConfig.language.substring(0, 2)) != -1
});
```

Он сделан для упрощения манипуляций с `div[contenteditable]`.
Главное его преимущество - реализация методов для обертывания нужных частей текста заданными тегами.
В отличие от подавляющего большинства доступных примеров кода компонент корректно работает с внутренней 
версткой любой степени вложенности.

#### Основные методы:

```javascript
editor.polishContents();
```

При вставке текста в `div[contenteditable]` можно столкнуться с неудачным переносом стилей.
Этот метод приводит вставляемые элементы к стилю всей страницы. Удаляет ненужные теги.
Следит чтобы при удалении всего содержимого не оставалось пустых тегов.


```javascript
editor.getCaretPos();
```

Получение позиции курсора в поле с учетом всей возможной вложенности элементов. 
Если курсор не находится внутри элемента редактора, возвращает null.


```javascript
editor.getValue();
```

Возвращает текстовое содержимое редактора в виде строки без тегов.

```javascript
editor.check();
```

Если в данный момент не делается проверка текста, то делает запрос к бэкенду для 
получение списка ошибок. Дальше для каждой ошибки создаются оборачивающие теги для 
выделения обнаруженных ошибок.

```javascript
editor.clear();
```

Очищает текстовое поле редактора.

```javascript
editor.highlightOne(el, offset, length, id);
```

Оборачивает нужную часть содержимого редактора для выделения. 
Также при клике на выделение будет показан информационный блок с пояснением.

```javascript
editor.copyText();
```

Копирует текстовое содержимое в буфер обмена. Код метода содержит разные варианты для разных браузеров. 
Если поддержка старых браузеров вам не нужна, метод можно сильно упростить.
