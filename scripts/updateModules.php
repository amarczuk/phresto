<?php

require_once __DIR__ . '/../kernel/class/Utils.php';

Phresto\Utils::registerAutoload();
Phresto\Utils::updateModules();

echo 'done.';