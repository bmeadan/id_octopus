<?php

namespace Drupal\id_octopus;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Config\ImmutableConfig;
use Drupal\Core\Database\Connection;
use Drupal\Core\Database\Database;
use Drupal\Core\Datetime\DrupalDateTime;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\id_octopus\Form\ReportMappingSettingsForm;

/**
 * Helper service to work with Alarm/Event logs.
 */
class OctopusHelper {

  use StringTranslationTrait;

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
  public ImmutableConfig $config;

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
   * Timeframes filter options.
   *
   * The keys will be used as "-N period" from now. See date_modify() example.
   *
   * @return array
   *   Timeframe options.
   */
  public function getTimeframeOptions(): array {
    return [
      '1 day' => $this->t('1 day'),
      '1 hour' => $this->t('1 hour'),
      '2 hours' => $this->t('2 hours'),
      '5 hours' => $this->t('5 hours'),
      '1 day' => $this->t('1 day'),
      '2 days' => $this->t('2 days'),
      '3 days' => $this->t('3 days'),
      '1 week' => $this->t('1 week'),
      '1 month' => $this->t('1 month'),
      'max' => $this->t('Max'),
    ];
  }

  public function getTimeframeOptionsfrom(): array {
    return [
      '1 second' => $this->t('Now'),
      '1 hour' => $this->t('1 hour'),
      '2 hours' => $this->t('2 hours'),
      '5 hours' => $this->t('5 hours'),
      '1 day' => $this->t('1 day'),
      '2 days' => $this->t('2 days'),
      '3 days' => $this->t('3 days'),
      '1 week' => $this->t('1 week'),
      '1 month' => $this->t('1 month'),
      'max' => $this->t('Max'),
    ];
  }
  /**
   * Helper function to get Alarm labels from settings.
   *
   * @return array
   *   Array with db_value => label structure.
   */
  public function getAlarmLabels(): array {
    $alarm_labels = $this->config->get('alarm') ?? [];
    return array_column($alarm_labels, 'label_value', 'db_value');
  }

  /**
   * Get Alarm report table data with processed labels.
   *
   * @param string $device_id
   *   Device ID.
   * @param array $filter
   *   Filter values to apply.
   *
   * @return array
   *   Array with report data.
   */
  public function getAlarmReport(string $device_id, array $filter): array {
    $report_data = $this->getAlarmReportData($device_id, $filter);
    $alarm_labels = $this->getAlarmLabels();
    foreach ($report_data as &$data) {
      if ($data['event_id'] ?? '') {
        $data['event_id'] = $alarm_labels[$data['event_id']] ?? $data['event_id'];
      }
    }

    return $report_data;
  }

  /**
   * Get Alarm report raw data.
   *
   * @param string $device_id
   *   Device ID.
   * @param array $filter
   *   Filter values to apply.
   *
   * @return array
   *   Array with report data.
   */
  public function getAlarmReportData(string $device_id, array $filter = []): array {
    $query = $this->externalDb->select('alarm_reports', 'ar');
    $query->fields('ar', ['event_id', 'datetime']);
    $query->condition('device_id', $device_id);

    if ($order_by = $filter['alarm_sort'] ?? NULL) {
      $order_by = explode('__', $order_by);
      $query->orderBy($order_by[0], strtoupper($order_by[1]));
    }
    else {
      $query->orderBy('datetime', 'DESC');
    }

    $timeframe = $filter['alarm_timeframe'] ?? array_key_first($this->getTimeframeOptions());
    if ($timeframe !== 'max') {
      $date = (new DrupalDateTime())->modify('-' . $timeframe)->format('Y-m-d H:i:s');
      $query->condition('datetime', $date, '>=');
    }

    if (
      ($alarm_type = $filter['alarm_type'] ?? NULL)
      && $alarm_type !== '_none'
    ) {
      $query->condition('event_id', $alarm_type);
    }

    // Uncomment if needed.
//    $query->range(0, 15);

    return $query->execute()->fetchAll(\PDO::FETCH_ASSOC);
  }

