<?php
    $fields = get_fields($module->ID);
    $post_title = $module->post_title;
 ?>

<h2 class=""><?php echo $post_title; ?></h2>
<div class="flowbox-content"></div>

<script>
  function ready(fn) {
    if (document.readyState != 'loading'){
      fn();
    } else {
      document.addEventListener('DOMContentLoaded', fn);
    }
  }

  ready(function() { 
    document.querySelectorAll('a.page-numbers').forEach(function(a) {
      a.href = a.href.replace('s&', '');
    });

    (function(d, id) {
      if (!window.flowbox) { var f = function () { f.q.push(arguments); }; f.q = []; window.flowbox = f; }
      if (d.getElementById(id)) {return;}
      var s = d.createElement('script'), fjs = d.scripts[d.scripts.length - 1]; s.id = id; s.async = true;
      s.src = ' https://connect.getflowbox.com/flowbox.js';
      fjs.parentNode.insertBefore(s, fjs);
    })(document, 'flowbox-js-embed');
    
    window.flowbox('init', {
      container: '.flowbox-content',
      key: '<?php echo $fields['flowbox_key']; ?>',
      locale: 'sv-SE',
    });
  });
</script>