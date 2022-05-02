<?php

namespace Drupal\id_octopus\Controller;

use Drupal\Component\Serialization\Json;
use Drupal\Core\Ajax\AjaxResponse;
use Drupal\Core\Ajax\CssCommand;
use Drupal\Core\Controller\ControllerBase;
use Drupal\id_octopus\OctopusCommandHelper;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

/**
 * Provides controller to handle commands.
 */
class DeviceSendCommandController extends ControllerBase {

  /**
   * OctopusHelper service definition.
   *
   * @var \Drupal\id_octopus\OctopusCommandHelper
   */
  protected OctopusCommandHelper $octopusCommandHelper;

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('id_octopus.octopus_command_helper')
    );
  }

  /**
   * DeviceSendCommandController constructor.
   *
   * @param \Drupal\id_octopus\OctopusCommandHelper $octopus_command_helper
   *   OctopusCommandHelper service.
   */
  public function __construct(OctopusCommandHelper $octopus_command_helper) {
    $this->octopusCommandHelper = $octopus_command_helper;
  }

  /**
   * Handle Start command click.
   *
   * @param string $device_id
   *   Device ID.
   *
   * @return \Drupal\Core\Ajax\AjaxResponse
   *   Ajax response.
   *
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   */
  public function startCommand(string $device_id): AjaxResponse {
    $response = new AjaxResponse();
    $result = $this->octopusCommandHelper->sendStartCommand($device_id);
    if ($result_value = $result['response'] ?? NULL) {
      $selector = '.command-start';
      $color = $result_value === 'ack' ? 'green' : 'red';
      $response->addCommand(new CssCommand($selector, [
        'color' => $color,
        'border-color' => $color,
      ]));
    }

    return $response;
  }

  /**
   * Handle Stop command click.
   *
   * @param string $device_id
   *   Device ID.
   *
   * @return \Drupal\Core\Ajax\AjaxResponse
   *   Ajax response.
   *
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   */
  public function stopCommand(string $device_id): AjaxResponse {
    $response = new AjaxResponse();
    $result = $this->octopusCommandHelper->sendStopCommand($device_id);
    if ($result_value = $result['response'] ?? NULL) {
      $selector = '.command-stop';
      $color = $result_value === 'ack' ? 'green' : 'red';
      $response->addCommand(new CssCommand($selector, [
        'color' => $color,
        'border-color' => $color,
      ]));
    }

    return $response;
  }

  /**
   * Temporary test endpoint to emulate Command Requests.
   *
   * @param \Symfony\Component\HttpFoundation\Request $request
   *   Request object.
   *
   * @return \Symfony\Component\HttpFoundation\JsonResponse
   *   Response object.
   *
   * @throws \Exception
   */
  public function test(Request $request) {
    $content = Json::decode($request->getContent());
    // Emulate Success/Error response data.
    $response = random_int(0, 1) ? 'ack' : 'nack';
    $data = [
      'type' => 'response',
      'sequence' => $content['sequence'],
      'response' => $response,
    ];

    return new JsonResponse($data);
  }

}
