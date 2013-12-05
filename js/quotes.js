$(document).ready( initDocument );

function initDocument() {
	$.ajax({ url: 'php/quoted.php', success : [loadQuotedList,loadQuotedSelect] });
	$.ajax({ url: 'php/quoteYears.php', success: initYearListAndMainQuotes });
	$('#side-panel-quotes-list').change( loadSelectedYearQuotes );
	$('#new-quote-submit').click( newQuote );
	$('#new-quote-cancel').click( function( event ) {
		$('#new-quote-quote').val('');
		$('#new-quote-quoted').select2('val', '');
		$('#new-quote-success').hide();
		$('#new-quote-info').hide();
		$('#new-quote-warning').hide();
		$('#new-quote-danger').hide();
		});
	$("#new-quote-quoted").select2({placeholder: "Quoteado(s)"});
	$('#new-quote-quoted-chk').click( function( event ) {
		var quotedUrl = event.target.checked ? 'php/quoted.php?all' : 'php/quoted.php';
		$.ajax({ url: quotedUrl, success : loadQuotedSelect });
	});
	$('#side-panel-quoted-all').click( function( event ) {
		if( event.target.value == "on" ) {
			event.target.value = "off";
		} else {
			event.target.value = "on";
		}
		var quotedUrl =  event.target.value == "on" ? 'php/quoted.php?all' : 'php/quoted.php';
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

	panel.html('');
	panel.append( title );
	title.html( title.text( ) + ' <small>(' + array.length +  ')</small>');

	var	container = quoteGroupPanel( );
	var body = quotesPanel( );
	var table = customTable( );
	var tbody = $(document.createElement('TBODY'));


	container.addClass( 'panel-no-border-top' );

	table.append( tbody );
	body.append( table );
	container.append( body );
	panel.append( container );

	for( var i=0; i < array.length ; i++ ) {
		var o = array[i];
		tbody.append( tableRowFromQuote( o.number, o.quote ) );
	}
	panel.scrollTop(0);
}

function loadQuotesWithYearSeparation( json ) {
	var array = $.parseJSON( json );
	var panel = $('#quotes-panel');
	var title = $('#title');
	title.html( title.text( ) + ' <small>' + array.length + ')</small>');

	panel.html('');
	panel.append( title );

	var last;
	if( array.length > 0 ) {
		last = array[0];
	}
	var head;
	var quoteCount;
	var count;
	for( var i=0; i < array.length ; i++ ) {
		var o = array[i];
		if( o.year != last.year || i == 0 ) {
			var accordion = quoteGroupPanel( );
			if( head ) {
				head.children().first().append(yearQuoteCount(quoteCount));
			}
			head = accordionHead( o.year );
			var body = quotesPanel( );
			body.attr( 'id' , 'y' + o.year );


			accordion.html( head );
			accordion.append( body );

			panel.append( accordion );

			if( i == 0 ) {
				body.addClass( 'in' );
			} else {
				body.removeClass( 'in' );
				body.addClass( 'collapse' );
			}
			var table = customTable( );
			body.html( table );

			var tbody = $(document.createElement('TBODY'));
			table.html( tbody );
			quoteCount = 0;
		}
		last = o;
		tbody.append( tableRowFromQuote( o.number, o.quote ) );
		quoteCount++;
	}
	
	head.children().first().append(yearQuoteCount(quoteCount));
	panel.scrollTop(0);
}

function yearQuoteCount( quoteCount ) {
	var count = document.createElement('SMALL');
	count.classList.add('pull-right');
	count.textContent = quoteCount + ( quoteCount == 1 ? ' quote': ' quotes' );
	return count;	
}

function tableRowFromQuote( number, quote ) {
	var tr = $(document.createElement('TR'));
	var td = $(document.createElement('TD'));

	td.addClass( 'number' );
	td.text( number );
	tr.append( td );

	td = $(document.createElement('TD'));
	td.html( quote );
	tr.append( td );

	return tr;
}

function customTable( ) {
	var table = $(document.createElement('TABLE'));
	table.addClass('table');
	table.addClass('table-striped');
	table.addClass('table-condensed');
	table.addClass('no-margin-bot');
	return table;
}

function quotesPanel( ) {
	var panel = $(document.createElement('DIV'));
	panel.addClass( 'panel-body' );
	panel.addClass( 'panel-quotes' );
	return panel;
}

function quoteGroupPanel( ){
	var panel = $(document.createElement('DIV'));
	panel.addClass( 'panel' );
	panel.addClass( 'panel-default' );
	return panel;
}

function accordionHead( year ) {
	var head = $(document.createElement('DIV'));
	head.addClass( 'panel-heading' );
	head.addClass( 'no-border-bottom' );

	var title = $(document.createElement('h4'));
	title.addClass( 'panel-title' );

	var anchor = $(document.createElement('A'));
	anchor.addClass( 'accordion-toggle' );
	anchor.attr( 'data-toggle', 'collapse' );
	anchor.attr( 'data-parent', '#quotes-panel' );
	anchor.attr( 'href' , '#y'+year );
	anchor.text( year );

	title.html( anchor );
	head.html( title );

	return head;
}

