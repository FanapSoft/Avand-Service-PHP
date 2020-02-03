<?php
return
    [
        'issueInvoice' => [
            'baseUri'   => 'PLATFORM-ADDRESS',
            'subUri' => 'nzh/doServiceCall',
            'method'    => 'POST'
        ],

        'verifyInvoice' => [
            'baseUri'   => 'PLATFORM-ADDRESS',
            'subUri'    => 'nzh/doServiceCall',
            'method'    => 'POST'
        ],

        'cancelInvoice' =>  [
            'baseUri' =>  'PLATFORM-ADDRESS',
            'subUri' => 'nzh/doServiceCall',
            'method' =>  'POST'
        ],

        'getInvoiceList' =>  [
            'baseUri' =>  'PLATFORM-ADDRESS',
            'subUri' => 'nzh/doServiceCall',
            'method' =>  'POST'
        ],

    ];
