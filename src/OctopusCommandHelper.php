<?php

namespace Drupal\id_octopus;

use Drupal\Component\Datetime\DateTimePlus;
use Drupal\Component\Serialization\Json;
use Drupal\Core\Database\Connection;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use GuzzleHttp\ClientInterface;

/**
 * Helper service to work with Device Commands.
 */
class OctopusCommandHelper {

  /**
   * Table name to store Command logs.
   */
  protected const COMMAND_LOG_TABLE = 'commands_sent';

  /**
   * Database connection service definition.
   *
   * @var \Drupal\Core\Database\Connection
   */
  protected Connection $database;

  /**
   * EntityTypeManager service definition.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected EntityTypeManagerInterface $entityTypeManager;

  /**
   * Http Client service definition.
   *
   * @var \GuzzleHttp\ClientInterface
   */
  protected ClientInterface $client;

  /**
   * OctopusCommandHelper constructor.
   *
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   EntityTypeManager service.
   * @param \Drupal\Core\Database\Connection $database
   *   Main connection to database.
   * @param \GuzzleHttp\ClientInterface $client
   *   Http Client service.
   */
  public function __construct(
    EntityTypeManagerInterface $entity_type_manager,
    Connection $database,
    ClientInterface $client
  ) {
    $this->database = $database;
    $this->entityTypeManager = $entity_type_manager;
    $this->client = $client;
  }

  /**
   * Handle Start Command.
   *
   * @param string $device_id
   *   Device ID.
   *
   * @return array
   *   Array with response data.
   *
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   * @throws \Exception
   */
  public function sendStartCommand(string $device_id): array {
    $response = [];

    if ($remote_url = $this->getDeviceCommandRemoteLink($device_id)) {
      $action = 'start_wash';
      if ($sequence = $this->logCommand($action, $device_id)) {
        $response = $this->sendCommand($action, $remote_url, $sequence, $device_id);
      }
    }

    return $response;
  }

  /**
   * Handle Stop Command.
   *
   * @param string $device_id
   *   Device ID.
   *
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   * @throws \Exception
   */
  public function sendStopCommand(string $device_id): array {
    $response = [];

    if ($remote_url = $this->getDeviceCommandRemoteLink($device_id)) {
      $action = 'stop_wash';
      if ($sequence = $this->logCommand($action, $device_id)) {
        $response = $this->sendCommand($action, $remote_url, $sequence, $device_id);
      }
    }

    return $response;
  }

  /**
   * Get Command remote link for device.
   *
   * @param string $device_id
   *   Device ID.
   *
   * @return string|null
   *   Remote link if set, Null otherwise.
   *
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   */
  protected function getDeviceCommandRemoteLink(string $device_id): ?string {
    $results = $this->entityTypeManager->getStorage('node')
      ->getAggregateQuery()
      ->condition('field_hash', $device_id)
      ->exists('field_command_remote_link')
      ->groupBy('field_command_remote_link')
      ->execute();

    return reset($results)['field_command_remote_link'] ?? NULL;
  }

  /**
   * Send Command data to remote.
   *
   * @param string $action
   *   Start/Stop command name.
   * @param string $remote_url
   *   Device remote link.
   * @param string $sequence
   *   Sequence ID.
   *
   * @return array
   *   Decoded JSON response.
   */
  protected function sendCommand(string $action, string $remote_url, string $sequence, string $device_id): array {
    $options = [
      'json' => [
        'type' => 'command',
        'sequence' => $sequence,
        'devide_id' => $device_id,
        'action' => $action,
      ],
    ];

    // Trick to test remote Response locally.
    // See id_octopus.receive_command.test route.
    if ($remote_url === '/receive-command/test') {
      // Do not use Dependency Injection, as needed only for tests.
      $request = \Drupal::request();
      $remote_url = $request->getSchemeAndHttpHost() . $remote_url;
      /** @var \Symfony\Component\HttpFoundation\Session\Session $session */
      $session = $request->getSession();
      // Pass Session Cookies to authenticate.
      $headers = [
        'Cookie' => $session->getName() . '=' . $session->getId(),
      ];
      $options['headers'] = $headers;
    }

    $response = $this->client->post($remote_url, $options);

    return Json::decode($response->getBody()) ?? [];
  }

  /**
   * Insert log into Database with Command data.
   *
   * @param string $type
   *   Command type.
   * @param string $device_id
   *   Device ID.
   *
   * @return string|null
   *   Last row inserted Seq_ID.
   *
   * @throws \Exception
   */
  protected function logCommand(string $type, string $device_id): ?string {
    $insert = $this->database->insert(self::COMMAND_LOG_TABLE)
      ->fields([
        'device_id' => $device_id,
        'type' => $type,
        'description' => '',
        'datetime' => (new DateTimePlus())->format(DateTimePlus::FORMAT),
      ]);
    return $insert->execute();
  }

}
