"use strict";
$(document).ready( function()  {
  var qr = new QuoteRepository( );
  var qv = new QuotesView( );
  var nvb = new NavBarView( qr, qv );
  var nqv = new NewQuoteView( qr, nvb );
});
function NavBarView( quoteRepository, quotesView ) {
  var self = this;
  this.init = function( ) {
    // This isn't a callback because it is necessary to know the first year to load the quotes of that year in the first screen.
    self.showYears( quoteRepository.quoteYears( ) );
    quoteRepository.getQuoted( true, self.loadQuotedList );
    $('#navbar-select').change( function( event ) {
      var id = event.target.selectedOptions[0].value;
      if( id[0] == 'q' ) {
        var quotedId = event.target.selectedOptions[0].value.slice(1);
        var quotedName = event.target.selectedOptions[0].textContent;
        quoteRepository.getQuotesByQuoted( quotedId , function( quotes ) {
          quotesView.showWithYearSeparation( quotedName,  quotes );
        });
      } else {
        var year = event.target.selectedOptions[0].value;
        var quotes = quoteRepository.getQuotesFromYear( year, function( quotes ) {
          quotesView.show( year, quotes );
        });
      }
    });
    $('#navbar-select').change( );
  }
  this.showYears = function( years ) {
    var optgroup = $('#opt-years');
    var title = new Option( );
    optgroup.children( ).remove( );
    years.forEach( function( year ) {
      var option = new Option( );
      option.value = year;
      option.text = year;
      optgroup.append( option );
    });
  }
  this.loadQuotedList = function( quotedCollection ) {
    var mcp = $('#opt-mcp');
    var exMcp = $('#opt-exmcp');
    var title = new Option;
    mcp.children( ).remove( );
    exMcp.children( ).remove( );
    quotedCollection.forEach( function( each ) {
      var quoted = new Option;
      quoted.value = 'q' + each.id;
      quoted.text = each.quoted;
      if( each.active ) {
        mcp.append( quoted );
      } else {
        exMcp.append( quoted );
      }
    });
  }
  this.init( );
}
function NewQuoteView ( quoteRepository, navBarView ) {
  self = this;
  function init( ) {
    $('#new-quote-quoted-all').change( function( event ) {
      quoteRepository.getQuoted( event.target.checked, self.loadQuotedList );
    });
    $('#new-quote-quoted-all').change( );
    $('#new-quote-submit').click( function( event ) {
      var ids = $('#new-quote-quoted').select2('val');
      var quote = $('#new-quote-quote').val( );
      $('#new-quote-success').hide();
      $('#new-quote-warning').hide();
      $('#new-quote-danger').hide();
      quoteRepository.addQuote( quote, ids, showResult );
    });
    $('#new-quote-cancel').click( function( event ) {
      $('#new-quote-quote').val('');
      $('#new-quote-quoted').select2('val', '');
      });
    $("#new-quote-quoted").select2({placeholder: "Quoteado(s)"});
    $('#new-quote-quoted-all').tooltip( );
  }
  function showResult( result ) {
    if( result.hasOwnProperty('success') && result.success ) {
      var success = $('#new-quote-success');
      success.html("Quote exitosa");
      success.show();
      $('#new-quote-quoted').select2("val", "");
      $('#new-quote-quote').val("");
      quoteRepository.quoteYears( navBarView.init );
    } else if ( result.hasOwnProperty('error') && result.error ) {
      var alert;
      if( result.hasOwnProperty('success') && result.success ) {
        alert = $('#new-quote-warning');
      } else {
        alert = $('#new-quote-danger');
      }
      message = result.errorDescription;
      if( result.hasOwnProperty('invalid') ) {
        message += ': ' + result.invalid;
      }
      alert.html( message );
      alert.show();
    }
  }
  this.loadQuotedList = function ( quotedCollection ) {
    var select = $('#new-quote-quoted');
    select.children( ).remove( );
    quotedCollection.forEach( function( each ) {
      var quoted = new Option;
      quoted.value = each.id;
      quoted.text = each.quoted;
      if( each.aliases != '' ) {
        quoted.text += ' / ' + each.aliases;
      }
      select.append( quoted );
    });
  }
  init( );
}
function QuotesView( ) {
  function title( titleText ) {
    var t = $(document.createElement( 'h1' ));
    t.text( titleText );
    return t;
  }
  function subtitle( count ) {
    var sub = $(document.createElement( 'h3' ));
    sub.text( count + ( count == 1 ? ' quote': ' quotes' ) );
    return sub;
  }
  function quoteCount( count ) {
    var small = $(document.createElement('small'));
    small.text( count + ( count == 1 ? ' quote': ' quotes' ) );
    return small;
  }
  function panel( ) {
    var panel = $(document.createElement('div'));
    panel.addClass( 'panel' );
    panel.addClass( 'panel-default' );
    return panel;
  }
  function panelHead( year ) {
    var head = $(document.createElement('div'));
    var title = $(document.createElement('h4'));
    var anchor = $(document.createElement('a'));
    var small = $(document.createElement('small'));
    title.append( year + ' ');
    head.addClass( 'panel-heading' );
    head.addClass( 'no-border-bottom' );
    title.addClass( 'panel-title' );
    anchor.addClass( 'accordion-toggle' );
    anchor.attr( 'data-toggle', 'collapse' );
    anchor.attr( 'data-parent', '#quotes-panel' );
    anchor.attr( 'href' , '#y'+year );
    anchor.text( '[ ver / ocultar ]' );
    small.append( anchor );
    title.append( small );
    head.append( title );
    return head;
  }
  function panelBody( ) {
    var panel = $(document.createElement('div'));
    panel.addClass( 'panel-body' );
    panel.addClass( 'panel-quotes' );
    return panel;
  }
  function table( ) {
    var aTable = $(document.createElement('table'));
    aTable.addClass('table');
    aTable.addClass('table-striped');
    aTable.addClass('table-condensed');
    aTable.addClass('no-margin-bot');
    return aTable;
  }
  function tableRowFrom( number, quote ) {
    var tr = $(document.createElement('tr'));
    var td = $(document.createElement('td'));
    td.text( number );
    td.addClass( 'number' );
    tr.append( td );
    td = $(document.createElement('td'));
    td.html( quote );
    tr.append( td );
    return tr;
  }
  this.container = $('#quotes-panel');
  this.show = function( titleText, quotes ) {
    this.container.children( ).remove( );
    this.container.append( title( titleText ) );
    this.container.append( subtitle( quotes.length ) );
    var aPanel = panel( );
    aPanel.addClass( 'panel-no-border-top' );
    this.container.append( aPanel );
    var aPanelBody = panelBody( );
    aPanel.append( aPanelBody );
    var aTable = table( );
    aPanelBody.append( aTable );
    var tBody = $(document.createElement('tbody'));
    aTable.append( tBody );
    quotes.forEach( function( quote ) {
      tBody.append( tableRowFrom( quote.number, quote.quote ) );
    });
    this.container.scrollTop(0);
  }
  this.showWithYearSeparation = function ( titleText, quotes ) {
    this.container.children( ).remove( );
    this.container.append( title( titleText ) );
    this.container.append( subtitle( quotes.length ) );
    var previous = quotes[0];
    var last = quotes[quotes.length-1];
    var first = true;
    var count = 0;
    var self = this;
    var aPanel, aPanelHead, aPanelBody, aTable, tBody;
    quotes.forEach( function( quote ) {
      if( first || quote.year != previous.year ) {
        aPanel = panel( );
        self.container.append( aPanel );
        if( aPanelHead ) {
          var counter = quoteCount( count );
          counter.addClass( 'pull-right' );
          aPanelHead.children( ).first( ).append( counter );
        }
        aPanelHead = panelHead( quote.year )
        aPanel.append( aPanelHead );
        aPanelBody = panelBody( );
        aPanelBody.attr( 'id' , 'y' + quote.year );
        if( first ) {
          first = false;
          aPanelBody.addClass( 'in' );
        } else {
          aPanelBody.removeClass( 'in' );
          aPanelBody.addClass( 'collapse' );
        }
        aPanel.append( aPanelBody );
        aTable = table( );
        aPanelBody.append( aTable );
        tBody = $(document.createElement('tbody'));
        aTable.append( tBody );
        count = 0;
      }
      tBody.append( tableRowFrom( quote.number, quote.quote ) );
      previous = quote;
      count++;
      if( quote == last ) {
        var counter = quoteCount( count );
        counter.addClass( 'pull-right' );
        aPanelHead.children( ).first( ).append( counter );
      }
    });
    this.container.scrollTop(0);
  }
}