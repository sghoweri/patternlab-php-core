<?php

/*!
 * Pattern Lab Builder CLI - v0.7.12
 *
 * Copyright (c) 2013-2014 Dave Olsen, http://dmolsen.com
 * Licensed under the MIT license
 *
 */

/*******************************
 * General Set-up
 *******************************/

// check to see if json_decode exists. might be disabled in installs of PHP 5.5
if (!function_exists("json_decode")) {
	print "Please check that your version of PHP includes the JSON extension. It's required for Pattern Lab to run. Aborting.\n";
	exit;
}

// auto-load classes
require(__DIR__.'/lib/autoload.php');

/*******************************
 * Console Set-up
 *******************************/

$console = new PatternLab\Console;

// set-up the generate command and options
$console->setCommand("g","generate","Generate Pattern Lab","The generate command generates an entire site a single time. By default it removes old content in public/, compiles the patterns and moves content from source/ into public/");
$console->setCommandOption("g","p","patternsonly","Generate only the patterns. Does NOT clean public/.","To generate only the patterns:");
$console->setCommandOption("g","n","nocache","Set the cacheBuster value to 0.","To turn off the cacheBuster:");
$console->setCommandOption("g","c","enablecss","Generate CSS for each pattern. Resource intensive.","To run and generate the CSS for each pattern:");

// set-up an alias for the generate command
$console->setCommand("b","build","Alias for the generate command","Alias for the generate command. Please refer to it's help for full options.");

// set-up the watch command and options
$console->setCommand("w","watch","Watch for changes and regenerate","The watch command builds Pattern Lab, watches for changes in source/ and regenerates Pattern Lab when there are any.");
$console->setCommandOption("w","p","patternsonly","Watches only the patterns. Does NOT clean public/.","To watch and generate only the patterns:");
$console->setCommandOption("w","n","nocache","Set the cacheBuster value to 0.","To turn off the cacheBuster:");
$console->setCommandOption("w","r","autoreload","Turn on the auto-reload service.","To turn on auto-reload:");

// set-up the snapshot command and options
$console->setCommand("s","snapshot","Take a snapshot of public/","The snapshot command copies the current state of public/ and puts it in snapshots/v*/.");
$console->setCommandOption("s","d:","dir:","Optional directory path","To add an optional directory path instead of the defaul v*/ path:","example-path/");

// set-up the fetch command and options
$console->setCommand("f:","fetch:","Fetch a starter kit","The fetch command grabs a starter kit from GitHub and puts it into source/.");
$console->setCommandSample("f","Install a starter kit:","github-org/github-repo");
$console->setCommandSample("f","Install a tagged version of a starter kit:","github-org/github-repo#tag");

// set-up the version command
$console->setCommand("v","version","Print the version number","The version command prints out the current version of Pattern Lab.");

// set-up the help command
$console->setCommand("h:","help:","Print the help for a given command","The help command prints out the help for a given flag. Just use -h with another command and it will tell you all of the options.");

/*******************************
 * Figure out what to run
 *******************************/

// get what was passed on the command line
$console->getArguments();

if ($console->findCommand("h|help")) {
	
	$helpCommand = $console->findCommandValue("h|help");
	$helpCommand = str_replace("-","",$helpCommand);
	$helpCommand = (strlen($helpCommand) == 1) ? $helpCommand : $console->findCommandShort($helpCommand);
	
	$helpCommand ? $console->writeHelpCommand($helpCommand) : $console->writeHelp();
	
} else if ($command = $console->getCommand()) {
	
	// run commands
	
	// load Pattern Lab's config, if first time set-up move files appropriately too
	$configurer = new PatternLab\Configurer;
	$config     = $configurer->getConfig();
	
	// set-up required vars
	$enableCSS     = $console->findCommandOption("c|enablecss");
	$moveStatic    = ($console->findCommandOption("p|patternsonly")) ? false : true;
	$noCacheBuster = $console->findCommandOption("n|nocache");
	$autoReload    = $console->findCommandOption("r|autoreload");
	
	if (($command == "g") || ($command == "b")) {
		
		// load the generator
		$g = new PatternLab\Generator($config);
		$g->generate($enableCSS,$moveStatic,$noCacheBuster);
		$g->printSaying();
		
	} else if ($command == "w") {
		
		// CSS feature should't be used with watch
		$enableCSS = false;
		
		// load the generator
		$g = new PatternLab\Generator($config);
		$g->generate($enableCSS,$moveStatic,$noCacheBuster);
		
		// load the watcher
		$w = new PatternLab\Watcher($config);
		$w->watch($autoReload,$moveStatic,$noCacheBuster);
		
	} else if ($command == "s") {
		
		// run the snapshot command
		$snapshotDir = $console->findCommandOptionValue("d|dir");
		$s = new PatternLab\Snapshot($config);
		$s->takeSnapshot($snapshotDir);
		
	} else if ($command == "f") {
		
		// run the snapshot command
		$starterKit = $console->findCommandValue("f|fetch");
		$sk = new PatternLab\StarterKit($config);
		$sk->fetch($starterKit);
		
	} else if ($command == "v") {
		
		// write out the version number
		print "You're running v".$config["v"]." of the PHP version of Pattern Lab.\n";
		exit;
		
	}
	
} else {
	
	// write the generic help
	$console->writeHelp();
	
}
