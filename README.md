BBCode Extension for [Mecha](https://github.com/mecha-cms/mecha)
================================================================

![Code Size](https://img.shields.io/github/languages/code-size/mecha-cms/x.b-b-code?color=%23444&style=for-the-badge)

[BBCode](https://www.bbcode.org) is short for Bulletin Board Code. It is used as a way for formatting posts made on message boards, blogs and more. It is similar to HTML in the sense that in BBCode one does also use tags to format something specific (contained within the tag). In BBCode, tags are indicated by rectangular brackets surrounding a keyword, which is in turn transformed into HTML before being delivered to a web browser.

BBCode was implemented as method of providing a safer and easier way of allowing posts to be formatted on forums. Before BBCode forums sometimes allowed users to include HTML code in their posts, which had many security issues (i.e. the user could execute JavaScript code, break the layout of the site and so on). With BBCode being parsed by the forum scripts, it is easier to control what the user can do and can not do (allowing or not allowing specific BBCode tags).

---

Release Notes
-------------

### 2.0.0

 - Updated for Mecha 3.0.0.

### 1.1.0

 - [@mecha-cms/mecha#96](https://github.com/mecha-cms/mecha/issues/96)

### 1.0.2

 - Removed automatic paragraph tags around page description data.

### 1.0.1

 - It is now possible to recurse the `[quote]` elements.
 - Added page type field with a value of `BBCode` for [panel](https://github.com/mecha-cms/x.panel) extension.

### 1.0.0

 - Initial stable release.