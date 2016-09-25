<?php

namespace Drupal\porterstemmer\Plugin;

use Drupal\Core\Plugin\DefaultPluginManager;
use Drupal\Core\Cache\CacheBackendInterface;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Component\Plugin\Factory\DefaultFactory;

/**
 * Provides the Stemmer plugin plugin manager.
 */
class StemmerPluginManager extends DefaultPluginManager {

  /**
   * Constructor for StemmerPluginManager objects.
   *
   * @param \Traversable $namespaces
   *   An object that implements \Traversable which contains the root paths
   *   keyed by the corresponding namespace to look for plugin implementations.
   * @param \Drupal\Core\Cache\CacheBackendInterface $cache_backend
   *   Cache backend instance to use.
   * @param \Drupal\Core\Extension\ModuleHandlerInterface $module_handler
   *   The module handler to invoke the alter hook with.
   */
  public function __construct(\Traversable $namespaces, CacheBackendInterface $cache_backend, ModuleHandlerInterface $module_handler) {
    parent::__construct('Plugin/StemmerPlugin', $namespaces, $module_handler, 'Drupal\porterstemmer\Plugin\StemmerPluginInterface', 'Drupal\porterstemmer\Annotation\StemmerPlugin');

    $this->alterInfo('porterstemmer_stemmer_plugin_info');
    $this->setCacheBackend($cache_backend, 'porterstemmer_stemmer_plugin_plugins');
    $this->factory = new DefaultFactory($this->getDiscovery());
  }

}
