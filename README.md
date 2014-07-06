XmlFuse
=======
[![Build Status](https://travis-ci.org/AydinHassan/XmlFuse.svg)](https://travis-ci.org/AydinHassan/XmlFuse)
[![Coverage Status](https://img.shields.io/coveralls/AydinHassan/XmlFuse.svg)](https://coveralls.io/r/AydinHassan/XmlFuse)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/AydinHassan/XmlFuse/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/AydinHassan/XmlFuse/?branch=master)
[![Latest Stable Version](https://poser.pugx.org/aydin-hassan/xml-fuse/version.svg)](https://packagist.org/packages/aydin-hassan/xml-fuse)
[![Latest Untable Version](https://poser.pugx.org/aydin-hassan/xml-fuse/v/unstable.png)](https://packagist.org/packages/aydin-hassan/xml-fuse)

A Small library for merging scalar data from multiple XPaths's. Similar to an SQL Join, but using XML.


This library is useful if you want to convert XML data into a flat format. This is useful for a source agnostic approach. For example, you may process data from multiple source but want to normalise them all to a similar format. 

This library aims to convert 3 dimensional data in to a flat array. Take the following XML as an example:

```xml
<?xml version="1.0" encoding="ISO-8859-1"?>
<order>
    <orderId>01</orderId>
    <customerEmail>aydin@hotmail.co.uk</customerEmail>
    <lines>
        <line>
            <id>1</id>
            <qty>2</qty>
            <status>despatched</status>
        </line>
        <line>
            <id>5</id>
            <qty>1</qty>
            <status>despatched</status>
        </line>
    </lines>
</order>
```

Imagine an import which updates the status of each item associated with an order. The import only accepts a flat array of data and can only process one item at a time. In order to update the status of an Item, the import must also know the order ID in order to load it and interact with its items. 

We could use an xpath to loop each order item like so: `//order/lines/line` and this would give us all the item information, but we still don't have the order ID in the data. What if we could get the data like so:

```php
array(2) {
  [0] =>
  array(5) {
    'orderId' => string(2) "01"
    'customerEmail' => string(19) "aydin@hotmail.co.uk"
    'id' => string(1) "1"
    'qty' => string(1) "2"
    'status' => string(10) "despatched"
  }
  [1] =>
  array(5) {
    'orderId' => string(2) "01"
    'customerEmail' => string(19) "aydin@hotmail.co.uk"
    'id' => string(1) "5"
    'qty' => string(1) "1"
    'status' => string(10) "despatched"
  }
}
```

This data structure is each of the nodes within each `<line>` embedded with the parent node's data. We can succesfully pass each of these data structures to our order status updater class, and it has all the data it needs to do its work.

Installation
------------

### Composer
    
```shell
composer require aydin-hassan/xml-fuse
```

Usage
-----

To use the tool you should instantiate the XmlFuser class, passing in your XML and the XPath's you want to combine. You can then call `parse()` to get an array of records back.

#### Example:

```php
$xml = '<?xml version="1.0" encoding="ISO-8859-1"?>
<order>
    <orderId>01</orderId>
    <customerEmail>aydin@hotmail.co.uk</customerEmail>
    <lines>
        <line>
            <id>1</id>
            <qty>2</qty>
            <status>despatched</status>
        </line>
        <line>
            <id>5</id>
            <qty>1</qty>
            <status>despatched</status>
        </line>
    </lines>
</order>';

$xPaths = [
    '//order',
    'lines/line'
];

$fuser = new \AydinHassan\XmlFuse\XmlFuse($xml, $xPaths);
$res = $fuser->parse();

var_dump($res);

//Output
array(2) {
  [0] =>
  array(5) {
    'orderId' => string(2) "01"
    'customerEmail' => string(19) "aydin@hotmail.co.uk"
    'id' => string(1) "1"
    'qty' => string(1) "2"
    'status' => string(10) "despatched"
  }
  [1] =>
  array(5) {
    'orderId' => string(2) "01"
    'customerEmail' => string(19) "aydin@hotmail.co.uk"
    'id' => string(1) "5"
    'qty' => string(1) "1"
    'status' => string(10) "despatched"
  }
}
```

### What's going on?

Each of the XPath's passed in to the parser are processed in the order they are given. So the XML will be loaded and the first XPath search will be performed. This will produce an array of <order> nodes (of which there is one).

Each piece of data in the node, which isn't a nested structure will be moved to an array. So we have: orderId & customerEmail:

```php
[
   'orderId' => 1,
   'customerEmail' => 'aydin@otmail.co.uk',
]
```

<lines> is skipped because it is a nested structure. 

Now from this point, the next XPATH is searched, remember our current position? (<order>). Our second XPath is: `lines/line` this will return an array of <line> nodes. 

Now each <line> nodes scalar data (non-nested) is converted to an array. Eg:
```php
[
   'id' => '1',
   'qty' => '1',
   'status' => 'despatched',
]
```

It is then merged with the data from the parent search, the <order> node. Which will leave us with: 

```php
[
   'orderId' => 1,
   'customerEmail' => 'aydin@otmail.co.uk',
   'id' => '1',
   'qty' => '1',
   'status' => 'despatched',
]
```

This is then repeated for each <line> node.

Notes
-----

1. Any amount of XPaths can be given, if they return no results, then no merging will be done, the parent will be returned instead.
2. You can use the `setXPaths()` method to pass in a new set of XPaths, and call the `parse()` method to reparse the XML.


Contributing & Running Tests
----------------------------

```shell
git clone git@github.com:AydinHassan/XmlFuse.git
cd XmlFuse
composer install
./vendor/bin/phpunit

//make sure you run the lint process aswell
./vendor/bin/phpcs --standard=PSR2 ./src
./vendor/bin/phpcs --standard=PSR2 ./test
```
