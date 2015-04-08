CheckTER
========

[![SensioLabsInsight](https://insight.sensiolabs.com/projects/15713a9d-d981-4964-a167-0a7ff9ce41be/mini.png)](https://insight.sensiolabs.com/projects/15713a9d-d981-4964-a167-0a7ff9ce41be)

# Development

## Installation

Clone repository and install dependencies:

```bash
$ git clone git@github.com:nicolasdewez/CheckTER.git
$ cd CheckTER
$ composer install
```

## Generating the PHAR

You need [box](http://box-project.org/) to build the phar, then

```bash
$ box build
```

Next, for execute application :

```bash
$ build/CheckTER.phar
```
