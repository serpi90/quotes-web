$(document).ready( initDocument );

function initDocument() {
	$.ajax({ url: 'php/quoted.php', success : [loadQuotedList,loadQuotedSelect] });
	$.ajax({ url: 'php/quoteYears.php', success: initYearListAndMainQuotes });
	$('#side-panel-quotes-list').change( loadSelectedYearQuotes );
	$('#new-quote-submit').click( newQuote );
	$('#new-quote-cancel').click( function( event ) {
		$('#new-quote-quote').val('');
		$('#new-quote-quoted').select2( 'val', '');
		$('#new-quote-success').hide();
		$('#new-quote-info').hide();
		$('#new-quote-warning').hide();
		$('#new-quote-danger').hide();
		});
	$("#new-quote-quoted").select2({placeholder: "Quoteado(s)"});
	$('#new-quote-quoted-chk').click( function( event ) {
		var quotedUrl = 'php/quoted.php';
		if( event.target.checked ) {
			quotedUrl += '?all';
		}
		$.ajax({ url: quotedUrl, success : loadQuotedSelect });
	});
	$('#side-panel-quoted-all').click( function( event ) {
		var quotedUrl = 'php/quoted.php';
		if( event.target.value == "on" ) {
			event.target.value = "off";
		} else {
			event.target.value = "on";
		}
		if( event.target.value == "on" ) {
			quotedUrl += '?all';
		}
		$.ajax({ url: quotedUrl, success : loadQuotedList });
	});
	$('#new-quote-quoted-chk').tooltip( );

}

function loadSelectedYearQuotes( event ) {
		clearQuotedSelection( );
		var year = getSelectedYear( );
		$.ajax({ url: 'php/quotesByYear.php?y='+year, success: loadQuotes });
		$('#title').text('Quotes del '+ year );
	}

function newQuote ( event ) {
	$('#new-quote-success').hide();
	$('#new-quote-info').hide();
	$('#new-quote-warning').hide();
	$('#new-quote-danger').hide();
	var ids = $('#new-quote-quoted').select2('val');
	var quote = $('#new-quote-quote').val( );
	$.ajax({ url: 'php/newQuote.php', data: {"quoted[]":ids, "quote":quote}, success : displayNewQuoteResult });
}

