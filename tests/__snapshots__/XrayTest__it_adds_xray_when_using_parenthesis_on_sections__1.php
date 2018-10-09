<?php return '<!--XRAY START 1 example2 /var/www/html/tests/views/example2.blade.php-->


<!--XRAY START 1 example2@section:title, trans(main.title /var/www/html/tests/views/example2.blade.php-->

<!--XRAY END 1-->
<!--XRAY START 2 layout2 /var/www/html/tests/views/layout2.blade.php-->
<html>
<head>
	<title>main.title</title>
</head>
<body>
<!--XRAY END 1--><script>var hasProp = {}.hasOwnProperty,
  bind = function(fn, me){ return function(){ return fn.apply(me, arguments); }; };

(function() {
  var $, MAX_ZINDEX, util;
  window.Xray = {};
  if (!($ = window.jQuery)) {
    return;
  }
  MAX_ZINDEX = 2147483647;
  Xray.init = (function() {
    if (Xray.initialized) {
      return;
    }
    Xray.initialized = true;
    $(document).keydown(function(e) {
      if (e.ctrlKey && e.shiftKey && e.which === 88) {
        if (Xray.isShowing) {
          Xray.hide();
        } else {
          Xray.show();
        }
      }
      if (Xray.isShowing && e.which === 27) {
        return Xray.hide();
      }
    });
    return $(function() {
      new Xray.Overlay;
      Xray.findTemplates();
      return typeof console !== "undefined" && console !== null ? console.log("Ready to Xray. Press ctrl+shift+x  to scan your UI.") : void 0;
    });
  })();
  Xray.specimens = function() {
    return Xray.Specimen.all;
  };
  Xray.constructorInfo = function(constructor) {
    var func, info, ref;
    if (window.XrayPaths) {
      ref = window.XrayPaths;
      for (info in ref) {
        if (!hasProp.call(ref, info)) continue;
        func = ref[info];
        if (func === constructor) {
          return JSON.parse(info);
        }
      }
    }
    return null;
  };
  Xray.findTemplates = function() {
    return util.bm(\'findTemplates\', function() {
      var $templateContents, _, comment, comments, el, i, id, len, name, path, ref, results;
      comments = $(\'*:not(iframe,script)\').contents().filter(function() {
        return this.nodeType === 8 && this.data.slice(0, 10) === "XRAY START";
      });
      results = [];
      for (i = 0, len = comments.length; i < len; i++) {
        comment = comments[i];
        ref = comment.data.match(/^XRAY START (\\d+) (.*?) (.*?)$/), _ = ref[0], id = ref[1], name = ref[2], path = ref[3];
        $templateContents = new jQuery;
        el = comment.nextSibling;
        while (!(!el || (el.nodeType === 8 && el.data === ("XRAY END " + id)))) {
          if (el.nodeType === 1 && el.tagName !== \'SCRIPT\') {
            $templateContents.push(el);
          }
          el = el.nextSibling;
        }
        if ((el != null ? el.nodeType : void 0) === 8) {
          el.parentNode.removeChild(el);
        }
        comment.parentNode.removeChild(comment);
        results.push(Xray.Specimen.add($templateContents, {
          name: name,
          path: path
        }));
      }
      return results;
    });
  };
  Xray.show = function() {
    return Xray.Overlay.instance().show();
  };
  Xray.hide = function() {
    return Xray.Overlay.instance().hide();
  };
  Xray.Specimen = (function() {
    Specimen.all = [];

    Specimen.add = function(el, info) {
      if (info == null) {
        info = {};
      }
      return this.all.push(new this(el, info));
    };

    Specimen.remove = function(el) {
      var ref;
      return (ref = this.find(el)) != null ? ref.remove() : void 0;
    };

    Specimen.find = function(el) {
      var i, len, ref, specimen;
      if (el instanceof jQuery) {
        el = el[0];
      }
      ref = this.all;
      for (i = 0, len = ref.length; i < len; i++) {
        specimen = ref[i];
        if (specimen.el === el) {
          return specimen;
        }
      }
      return null;
    };

    Specimen.reset = function() {
      return this.all = [];
    };

    function Specimen(contents, info) {
      if (info == null) {
        info = {};
      }
      this.makeLabel = bind(this.makeLabel, this);
      this.el = contents instanceof jQuery ? contents[0] : contents;
      this.$contents = $(contents);
      this.name = info.name;
      this.path = info.path;
    }

    Specimen.prototype.remove = function() {
      var idx;
      idx = this.constructor.all.indexOf(this);
      if (idx !== -1) {
        return this.constructor.all.splice(idx, 1);
      }
    };

    Specimen.prototype.isVisible = function() {
      return this.$contents.length && this.$contents.is(\':visible\');
    };

    Specimen.prototype.makeBox = function() {
      this.bounds = util.computeBoundingBox(this.$contents);
      this.$box = $("<div class=\'xray-specimen " + this.constructor.name + "\'>").css(this.bounds).attr(\'title\', this.path);
      if (this.$contents.css(\'position\') === \'fixed\') {
        this.$box.css({
          position: \'fixed\',
          top: this.$contents.css(\'top\'),
          left: this.$contents.css(\'left\')
        });
      }
      return this.$box.append(this.makeLabel);
    };

    Specimen.prototype.makeLabel = function() {
      return $("<div class=\'xray-specimen-handle " + this.constructor.name + "\'>").append(this.name);
    };

    return Specimen;

  })();
  Xray.Overlay = (function() {
    Overlay.instance = function() {
      return this.singletonInstance || (this.singletonInstance = new this);
    };

    function Overlay() {
      Xray.Overlay.singletonInstance = this;
      this.bar = new Xray.Bar(\'#xray-bar\');
      this.shownBoxes = [];
      this.$overlay = $(\'<div id="xray-overlay">\');
      this.$overlay.click((function(_this) {
        return function() {
          return _this.hide();
        };
      })(this));
    }

    Overlay.prototype.show = function() {
      this.reset();
      Xray.isShowing = true;
      return util.bm(\'show\', (function(_this) {
        return function() {
          var element, i, len, results, specimens;
          _this.bar.$el().find(\'#xray-bar-togglers .xray-bar-btn\').removeClass(\'active\');
          if (!_this.$overlay.is(\':visible\')) {
            $(\'body\').append(_this.$overlay);
            _this.bar.show();
            Xray.findTemplates();
            specimens = Xray.specimens();
            _this.bar.$el().find(\'.xray-bar-all-toggler\').addClass(\'active\');
            results = [];
            for (i = 0, len = specimens.length; i < len; i++) {
              element = specimens[i];
              if (!element.isVisible()) {
                continue;
              }
              element.makeBox();
              element.$box.css({
                zIndex: Math.ceil(MAX_ZINDEX * 0.9 + element.bounds.top + element.bounds.left)
              });
              _this.shownBoxes.push(element.$box);
              results.push($(\'body\').append(element.$box));
            }
            return results;
          }
        };
      })(this));
    };

    Overlay.prototype.reset = function() {
      var $box, i, len, ref;
      ref = this.shownBoxes;
      for (i = 0, len = ref.length; i < len; i++) {
        $box = ref[i];
        $box.remove();
      }
      return this.shownBoxes = [];
    };

    Overlay.prototype.hide = function() {
      Xray.isShowing = false;
      this.$overlay.detach();
      this.reset();
      return this.bar.hide();
    };

    return Overlay;

  })();
  Xray.Bar = (function() {
    function Bar(el) {
      this.el = el;
    }

    Bar.prototype.$el = function() {
      if ((this.$el_memo != null) && $.contains(window.document, this.$el_memo[0])) {
        return this.$el_memo;
      }
      this.$el_memo = $(this.el);
      this.$el_memo.css({
        zIndex: MAX_ZINDEX
      });
      this.$el_memo.find(\'.xray-bar-all-toggler\').click(function() {
        return Xray.show();
      });
      return this.$el_memo;
    };

    Bar.prototype.show = function() {
      this.$el().show();
      this.originalPadding = parseInt($(\'html\').css(\'padding-bottom\'));
      if (this.originalPadding < 40) {
        return $(\'html\').css({
          paddingBottom: 40
        });
      }
    };

    Bar.prototype.hide = function() {
      this.$el().hide();
      return $(\'html\').css({
        paddingBottom: this.originalPadding
      });
    };

    return Bar;

  })();
  return util = {
    bm: function(name, fn) {
      var result, time;
      time = new Date;
      result = fn();
      return result;
    },
    computeBoundingBox: function($contents) {
      var $el, boxFrame, el, frame, i, len;
      if ($contents.length === 1 && $contents.height() <= 0) {
        return util.computeBoundingBox($contents.children());
      }
      boxFrame = {
        top: Number.POSITIVE_INFINITY,
        left: Number.POSITIVE_INFINITY,
        right: Number.NEGATIVE_INFINITY,
        bottom: Number.NEGATIVE_INFINITY
      };
      for (i = 0, len = $contents.length; i < len; i++) {
        el = $contents[i];
        $el = $(el);
        if (!$el.is(\':visible\')) {
          continue;
        }
        frame = $el.offset();
        frame.right = frame.left + $el.outerWidth();
        frame.bottom = frame.top + $el.outerHeight();
        if (frame.top < boxFrame.top) {
          boxFrame.top = frame.top;
        }
        if (frame.left < boxFrame.left) {
          boxFrame.left = frame.left;
        }
        if (frame.right > boxFrame.right) {
          boxFrame.right = frame.right;
        }
        if (frame.bottom > boxFrame.bottom) {
          boxFrame.bottom = frame.bottom;
        }
      }
      return {
        left: boxFrame.left,
        top: boxFrame.top,
        width: boxFrame.right - boxFrame.left,
        height: boxFrame.bottom - boxFrame.top
      };
    }
  };
})();

// ---
// generated by coffee-script 1.9.2</script><style>@charset "UTF-8";

/* selector for element and children */
#xray-overlay, #xray-overlay *, #xray-overlay a:hover, #xray-overlay a:visited, #xray-overlay a:active,
#xray-bar, #xray-bar *, #xray-bar a:hover, #xray-bar a:visited, #xray-bar a:active {
    background:none;
    border:none;
    bottom:auto;
    clear:none;
    cursor:default;
    float:none;
    font-family:Arial, Helvetica, sans-serif;
    font-size:medium;
    font-style:normal;
    font-weight:normal;
    height:auto;
    left:auto;
    letter-spacing:normal;
    line-height:normal;
    max-height:none;
    max-width:none;
    min-height:0;
    min-width:0;
    overflow:visible;
    position:static;
    right:auto;
    text-align:left;
    text-decoration:none;
    text-indent:0;
    text-transform:none;
    top:auto;
    visibility:visible;
    white-space:normal;
    width:auto;
    z-index:auto;
}

#xray-overlay {
    position: fixed; left: 0; top: 0; bottom: 0; right: 0;
    background: rgba(0,0,0,0.7);
    background: -webkit-radial-gradient(center, ellipse cover, rgba(0,0,0,0.4) 10%, rgba(0,0,0,0.8) 100%);
    z-index: 9000;
}

.xray-specimen {
    position: absolute;
    background: rgba(255,50,50,0.1);
    outline: 1px solid rgba(255,50,50,0.8);
    outline-offset: -1px;
    color: #666;
    font-family: "Helvetica Neue", sans-serif;
    font-size: 13px;
    box-shadow: 0 1px 3px rgba(0,0,0,0.7);
}

.xray-specimen:hover {
    cursor: pointer;
    background: rgba(255,50,50,0.4);
}

.xray-specimen-handle {
    float:left;
    background: #fff;
    padding: 0 3px;
    color: #333;
    font-size: 10px;
}

.xray-specimen-handle.TemplateSpecimen {
    background: rgba(255,50,50,0.8);
    color: #fff;
}

#xray-bar {
    position: fixed;
    left: 0;
    right: 0;
    bottom: 0;
    height: 40px;
    padding: 0 8px;
    background: #222;
    font-family: "Helvetica Neue", Helvetica, Arial, sans-serif;
    font-weight: 200;
    color: #fff;
    z-index: 10000;
    box-shadow: 0 -1px 0 rgba(255,255,255,0.1), inset 0 2px 6px rgba(0,0,0,0.8);
    background-image: linear-gradient(rgba(0,0,0,0), rgba(0,0,0,0.3)),
    url(data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAoAAAAKCAIAAAACUFjqAAAAGXRFWHRTb2Z0d2FyZQBBZG9iZSBJbWFnZVJlYWR5ccllPAAAAyRpVFh0WE1MOmNvbS5hZG9iZS54bXAAAAAAADw/eHBhY2tldCBiZWdpbj0i77u/IiBpZD0iVzVNME1wQ2VoaUh6cmVTek5UY3prYzlkIj8+IDx4OnhtcG1ldGEgeG1sbnM6eD0iYWRvYmU6bnM6bWV0YS8iIHg6eG1wdGs9IkFkb2JlIFhNUCBDb3JlIDUuMy1jMDExIDY2LjE0NTY2MSwgMjAxMi8wMi8wNi0xNDo1NjoyNyAgICAgICAgIj4gPHJkZjpSREYgeG1sbnM6cmRmPSJodHRwOi8vd3d3LnczLm9yZy8xOTk5LzAyLzIyLXJkZi1zeW50YXgtbnMjIj4gPHJkZjpEZXNjcmlwdGlvbiByZGY6YWJvdXQ9IiIgeG1sbnM6eG1wPSJodHRwOi8vbnMuYWRvYmUuY29tL3hhcC8xLjAvIiB4bWxuczp4bXBNTT0iaHR0cDovL25zLmFkb2JlLmNvbS94YXAvMS4wL21tLyIgeG1sbnM6c3RSZWY9Imh0dHA6Ly9ucy5hZG9iZS5jb20veGFwLzEuMC9zVHlwZS9SZXNvdXJjZVJlZiMiIHhtcDpDcmVhdG9yVG9vbD0iQWRvYmUgUGhvdG9zaG9wIENTNiAoTWFjaW50b3NoKSIgeG1wTU06SW5zdGFuY2VJRD0ieG1wLmlpZDpDRkNBMTUwNzdGRTIxMUUyQjBGQ0NBRTc5RDQ3MEJFNSIgeG1wTU06RG9jdW1lbnRJRD0ieG1wLmRpZDpDRkNBMTUwODdGRTIxMUUyQjBGQ0NBRTc5RDQ3MEJFNSI+IDx4bXBNTTpEZXJpdmVkRnJvbSBzdFJlZjppbnN0YW5jZUlEPSJ4bXAuaWlkOkNGQ0ExNTA1N0ZFMjExRTJCMEZDQ0FFNzlENDcwQkU1IiBzdFJlZjpkb2N1bWVudElEPSJ4bXAuZGlkOkNGQ0ExNTA2N0ZFMjExRTJCMEZDQ0FFNzlENDcwQkU1Ii8+IDwvcmRmOkRlc2NyaXB0aW9uPiA8L3JkZjpSREY+IDwveDp4bXBtZXRhPiA8P3hwYWNrZXQgZW5kPSJyIj8+aIv7XwAAAEVJREFUeNpiFBMTY2BgEBISYsAGWICAATdgkZeXB1Lv37/HLo1LAgKYGPACdOl3YIAwHNOpKFw0aTSXokujuZSA0wACDABh2BIyJ1wQkwAAAABJRU5ErkJggg==);
}

@media (-webkit-min-device-pixel-ratio: 2), (min-resolution: 192dppx) {
    #xray-bar {
        background-image: linear-gradient(rgba(0,0,0,0), rgba(0,0,0,0.3)),
        url(data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAABQAAAAUCAIAAAAC64paAAAAGXRFWHRTb2Z0d2FyZQBBZG9iZSBJbWFnZVJlYWR5ccllPAAAAyRpVFh0WE1MOmNvbS5hZG9iZS54bXAAAAAAADw/eHBhY2tldCBiZWdpbj0i77u/IiBpZD0iVzVNME1wQ2VoaUh6cmVTek5UY3prYzlkIj8+IDx4OnhtcG1ldGEgeG1sbnM6eD0iYWRvYmU6bnM6bWV0YS8iIHg6eG1wdGs9IkFkb2JlIFhNUCBDb3JlIDUuMy1jMDExIDY2LjE0NTY2MSwgMjAxMi8wMi8wNi0xNDo1NjoyNyAgICAgICAgIj4gPHJkZjpSREYgeG1sbnM6cmRmPSJodHRwOi8vd3d3LnczLm9yZy8xOTk5LzAyLzIyLXJkZi1zeW50YXgtbnMjIj4gPHJkZjpEZXNjcmlwdGlvbiByZGY6YWJvdXQ9IiIgeG1sbnM6eG1wPSJodHRwOi8vbnMuYWRvYmUuY29tL3hhcC8xLjAvIiB4bWxuczp4bXBNTT0iaHR0cDovL25zLmFkb2JlLmNvbS94YXAvMS4wL21tLyIgeG1sbnM6c3RSZWY9Imh0dHA6Ly9ucy5hZG9iZS5jb20veGFwLzEuMC9zVHlwZS9SZXNvdXJjZVJlZiMiIHhtcDpDcmVhdG9yVG9vbD0iQWRvYmUgUGhvdG9zaG9wIENTNiAoTWFjaW50b3NoKSIgeG1wTU06SW5zdGFuY2VJRD0ieG1wLmlpZDo0Q0ZGQkRGRTdGRTMxMUUyQjBGQ0NBRTc5RDQ3MEJFNSIgeG1wTU06RG9jdW1lbnRJRD0ieG1wLmRpZDo0Q0ZGQkRGRjdGRTMxMUUyQjBGQ0NBRTc5RDQ3MEJFNSI+IDx4bXBNTTpEZXJpdmVkRnJvbSBzdFJlZjppbnN0YW5jZUlEPSJ4bXAuaWlkOkNGQ0ExNTA5N0ZFMjExRTJCMEZDQ0FFNzlENDcwQkU1IiBzdFJlZjpkb2N1bWVudElEPSJ4bXAuZGlkOkNGQ0ExNTBBN0ZFMjExRTJCMEZDQ0FFNzlENDcwQkU1Ii8+IDwvcmRmOkRlc2NyaXB0aW9uPiA8L3JkZjpSREY+IDwveDp4bXBtZXRhPiA8P3hwYWNrZXQgZW5kPSJyIj8+T6y4zwAAAIZJREFUeNrkkjEKwCAMRWMbERw8gE5O3v9q6lIEbbDQMZJ2Kv0gBOSR8HkqxphzBgDnnDEGJNkRsfc+xmitWWtF8KaUuqZ7EMDee1qutQ4hSGGkl1Kis2utYviYgUfZ4EU+CiP/TV0y/i02l1L6DA3is3n/FjDvHy5bYfxbF8b490fDTgEGAJveOCvuYEabAAAAAElFTkSuQmCC);
        background-size: auto, 10px 10px;
    }
}

#xray-bar .xray-bar-btn {
    position: relative;
    color: #fff;
    margin: 8px 1px;
    height: 24px;
    line-height: 24px;
    padding: 0 8px;
    float: left;
    font-size: 14px;
    cursor: pointer;
    vertical-align: middle;
    background-color: #444;
    background-image: linear-gradient(rgba(0,0,0,0), rgba(0,0,0,0.2));
    border-radius: 2px;
    box-shadow: 1px 1px 1px rgba(0,0,0,0.5),
    inset 0 1px 0 rgba(255, 255, 255, 0.2),
    inset 0 0 2px rgba(255, 255, 255, 0.2);
    text-shadow: 0 -1px 0 rgba(0,0,0,0.4);
    transition: background-color 0.1s;
}

#xray-bar .xray-bar-btn b {
    position: absolute;
    display: block;
    right: -19px;
    top: 0;
    width: 20px;
    height: 24px;
    z-index: 10;
    overflow: hidden;
    font-size: 44px;
    line-height: 19px;
    text-indent: -7px;
}

#xray-bar .xray-bar-btn b:before {
    content: "";
    width: 18px;
    height: 18px;
    display: block;
    position: absolute;
    left: -9px;
    top: 3px;
    border-radius: 2px;
    box-shadow: 1px -1px 1px rgba(0,0,0,0.5), inset 0 1px 0 rgba(255, 255, 255, 0.2), inset 0 0 2px rgba(255, 255, 255, 0.2);
    background-image: linear-gradient(135deg, rgba(0,0,0,0), rgba(0,0,0,0.2));
    -webkit-transform: rotate(45deg);
    transform: rotate(45deg);
    transition: background-color 0.1s;
}

#xray-bar .xray-bar-btn:hover {
    background-color: #555;
}

#xray-bar #xray-bar-controller-path {
    margin-right: 20px;
}

#xray-bar #xray-bar-controller-path .xray-bar-btn {
    border-radius: 2px 0 0 2px;
    padding: 0 6px 0 16px;
    margin: 8px 1px 8px 0;
}

