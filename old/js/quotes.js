$(document).ready( initDocument );

function initDocument() {
	$.ajax({url: 'php/quoted.php', success : [loadQuotedList,loadQuotedSelect] });
	$.ajax({url: 'php/quoteYears.php', success: initYearListAndMainQuotes });
	$('#side-panel-quotes-list').change( loadSelectedYearQuotes );
	$('#new-quote-submit').click( newQuote );
	$('#new-quote-cancel').click( function(event) {
		$('#new-quote-quote').val('');
		$('#new-quote-quoted').select2('val','');
		$('#new-quote-success').hide();
		$('#new-quote-info').hide();
		$('#new-quote-warning').hide();
		$('#new-quote-danger').hide();
		});
	$("#new-quote-quoted").select2({placeholder: "Quoteado(s)"});
	$('#new-quote-quoted-chk').click( function(event) {
		var quotedUrl = 'php/quoted.php';
		if( event.target.checked ) {
			quotedUrl += '?all';
		}
		$.ajax({url: quotedUrl, success : loadQuotedSelect });
	});
	$('#side-panel-quoted-all').click( function(event) {
		var quotedUrl = 'php/quoted.php';
		if( event.target.value == "on" ) {
			event.target.value = "off";
		} else {
			event.target.value = "on";
		}
		if( event.target.value == "on" ) {
			quotedUrl += '?all';
		}
		$.ajax({url: quotedUrl, success : loadQuotedList });
	});

}

function loadSelectedYearQuotes( event ) {
		clearQuotedSelection( );
		year = getSelectedYear( );
		$.ajax({url: 'php/quotesByYear.php?y='+year, success: loadQuotes });
		$('#title').text('Quotes del '+ year );
	}

function newQuote ( event ) {
	$('#new-quote-success').hide();
	$('#new-quote-info').hide();
	$('#new-quote-warning').hide();
	$('#new-quote-danger').hide();
	var ids = $('#new-quote-quoted').select2('val');
	var quote = $('#new-quote-quote').val( );
	$.ajax({url: 'php/newQuote.php', data: {"quoted[]":ids, "quote":quote}, success : displayNewQuoteResult });
}

function displayNewQuoteResult( json ) {
	var result = $.parseJSON( json );
	if( result.hasOwnProperty('success') && result.success ) {
		var success = $('#new-quote-success');
		success.html("Quote exitosa");
		success.show();
		$.ajax({url: 'php/quoteYears.php', success: initYearListAndMainQuotes });
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
	year = loadYearList( json );
	$.ajax({url:'php/quotesByYear.php?y='+year, success: loadQuotes });
	$('#title').text('Quotes del '+ year ) ;
	clearQuotedSelection( );
}

function getSelectedYear() {
	years = $('#side-panel-quotes-list').children( );
	for( var i=0; i < years.length ; i++ ) {
		year = years[i];
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
	anchor.attr('type', 'button');
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
	var option = $(document.createElement('OPTION'));
	var select = $('#new-quote-quoted');
	select.html('');

	for( var i=0; i < array.length ; i++ ) {
		var each = array[i];
		var quoted = option.clone();
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
		listElements[i].classList.remove('active');
	}
}

function loadUserQuotes( event ) {
	clearQuotedSelection( );
	event.target.parentElement.classList.add('active');
	id = event.target.attributes.getNamedItem('quoted').value;
	quoted = event.target.textContent;
	$.ajax({url:'php/quotesByQuoted.php?q='+id, success: loadQuotes} );
	$('#title').text('Quotes por '+ quoted );
	$('#side-panel-quotes-list').children( )[0].selected = true;
}

function loadYearList(json) {
	var array = $.parseJSON(json);
	var option = $(document.createElement('OPTION'));
	var list = $('#side-panel-quotes-list');
	list.html('');
	title = option.clone( );
	title.attr('disabled',true);
	title.text( 'Año' );
	list.append( title );
	for( var i=0; i < array.length ; i++ ) {
		year = option.clone();
		year.value = array[i];
		year.text(array[i]);
		list.append(year);
	}
	delete(option);
	return array[0];
}
function loadQuotes(json) {
	array = $.parseJSON(json);
	table = $('#quotes-panel-table');
	table.html('');
	tr = $(document.createElement('TR'));
	td = $(document.createElement('TD'));
	var last;
	if( array.length > 0 ) {
		last = array[0];
	}
	for( var i=0; i < array.length ; i++ ) {
		o = array[i];
		if( o.hasOwnProperty('year') && (o.year != last.year || i == 0) ) {
			row = tr.clone( );
			table.append( row );
			th = $(document.createElement('TH'));
			th.attr('colspan',2);
			th.text(o.year);
			row.append( th );
		}
		last = o;
		row = tr.clone( );
		table.append( row );
		number = td.clone( );
		number.addClass( 'number' )
		number.text( o.number );
		row.append( number );
		quote = td.clone( );
		quote.html( o.quote );
		row.append( quote );
	}
	delete( tr );
	delete( td );
	$('#quotes-panel').scrollTop(0);
}