function displayNewQuoteResult( json ) {
	var result = $.parseJSON( json );
	if( result.hasOwnProperty('success') && result.success ) {
		var success = $('#new-quote-success');
		success.html("Quote exitosa");
		success.show();
		$('#new-quote-quoted').select2("val", "");
		$('#new-quote-quote').val("");
		$.ajax({ url: 'php/quoteYears.php', success: initYearListAndMainQuotes });
	}
	if( result.hasOwnProperty('error') && result.error ) {
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
function initYearListAndMainQuotes( json ) {
	var year = loadYearList( json );
	$.ajax({url:'php/quotesByYear.php?y='+year, success: loadQuotes });
	$('#title').text('Quotes del '+ year ) ;
	clearQuotedSelection( );
}

function getSelectedYear() {
	years = $('#side-panel-quotes-list').children( );
	for( var i=0; i < years.length ; i++ ) {
		var year = years[i];
		if(year.selected) {
			return year.value;
		}
	}
}

function loadQuotedList( json ) {
	var array = $.parseJSON(json);
	var li = $(document.createElement('LI'));
	var anchor = $(document.createElement('A'));
	anchor.addClass('btn-lnk-quoted');
	anchor.attr( 'type', 'button' );
	var list = $('#side-panel-quoted-list');
	list.html('');

	for( var i=0; i < array.length ; i++ ) {
		var each = array[i];

		var listElement = li.clone();
		var link = anchor.clone();
		link.attr( 'quoted', each.id);
		link.text( each.quoted );
		link.click( loadUserQuotes );

		listElement.append( link );
		list.append( listElement );
	}
	delete(li);
	delete(anchor);
}

function loadQuotedSelect( json ) {
	var array = $.parseJSON(json);
	var option = $(document.createElement( 'OPTION' ));
	var select = $('#new-quote-quoted');
	select.html('');

	for( var i=0; i < array.length ; i++ ) {
		var each = array[i];
		var quoted = option.clone( );
		quoted.val( each.id );
		if( each.aliases == '' ) {
			quoted.text( each.quoted );
		} else {
			quoted.text( each.quoted + ' / ' + each.aliases );
		}
		select.append( quoted );
	}
	delete(option);
}

function clearQuotedSelection( ) {
	listElements = $('#side-panel-quoted-list').children();
	for( var i=0; i < listElements.length ; i++ ) {
		listElements[i].classList.remove( 'active' );
	}
}

function loadUserQuotes( event ) {
	clearQuotedSelection( );
	event.target.parentElement.classList.add( 'active' );
	id = event.target.attributes.getNamedItem( 'quoted' ).value;
	quoted = event.target.textContent;
	$.ajax({url:'php/quotesByQuoted.php?q='+id, success: loadQuotesWithYearSeparation} );
	$('#title').text( 'Quotes por ' + quoted );
	$('#side-panel-quotes-list').children( )[0].selected = true;
}

function loadYearList( json ) {
	var array = $.parseJSON( json );
	var option = $(document.createElement( 'OPTION' ));
	var list = $('#side-panel-quotes-list');
	list.html('');
	
	var title = option.clone( );
	title.attr( 'disabled', true );
	title.text( 'Año' );
	list.append( title );
	
	var year;
	for( var i=0; i < array.length ; i++ ) {
		year = option.clone( );
		year.value = array[i];
		year.text( array[i] );
		list.append( year );
	}
	delete( option );
	return array[0];
}
function loadQuotes( json ) {
	var array = $.parseJSON( json );
	var panel = $('#quotes-panel');
	var title = $('#title');

	var table = $(document.createElement('TABLE'));
	table.addClass('table');
	table.addClass('table-striped');
	table.addClass('table-condensed');
	table.addClass('no-margin-bot');

	var body = $(document.createElement('DIV'));
	body.addClass( 'panel-body' );
	body.addClass( 'panel-quotes' );
	body.append( table );
	
	var tbody = $(document.createElement('TBODY'));
	table.append( tbody );

	var	div = $(document.createElement('DIV'));
	div.addClass( 'panel' );
	div.addClass( 'panel-default' );
	div.addClass( 'panel-no-border-top' );
	div.append( body );
	

	panel.html('');
	panel.append( title );
	panel.append( div );

	var tr = $(document.createElement('TR'));
	var td = $(document.createElement('TD'));

	var o;
	var number;
	var quote;
	var row;
	for( var i=0; i < array.length ; i++ ) {
		o = array[i];
		
		row = tr.clone( );
		tbody.append( row );
		
		number = td.clone( );
		number.addClass( 'number' )
		number.text( o.number );
		row.append( number );
		
		quote = td.clone( );
		quote.html( o.quote );
		row.append( quote );
	}
	panel.scrollTop(0);
}

function loadQuotesWithYearSeparation( json ) {
	var array = $.parseJSON( json );

	var panel = $('#quotes-panel');
	var title = $('#title');

	panel.html('');
	panel.append( title );

	var	div = $(document.createElement('DIV'));

	var last;
	if( array.length > 0 ) {
		last = array[0];
	}
	var accordion = div.clone( );
	accordion.addClass( 'panel' );
	accordion.addClass( 'panel-default' );

	var head = div.clone( );
	head.addClass( 'panel-heading' );
	head.addClass( 'no-border-bottom' );

	var title = $(document.createElement('h4'));
	title.addClass( 'panel-title' );

	var anchor = $(document.createElement('A'));
	anchor.addClass( 'accordion-toggle' );
	anchor.attr( 'data-toggle', 'collapse' );
	anchor.attr( 'data-parent', '#quotes-panel' );

	var body = div.clone( );
	body.addClass( 'panel-body' );
	body.addClass( 'panel-collapse' );
	body.addClass( 'panel-quotes' );

	var row = div.clone( );
	row.addClass( 'row' );
	row.addClass( 'row-quote' );

	var tablePrototype = $(document.createElement('TABLE'));
	var tbodyPrototype = $(document.createElement('TBODY'));
	var tr = $(document.createElement('TR'));
	var td = $(document.createElement('TD'));

	tablePrototype.addClass('table');
	tablePrototype.addClass('table-striped');
	tablePrototype.addClass('table-condensed');
	tablePrototype.addClass('no-margin-bot');

	var o;
	var number;
	var quote;
	var row;
	var table;
	var tbody;
	
	for( var i=0; i < array.length ; i++ ) {
		o = array[i];
		if( o.year != last.year || i == 0 ) {
			anchor.attr( 'href' , '#y'+o.year );
			anchor.text( o.year );
			title.html( anchor );
			anchor = anchor.clone( );
			head.html( title );
			title = title.clone( )
			accordion.html( head );
			head = head.clone( )
			body = body.clone( );
			body.attr( 'id' , 'y' + o.year );
			accordion.append( body );

			panel.append( accordion );
			accordion = accordion.clone( );
			if( i == 0 ) {
				body.addClass( 'in' );
			} else {
				body.removeClass( 'in' );
				body.addClass( 'collapse' );
			}
			table = tablePrototype.clone( );
			body.html( table );

			tbody = tbodyPrototype.clone( );
			table.html( tbody );
		}
		last = o;
		
		row = tr.clone( );
		tbody.append( row );
		
		number = td.clone( );
		number.addClass( 'number' )
		number.text( o.number );
		row.append( number );
		
		quote = td.clone( );
		quote.html( o.quote );
		row.append( quote );
	}
	panel.scrollTop(0);
}
