/* jslint browser: true, devel: true, todo: true, indent: 2 */
"use strict";
function SecurityManager() {
  var self = this;
  function LocalStorageStrategy() {
    this.set = function (key, value) {
      localStorage.setItem(key, value);
    };
    this.get = function (key) {
      return localStorage.getItem(key);
    };
    this.remove = function (key) {
      localStorage.removeItem(key);
    };
    this.missingElement = function( ) {
      return null;
    }
  }
  LocalStorageStrategy.prototype.available = function () {
    return Storage !== "undefined";
  };
  function CookieStrategy(days) {
    this.set = function (key, value) {
      var expires, d = new Date();
      d.setTime(d.getTime() + (days * 24 * 60 * 60 * 1000));
      expires = "expires=" + d.toGMTString();
      document.cookie = key + "=" + value + "; " + expires;
    };
    this.get = function (key) {
      var i, c, ca = document.cookie.split(';');
      key += "=";
      for (i = 0; i < ca.length; i += 1) {
        c = ca[i].trim();
        if (c.indexOf(key) === 0) {
          return c.substring(key.length, c.length);
        }
      }
    };
    this.remove = function (key) {
      document.cookie = key + '=; expires=Thu, 01 Jan 1970 00:00:01 GMT;';
    };
    this.missingElement = function( ) {
      return "undefined";
    }
  }
  CookieStrategy.prototype.available = function () {
    return navigator.cookieEnabled;
  };
  function NoPersistentStorage() {
    var that = this;
    this.dictionary = [];
    this.set = function (key, value) {
      that.dictionary[key] = value;
    };
    this.get = function (key) {
      return that.dictionary[key];
    };
    this.remove = function (key) {
      delete (that.dictionary[key]);
    };
    this.missingElement = function( ) {
      return "undefined";
    }
  }
  NoPersistentStorage.prototype.available = function () {
    return true;
  };
  if (LocalStorageStrategy.prototype.available()) {
    this.storage = new LocalStorageStrategy();
  } else if (CookieStrategy.prototype.available()) {
    this.storage = new CookieStrategy(30);
  } else {
    this.storage = new NoPersistentStorage();
  }
  this.logIn = function (password) {
    jQuery.ajax({
      type: 'POST',
      url: 'php/login.php',
      data: { 'phash': md5(password) },
      success : function (json) {
        if (json.error) {
          jQuery('#login-danger').text(json.errorDescription);
          jQuery('#login-danger').show();
        } else {
          self.storage.set('token', json.token);
          jQuery('#login-danger').hide();
          jQuery('#login').modal('hide');
        }
      }
    });
  };
  this.logOut = function () {
    jQuery.ajax({
      type: 'GET',
      url: 'php/logout.php',
      success: function () {
        self.storage.remove('token');
      }
    });
    self.storage.remove('token');
  };
  this.token = function () {
    return self.storage.get('token');
  };
  this.isLoggedIn = function() {
    return self.token( ) !== self.storage.missingElement( );
  };
}
function LoginWindow(securityManager) {
  var init, self = this;
  this.securityManager = securityManager;
  init = function () {
    jQuery('#login-submit').click(function () {
      self.securityManager.logIn(jQuery('#login-pass').val());
    });
    jQuery('#login-pass').keyup(function (event) {
      if (event.which === 13) {
        self.securityManager.logIn(jQuery('#login-pass').val());
      }
    });
  };
  this.open = function () {
    jQuery('#login').modal({keyboard: false, backdrop: 'static'});
  };
  init();
}
function NavBarView(securityManager) {
  jQuery('#logout-btn').click(securityManager.logOut);
  this.loadYears = function() {
    var years, select, option, i;
    years = jQuery.parseJSON(jQuery.ajax({
      type: 'GET',
      url: 'php/quoteYears.php',
      async: false
    }).responseText);
    select = document.getElementById('quotes-year');
    for (i = 0; i < years.length; i += 1) {
      option = document.createElement('option');
      option.text = years[i];
      option.value = years[i];
      select.add(option);
    }
    if (i > 0) {
      select.selectedIndex = 0;
    }
  }
}
function QuoteView( loginWindow, editQuoteWindow ) {
  var self = this;
  this.loginWindow = loginWindow;
  this.editQuoteWindow = editQuoteWindow;
  this.deleteQuote;
  this.show = function () {
    var year, quotes, quotedList, tbody, tr, td;
    year = document.getElementById('quotes-year').selectedOptions[0].value;
    quotes = jQuery.parseJSON(jQuery.ajax({
      type: 'GET',
      url: 'php/quotesByYear.php',
      data: { y: year },
      async: false
    }).responseText);
    quotedList = jQuery.parseJSON(jQuery.ajax({
      type: 'GET',
      url: 'php/quoted.php',
      data: { all: 'all' },
      async: false
    }).responseText);
    tbody = jQuery('#admin-tbody');
    tbody.html('');
    // Add table headers
    tr = jQuery(document.createElement('tr'));
    td = jQuery(document.createElement('th'));
    td.text('Numero');
    tr.append(td);
    td = jQuery(document.createElement('th'));
    td.text('Quote');
    tr.append(td);
    td = jQuery(document.createElement('th'));
    td.text('Editar');
    tr.append(td);
    tbody.append(tr);
    // Add quotes to the table
    quotes.forEach(function (quote) {
      function quoteNumber() {
        var td = jQuery(document.createElement('td'));
        td.text(quote.number);
        td.addClass('quote-number');
        return td;
      }
      function quoteText() {
        var td = jQuery(document.createElement('td'));
        td.addClass('quote-quote');
        td.html(quote.quote);
        return td;
      }
      function quoteButtons() {
        var td, button, btn;
        td = jQuery(document.createElement('td'));
        td.addClass('action-buttons');
        button = jQuery(document.createElement('button'));
        button.addClass('btn');
        button.addClass('btn-xs');
        button.attr('type', 'button');
        // Edit Button
        btn = button.clone( );
        btn.addClass('btn-primary');
        btn.attr('title', 'Editar');
        btn.html('<span class="glyphicon glyphicon-pencil"></span>');
        btn.click(self.editQuoteWindow.open(quote, quotedList, event));
        td.append(btn);
        td.append(' ');
        // Delete Button
        btn = button.clone( );
        btn.click(self.deleteQuote(quote));
        btn.addClass('btn-danger');
        btn.attr('title', 'Eliminar');
        btn.attr('qnr', quote.number);
        btn.html('<span class="glyphicon glyphicon-remove"></span>');
        td.append(btn);
        return td;
      }
      tr = jQuery(document.createElement('tr'));
      tr.append(quoteNumber());
      tr.append(quoteText());
      tr.append(quoteButtons());
      tbody.append(tr);
    });
    jQuery("#edit-quote-quoted").select2();
    jQuery(document).scrollTop(0);
    jQuery('#edit-quotes-year').modal('hide');
    // Update navbar
    jQuery('#nav-list').children().removeClass('active');
    jQuery('#nav-edit-quotes').addClass('active');
  }
  this.deleteQuote = function (quote) {
    return function () {
      if (confirm('Borrar la quote: ' + quote.number + ' ' + quote.quote + '?')) {
        jQuery.ajax({
          type: 'GET',
          url: 'php/deleteQuote.php',
          data: { t: self.loginWindow.securityManager.token(), n: quote.number },
          success : function (json) {
            if (json.error) {
              alert(json.errorDescription);
              if (json.errorCode === 1) {
                self.loginWindow.open();
              }
            } else {
              self.show();
            }
          }
        });
      }
    };
  }
}
function QuotedView(quoteRepository) {
  function quotedNameCell(name) {
    var td = jQuery(document.createElement('td'));
    td.addClass('quoted-name');
    td.text(name);
    return td;
  }
  function quotedActiveCell(active) {
    var td, glyph;
    td = jQuery(document.createElement('td'));
    glyph = jQuery(document.createElement('span'));
    glyph.addClass('glyphicon');
    if (active) {
      glyph.addClass('glyphicon-ok');
      glyph.addClass('green');
    } else {
      glyph.addClass('glyphicon-remove');
      glyph.addClass('red');
    }
    td.append(glyph);
    td.addClass('quoted-active');
    return td;
  }
  function quotedAliasesCell(aliases) {
    var td = jQuery(document.createElement('td'));
    td.addClass('quoted-aliases');
    td.text(aliases);
    return td;
  }
  function editButtonCell(quoted) {
    var td, button, glyph;
    function editQuotedWindow(quoted) {
      return function () {
        // Load clicked quoted into the modal window
        jQuery('#edit-quoted-id').val(quoted.id);
        jQuery('#edit-quoted-name').val(quoted.quoted);
        jQuery("#edit-quoted-alias").val(quoted.aliases);
        jQuery("#edit-quoted-alias").select2({
          tags: quoted.aliases.split(', '),
          tokenSeparators: [","]
        });
        jQuery('#edit-quoted-active').attr('checked', quoted.active);
        // Open modal window
        jQuery('#edit-quoted').modal();
      };
    }
    td = jQuery(document.createElement('td'));
    button = jQuery(document.createElement('button'));
    glyph = jQuery(document.createElement('span'));
    glyph.addClass('glyphicon');
    glyph.addClass('glyphicon-pencil');
    button.addClass('btn');
    button.addClass('btn-xs');
    button.addClass('btn-primary');
    button.attr('title', 'Editar');
    button.attr('type', 'button');
    button.append(glyph);
    button.click(editQuotedWindow(quoted, event));
    td.addClass('action-buttons');
    td.append(button);
    return td;
  }
  function quotedToRow(quoted) {
    var tr = jQuery(document.createElement('tr'));
    tr.append(editButtonCell(quoted));
    tr.append(quotedNameCell(quoted.quoted));
    tr.append(quotedActiveCell(quoted.active));
    tr.append(quotedAliasesCell(quoted.aliases));
    return tr;
  }
  this.show = function () {
    var quotedList, tbody, tr;
    quoteRepository.getQuoted(true, function( quotedList ) {
      tbody = jQuery('#admin-tbody');
      tbody.children().remove();
      tr = jQuery(document.createElement('tr'));
      ['Editar', 'Nombre', 'Activo', 'Aliases'].forEach(function (each) {
        var th = jQuery(document.createElement('th'));
        th.text(each);
        tr.append(th);
      });
      tbody.append(tr);
      quotedList.forEach(function (quoted) {
        tbody.append(quotedToRow(quoted));
      });
      jQuery('#nav-list').children().removeClass('active');
      jQuery('#nav-edit-quoted').addClass('active');
    });
  };
}
function EditQuoteWindow( loginWindow ) {
  var self = this;
  this.quotedView = null;
  this.loginWindow = loginWindow;
  this.editQuote = function (number, text, ids) {
    jQuery.ajax({
      type: 'GET',
      url: 'php/editQuote.php',
      data: { t: self.loginWindow.securityManager.token(), n: number, quote: text, quoted: ids },
      success : function (json) {
        if (json.error) {
          jQuery('#edit-quote-danger').text( json.errorDescription );
          jQuery('#edit-quote-danger').show( );
          if (json.errorCode === 1) {
            self.loginWindow.open();
          }
        } else {
          if( this.quotedView ) {
            self.quotedView.show();
          }
          jQuery('#edit-quote-danger').hide( );
          jQuery('#edit-quote').modal('hide');
        }
      }
    });
  }

  this.open = function (quote, quotedList) {
    return function () {
      var quoted;
      // Load clicked quote into the modal window
      jQuery('#edit-quote-number').text(quote.number);
      jQuery('#edit-quote-text').val(quote.quote);
      jQuery('#edit-quote').modal();
      // Find quoted people for that quote
      quoted = jQuery.parseJSON(jQuery.ajax({
        type: 'GET',
        url: 'php/quoteByNumber.php',
        data: { 'n': quote.number },
        async: false
      }).responseText).quoted;
      jQuery("#edit-quote-quoted").html('');
      quotedList.forEach(function (quotedPerson) {
        var option = jQuery(document.createElement('option'));
        option.text(quotedPerson.quoted);
        option.val(quotedPerson.id);
        if (quoted.indexOf(quotedPerson.id) !== -1) {
          option.attr('selected', 'selected');
        }
        jQuery("#edit-quote-quoted").append(option);
      });
      jQuery("#edit-quote-quoted").select2();
    };
  }

  jQuery('#edit-quote-submit').click( function() {
    var quote, number, ids, options, i;
    quote = jQuery('#edit-quote-text').val( );
    number = parseInt(jQuery('#edit-quote-number').text( ));
    options = document.getElementById('edit-quote-quoted').selectedOptions;
    ids = [];
    for( i = 0; i < options.length ; i++ ) {
      ids.push( options[i].value );
    }
    self.editQuote( number, quote, ids );
  });
}
function EditQuotedWindow(loginWindow, quotedView) {
  var self = this;
  this.loginWindow = loginWindow;
  this.quotedView = quotedView;
  function editQuoted() {
    var id, name, aliases, active;
    id = document.getElementById('edit-quoted-id').value;
    name = document.getElementById('edit-quoted-name').value;
    aliases = document.getElementById('edit-quoted-alias').value.split(',');
    active = document.getElementById('edit-quoted-active').checked;
    jQuery.ajax({
      type: 'GET',
      url: 'php/editQuoted.php',
      data: { t: self.loginWindow.securityManager.token(), id: id, name: name, aliases: aliases, active: active },
      success : function (json) {
        if (json.error) {
          jQuery('#edit-quoted-danger').text( json.errorDescription );
          jQuery('#edit-quoted-danger').show( );
          if (json.errorCode === 1) {
            self.loginWindow.open();
          }
        } else {
          jQuery('#edit-quoted-danger').hide( );
          jQuery('#edit-quoted').modal('hide');
          quotedView.show( );
        }
      }
    });
  }
  jQuery('#edit-quoted-submit').click(editQuoted);
}
function NewQuotedWindow(loginWindow) {
  var self = this;
  this.loginWindow = loginWindow;
  function addQuoted() {
    var name, aliases;
    name = document.getElementById('new-quoted-name').value;
    aliases = document.getElementById('new-quoted-alias').value.split(',');
    jQuery.ajax({
      type: 'GET',
      url: 'php/newQuoted.php',
      data: { t: self.loginWindow.securityManager.token(), name: name, aliases: aliases },
      success : function (json) {
        if (json.error) {
          jQuery('#new-quoted-danger').text( json.errorDescription );
          jQuery('#new-quoted-danger').show( );
          if (json.errorCode === 1) {
            self.loginWindow.open();
          }
        } else {
          jQuery('#new-quoted').modal('hide');
          quotedView.show( );
          jQuery('#new-quoted-danger').hide( );
          document.getElementById('new-quoted-name').value = '';
          jQuery("#new-quoted-alias").select2('val','');
        }
      }
    });
  }
  jQuery('#new-quoted-submit').click(addQuoted);
}

jQuery(document).ready(function () {
  var qr, sm, lw, qdv, nvb, eqv, qv, eqd, nqd;
  qr = new QuoteRepository();
  sm = new SecurityManager();
  lw = new LoginWindow(sm);
  qdv = new QuotedView(qr);
  nvb = new NavBarView(sm);
  eqv = new EditQuoteWindow(lw);
  qv = new QuoteView(lw,eqv);
  eqd = new EditQuotedWindow(lw, qdv);
  nqd = new NewQuotedWindow(lw, qdv);
  eqv.quotedView = qv;
  nvb.loadYears();
  qv.show();
  jQuery("#edit-quotes-year-submit").click(qv.show);
  jQuery("#edit-quoted-btn").click(qdv.show);
  jQuery("#new-quoted-alias").select2({ tags: [], tokenSeparators: [","] });
  if( ! sm.isLoggedIn( ) ) {
    lw.open();
  }
});
