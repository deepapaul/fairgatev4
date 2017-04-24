<?php

$apiSecrets = array('secret_key' => '8f,q14OSN/b5vq)', 'secret_iv' => '7A6B1w-6Z+4');
$container->setParameter('apiSecrets', $apiSecrets);
$container->setParameter('xTenantToken', '0125ee8dfe42bafbec95aa0d2676c91d8a780715b76504cf798aae6e74c08a30');
$container->setParameter('gc_api_sales_email', 'sales@yopmail.com');
$gotcourtsApiPublicKeys = array('public_key' => 'y7h2JpskAenz6nGX', 'iterations' => 1000);
$container->setParameter('gotcourtsApiPublicKeys', $gotcourtsApiPublicKeys);