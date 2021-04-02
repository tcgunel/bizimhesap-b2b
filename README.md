[![License](https://poser.pugx.org/tcgunel/netgsm/license)](https://packagist.org/packages/tcgunel/netgsm)
[![Buy us a tree](https://img.shields.io/badge/Treeware-%F0%9F%8C%B3-lightgreen)](https://plant.treeware.earth/tcgunel/netgsm)
[![PHP Composer](https://github.com/tcgunel/xml-aligner/actions/workflows/tests.yml/badge.svg)](https://github.com/tcgunel/xml-aligner/actions/workflows/tests.yml)

[comment]: <> ([![PHP Composer]&#40;https://github.com/tcgunel/netgsm/actions/workflows/laravel8-tests.yml/badge.svg&#41;]&#40;https://github.com/tcgunel/netgsm/actions/workflows/laravel8-tests.yml&#41;)

# Xml aligner
Converts small/large xml files by the data structure of given array with minimum memory consumption

Only tags and their contents are being processed. Attributes will be ignored.

Uses XMLWriter, XMLReader, fopen for memory efficiency. SimpleXML for parsing small parts of XML tags. 

## Requirements
| PHP    | Package |
|--------|---------|
| ^7.2.5 | v1.0.0  |

## Kurulum

1) Download package with composer:
```
composer require TCGunel/xml-aligner
```

Example Usage
====================
**1:n Gönderim**

```
// Each key represents xml tag from source xml file, except "xmlNode" and "values",
// Each value represents correspondent output xml tag,
$format = [
    "urun" => [ // Each <urun> tag,
        "xmlNode" => "item", // Gets converted to <item> tag,
        "values"  => [ // Has these children,
            "kategori" => "categoryTree",
            "urunadi"  => "name",
            "urunid"   => "code",
            "detay"    => "description",
            "resimler" => [ // Child with children,
                "xmlNode" => "pictures",
                "values"  => [
                    "resim" => "picture[]", // If name has [] in it, then this tag is a repeater. 
                ],
            ],
        ],
    ],
];

// Initiate class.
$instance = new BizimHesapB2b();

// Source file.
$xml_file    = __DIR__ . '/../../storage/public/test.xml';

// Target Path.
$output_path = __DIR__ . '/../../storage/public/outputs/';

$result = $instance
    ->setDataStructure($format)
    ->setValidXmlFilePath($xml_file)
    ->setOutputPath($output_path)
    ->convert();

// Contains filename created with sha1 hash of the source file.
// Example: 20b5918bb61909f47c4ab14b44aecc9fd093fe43.xml
$instance->getOutputFileName()
```

## Test
```
composer test
```
For windows:
```
vendor\bin\paratest.bat
```

## Authors

* [**Tolga Can GÜNEL**](https://github.com/tcgunel) - *Altyapı ve proje başlangıcı*

[comment]: <> (See also the list of [contributors]&#40;https://github.com/freshbitsweb/laravel-log-enhancer/graphs/contributors&#41; who participated in this project.)

## License

This project is licensed under the MIT License - see the [LICENSE](LICENSE) file for details

## Treeware

This package is [Treeware](https://treeware.earth). If you use it in production, then we ask that you [**buy the world a tree**](https://plant.treeware.earth/tcgunel/netgsm) to thank us for our work. By contributing to the Treeware forest you’ll be creating employment for local families and restoring wildlife habitats.
