do ->
  window.Xray = {}
  return unless $ = window.jQuery

  # Max CSS z-index. The overlay and xray bar use this.
  MAX_ZINDEX = 2147483647

  # Initialize Xray. Called immediately, but some setup is deferred until DOM ready.
  Xray.init = do ->
    return if Xray.initialized
    Xray.initialized = true

    # Register keyboard shortcuts
    $(document).keydown (e) ->
      if e.ctrlKey and e.shiftKey and e.which is 88
        if Xray.isShowing then Xray.hide() else Xray.show()
      if Xray.isShowing and e.which is 27 # esc
        Xray.hide()

    $ ->
  # Instantiate the overlay singleton.
      new Xray.Overlay
      # Go ahead and do a pass on the DOM to find templates.
      Xray.findTemplates()
      # Ready to rock.
      console?.log "Ready to Xray. Press ctrl+shift+x to scan your UI."

  # Returns all currently created Xray.Specimen objects.
  Xray.specimens = ->
    Xray.Specimen.all

  # Looks up the stored constructor info
  Xray.constructorInfo = (constructor) ->
    if window.XrayPaths
      for own info, func of window.XrayPaths
        return JSON.parse(info) if func == constructor
    null

  # Scans the document for templates, creating Xray.Specimens for them.
  Xray.findTemplates = -> util.bm 'findTemplates', ->
  # Find all <!-- XRAY START ... --> comments
    comments = $('*:not(iframe,script)').contents().filter ->
      this.nodeType == 8 and this.data[0..9] == "XRAY START"

    # Find the <!-- XRAY END ... --> comment for each. Everything between the
    # start and end comment becomes the contents of an Xray.Specimen.
    for comment in comments
      [_, id, name, path] = comment.data.match(/^XRAY START (\d+) (.*?) (.*?)$/)
      $templateContents = new jQuery
      el = comment.nextSibling
      until !el or (el.nodeType == 8 and el.data == "XRAY END #{id}")
        if el.nodeType == 1 and el.tagName != 'SCRIPT'
          $templateContents.push el
        el = el.nextSibling
      # Remove XRAY template comments from the DOM.
      el.parentNode.removeChild(el) if el?.nodeType == 8
      comment.parentNode.removeChild(comment)
      # Add the specimen
      Xray.Specimen.add $templateContents,
        name: name
        path: path

  # Show the Xray overlay
  Xray.show = ->
    Xray.Overlay.instance().show()

  # Hide the Xray overlay
  Xray.hide = ->
    Xray.Overlay.instance().hide()

  # Wraps a DOM element that Xray is tracking.
  class Xray.Specimen
    @all = []

    @add: (el, info = {}) ->
      @all.push new this(el, info)

    @remove: (el) ->
      @find(el)?.remove()

    @find: (el) ->
      el = el[0] if el instanceof jQuery
      for specimen in @all
        return specimen if specimen.el == el
      null

    @reset: ->
      @all = []

    constructor: (contents, info = {}) ->
      @el = if contents instanceof jQuery then contents[0] else contents
      @$contents = $(contents)
      @name = info.name
      @path = info.path

    remove: ->
      idx = @constructor.all.indexOf(this)
      @constructor.all.splice(idx, 1) unless idx == -1

    isVisible: ->
      @$contents.length and @$contents.is(':visible')

    makeBox: ->
      @bounds = util.computeBoundingBox(@$contents)
      @$box = $("<div class='xray-specimen #{@constructor.name}'>").css(@bounds).attr('title', @path)

      # If the element is fixed, override the computed position with the fixed one.
      if @$contents.css('position') == 'fixed'
        @$box.css
          position : 'fixed'
          top      : @$contents.css('top')
          left     : @$contents.css('left')

      @$box.append @makeLabel

    makeLabel: =>
      $("<div class='xray-specimen-handle #{@constructor.name}'>").append(@name)

  # Singleton class for the Xray "overlay" invoked by the keyboard shortcut
  class Xray.Overlay
    @instance: ->
      @singletonInstance ||= new this

    constructor: ->
      Xray.Overlay.singletonInstance = this
      @bar = new Xray.Bar('#xray-bar')
      @shownBoxes = []
      @$overlay = $('<div id="xray-overlay">')
      @$overlay.click => @hide()

    show: ->
      @reset()
      Xray.isShowing = true
      util.bm 'show', =>
        @bar.$el().find('#xray-bar-togglers .xray-bar-btn').removeClass('active')
        unless @$overlay.is(':visible')
          $('body').append @$overlay
          @bar.show()
          Xray.findTemplates()
          specimens = Xray.specimens()
          @bar.$el().find('.xray-bar-all-toggler').addClass('active')
          for element in specimens
            continue unless element.isVisible()
            element.makeBox()
            # A cheap way to "order" the boxes, where boxes positioned closer to the
            # bottom right of the document have a higher z-index.
            element.$box.css
              zIndex: Math.ceil(MAX_ZINDEX*0.9 + element.bounds.top + element.bounds.left)
            @shownBoxes.push element.$box
            $('body').append element.$box

    reset: ->
      $box.remove() for $box in @shownBoxes
      @shownBoxes = []

    hide: ->
      Xray.isShowing = false
      @$overlay.detach()
      @reset()
      @bar.hide()


  # The Xray bar shows controller, action, and view information, and has
  # toggle buttons for showing the different types of specimens in the overlay.
  class Xray.Bar
    constructor: (el) ->
      @el = el

  # Defer wiring up jQuery event handlers until needed and then memoize the
  # result. If the Bar element no longer exists in the DOM, re-wire it.
  # This allows the Bar to keep working even if e.g. Turbolinks replaces the
  # DOM out from under us.
    $el: ->
      return @$el_memo if @$el_memo? && $.contains(window.document, @$el_memo[0])
      @$el_memo = $(@el)
      @$el_memo.css(zIndex: MAX_ZINDEX)
      @$el_memo.find('.xray-bar-all-toggler').click       -> Xray.show()
      @$el_memo

    show: ->
      @$el().show()
      @originalPadding = parseInt $('html').css('padding-bottom')
      if @originalPadding < 40
        $('html').css paddingBottom: 40

    hide: ->
      @$el().hide()
      $('html').css paddingBottom: @originalPadding

  # Utility methods.
  util =
  # Benchmark a piece of code
    bm: (name, fn) ->
      time = new Date
      result = fn()
      # console.log "#{name} : #{new Date() - time}ms"
      result

  # Computes the bounding box of a jQuery set, which may be many sibling
  # elements with no parent in the set.
    computeBoundingBox: ($contents) ->
  # Edge case: the container may not physically wrap its children, for
  # example if they are floated and no clearfix is present.
      if $contents.length == 1 and $contents.height() <= 0
        return util.computeBoundingBox($contents.children())

      boxFrame =
        top    : Number.POSITIVE_INFINITY
        left   : Number.POSITIVE_INFINITY
        right  : Number.NEGATIVE_INFINITY
        bottom : Number.NEGATIVE_INFINITY

      for el in $contents
        $el = $(el)
        continue unless $el.is(':visible')
        frame = $el.offset()
        frame.right  = frame.left + $el.outerWidth()
        frame.bottom = frame.top + $el.outerHeight()
        boxFrame.top    = frame.top if frame.top < boxFrame.top
        boxFrame.left   = frame.left if frame.left < boxFrame.left
        boxFrame.right  = frame.right if frame.right > boxFrame.right
        boxFrame.bottom = frame.bottom if frame.bottom > boxFrame.bottom

      return {
        left   : boxFrame.left
        top    : boxFrame.top
        width  : boxFrame.right - boxFrame.left
        height : boxFrame.bottom - boxFrame.top
      }