  /**
   * Helper function to get Alarm labels from settings.
   *
   * @return array
   *   Array with db_value => label structure.
   */
  public function getEventLabels(): array {
    $alarm_labels = $this->config->get('event') ?? [];
    return array_column($alarm_labels, 'label_value', 'db_value');
  }

  /**
   * Get Event report table data with processed labels.
   *
   * @param string $device_id
   *   Device ID.
   * @param array $filters
   *   Filter values to apply.
   *
   * @return array
   *   Array with report data.
   */
  public function getEventReport(string $device_id, array $filters): array {
    $report_data = $this->getEventReportData($device_id, $filters);
    $alarm_labels = $this->getEventLabels();
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
   * @param array $filter
   *   Filter values to apply.
   *
   * @return array
   *   Array with report data.
   */
  public function getEventReportData(string $device_id, array $filter): array {
    $query = $this->externalDb->select('event_reports', 'er');
    $query->fields('er', ['event_id', 'datetime']);
    $query->condition('device_id', $device_id);

    if ($order_by = $filter['event_sort'] ?? NULL) {
      $order_by = explode('__', $order_by);
      $query->orderBy($order_by[0], strtoupper($order_by[1]));
    }
    else {
      $query->orderBy('datetime', 'DESC');
    }
    $timeframe_from = $filter['event_timeframe_from'] ?? array_key_first($this->getTimeframeOptionsfrom()); 
    $timeframe_to = $filter['event_timeframe_to'] ?? array_key_first($this->getTimeframeOptions());
    if ($timeframe_to !== 'max') {
      $date_from = (new DrupalDateTime())->modify('-' . $timeframe_from)->format('Y-m-d H:i:s');
      $date_to = (new DrupalDateTime())->modify('-' . $timeframe_to)->format('Y-m-d H:i:s');
      //$query->condition('datetime', $date, '>=');
      $query->condition('datetime', [$date_to, $date_from], 'BETWEEN');

    }

    if (
      ($alarm_type = $filter['event_type'] ?? NULL)
      && $alarm_type !== '_none'
    ) {
      $query->condition('event_id', $alarm_type);
    }

    // Uncomment if needed.
//    $query->range(0, 15);

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
    $query->fields('er', [
      'temperature',
      'voltage',
    ]);
    $query->orderBy('datetime', 'DESC');
    $query->range(0, 1);
    $query->condition('device_id', $device_id);

    return $query->execute()->fetchAll(\PDO::FETCH_ASSOC);
  }


/**
   * Get Water Calculation report raw data.
   *
   * @param string $device_id
   *   Device ID.
   *
   * @return array
   *   Array with report data.
   */

  public function getWaterCalculationData(string $device_id,string $calctimeframe) {
    //$dater = date('Y-m-d H:i:s', strtotime($calctimeframe));
    $dater = (new DrupalDateTime())->modify($calctimeframe)->format('Y-m-d H:i:s');

    $query = $this->externalDb->select('wash_time', 'wr');
    $query->fields('wr', [
      'start_time',
      'stop_time',
      'duration',
    ]);
    $query->orderBy('start_time', 'DESC');
    $query->condition('device_id', $device_id);
    $query->condition('start_time', $dater, '>=' );
    return $query->execute()->fetchAll(\PDO::FETCH_ASSOC);
  }



/**
   * Get Stop Start data.
   *
   * @param string $device_id
   *   Device ID.
   *
   * @return array
   *   Array with report data.
   */

  public function getEventStartStopData(string $device_id) {
    $query = $this->externalDb->select('event_reports', 'er');
    $query->fields('er', [
      'datetime',
      'event_id',
    ]);
    $query->orderBy('datetime', 'DESC');
    $query->range(0, 1);
    $query->condition('device_id', $device_id);

    return $query->execute()->fetchAll(\PDO::FETCH_ASSOC);
  }
}

