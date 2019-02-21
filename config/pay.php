<?php

return [
    'alipay' => [
        'app_id'         => '2016092700607975',
        'ali_public_key' => 'MIIBIjANBgkqhkiG9w0BAQEFAAOCAQ8AMIIBCgKCAQEAxEw9hVNupIhACEp0NTlyjMUt+GB5vjnXuu1GzsOYG3W5qgXNfE9xBvIpuAz/Gu+cDbM7Obcj2Hjjj8OeaKmN03uS02OazbTwEVoq+9sYet2j2Eqe1oIGP5O0uBPYFmsElfIeODg5FvvjW5dcYKhp9VBfCCmPMT/GUiuAuAYrYjC1Vz48Wx714N+mrXE6vJYCj17NAAp2I+yBRkIiTLL6DhS4Q91NJzEc1QHv4Hhm4kO3Emj81MRkG5hNn8QaKIj0SYUbDuKXhZqzfoank8lQRFgJ41HrgC5ujYhi3l8ROVYliJHUWUjz36KpnyMrGVcl8VrkJFa1ATJXEzyvERGhMQIDAQAB',
        'private_key'    => 'MIIEpAIBAAKCAQEAuujfrLNuzRoj1K87yIq0wbCQHdleJkEP5iBXUtQ4bwrAQI/MVcHc2xg2JmYNm6gPHqivEcnZ4ycFKsfNiwcyphig4T2Wjq4R/wyDFYnsMnCkvdLPkCsOJ4HQDrLsG3zG4fIpqpKYspwfzoDYLnl8K3Z/pIKfNkcCnF8oHy+KmibNQMHSLJ3trIw4EQu5q7Qh4W9KWY5W/fCklexIUUH6JIFKdrY/6x/9lHIK+GUAhUqQuXiEL2JWMH+L0ni/jaUpLc8n70297TbuUJJXA/+dta56NoDw/DiJe0ooLzYGQONKLJVOPQ8xFKSM8lS0KAfETOxZ55mHIhomz1YVVPgNUQIDAQABAoIBAQCcee0YFjgCuYmZuwJfx8+//+MHOj0Z5Yo77INusoB0ZhnBcyFsAhq1ybU7vGBUGgRedHXsfIJPKxGmWRN4+xOp/iBhsT/kP1KNpZnwZEE7kRY6XMysM39sD79xLtV/xZxy+Fttk8EGdrIiHbfeYJJViKUfwsMrm5NR+lAKm7B7j+ONMgtVWvi9OsNuiewA8FTrYvvhDANXycCgnKCwoPV0/aC25qhO2zKhlnu2KIEs2YGwU+BwYgkUcsGzMt6LpC3whZ4AGrPC4POWNuFbqAnFW/E8AgJqFF/uqS/mcNiwCu98iN86x9txDPTV/BxXuMc6GTm3dH+TFVtteIcLBk/hAoGBAPaZCU2bSAgmcVs3cUzNZAALP0pHUJffjYV4iLhxNsi5BOf+NtMm9p/CtTJ6+d2717FHNgsEO3JinG/ThLhs1IemrjvrE7PmQQWXs+42xZOH2GbNKO1rvxDxNPFSur38hlyHgdBtgmVnMQglljjbY37fwtKS/sW1PvQwg8Co3jU9AoGBAMIJPYDUFaysZU8Vi2hnXjSJ+ZSbFWc7hBuMYDSwIsFpAgSNf06nm8bt579RqRox/9PbYny7a3QLRc1ULZs9lOP8YDB1GekKiAZO1D1cCNONqCHp0/KsX3tOXQBdeNA8qBuRuGLL9iQ93mWNbFdkK9uzujXLjmoBscyjab8fXIGlAoGATRgxMWMPlQS3wcXGlTVNQzpA8i7zglewvf6d99PlRcGaSBFRAJiY1/+eeIa74Vedql3SBoWvNCaiM8/UGXhkxXgV1sO6qC6fO2lfJmVXRbVdsV4Ioplh5aETZTLxEP6G3xy6N/hUoPEvAhYGLgZgx8Sa+wxbvGHaqasVNcFVnx0CgYEArfEWn2yAWaDZ2MFZjahMfRDLGfrEfxyNWxzGCbV0EqoN0wL9gwch0Hzo6UtNKlx0F/ltGp/wvcB1PJIfqDDWTmKp4Aa5yX1VX0I/ZRoiF3O/q1cwVzVLCwC+wlsIuLQHh19kSbzJ1MdZnPPgkZ8M6hD99seZzVHjj61WWKzGgrkCgYBZgtinbU/Xbb2RG50r1kcR1em8tZbSoMR5z2fRjo5VdHEaB4N9Ijt9r3l+U+LhcIg30eYP3rj4m1TF03jR+f0sgc6wPHo8lgOL5VlmWAc32JTGExe3Z6T3hYjcRf+UEACt20b502h9WrOgfwc/71XIhOxeShdEBtdatAJSEUUUIg==',
        'log'            => [
            'file' => storage_path('logs/alipay.log'),
        ],
    ],

    'wechat' => [
        'app_id'      => '',
        'mch_id'      => '',
        'key'         => '',
        'cert_client' => '',
        'cert_key'    => '',
        'log'         => [
            'file' => storage_path('logs/wechat_pay.log'),
        ],
    ],
];