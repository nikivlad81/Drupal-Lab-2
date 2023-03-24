<?php

namespace Drupal\mymodule;

/**
 * {@inheritdoc}
 */

use Drupal\Core\Cache\CacheableRedirectResponse;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\Core\Url;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\KernelEvents;

/**
 * {@inheritdoc}
 */
class EventSubscriberHW implements EventSubscriberInterface {

  use StringTranslationTrait;

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents() {
    $events[KernelEvents::REQUEST][] = ['redirectHomeWork', 1200];
    return $events;
  }

  /**
   * Add param to URL.
   *
   * @param \Symfony\Component\HttpKernel\Event\RequestEvent $event
   *   Symfony lib event.
   */
  public function redirectHomeWork(RequestEvent $event) {
    $request = $event->getRequest();

    if (array_key_exists('param1', $request->query->all())) {
      return;
    }
    $link = $request->getPathInfo();

    $url_object = \Drupal::service('path.validator')->getUrlIfValid($link);

    if ($url_object == FALSE) {
      return;
    }

    $route_name = $url_object->getRouteName();
    $route_params = $url_object->getRouteParameters();

    $request->query->set('param1', 'test');
    $query['query'] = $request->query->all();

    $url = Url::fromRoute($route_name, $route_params, $query)->toString();

    $event->setResponse(new CacheableRedirectResponse($url));
  }

}
