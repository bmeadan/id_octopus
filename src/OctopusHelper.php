<?php

namespace Drupal\id_octopus;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Config\ImmutableConfig;
use Drupal\Core\Database\Connection;
use Drupal\Core\Database\Database;
use Drupal\Core\Datetime\DrupalDateTime;
use Drupal\id_octopus\Form\ReportMappingSettingsForm;

/**
 * Helper service to work with Alarm/Event logs.
 */
class OctopusHelper {

  /**
   * Database connection service definition.
   *
   * @var \Drupal\Core\Database\Connection
   */
  protected Connection $database;

  /**
   * Module configuration.
   *
   * @var \Drupal\Core\Config\ImmutableConfig
   */
  protected ImmutableConfig $config;

  /**
   * External Database connection.
   *
   * @var \Drupal\Core\Database\Connection
   */
  public Connection $externalDb;

  /**
   * OctopusHelper constructor.
   *
   * @param \Drupal\Core\Database\Connection $database
   *   Main connection to database.
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   ConfigFactory service.
   */
  public function __construct(Connection $database, ConfigFactoryInterface $config_factory) {
    $this->database = $database;
    $this->config = $config_factory->get(ReportMappingSettingsForm::SETTINGS_NAME);
    $this->externalDb = Database::getConnection('external');
  }

  /**
   * Get Alarm report table data with processed labels.
   *
   * @param string $device_id
   *   Device ID.
   *
   * @return array
   *   Array with report data.
   */
  public function getAlarmReport(string $device_id): array {
    $report_data = $this->getAlarmReportData($device_id);
    $alarm_labels = $this->config->get('alarm') ?? [];
    $alarm_labels = array_column($alarm_labels, 'label_value', 'db_value');
    foreach ($report_data as &$data) {
      $data['alarm_id'] = $alarm_labels[$data['alarm_id']] ?? $data['alarm_id'];
    }

    return $report_data;
  }

  /**
   * Get Alarm report raw data.
   *
   * @param string $device_id
   *   Device ID.
   *
   * @return array
   *   Array with report data.
   */
  public function getAlarmReportData(string $device_id): array {
    $query = $this->externalDb->select('alarm_reports', 'ar');
//    $query = $this->database->select('alarm_reports', 'ar');
    $query->fields('ar', ['alarm_id', 'datetime']);
    $query->orderBy('datetime', 'DESC');
    $query->range(0, 15);
    $query->condition('device_id', $device_id);

    return $query->execute()->fetchAll(\PDO::FETCH_ASSOC);
  }

  /**
   * Get Event report table data with processed labels.
   *
   * @param string $device_id
   *   Device ID.
   *
   * @return array
   *   Array with report data.
   */
  public function getEventReport(string $device_id): array {
    $report_data = $this->getEventReportData($device_id);
    $alarm_labels = $this->config->get('event') ?? [];
    $alarm_labels = array_column($alarm_labels, 'label_value', 'db_value');
    foreach ($report_data as &$data) {
      $data['event_id'] = $alarm_labels[$data['event_id']] ?? $data['event_id'];
    }

    return $report_data;
  }

  /**
   * Get Event report raw data.
   *
   * @param string $device_id
   *   Device ID.
   *
   * @return array
   *   Array with report data.
   */
  public function getEventReportData(string $device_id): array {
    $query = $this->externalDb->select('event_reports', 'er');
//    $query = $this->database->select('event_reports', 'er');
    $query->fields('er', ['event_id', 'datetime']);
    $query->orderBy('datetime', 'DESC');
    $query->range(0, 15);
    $query->condition('device_id', $device_id);

    return $query->execute()->fetchAll(\PDO::FETCH_ASSOC);
  }

  /**
   * Get Pressure report raw data.
   *
   * @param string $device_id
   *   Device ID.
   * @param string $timeframe
   *   Timeframe to select data from.
   *
   * @return array
   *   Array with report data.
   */
  public function getEventPressureData(string $device_id, string $timeframe): array {
    $query = $this->externalDb->select('event_reports', 'er');
//    $query = $this->database->select('event_reports', 'er');
    $query->fields('er', [
      'datetime',
      'left_pressure',
      'right_pressure',
    ]);
    $query->orderBy('datetime');
    $query->condition('device_id', $device_id);

    if ($timeframe !== 'max') {
      $date = (new DrupalDateTime())->modify('-' . $timeframe)->format('Y-m-d H:i:s');
      $query->condition('datetime', $date, '>=');
    }

    return $query->execute()->fetchAll(\PDO::FETCH_ASSOC);
  }

  /**
   * Get Temperature and Voltage report raw data.
   *
   * @param string $device_id
   *   Device ID.
   *
   * @return array
   *   Array with report data.
   */
  public function getEventTempVoltageData(string $device_id) {
    $query = $this->externalDb->select('event_reports', 'er');
//    $query = $this->database->select('event_reports', 'er');
    $query->fields('er', [
      'temperature',
      'voltage',
    ]);
    $query->orderBy('datetime', 'DESC');
    $query->range(0, 1);
    $query->condition('device_id', $device_id);

    return $query->execute()->fetchAll(\PDO::FETCH_ASSOC);
  }

}
