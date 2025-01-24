<?php return array(
    'root' => array(
        'name' => 'wpauth/pdf-embedder',
        'pretty_version' => '4.9.0',
        'version' => '4.9.0.0',
        'reference' => null,
        'type' => 'library',
        'install_path' => __DIR__ . '/../../',
        'aliases' => array(),
        'dev' => true,
    ),
    'versions' => array(
        'roave/security-advisories' => array(
            'pretty_version' => 'dev-latest',
            'version' => 'dev-latest',
            'reference' => 'e7a38fcc13e4ddfe9a28d5c7bf50aa9a9da758ec',
            'type' => 'metapackage',
            'install_path' => null,
            'aliases' => array(
                0 => '9999999-dev',
            ),
            'dev_requirement' => true,
        ),
        'woocommerce/action-scheduler' => array(
            'pretty_version' => '3.9.0',
            'version' => '3.9.0.0',
            'reference' => '90b98e6fe97d455679b1d288f050cad8f6f79771',
            'type' => 'wordpress-plugin',
            'install_path' => __DIR__ . '/../woocommerce/action-scheduler',
            'aliases' => array(),
            'dev_requirement' => false,
        ),
        'wpauth/pdf-embedder' => array(
            'pretty_version' => '4.9.0',
            'version' => '4.9.0.0',
            'reference' => null,
            'type' => 'library',
            'install_path' => __DIR__ . '/../../',
            'aliases' => array(),
            'dev_requirement' => false,
        ),
    ),
);
