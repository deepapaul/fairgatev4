<?php

$digests             = openssl_get_md_methods();

echo "<pre>";
print_r($digests);
phpinfo();