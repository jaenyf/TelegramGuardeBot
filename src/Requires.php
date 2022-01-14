<?php

declare(strict_types=1);

/**
 * Global functions to help including and requiring php files
 */

 /**
  * helper to include or require files from app root folder
  */
 function fromAppRoot(string $fileName)
 {
    return __DIR__ . '/../' . $fileName;
 }

 /**
  * helper to include or require files from src folder
  */
 function fromAppSource(string $fileName)
 {
    return __DIR__ . '/' . $fileName;
 }
