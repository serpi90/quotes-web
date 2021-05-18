<?php
  if( !isset($_REQUEST['quote']) or empty($_REQUEST['quote']) ) {
    $result['error'] = TRUE;
    $result['errorDescription'] = 'No se ingreso quote';
    $result['errorDetail'] = 'Parametro: `quote` ausente';
    die(json_encode($result));
  } else if ( !isset($_REQUEST['quoted']) or empty($_REQUEST['quoted']) ) {
    $result['error'] = TRUE;
    $result['errorDescription'] = 'Debe elegirse al menos un quoteado';
  $result['errorDetail'] = 'Parametro: `quoted` (QuotedId) ausente';
    die(json_encode($result));
  }
  $quote = utf8_decode( $_REQUEST['quote'] );
  $unknown = array( );
  $known = array( );

  foreach( $_REQUEST['quoted'] as $id ) {
    if( (int) $id <= 0 ) {
      array_push( $unknown, $id );
    } else {
      array_push( $known, $id );
    }
  }

  if( !empty($unknown) ) {
    $result['error'] = TRUE;
    $result['errorDescription'] = 'QuotedId invalidos';
    $result['invalid'] = $unknown;
  }
  if( empty( $known ) ) {
    $result['error'] = TRUE;
    $result['errorDescription'] = 'Debe elegirse al menos un quoteado valido';
    die(json_encode($result));
  }

  require_once('header.php');

  $qdr = new QuotedRepository( $db );
  $qr = new QuoteRepository( $db, $qdr );

  $quoted = array( );

  foreach( array_keys($known) as $key) {
    $id = $known[$key];
    try {
      $q = $qdr->getQuotedWithId( $id );
      array_push( $quoted, $q );
    } catch (OutOfRangeException $e) {
      array_push( $unknown, $id );
      unset( $known[$key] );
    }
  }

  $qr->addQuote( $quote, $quoted );
  $result['success'] = TRUE;

  $message = 'Nueva quote: <br/>&nbsp;&nbsp;&nbsp;&nbsp;<i>'.$quote.'</i>';
  $result['mail'] = sendMail( $message,  $newQuoteSubject );

  echo json_encode($result);

  $bot = new TelegramBot( $telegram['token'] );
  $authors = implode(", ", array_map(function ($author) { return $author->name(); }, $quoted));
  $message = 'Quote Fresca: ' . utf8_encode($quote) . "\n- _{$authors}_";
  foreach( $telegram['chat_ids'] as $chatId ) {
    $bot->sendMessage( $chatId, $message, true);
  }

  $slack = new SlackWebhook($slackWebhook);
  $slack->send($message);
?>