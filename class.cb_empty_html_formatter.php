<?php

Cb::import("CbHtmlFormatter");

/**
 * HTML formatter that doesn't include any content. This is useful for pages
 * which shall be completely created by Javascript. You can set a cookie in the
 * client and depending on its value have the resource handler instruct the
 * provider to use this formatter. This will output an HTML document with only
 * Javascript and an empty body. Don't forget to state 'vary' => 'Cookie' in the
 * meta() method then.
 */
class CbEmptyHtmlFormatter extends CbHtmlFormatter {
   function content() {
      return '';
   }
}