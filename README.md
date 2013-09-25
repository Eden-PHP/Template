# Templating

In *Eden*, we promote PHP templating and provided a one stop class to simply bind variables to template files or strings and return the final results.

**Figure 1. Template String**

	echo eden('template')
		->set('[name]', 'Chris')
		->set('[job]', 'author')
		->parseString('I am [name] an [job]'); //--> I am Chris an author

The above explains a simple example to create a string template. the `set()` method sets variable names to values, which later on will attempt to replace these variables found in the template with the actual value. `parseString()` will consider the given string and return the final string output. Although this is a rudimentary example, Figure 2 explains how we could bind template variables to a PHP file containing PHP and HTML.

**Figure 2. PHP Templating**

	echo eden('template')
		->set('name', 'Chris')
		->set('job', array('author', 'programmer'))
		->parsePHP(dirname(__FILE__).'/template.php'); //--> I am Chris an author, programmer

**Figure 3. Sample PHP Template File**

	<p>I am <?php echo $name; ?> an <?php echo implode(', ', $job); ?></p>

`Figure 2` and `Figure 3` explains how to configure a PHP template. `parsePHP()` will load the given file and return the final string output. It's of note to know that variable names should follow PHP variable naming conventions to prevent *Fatal Errors*. You can also bind variables by groups using `set()` as in `Figure 4`

**Figure 4. Binding a group of variables**

	echo eden('template')
		->set(array(
			'name' => 'Chris',
			'job' => array('author', 'programmer'))
		->parsePHP(dirname(__FILE__).'/template.php'); //--> I am Chris an author, programmer

It's also common practice to wrap the template class into a function or method for an even simplier interface.

**Figure 5. Template function**

	function template($file, array $data = array()) {
		return Eden_Template::i()->set($data)->parsePhp($file);
	}

## Template System

Because *Eden's* template object is simple, doesn't mean that it's not robust. In fact we use this simple concept to combine reusable templates together into a single output. `Figure 6` shows an example of how to define partial templates combining into one master template.

**Figure 6. Templating With Partials**
  
	$root = dirname(__FILE__);
	//partials
	$head = template($root.'/head.phtml', array('active' => 'home'));
	$body = template($root.'/body.phtml', array('name' => 'Chris'));
	$foot = template($root.'/foot.phtml');
	 
	//page
	return template($root.'/page.phtml', array(
		'title'         => 'Welcome Page',
		'class'         => 'home',
		'head'          => $head,
		'body'          => $body,
		'foot'          => $foot));

Though we are using .phtml in this example, it's important to know you can use any extension you like.

**Figure 6a. head.phtml**
	<ul>
		<li class="<?php echo $active=='home' ? 'active':NULL; ?>">Home</li>
		<li class="<?php echo $active=='posts' ? 'active':NULL; ?>">Posts</li>
	</ul>

**Figure 6b. body.phtml**

	<h1>Welcome <?php echo $name; ?></h1>

**Figure 6c. foot.phtml**

	&copy;Openovate Labs

**Figure 6d. page.phtml**

	<!DOCTYPE html>
	<html class="<?php echo $class; ?>">
	<head>
		<title><?php echo $title; ?></title>
		<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
	</head>
	<body>
		<div class="page">
			<div class="head"><?php echo $head; ?></div>
			<div class="body"><?php echo $body; ?></div>
			<div class="foot"><?php echo $foot; ?></div>
		</div>
	</body>
	</html>
	
The latest version of Eden 3 includes a tiny template engine that can lazy load variables as it's needed. Before we get into lazy loading, let's over the simple syntax.

**Figure 7. Template Engine**

	$row = array(
		'title' => 'Post 1', 
		'detail' => 'Some Post',
		'comments' => array(
		array('detail' => 'Comment 1'),
		array('detail' => 'Comment 1')));
	
	$template = '<h1>{title/}</h2><p>{detail/}</p>'
	.'{!comments}<span>{#comments}</span>{/!comments}';
	
	$string = eden('template')->set($row)->parseEngine($template);

Much like our PHP template engine, we follow the same pursuit in syntax to reduce the learning curve. `{!comments}` in `Figure 7` means to only show its inner content if the size of comments is more than zero. `{#comments}` simply returns the length of comments, in this case 2. 

Nested templating structures are also supported in the Template Engine as in `Figure 8`.

**Figure 8. Table Data **	
	
	$data = array(
		'rows' => array(
			array(
				'title' => 'Post 1', 
				'detail' => 'Some Post 1',
				'comments' => array(
				array('detail' => 'Comment 1'),
				array('detail' => 'Comment 1'))),
			array(
				'title' => 'Post 2', 
				'detail' => 'Some Post 2',
				'comments' => array()),
			array(
				'title' => 'Post 3', 
				'detail' => 'Some Post 3',
				'comments' => array(
				array('detail' => 'Comment 1')))
	));
	
	$template = '{rows}<h1>{title/}</h1><p>{detail/}</p>'
	.'{!comments}<span>{#comments}</span>{/!comments}{/rows}';
	
	$string = eden('template')->set($data)->parseEngine($template);

Much like `Figure 7`, `Figure 8` just has the `{rows}` wrapper to repeat what's inside of it per array item in the matrix. There are times for example you may want to define a generic list of key values but can't because of the memory size or time it would consume to gather it. An example would be calling serveral database queries or referencing an API library. Almost all template engines fail in speed because of this fact. *Eden's* template engine cases for this by allowing binded variables to be lazy loaded.

**Figure 9. Lazy Loading Data **
	
	$row = array(
		'title' => 'Post 1', 
		'comments' => array(
		array('detail' => 'Comment 1'),
		array('detail' => 'Comment 1')));
	
	$template = '<h1>{title/}</h2><p>{detail/}</p>'
	.'{!comments}<span>{#comments}</span>{/!comments}';
	
	$string = eden('template')
		->set($row)
		->parseEngine($template, function($label, $type, array $args) {
			if($label == 'detail') {
				return 'hi';
			}
		});

`Figure 9` is like `Figure 7` except we removed the `detail` key and value from the `$row` array. This when our engine is looking for this and it is not found in our loaded data, Eden will call a callback to allow you to return an evaluated value.

====

#Contibuting to Eden

##Setting up your machine with the Eden repository and your fork

1. Fork the main Eden repository (https://github.com/Eden-PHP/Template)
2. Fire up your local terminal and clone the *MAIN EDEN REPOSITORY* (git clone git://github.com/Eden-PHP/System.git)
3. Add your *FORKED EDEN REPOSITORY* as a remote (git remote add fork git@github.com:*github_username*/Template.git)

##Making pull requests

1. Before anything, make sure to update the *MAIN EDEN REPOSITORY*. (git checkout master; git pull origin master)
2. If PHP Unit testing is included in this package please make sure to update it and run the test to ensure everything still works (phpunit)
3. Once updated with the latest code, create a new branch with a branch name describing what your changes are (git checkout -b bugfix/fix-twitter-auth)
    Possible types:
    - bugfix
    - feature
    - improvement
4. Make your code changes. Always make sure to sign-off (-s) on all commits made (git commit -s -m "Commit message")
5. Once you've committed all the code to this branch, push the branch to your *FORKED EDEN REPOSITORY* (git push fork bugfix/fix-twitter-auth)
6. Go back to your *FORKED EDEN REPOSITORY* on GitHub and submit a pull request.
7. An Eden developer will review your code and merge it in when it has been classified as suitable.