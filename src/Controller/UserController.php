<?php

namespace Drupal\aarhus_kommune_management\Controller;

use Drupal\aarhus_kommune_management\Security\SecurityManager;
use Drupal\aarhus_kommune_management\Service\UserManager;
use GuzzleHttp\Psr7\ServerRequest;

/**
 * User controller.
 */
class UserController {
  /**
   * The user manager.
   *
   * @var \Drupal\aarhus_kommune_management\Service\UserManager
   */
  private $userManager;

  /**
   * The authentication manager.
   *
   * @var \Drupal\aarhus_kommune_management\Security\SecurityManager
   */
  private $securityManager;

  /**
   * Constructor.
   */
  public function __construct() {
    $this->userManager = new UserManager();
    $this->securityManager = new SecurityManager();
  }

  /**
   * Handler.
   */
  public function handle() {
    $request = $this->authenticate();
    switch ($_SERVER['REQUEST_METHOD']) {
      case 'GET':
        return $this->get();

      case 'POST':
        return $this->update();
    }

    throw new \Exception('Invalid request');
  }

  /**
   * Get users.
   */
  public function get() {
    $data = $this->userManager->getUsers();

    $users = array_values(array_map([$this->userManager, 'serializeUser'], $data));

    return ['data' => $users];
  }

  /**
   * Update users.
   */
  public function update() {
    $request = ServerRequest::fromGlobals();
    $payload = json_decode((string) $request->getBody(), TRUE);

    $result = [];

    if (isset($payload['users'])) {
      $commands = $payload['users'];

      if (isset($commands['create']) && is_array($commands['create'])) {
        foreach ($commands['create'] as $item) {
          $user = $this->userManager->createUser($item);
          $result['create'][] = $user;
        }
      }

      if (isset($commands['update']) && is_array($commands['update'])) {
        foreach ($commands['update'] as $item) {
          $user = $this->userManager->updateUser($item);
          $result['update'][] = $user;
        }
      }
    }

    return $result;
  }

  /**
   * Authenticate.
   */
  protected function authenticate() {
    return $this->securityManager->validateToken();
  }

}
