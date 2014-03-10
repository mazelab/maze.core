/*
 * Jeditable - jQuery in place edit plugin
 *
 * Copyright (c) 2006-2009 Mika Tuupola, Dylan Verheul
 *
 * Licensed under the MIT license:
 *   http://www.opensource.org/licenses/mit-license.php
 *
 * Project home:
 *   http://www.appelsiini.net/projects/jeditable
 *
 * Based on editable by Dylan Verheul <dylan_at_dyve.net>:
 *    http://www.dyve.net/jquery/?editable
 *
 */

/**
  * Version 1.7.2-dev
  *
  * ** means there is basic unit tests for this parameter. 
  *
  * @name  Jeditable
  * @type  jQuery
  * @param String  target             (POST) URL or function to send edited content to **
  * @param Hash    options            additional options 
  * @param String  options[method]    method to use to send edited content (POST or PUT) **
  * @param Function options[callback] Function to run after submitting edited content **
  * @param String  options[name]      POST parameter name of edited content
  * @param String  options[id]        POST parameter name of edited div id
  * @param Hash    options[submitdata] Extra parameters to send when submitting edited content.
  * @param String  options[type]      text, textarea or select (or any 3rd party input type) **
  * @param Integer options[rows]      number of rows if using textarea ** 
  * @param Integer options[cols]      number of columns if using textarea **
  * @param Mixed   options[height]    'auto', 'none' or height in pixels **
  * @param Mixed   options[width]     'auto', 'none' or width in pixels **
  * @param String  options[loadurl]   URL to fetch input content before editing **
  * @param String  options[loadtype]  Request type for load url. Should be GET or POST.
  * @param String  options[loadtext]  Text to display while loading external content.
  * @param Mixed   options[loaddata]  Extra parameters to pass when fetching content before editing.
  * @param Mixed   options[data]      Or content given as paramameter. String or function.**
  * @param String  options[indicator] indicator html to show when saving
  * @param String  options[tooltip]   optional tooltip text via title attribute **
  * @param String  options[event]     jQuery event such as 'click' of 'dblclick' **
  * @param String  options[submit]    submit button value, empty means no button **
  * @param String  options[cancel]    cancel button value, empty means no button **
  * @param String  options[cssclass]  CSS class to apply to input form. 'inherit' to copy from parent. **
  * @param String  options[cssclassplaceholder]  CSS class to apply to input placeholder form. **
  * @param String  options[style]     Style to apply to input form 'inherit' to copy from parent. **
  * @param String  options[select]    true or false, when true text is highlighted ??
  * @param String  options[placeholder] Placeholder text or html to insert when element is empty. **
  * @param String  options[onblur]    'cancel', 'submit', 'ignore' or function ??
  *             
  * @param Function options[onsubmit] function(settings, original) { ... } called before submit
  * @param Function options[onreset]  function(settings, original) { ... } called before reset
  * @param Function options[onerror]  function(settings, original, xhr) { ... } called on error
  *             
  * @param Hash    options[ajaxoptions]  jQuery Ajax options. See docs.jquery.com.
  *             
  */

