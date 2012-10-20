<!DOCTYPE html>
<html>
  <head>
    <title></title>
    <meta http-equiv="Refresh" content="5; URL='?<?php echo $this->nojs;?>'"/><!-- refresh page after some time to indicate "no JS" -->
    <script type="text/javascript" src="/module/jscript/lib/modernizr/modernizr.js"></script>
  </head>
  <body>
     <form action="" method="POST" id="detect-form">
        <input type="hidden" name="width" id="width-val" />
        <input type="hidden" name="height" id="height-val" />
        <input type="hidden" name="webkitpoint" id="web-kit-point-val" />
        <input type="hidden" name="eventlistener" id="event-listener" />
        <input type="hidden" name="contextualfragment" id="contextual-fragment" />
        <input type="hidden" name="javascript" value="true" />
     </form>
     <script type="text/javascript">
        var form = document.getElementById('detect-form');
        var addField = function(prefix, test, value) {
           var el = document.createElement('input');
           el.name = prefix + test;
           el.type = "hidden";
           el.value = value;
           form.appendChild(el);
        };

        if ((document.cookie.match('(^|; )<?php echo $this->session_name;?>=([^;]*)') || 0)[2] !== 'running') {
           form.action = "?<?php echo $this->nocookies;?>";
           addField('', 'cookies', false);
        }

        function parseModernizr(parent, prefix) {
           for (var test in parent) {
              if (parent.hasOwnProperty(test)) {
                 if (test.charAt(0) == '_') continue;
                 var type = typeof parent[test];
                 if (type == 'string' || type == 'boolean' || type == 'number') {
                    addField(prefix, test, parent[test]);
                 } else if (type == 'object') {
                    parseModernizr(parent[test], prefix + test + '_')
                 }
              }
           }
        }

        document.getElementById("width-val").value = screen.width;
        document.getElementById("height-val").value = screen.height;
        document.getElementById("web-kit-point-val").value = ('WebKitPoint' in window);
        document.getElementById("event-listener").value = ('addEventListener' in window && 'removeEventListener' in window);
        document.getElementById("contextual-fragment").value = (typeof Range !== "undefined" && 'createContextualFragment' in Range.prototype);
        parseModernizr(Modernizr, '');

        form.submit();
    </script>
  </body>
</html>