#xray-bar #xray-bar-controller-path .xray-bar-btn:first-child {
    padding-left: 8px;
}

#xray-bar #xray-bar-controller-path .xray-bar-btn:last-child {
    border-radius: 2px;
    padding-right: 10px;
}

#xray-bar-controller-path .xray-bar-controller { padding-left: 6px; }
#xray-bar-controller-path .xray-bar-controller,
#xray-bar-controller-path .xray-bar-controller b:before { background-color: #444; }
#xray-bar-controller-path .xray-bar-controller:hover,
#xray-bar-controller-path .xray-bar-controller:hover b:before { background-color: #555; }
#xray-bar-controller-path .xray-bar-controller-action { color: #ddd; }
#xray-bar-controller-path .xray-bar-layout,
#xray-bar-controller-path .xray-bar-layout b:before { background-color: #c12e27; }
#xray-bar-controller-path .xray-bar-layout:hover,
#xray-bar-controller-path .xray-bar-layout:hover b:before { background-color: #de362d; }
#xray-bar-controller-path .xray-bar-view,
#xray-bar-controller-path .xray-bar-view b:before { background-color: #ff2c1e; }
#xray-bar-controller-path .xray-bar-view:hover,
#xray-bar-controller-path .xray-bar-view:hover b:before { background-color: #ff4c36; }