(function($) {

    $.fn.editable = function(target, options) {
            
        if ('disable' == target) {
            $(this).data('disabled.editable', true);
            return;
        }
        if ('enable' == target) {
            $(this).data('disabled.editable', false);
            return;
        }
        if ('destroy' == target) {
            $(this)
            .unbind($(this).data('event.editable'))
            .removeData('disabled.editable')
            .removeData('event.editable');
            return;
        }
        
        var settings = $.extend({}, $.fn.editable.defaults, {target:target}, options);
        
        /* setup some functions */
        var plugin   = $.editable.types[settings.type].plugin || function() { };
        var submit   = $.editable.types[settings.type].submit || function() { };
        var buttons  = $.editable.types[settings.type].buttons 
        || $.editable.types['defaults'].buttons;
        var content  = $.editable.types[settings.type].content 
        || $.editable.types['defaults'].content;
        var element  = $.editable.types[settings.type].element 
        || $.editable.types['defaults'].element;
        var reset    = $.editable.types[settings.type].reset 
        || $.editable.types['defaults'].reset;
        var callback = settings.callback || function() { };
        var onedit   = settings.onedit   || function() { }; 
        var onsubmit = settings.onsubmit || function() { };
        var onreset  = settings.onreset  || function() { };
        var onerror  = settings.onerror  || reset;
          
        /* Show tooltip. */
        if (settings.tooltip) {
            $(this).attr('title', settings.tooltip);
        }
        
        settings.autowidth  = 'auto' == settings.width;
        settings.autoheight = 'auto' == settings.height;
        
        return this.each(function() {
                        
            /* Save this to self because this changes when scope changes. */
            var self = this;  
                   
            /* Inlined block elements lose their width and height after first edit. */
            /* Save them for later use as workaround. */
            var savedwidth  = $(self).width();
            var savedheight = $(self).height();

            /* Save so it can be later used by $.editable('destroy') */
            $(this).data('event.editable', settings.event);
            
            /* If element is empty add something clickable (if requested) */
            if (!$.trim($(this).html())) {
                if(settings.cssclassplaceholder) {
                    $(this).addClass(settings.cssclassplaceholder);
                }
                $(this).html(settings.placeholder);
            }
            
            $(this).bind(settings.event, function(e) {
                
                /* Abort if element is disabled. */
                if (true === $(this).data('disabled.editable')) {
                    return;
                }
                
                /* Prevent throwing an exeption if edit field is clicked again. */
                if (self.editing) {
                    return;
                }
                
                /* Abort if onedit hook returns false. */
                if (false === onedit.apply(this, [settings, self])) {
                    return;
                }
                
                /* Prevent default action and bubbling. */
                e.preventDefault();
                e.stopPropagation();
                
                /* Remove tooltip. */
                if (settings.tooltip) {
                    $(self).removeAttr('title');
                }
                
                /* Figure out how wide and tall we are, saved width and height. */
                /* Workaround for http://dev.jquery.com/ticket/2190 */
                if (0 == $(self).width() && (settings.width != 'none' && settings.height != 'none')) {
                    settings.width  = savedwidth;
                    settings.height = savedheight;
                } else {
                    if (settings.width != 'none') {
                        settings.width = 
                        settings.autowidth ? $(self).width()  : settings.width;
                    }
                    if (settings.height != 'none') {
                        settings.height = 
                        settings.autoheight ? $(self).height() : settings.height;
                    }
                }
                
                /* Remove placeholder text, replace is here because of IE. */
                if ($(this).html().toLowerCase().replace(/(;|"|\/)/g, '') == 
                    settings.placeholder.toLowerCase().replace(/(;|"|\/)/g, '')) {
                    $(this).html('');
                }
                                
                self.editing    = true;
                self.revert     = $(self).html();
                $(self).html('');

                /* Create the form object. */
                var form = $('<form />');
                
                /* Apply css or style or both. */
                if (settings.cssclass) {
                    if ('inherit' == settings.cssclass) {
                        form.attr('class', $(self).attr('class'));
                    } else {
                        form.attr('class', settings.cssclass);
                    }
                }

                if (settings.style) {
                    if ('inherit' == settings.style) {
                        form.attr('style', $(self).attr('style'));
                        /* IE needs the second line or display wont be inherited. */
                        form.css('display', $(self).css('display'));                
                    } else {
                        form.attr('style', settings.style);
                    }
                }

                /* Add main input element to form and store it in input. */
                var input = element.apply(form, [settings, self]);

                /* Set input content via POST, GET, given data or existing value. */
                var input_content;
                
                if (settings.loadurl) {
                    var t = setTimeout(function() {
                        input.disabled = true;
                        content.apply(form, [settings.loadtext, settings, self]);
                    }, 100);

                    var loaddata = {};
                    loaddata[settings.id] = self.id;
                    if ($.isFunction(settings.loaddata)) {
                        $.extend(loaddata, settings.loaddata.apply(self, [self.revert, settings]));
                    } else {
                        $.extend(loaddata, settings.loaddata);
                    }
                    $.ajax({
                        type : settings.loadtype,
                        url  : settings.loadurl,
                        data : loaddata,
                        async : false,
                        success: function(result) {
                            window.clearTimeout(t);
                            input_content = result;
                            input.disabled = false;
                        }
                    });
                } else if (settings.data) {
                    input_content = settings.data;
                    if ($.isFunction(settings.data)) {
                        input_content = settings.data.apply(self, [self.revert, settings]);
                    }
                } else {
                    input_content = self.revert;
                }
                content.apply(form, [input_content, settings, self]);

                input.attr('name', settings.name);
        
                /* Add buttons to the form. */
                buttons.apply(form, [settings, self]);
         
                /* Add created form to self. */
                $(self).append(form);
         
                /* Attach 3rd party plugin if requested. */
                plugin.apply(form, [settings, self]);

                /* Focus to first visible form element. */
                $(':input:visible:enabled:first', form).focus();

                /* Highlight input contents when requested. */
                if (settings.select) {
                    input.select();
                }
        
                /* discard changes if pressing esc */
                input.keydown(function(e) {
                    if (e.keyCode == 27) {
                        e.preventDefault();
                        reset.apply(form, [settings, self]);
                    }
                });

                /* Discard, submit or nothing with changes when clicking outside. */
                /* Do nothing is usable when navigating with tab. */
                var t;
                if ('cancel' == settings.onblur) {
                    input.blur(function(e) {
                        /* Prevent canceling if submit was clicked. */
                        t = setTimeout(function() {
                            reset.apply(form, [settings, self]);
                        }, 100);
                    });
                } else if ('submit' == settings.onblur) {
                    input.blur(function(e) {
                        /* Prevent double submit if submit was clicked. */
                        t = setTimeout(function() {
                            form.submit();
                        }, 100);
                    });
                } else if ($.isFunction(settings.onblur)) {
                    input.blur(function(e) {
                        settings.onblur.apply(self, [input.val(), settings]);
                    });
                } else {
                    input.blur(function(e) {
                        /* TODO: maybe something here */
                        });
                }

                form.submit(function(e) {

                    if (t) { 
                        clearTimeout(t);
                    }

                    /* Do no submit. */
                    e.preventDefault(); 
            
                    /* Call before submit hook. */
                    /* If it returns false abort submitting. */                    
                    if (false !== onsubmit.apply(form, [settings, self])) {
                        /* Custom inputs call before submit hook. */
                        /* If it returns false abort submitting. */
                        if (false !== submit.apply(form, [settings, self])) {

                            /* Check if given target is function */
                            if ($.isFunction(settings.target)) {
                                var str = settings.target.apply(self, [input.val(), settings]);
                                $(self).html(str);
                                self.editing = false;
                                callback.apply(self, [self.innerHTML, settings]);
                                /* TODO: this is not dry */                              
                                if (!$.trim($(self).html())) {
                                    $(self).html(settings.placeholder);
                                }
                            } else {
                                /* Add edited content and id of edited element to POST. */
                                var submitdata = {};
                                submitdata[settings.name] = input.val();
                                //submitdata[settings.id] = self.id;
                                /* Add extra data to be POST:ed. */
                                if ($.isFunction(settings.submitdata)) {
                                    $.extend(submitdata, settings.submitdata.apply(self, [self.revert, settings]));
                                } else {
                                    $.extend(submitdata, settings.submitdata);
                                }

                                /* Quick and dirty PUT support. */
                                if ('PUT' == settings.method) {
                                    submitdata['_method'] = 'put';
                                }

                                /* Show the saving indicator. */
                                $(self).html(settings.indicator);
                              
                                /* Defaults for ajaxoptions. */
                                var ajaxoptions = {
                                    type    : 'POST',
                                    data    : submitdata,
                                    dataType: 'html',
                                    url     : settings.target,
                                    success : function(result, status) {
                                        if (ajaxoptions.dataType == 'html') {
                                            //buggy when no .html usage
                                            $(self).html(result);
                                        }
                                        self.editing = false;
                                        callback.apply(self, [result, settings]);
                                        if (!$.trim($(self).html())) {
                                            $(self).html(settings.placeholder);
                                        }
                                    },
                                    error   : function(xhr, status, error) {
                                        onerror.apply(form, [settings, self, xhr]);
                                    }
                                };
                              
                                /* Override with what is given in settings.ajaxoptions. */
                                $.extend(ajaxoptions, settings.ajaxoptions);   
                                $.ajax(ajaxoptions);          
                              
                            }
                        }
                    }
                    
                    /* Show tooltip again. */
                    $(self).attr('title', settings.tooltip);
                    
                    return false;
                });
            });
            
            /* Privileged methods */
            this.reset = function(form) {
                /* Prevent calling reset twice when blurring. */
                if (this.editing) {
                    /* Before reset hook, if it returns false abort reseting. */
                    if (false !== onreset.apply(form, [settings, self])) {
                        $(self).html(self.revert);
                        self.editing   = false;
                        if (!$.trim($(self).html())) {
                            $(self).html(settings.placeholder);
                        }
                        /* Show tooltip again. */
                        if (settings.tooltip) {
                            $(self).attr('title', settings.tooltip);                
                        }
                    }
                }
            };            
        });

    };


    $.editable = {
        types: {
            defaults: {
                element : function(settings, original) {
                    var input = $('<input type="hidden"></input>');                
                    $(this).append(input);
                    return(input);
                },
                content : function(string, settings, original) {
                    $(':input:first', this).val(string);
                },
                reset : function(settings, original) {
                    original.reset(this);
                },
                buttons : function(settings, original) {
                    var form = this;
                    if (settings.submit) {
                        /* If given html string use that. */
                        if (settings.submit.match(/>$/)) {
                            var submit = $(settings.submit).click(function() {
                                if (submit.attr("type") != "submit") {
                                    form.submit();
                                }
                            });
                        /* Otherwise use button with given string as text. */
                        } else {
                            var submit = $('<button type="submit" />');
                            submit.html(settings.submit);                            
                        }
                        $(this).append(submit);
                    }
                    if (settings.cancel) {
                        /* If given html string use that. */
                        if (settings.cancel.match(/>$/)) {
                            var cancel = $(settings.cancel);
                        /* otherwise use button with given string as text */
                        } else {
                            var cancel = $('<button type="cancel" />');
                            cancel.html(settings.cancel);
                        }
                        $(this).append(cancel);

                        $(cancel).click(function(event) {
                            if ($.isFunction($.editable.types[settings.type].reset)) {
                                var reset = $.editable.types[settings.type].reset;                                                                
                            } else {
                                var reset = $.editable.types['defaults'].reset;                                
                            }
                            reset.apply(form, [settings, original]);
                            return false;
                        });
                    }
                }
            },
            text: {
                element : function(settings, original) {
                    var input = $('<input />');
                    if (settings.width  != 'none') {
                        input.attr('width', settings.width);
                    }
                    if (settings.height != 'none') {
                        input.attr('height', settings.height);
                    }
                    /* https://bugzilla.mozilla.org/show_bug.cgi?id=236791 */
                    //input[0].setAttribute('autocomplete','off');
                    input.attr('autocomplete','off').attr('type','text');
                    $(this).append(input);
                    return(input);
                }
            },
            textarea: {
                element : function(settings, original) {
                    var textarea = $('<textarea />');
                    if (settings.rows) {
                        textarea.attr('rows', settings.rows);
                    } else if (settings.height != "none") {
                        textarea.height(settings.height);
                    }
                    if (settings.cols) {
                        textarea.attr('cols', settings.cols);
                    } else if (settings.width != "none") {
                        textarea.width(settings.width);
                    }
                    $(this).append(textarea);
                    return(textarea);
                }
            },
            select: {
                element : function(settings, original) {
                    var select = $('<select />');
                    $(this).append(select);
                    return(select);
                },
                content : function(data, settings, original) {
                    /* If it is string assume it is json. */
                    if (String == data.constructor) {      
                        eval ('var json = ' + data);
                    } else {
                        /* Otherwise assume it is a hash already. */
                        var json = data;
                    }
                    for (var key in json) {
                        if (!json.hasOwnProperty(key)) {
                            continue;
                        }
                        if ('selected' == key) {
                            continue;
                        } 
                        var option = $('<option />').val(key).append(json[key]);
                        $('select', this).append(option);    
                    }                    
                    /* Loop option again to set selected. IE needed this... */ 
                    $('select', this).children().each(function() {
                        if ($(this).val() == json['selected'] || 
                            $(this).text() == $.trim(original.revert)) {
                            $(this).attr('selected', 'selected');
                        }
                    });
                    /* Submit on change if no submit button defined. */
                    if (!settings.submit) {
                        var form = this;
                        $('select', this).change(function() {
                            form.submit();
                        });
                    }
                }
            }
        },

        /* Add new input type */
        addInputType: function(name, input) {
            $.editable.types[name] = input;
        }
    };

    /* Publicly accessible defaults. */
    $.fn.editable.defaults = {
        name       : 'value',
        id         : 'id',
        type       : 'text',
        width      : 'auto',
        height     : 'auto',
        event      : 'click.editable',
        onblur     : 'cancel',
        loadtype   : 'GET',
        loadtext   : 'Loading...',
        placeholder: 'Click to edit',
        loaddata   : {},
        submitdata : {},
        ajaxoptions: {}
    };

})(jQuery);

/**
 * maze wrapper object for jeditable
 * 
 * @param {object} $
 * @returns {undefined}
 */
(function($) {

    /**
     * maze core implementation of jeditable
     * 
     * @param {string} target
     * @param {object} options
     * @returns {unresolved}
     */
    $.fn.mazeEditable = function(target, options) {
        var settings = $.extend({}, $.fn.mazeEditable.defaults, {target:target}, options);

        // init body click event
        $.fn.mazeEditable.initBodyClickEvent();

        if (settings.type === "textarea" && settings.whiteSpace === true) {
            this.addClass("whiteSpace");
        }
        
        // init jeditable
        return this.each(function() {
            var elemSettings = settings;
            var self = this;
            
            elemSettings.name = $(self).attr('name');
            if($(self).attr('jsLabel')) {
                elemSettings.placeholder = $(self).attr('jsLabel');
            }
            
            elemSettings.callback = function(value, self) {
                var name = $(self).attr('name');
                if ($.isFunction(settings.onsuccess)){
                    settings.onsuccess(value, self);
                }
                
                if(value.result === true) {
                    $(this).html($(this).find('[name="' +name+ '"]').val());
                } else {
                    if(value.formErrors && value.formErrors[name]) {
                        setErrorTooltip(value.formErrors);
                    }
                }
                
                setMessages(value);
                
                // css customization
                if($(this).html()) {
                    $(this).removeClass('cssColorGray');
                } else {
                    $(this).addClass('cssColorGray');
                }
            };
            
            // define jeditale ajaxoptions
            elemSettings.ajaxoptions = {
                dataType: 'json',
                complete: function(){
                    var editable = $('span[name="' + $(self).attr('name') + '"]');
                    var selector = editable.eq(editable.length -1);
                    
                    if (selector.find("form").length){
                        selector[0].editing = true;
                    }
                }
            };
            
            // init original editable
            $(self).editable(settings.target, elemSettings);

            // set onclick event
            if($.isFunction(settings.onclick)) {
                $(self).click(settings.onclick);
            }
            
            // set keydown event
            if($.isFunction(settings.keydown)) {
                $(self).keydown(settings.keydown);
            }
        });
    };
    
    /**
     * 
     * special jeditable for additional fields as textareas
     * 
     * @param {string} target
     * @param {object} options
     * @returns {unresolved}
     */
    $.fn.mazeEditableFields = function(target, options) {
        var settings = $.extend({}, $.fn.mazeEditable.defaults, $.fn.mazeEditableFields.defaults, {target:target}, options);
        plain2html = function(value){
            html = value.replace(new RegExp("\n", "g"), "<br\>")
                        .replace(new RegExp("\t", "g"), "&#9;");
            return html;
        };
        html2plain = function(value){
            text = value.replace(new RegExp("<br\>", "g"), "\n")
                        .replace(new RegExp("&#9;", "g"), "\t");
            return text;
        };
        // init body click event
        $.fn.mazeEditable.initBodyClickEvent();

        // init jeditable
        return this.each(function() {
            var elemSettings = settings;
            var self = this;
            
            elemSettings.name = $(self).attr('name');
            if($(self).attr('jsLabel')) {
                elemSettings.placeholder = $(self).attr('jsLabel');
            }

            self.revertHTML = self.innerHTML = plain2html(self.innerHTML);
            elemSettings.callback = function(value, self) {
                var textarea = $(this).find("textarea");
                var name = $(self).attr('name');
                if ($.isFunction(settings.onsuccess)){
                    settings.onsuccess(value, self);
                }
                
                regex = name.match(/\[(.*)\]/);
                name = regex[1];

                if(value.result === true) {
                    self.revert = plain2html($(this).find('textarea').val());
                    $(this).html(self.revert);
                } else {
                    if(typeof value.errors.additionalFields !== 'undefined'
                        && typeof value.errors.additionalFields[name] !== 'undefined') {
                        setErrorTooltip(value.errors.additionalFields[name], $(this));
                    }
                }

                setMessages(value);
                
                if(!$(this).html() || $.trim(textarea.val()) === "") {
                    $(this).closest('dl').remove();
                }
            };
            
            // define jeditale ajaxoptions
            elemSettings.ajaxoptions = {
                dataType: 'json',
                complete: function(){
                    var editable = $('span[name="' + $(self).attr('name') + '"]');
                    var selector = editable.eq(editable.length -1);
                    
                    if (selector.find("form").length){
                        selector[0].editing = true;
                    }
                }
            };
            
            // init original editable
            $(self).editable(settings.target, elemSettings);

            // set onclick event
            if($.isFunction(settings.onclick)) {
                $(self).click(settings.onclick);
            }
            
            // set keydown event
            if($.isFunction(settings.keydown)) {
                $(self).keydown(settings.keydown);
            }
        });
    };
    
    /**
     * enables click on certain jeditable parent opens jeditable
     */
    $.fn.mazeEditable.initBodyClickEvent = function() {
        $('body').unbind('click').click(function(e){
            var Elem = e.target;
            var editable = '';
            
            // check for editable
            if (Elem.nodeName==='DT' || Elem.nodeName==='DD' || Elem.nodeName==='LABEL'){
                editable = $(Elem).closest('dl').find('dd span.jsEditable');
            } else if (Elem.nodeName==='DL') {
                editable = $(Elem).find('dd span.jsEditable');
            }

            // open editable
            if($(editable).length > 0) {
                if($(editable).find('form').length > 0)
                    return false;

                $(editable).click();
            }
        });
    };
    
    /**
     * jeditable function for jeditable tab support
     * 
     * @param string|object self
     * @param boolean disableAjaxEvent don't use ajaxComplete Event
     */
    $.fn.mazeEditable.initTabKey = function (self, disableAjaxEvent)
    {
        var editables = '';

        if($(self).length === 0) {
            return false;
        }

        // if in colorbox, then only own jeditables
        if($(self).parents('#colorbox').length > 0) {
            editables = $('#colorbox span.jsEditable, #colorbox span.jsEditableAdditionalFields');
        } else {
            editables = $('span.jsEditable, span.jsEditableAdditionalFields');
        }

        var currentEditableIndex = editables.index(self);
        var form = $(self).find('form');
        var nextEditable = '';

        // clear old event
        $(document).unbind('ajaxComplete');

        // submit actual editable
        form.submit();

        // wait for ajax request of editable and try again
        if($(self).find("form").length !== 0 && disableAjaxEvent !== true) {
            $(document).ajaxComplete(function(evt, request) {

                var data = $.parseJSON(request.responseText);
                if($.isEmptyObject(data) || !data.result || data.result === false) {
                    $(document).unbind('ajaxComplete');

                    form.find('input').focus();
                    return;
                }

                $.fn.mazeEditable.initTabKey(self, true);
            });

            return false;
        }

        // start on top or next editable
        if (currentEditableIndex === (editables.length-1)) {
            nextEditable = editables.first();
        } else {
            nextEditable = editables.eq(currentEditableIndex+1);
        }

        nextEditable.click();
        return false;
    };
    
    /* Publicly accessible defaults. */
    $.fn.mazeEditable.defaults = {
        name       : 'value',
        id         : 'id',
        type       : 'text',
        width      : 'none',
        height     : 'none',
        cssclass: 'cssFormEditable',
        cssclassplaceholder: 'cssColorGray',
        cancel: '<button class="jsButton buttons ui-icon ui-icon-close" type="submit" value="abbrechen"/>',
        submit: '<button class="jsButton buttons ui-icon ui-icon-check" type="submit" value="speichern"/>',
        event      : 'click.editable',
        onblur     : 'cancel',
        loadtype   : 'GET',
        loadtext   : 'Loading...',
        placeholder: 'Click to edit',
        activeClass: 'active',
        loaddata   : {},
        submitdata : {},
        onsuccess: function(response, elemSettings){
            var element = (elemSettings.name || null);
            if (element && !$.isEmptyObject(response.formErrors) && typeof response.formErrors[element] !== "object"){
                $("[name='"+ element +"']").removeClass(elemSettings.activeClass);
            }
        },
        onedit: function(settings, self) {
            $(self).data("loadonclick", function(){
                var editable = $(self).find('[name="' +self.getAttribute("name")+ '"]');
                if (editable.length && settings.type && typeof settings[settings.type] === "object") {
                    for (var attribute in settings[settings.type]) {
                        if (attribute === "class") {
                            editable.addClass((settings[settings.type]["class"] === true ? self.className : settings[settings.type][attribute]));
                        } else {
                            editable.attr(attribute, settings[settings.type][attribute]);
                        }
                    }
                }
            });
            resetTooltips();
            if (settings.type === "textarea" && typeof html2plain === "function"){
                self.innerHTML = html2plain(self.innerHTML);
            }
        },
        onsubmit: function(settings, self) {
            resetTooltips();

            // don't submit form without changes
            if($(this).find('[name="' +$(self).attr('name')+ '"]').val() === self.revert) {
                self.reset();
                return false;
            }
        },
        onreset: function(settings, self) {
            $(self).removeClass(settings.activeClass);
            resetTooltips();
        },
        onclick: function(event) {
            $(this).find('.jsButton').button();
        },
        keydown: function(event) {
            // custom behavior on tab press
            if (9 === (event.keyCode || event.which) && event.target.nodeName.toLowerCase() === "textarea") {
					var startPos = event.target.selectionStart;
					var endPos = event.target.selectionEnd;
					event.target.value = event.target.value.substring(0, startPos) + "\t" + event.target.value.substring(endPos, event.target.value.length);
					event.target.focus();
					event.target.selectionStart = startPos + "\t".length;
					event.target.selectionEnd = startPos + "\t".length;
					event.preventDefault();
			}else if (event.which === 9) {
                event.preventDefault();
                $.fn.mazeEditable.initTabKey(this);
            }
        }
    };
    
    $.fn.mazeEditableFields.defaults = {
        'type': 'textarea',
        onsubmit: function(settings, self) {
            resetTooltips();

            // don't submit form without changes
            if($(this).find('textarea').val() === self.revert) {
                self.reset();
                return false;
            }
        },
        keydown: $.fn.mazeEditable.defaults.keydown
    };

})(jQuery);
