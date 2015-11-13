![logo](http://eden.openovate.com/assets/images/cloud-social.png) Eden Template
====
[![Build Status](https://api.travis-ci.org/Eden-PHP/Template.svg)](https://travis-ci.org/Eden-PHP/Template)
====

 - [Install](#install)
 - [Introduction](#intro)
 - [API](#api)
    - [set](#set)
    - [parseEngine](#parseEngine)
    - [parseString](#parseString)
    - [parsePhp](#parsePhp)
 - [Contributing](#contributing)

====

<a name="install"></a>
## Install

`composer install eden/template`

====

<a name="intro"></a>
## Introduction

Instantiate template in this manner.

```
$template = eden('template');
```

====

<a name="api"></a>
## API

==== 

<a name="set"></a>

### set

Sets template variables 

#### Usage

```
eden('template')->set(*array|string $data, mixed $value);
```

#### Parameters

 - `*array|string $data` - data
 - `mixed $value` - value

Returns `Eden\Template\Index`

#### Example

```
eden('template')->set(array('foo' => 'bar'));
```

==== 

<a name="parseEngine"></a>

### parseEngine

Engine Parser. This parser also cases for lazy loaded variables. One problem with template engines is that it requires you to preload variables. This becomes problematic when your template requires a plethora of MySQL, Facebook, Twitter calls for example. Sometimes it's just best to wait till it's needed. ex {$title} ex {products}{$title}{/products} 

#### Usage

```
eden('template')->parseEngine(*string $template, callable|null $callback);
```

#### Parameters

 - `*string $template` - The template string
 - `callable|null $callback` - Callback to be used when key does not exist in data

Returns `string`

#### Example

```
eden('template')->parseEngine('foo');
```

==== 

<a name="parseString"></a>

### parseString

Simple string replace template parser 

#### Usage

```
eden('template')->parseString(*string $string);
```

#### Parameters

 - `*string $string` - The template string

Returns `string`

#### Example

```
eden('template')->parseString('foo');
```

==== 

<a name="parsePhp"></a>

### parsePhp

For PHP templates, this will transform the given document to an actual page or partial 

#### Usage

```
eden('template')->parsePhp(*string $___file, bool $___evalString);
```

#### Parameters

 - `*string $___file` - Template file or PHP template string
 - `bool $___evalString` - Whether to evaluate the first argument

Returns `string`

#### Example

```
eden('template')->parsePhp('foo');
```

==== 

<a name="contributing"></a>
#Contributing to Eden

Contributions to *Eden* are following the Github work flow. Please read up before contributing.

##Setting up your machine with the Eden repository and your fork

1. Fork the repository
2. Fire up your local terminal create a new branch from the `v4` branch of your 
fork with a branch name describing what your changes are. 
 Possible branch name types:
    - bugfix
    - feature
    - improvement
3. Make your changes. Always make sure to sign-off (-s) on all commits made (git commit -s -m "Commit message")

##Making pull requests

1. Please ensure to run `phpunit` before making a pull request.
2. Push your code to your remote forked version.
3. Go back to your forked version on GitHub and submit a pull request.
4. An Eden developer will review your code and merge it in when it has been classified as suitable.