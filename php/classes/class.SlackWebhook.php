<?php 

// Gist: https://gist.github.com/stefanzweifel/04be27486517cd7d3422

class SlackWebhook {
  private $icon = 'https://i.ibb.co/q7psxJh/photo-2021-05-18-20-18-01.jpg';
  private $channel = '#random';
  private $from = 'Quotes Web';
  private $fallback = 'Quotes fresca!';
  private $url;

  public function __construct($url) {
    $this->url = $url;
  }

  public function send($message) {
    $payload = [
      'icon_url' => $this->icon,
      'channel'  => $this->channel,
      'username' => $this->from,
      'text' => $this->fallback,
      'blocks' => [[
        'type' => 'section',
        'text' => [
          'type' => 'mrkdwn',
          'text' => "> {$message}",
        ],
      ]]
    ];

    $data_string = json_encode($payload);

    $ch = curl_init($this->url);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
    curl_setopt($ch, CURLOPT_POSTFIELDS, $data_string);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, array(
        'Content-Type: application/json',
        'Content-Length: ' . strlen($data_string))
    );

    //Execute CURL
    return curl_exec($ch);
  }
}