#xray-bar #xray-bar-togglers {
    float: left;
    margin-left: 20px;
}

#xray-bar #xray-bar-togglers .xray-bar-btn {
    border-radius: 0;
    margin-right: 0;
    color: #999;
}

#xray-bar #xray-bar-togglers .xray-bar-btn:first-child {
    border-radius: 2px 0 0 2px;
}

#xray-bar #xray-bar-togglers .xray-bar-btn:last-child {
    border-radius: 0 2px 2px 0;
}

#xray-bar #xray-bar-togglers .xray-bar-btn:before {
    font-size: 9px;
    vertical-align: middle;
    margin-bottom: 1px;
    margin-right: 5px;
    background: rgba(255,255,255,0.2);
    color: #eee;
    padding: 2px 4px;
    text-shadow: none;
}

#xray-bar #xray-bar-togglers .xray-bar-btn.active {
    background: #555;
    color: #fff;
}

#xray-bar #xray-bar-togglers .xray-bar-templates-toggler:before { content: \'HTML\'; }
#xray-bar #xray-bar-togglers .xray-bar-templates-toggler.active:before { background: red; }
#xray-bar #xray-bar-togglers .xray-bar-views-toggler:before { content: \'JS\'; }
#xray-bar #xray-bar-togglers .xray-bar-views-toggler.active:before { background: #fff; color: #333; }
#xray-bar #xray-bar-togglers .xray-bar-styles-toggler:before { content: \'CSS\'; }

