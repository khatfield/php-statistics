# Statistics plugin for CakePHP

[![Build Status](https://travis-ci.org/tersmitten/cakephp-statistics.png?branch=master)](https://travis-ci.org/tersmitten/cakephp-statistics) [![Coverage Status](https://coveralls.io/repos/tersmitten/cakephp-statistics/badge.png)](https://coveralls.io/r/tersmitten/cakephp-statistics)  [![Packagist downloads](http://img.shields.io/packagist/dt/tersmitten/cakephp-statistics.svg)](https://packagist.org/packages/tersmitten/cakephp-statistics)

## Requirements

* CakePHP 2.0 or greater.
* PHP 5.3.10 or greater.

## Installation

Clone/Copy the files in this directory into `app/Plugin/Statistics`

## Configuration

Ensure the plugin is loaded in `app/Config/bootstrap.php` by calling:

```
CakePlugin::load('Statistics');
```

## Usage

### Sum
```
Statistics::sum(array(1, 2, 3));
```

### Minimum
```
Statistics::min(array(1, 2, 3));
```

### Maximum
```
Statistics::max(array(1, 2, 3));
```

### Mean
```
Statistics::mean(array(1, 2, 3));
```

### Frequency
```
Statistics::frequency(array(1, 2, 3, 3, 3));
```

### Mode
```
Statistics::mode(array(1, 2, 2, 3));
```

### Variance (sample and population)
```
Statistics::variance(array(1, 2, 3));
Statistics::variance(array(1, 2, 3), false);
```

### Standard deviation (sample and population)
```
Statistics::standardDeviation(array(1, 2, 3));
Statistics::standardDeviation(array(1, 2, 3), false);
```

### Range
```
Statistics::range(array(4, 6, 10, 15, 18));
```
