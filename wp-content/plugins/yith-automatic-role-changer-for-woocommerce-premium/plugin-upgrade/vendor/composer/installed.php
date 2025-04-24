<?php return array(
    'root' => array(
        'name' => 'yith/plugin-upgrade-framework',
        'pretty_version' => 'dev-master',
        'version' => 'dev-master',
        'reference' => '3da88f41dc0322ef4d68e93e1ecb2ff9b61c080a',
        'type' => 'library',
        'install_path' => __DIR__ . '/../../',
        'aliases' => array(),
        'dev' => true,
    ),
    'versions' => array(
        'newfold-labs/wp-pls-utility' => array(
            'pretty_version' => 'dev-main',
            'version' => 'dev-main',
            'reference' => '22c80ae0550d41dbf2f713de11c72e45ef97b53a',
            'type' => 'library',
            'install_path' => __DIR__ . '/../newfold-labs/wp-pls-utility',
            'aliases' => array(
                0 => '9999999-dev',
            ),
            'dev_requirement' => false,
        ),
        'yith/plugin-upgrade-framework' => array(
            'pretty_version' => 'dev-master',
            'version' => 'dev-master',
            'reference' => '3da88f41dc0322ef4d68e93e1ecb2ff9b61c080a',
            'type' => 'library',
            'install_path' => __DIR__ . '/../../',
            'aliases' => array(),
            'dev_requirement' => false,
        ),
    ),
);
