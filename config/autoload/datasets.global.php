<?php

declare(strict_types=1);

return [
    'datasets' => [
        'shared' => [
            'base_path' => getcwd().'/data/datasets/shared/',
            'csv_path'  => getcwd().'/data/datasets/shared/csv/',
            'geo_path'  => getcwd().'/data/datasets/shared/geo/',
            'i18n_path' => getcwd().'/data/datasets/shared/i18n/',
            'download' => [
                'base' => [
                    'country.json' => 'https://raw.githubusercontent.com/lukes/ISO-3166-Countries-with-Regional-Codes/master/slim-2/slim-2.json',
                    'currency.json' => 'https://raw.githubusercontent.com/tammoippen/iso4217parse/master/iso4217parse/data.json',
                ],
                'i18n' => [
                    'cs' => 'https://raw.githubusercontent.com/michaelwittig/node-i18n-iso-countries/master/langs/cs.json',
                    'hu' => 'https://raw.githubusercontent.com/michaelwittig/node-i18n-iso-countries/master/langs/hu.json',
                    'sk' => 'https://raw.githubusercontent.com/michaelwittig/node-i18n-iso-countries/master/langs/sk.json',
                ],
            ],
            'files' => ['category', 'type'],
            'i18n' => ['sk'],
        ],
        'address' => [
            'sk' => [
                'base_path' => getcwd().'/data/datasets/sk/',
                'csv_path'  => getcwd().'/data/datasets/sk/csv/',
                'temp_path' => getcwd().'/data/datasets/sk/temp/',
                'json_file' => 'address_sk.json',
                'csv_file' => 'address_sk.csv',
                // Source-1
                'url_base' => 'https://data.gov.sk/dataset/b27f57f1-7e76-45e0-8968-631f9176b2e9/resource/',
                'url_path' => [
                    'banskobystricky' => '3cd86f57-c437-47c1-8840-6c3cb2b85452/download/adresybbkrajutf8.csv',
                    'bratislavsky' => 'e37f974a-912f-46f7-8273-a2eca60c4b96/download/adresybakrajutf8.csv',
                    'kosicky' => 'f2a97ec1-6169-4394-945a-5dbc2dd13edf/download/adresykekrajutf8.csv',
                    'nitriansky' => 'a4558f5c-e90f-45d0-8637-c28f93788934/download/adresynrkrajutf8.csv',
                    'presovsky' => '03785168-1b0b-45e3-b6ba-be3884ada2b8/download/adresypokrajutf8.csv',
                    'trenciansky' => '87706bd7-c5db-47d3-a816-8580bfce1f99/download/adresytnkrajutf8.csv',
                    'trnavsky' => 'a9e83b89-6470-455b-b859-588a41bfdb15/download/adresyttkrajutf8.csv',
                    'zilinsky' => 'a0af4445-ce7f-41bd-a996-9658b36cda0a/download/adresyzakrajutf8.csv',
                ],
                'columns' => [
                    'KRAJ' => 'county',
                    'OKRES' => 'district',
                    'OBEC' => 'town',
                    'CAST_OBCE' => 'town_part',
                    'ULICA' => 'street',
                    'PSC' => 'postcode',
                    'ADRBOD_X' => 'longitude',
                    'ADRBOD_Y' => 'latitude',
                ],
                // Source-2
                'baseurl' => 'https://data.gov.sk/api/action/datastore_search_sql?sql=',
                'search_sql' => [
                    'county'    => 'SELECT "objectId","regionCode","regionName" FROM "3bbb0b04-8732-4099-b074-c7bd8f8fa080" WHERE "validTo">=now()',
                    'district'  => 'SELECT "regionIdentifier","objectId","countyCode","countyName" FROM "1829233e-53f3-4c6a-9ad6-b27f33ec7550" WHERE "validTo">=now()',
                    'town'      => 'SELECT "status","objectId","countyIdentifier","municipalityCode","municipalityName" FROM "15262453-4a0f-4cce-a9e4-7709e135e4b8" WHERE "validTo">=now()',
                    'town_part' => 'SELECT "objectId","municipalityIdentifier","districtName","districtCode" FROM "cc20ba54-79e5-4232-a129-6af5e75e3d85" WHERE "validTo">=now()',
                    'street'    => 'SELECT "objectId","municipalityIdentifiers","streetName","districtIdentifiers" FROM "fc7dc622-a728-4e11-88b1-ee305ceaa896" WHERE "validTo">=now()'
                ],
                'map_columns' => [
                    'countyCode' => 'alpha',
                    'countyIdentifier' => 'district_id',
                    'countyName' => 'name',
                    'districtIdentifiers' => 'county_ids',
                    'districtName' => 'name',
                    'districtCode' => 'numeric',
                    'municipalityCode' => 'alpha',
                    'municipalityIdentifier' => 'town_id',
                    'municipalityIdentifiers' => 'town_ids',
                    'municipalityName' => 'name',
                    'objectId' => 'id',
                    'regionCode' => 'alpha',
                    'regionIdentifier' => 'county_id',
                    'regionName' => 'name',
                    'status' => 'status',
                    'streetName' => 'name'
                ],
                // Source-3
                'street_file' => 'postcode_post_street.csv',
                'street_map' => [
                    'OBCE' => 'name_town',
                    'DULICA' => 'name',
                    'PSC' => 'postcode'
                ],
                'town_file' => 'postcode_post_town.csv',
                'town_map' => [
                    'KRAJ' => 'alpha-2',
                    'KOD_OKR' => 'numeric',
                    'DOBEC' => 'name',
                    'CAST' => 'name_part',
                    'PSC' => 'postcode'
                ],
                'xref_county_file' => 'xref_county.csv',
                'xref_district_file' => 'xref_district.csv',
            ],
        ],
    ],
];