#xray-bar #xray-bar-togglers .xray-icon-search:before {
    font-size: 16px;
    background: none;
    padding: 0;
    margin: 0;
}

#xray-bar .xray-bar-settings-btn {
    position: absolute;
    right: 10px;
    top: 10px;
    color: #666;
    cursor: pointer;
    text-shadow: 0 1px 0 #000;
    font-size: 16px;
    -webkit-touch-callout: none;
    -webkit-user-select: none;
    -khtml-user-select: none;
    -moz-user-select: none;
    -ms-user-select: none;
    user-select: none;
}

#xray-bar .xray-bar-settings-btn:hover {
    color: #fff;
}


@font-face {
    font-family: \'xray-icons\';
    src: url("data:application/octet-stream;base64,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") format(\'woff\'), url("data:application/octet-stream;base64,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JWBkI7ADJWBkUFghGyMhWSMgILAEJiNGYThZLbAwLLAAFiAgILAFJiAuRyNHI2EjPDgtsDEssAAWILAJI0IgICBGI0ewACsjYTgtsDIssAAWsAMlsAIlRyNHI2GwAFRYLiA8IyEbsAIlsAIlRyNHI2EgsAUlsAQlRyNHI2GwBiWwBSVJsAIlYbABRWMjIFhiGyFZY7ABRWJgIy4jICA8ijgjIVktsDMssAAWILAJQyAuRyNHI2EgYLAgYGawgGIjICA8ijgtsDQsIyAuRrACJUZSWCA8WS6xJAEUKy2wNSwjIC5GsAIlRlBYIDxZLrEkARQrLbA2LCMgLkawAiVGUlggPFkjIC5GsAIlRlBYIDxZLrEkARQrLbA3LLAuKyMgLkawAiVGUlggPFkusSQBFCstsDgssC8riiAgPLAEI0KKOCMgLkawAiVGUlggPFkusSQBFCuwBEMusCQrLbA5LLAAFrAEJbAEJiAuRyNHI2GwBkUrIyA8IC4jOLEkARQrLbA6LLEJBCVCsAAWsAQlsAQlIC5HI0cjYSCwBCNCsAZFKyCwYFBYILBAUVizAiADIBuzAiYDGllCQiMgR7AEQ7CAYmAgsAArIIqKYSCwAkNgZCOwA0NhZFBYsAJDYRuwA0NgWbADJbCAYmGwAiVGYTgjIDwjOBshICBGI0ewACsjYTghWbEkARQrLbA7LLAuKy6xJAEUKy2wPCywLyshIyAgPLAEI0IjOLEkARQrsARDLrAkKy2wPSywABUgR7AAI0KyAAEBFRQTLrAqKi2wPiywABUgR7AAI0KyAAEBFRQTLrAqKi2wPyyxAAEUE7ArKi2wQCywLSotsEEssAAWRSMgLiBGiiNhOLEkARQrLbBCLLAJI0KwQSstsEMssgAAOistsEQssgABOistsEUssgEAOistsEYssgEBOistsEcssgAAOystsEgssgABOystsEkssgEAOystsEossgEBOystsEsssgAANystsEwssgABNystsE0ssgEANystsE4ssgEBNystsE8ssgAAOSstsFAssgABOSstsFEssgEAOSstsFIssgEBOSstsFMssgAAPCstsFQssgABPCstsFUssgEAPCstsFYssgEBPCstsFcssgAAOCstsFgssgABOCstsFkssgEAOCstsFossgEBOCstsFsssDArLrEkARQrLbBcLLAwK7A0Ky2wXSywMCuwNSstsF4ssAAWsDArsDYrLbBfLLAxKy6xJAEUKy2wYCywMSuwNCstsGEssDErsDUrLbBiLLAxK7A2Ky2wYyywMisusSQBFCstsGQssDIrsDQrLbBlLLAyK7A1Ky2wZiywMiuwNistsGcssDMrLrEkARQrLbBoLLAzK7A0Ky2waSywMyuwNSstsGossDMrsDYrLbBrLCuwCGWwAyRQeLABFTAtAABLuADIUlixAQGOWbkIAAgAYyCwASNEILADI3CyBCgJRVJEswoLBgQrsQYBRLEkAYhRWLBAiFixBgNEsSYBiFFYuAQAiFixBgFEWVlZWbgB/4WwBI2xBQBE") format(\'truetype\');
}

[class^="xray-icon-"]:before,
[class*=" xray-icon-"]:before {
    font-family: \'xray-icons\';
    font-style: normal;
    font-weight: normal;
    speak: none;
    display: inline-block;
    text-decoration: inherit;
    width: 1em;
    margin-right: 0.1em;
    text-align: center;
    line-height: 1em;
}

.xray-icon-cog:before { content: \'\\e800\'; } /* \'\' */
.xray-icon-flash:before { content: \'\\26a1\'; } /* \'⚡\' */
.xray-icon-cancel:before { content: \'\\2715\'; } /* \'✕\' */
.xray-icon-github:before { content: \'\\f056\'; } /* \'\' */
.xray-icon-columns:before { content: \'\\f0db\'; } /* \'\' */
.xray-icon-doc:before { content: \'📄\'; } /* \'\\1f4c4\' */
.xray-icon-search:before { content: \'🔍\'; } /* \'\\1f50d\' */</style><!--XRAY START 3 xray::xray /var/www/html/src/../resources/views/xray.blade.php-->
<div id="xray-bar" style="display:none">
    <div id="xray-bar-controller-path">
        <span class="xray-bar-btn xray-bar-controller xray-icon-flash">
            <b></b>
            -
        </span>
        <span class="xray-bar-btn xray-bar-layout xray-icon-columns">
            <b></b>
            /var/www/html/tests/views/example2.blade.php
        </span>
        <span class="xray-bar-btn xray-bar-view xray-icon-doc">
            example2
        </span>
    </div>
</div>
<!--XRAY END 3--></body>
</html>
<!--XRAY END 2-->';
