<?php
if (empty($import_adminkey) or isset($_REQUEST['import_adminkey']) or $import_adminkey != $adminkey) die('bla');

echo '
	<center>
	<table class = "box" border="0" cellpadding="1" cellspacing="2" width="720">
	<tbody><tr>
		<td class="heading" align="center" colspan="2">Server configuration check</td>
	</tr></tbody></table><br>';

// Check access rights
echo '
	<table class="box" border="0" cellpadding="1" cellspacing="2">
	<tr>
		<td class="smheading" align="center" height="25" width="550" colspan="2">Checking access rights</td>
	</tr>';

// directories
foreach (array("logs", "logs/ac", "logs/backup", "logs/utdc", "logs/ace") as $dir)
{
	echo '
	<tr>
		<td class="smheading" align="left" width="350">', $dir, '</td>';
	if (!file_exists($dir)) {
		if (mkdir($dir, 0777)) {
		      echo '
			    <td class="warn" align="left" width="200">Created</td></tr>';
		}
		else {
		      echo '
			    <td class="warn" align="left" width="200">Not found!</td></tr>';
		}
	}
	else if (!is_dir($dir)) {
		echo '
		      <td class="warn" align="left" width="200">Not a directory!</td></tr>';
	}
	else if (!is_writable($dir)) {
		if (@chmod($dir, 0777)) {
			echo '
			      <td class="grey" align="left" width="200">SET</td></tr>';
		}
		else {
			echo '
			      <td class="warn" align="left" width="200">Incorrect access rights: ' , substr(sprintf('%o', fileperms($dir)), -4) , '</td></tr>';
		}
	}
	else {
		echo '
		      <td class="grey" align="left" width="200">OK</td></tr>';
	}
}

// files:
foreach (array("includes/ftptimestamp.php") as $file)
{
	echo '
	<tr>
		<td class="smheading" align="left" width="350">', $file, '</td>';
	if (!file_exists($file)) {
		echo '
		      <td class="warn" align="left" width="200">Not found!</td></tr>';
	}
	else if (!is_file($file)) {
		echo '
		      <td class="warn" align="left" width="200">Not a file!</td></tr>';
	}
	else if (!is_writable($file)) {
		if (@chmod($file, 0777)) {
			echo '
			      <td class="grey" align="left" width="200">SET</td></tr>';
		}
		else {
			echo '
			      <td class="warn" align="left" width="200">Incorrect access rights: ' , substr(sprintf('%o', fileperms($file)), -4) , '</td></tr>';
		}
	}
	else {
		echo '
		      <td class="grey" align="left" width="200">OK</td></tr>';
	}
}

echo '
	</tbody></table>
<div class="opnote">* Might not work correctly on Windows systems *</div><br>';


// connect to the database and check structure
echo '
	<table class="box" border="0" cellpadding="1" cellspacing="2">
	<tr>
		<td class="smheading" align="center" height="25" width="550" colspan="2">Checking mysql tables</td>
	</tr>';

// database creation array
// to add a database simply add $create_table['dbname'] = "database creation sql"
$create_table['uts_events'] = "
CREATE TABLE `uts_events` (
  `id` mediumint(10) NOT NULL auto_increment,
  `matchid` mediumint(10) NOT NULL default '0',
  `playerid` tinyint(3) NOT NULL default '0',
  `col0` varchar(20) NOT NULL default '',
  `col1` varchar(120) NOT NULL default '',
  `col2` varchar(120) NOT NULL default '',
  `col3` varchar(120) NOT NULL default '',
  `col4` varchar(120) NOT NULL default '',
  PRIMARY KEY  (`id`)
);
";

$create_table['uts_games'] = "
CREATE TABLE `uts_games` (
  `id` tinyint(3) unsigned NOT NULL auto_increment,
  `gamename` varchar(100) NOT NULL default '',
  `name` varchar(100) NOT NULL default '',
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=9 ;
";

$create_table['uts_gamestype'] = "
CREATE TABLE `uts_gamestype` (
  `id` smallint(5) unsigned NOT NULL auto_increment,
  `serverip` varchar(21) NOT NULL default '',
  `gamename` varchar(100) NOT NULL default '',
  `mutator` varchar(100) NOT NULL default '',
  `gid` tinyint(3) unsigned NOT NULL default '0',
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM;
";

$create_table['uts_ignoreips'] = "
CREATE TABLE IF NOT EXISTS `uts_ignoreips` (
  `ip` bigint(15) NOT NULL default '0',
  PRIMARY KEY  (`ip`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;
";

$create_table['uts_killsmatrix'] = "
CREATE TABLE `uts_killsmatrix` (
  `matchid` mediumint(8) unsigned NOT NULL default '0',
  `killer` tinyint(4) NOT NULL default '0',
  `victim` tinyint(4) NOT NULL default '0',
  `kills` tinyint(3) unsigned NOT NULL default '0',
  KEY `matchid` (`matchid`)
) ENGINE=MyISAM;
";

$create_table['uts_match'] = "
CREATE TABLE `uts_match` (
  `id` mediumint(10) NOT NULL auto_increment,
  `time` varchar(14) default NULL,
  `servername` varchar(100) NOT NULL default '',
  `serverip` varchar(21) NOT NULL default '0',
  `gamename` varchar(100) NOT NULL default '0',
  `gid` tinyint(3) unsigned NOT NULL default '0',
  `gametime` float NOT NULL default '0',
  `mutators` longtext NOT NULL,
  `insta` tinyint(1) NOT NULL default '0',
  `tournament` varchar(5) NOT NULL default '',
  `teamgame` varchar(5) NOT NULL default '',
  `mapname` varchar(100) NOT NULL default '',
  `mapfile` varchar(100) NOT NULL default '',
  `serverinfo` mediumtext NOT NULL,
  `gameinfo` mediumtext NOT NULL,
  `firstblood` int(10) unsigned NOT NULL default '0',
  `frags` mediumint(5) NOT NULL default '0',
  `deaths` mediumint(5) NOT NULL default '0',
  `kills` mediumint(5) NOT NULL default '0',
  `suicides` mediumint(5) NOT NULL default '0',
  `teamkills` mediumint(5) NOT NULL default '0',
  `assaultid` varchar(10) NOT NULL default '',
  `ass_att` tinyint(1) NOT NULL default '0',
  `ass_win` tinyint(4) NOT NULL default '0',
  `t0` tinyint(1) NOT NULL default '0',
  `t1` tinyint(1) NOT NULL default '0',
  `t2` tinyint(1) NOT NULL default '0',
  `t3` tinyint(1) NOT NULL default '0',
  `t0score` mediumint(5) NOT NULL default '0',
  `t1score` mediumint(5) NOT NULL default '0',
  `t2score` mediumint(5) NOT NULL default '0',
  `t3score` mediumint(5) NOT NULL default '0',
  PRIMARY KEY  (`id`),
  KEY `serverip` (`serverip`)
) ENGINE=MyISAM AUTO_INCREMENT=83 ;
";

$create_table['uts_pinfo'] = "
CREATE TABLE `uts_pinfo` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `name` varchar(50) NOT NULL default '',
  `country` char(2) NOT NULL default '',
  `banned` enum('Y','N') NOT NULL default 'N',
  PRIMARY KEY  (`id`),
  KEY `name` (`name`(22))
) ENGINE=MyISAM AUTO_INCREMENT=136 ;
";

$create_table['uts_player'] = "
CREATE TABLE `uts_player` (
  `id` mediumint(10) NOT NULL auto_increment,
  `matchid` mediumint(10) NOT NULL default '0',
  `insta` tinyint(1) NOT NULL default '0',
  `playerid` tinyint(3) NOT NULL default '0',
  `pid` int(10) unsigned NOT NULL default '0',
  `team` tinyint(2) unsigned NOT NULL default '0',
  `isabot` tinyint(1) NOT NULL default '0',
  `country` char(2) NOT NULL default '',
  `ip` int(10) unsigned NOT NULL default '0',
  `gid` tinyint(3) unsigned NOT NULL default '0',
  `gametime` float NOT NULL default '0',
  `gamescore` smallint(5) NOT NULL default '0',
  `lowping` smallint(5) unsigned NOT NULL default '0',
  `highping` smallint(5) unsigned NOT NULL default '0',
  `avgping` smallint(5) unsigned NOT NULL default '0',
  `frags` smallint(5) NOT NULL default '0',
  `deaths` smallint(5) unsigned NOT NULL default '0',
  `kills` smallint(5) unsigned NOT NULL default '0',
  `suicides` smallint(5) unsigned NOT NULL default '0',
  `teamkills` smallint(5) unsigned NOT NULL default '0',
  `eff` float NOT NULL default '0',
  `accuracy` float NOT NULL default '0',
  `ttl` float NOT NULL default '0',
  `flag_taken` smallint(5) unsigned NOT NULL default '0',
  `flag_dropped` smallint(5) unsigned NOT NULL default '0',
  `flag_return` smallint(5) unsigned NOT NULL default '0',
  `flag_capture` tinyint(3) unsigned NOT NULL default '0',
  `flag_cover` smallint(5) unsigned NOT NULL default '0',
  `flag_seal` smallint(5) unsigned NOT NULL default '0',
  `flag_assist` smallint(5) unsigned NOT NULL default '0',
  `flag_kill` mediumint(5) unsigned NOT NULL default '0',
  `flag_pickedup` smallint(5) unsigned NOT NULL default '0',
  `dom_cp` smallint(5) unsigned NOT NULL default '0',
  `ass_obj` smallint(5) unsigned NOT NULL default '0',
  `spree_double` smallint(5) unsigned NOT NULL default '0',
  `spree_triple` smallint(5) unsigned NOT NULL default '0',
  `spree_multi` smallint(5) unsigned NOT NULL default '0',
  `spree_mega` tinyint(3) unsigned NOT NULL default '0',
  `spree_ultra` tinyint(3) unsigned NOT NULL default '0',
  `spree_monster` tinyint(3) unsigned NOT NULL default '0',
  `spree_kill` smallint(5) unsigned NOT NULL default '0',
  `spree_rampage` smallint(5) unsigned NOT NULL default '0',
  `spree_dom` tinyint(3) unsigned NOT NULL default '0',
  `spree_uns` tinyint(3) unsigned NOT NULL default '0',
  `spree_god` smallint(5) unsigned NOT NULL default '0',
  `pu_pads` tinyint(3) unsigned NOT NULL default '0',
  `pu_armour` tinyint(3) unsigned NOT NULL default '0',
  `pu_keg` tinyint(3) unsigned NOT NULL default '0',
  `pu_invis` tinyint(3) unsigned NOT NULL default '0',
  `pu_belt` tinyint(3) unsigned NOT NULL default '0',
  `pu_amp` tinyint(3) unsigned NOT NULL default '0',
  `rank` float NOT NULL default '0',
  PRIMARY KEY  (`id`),
  KEY `matchid` (`matchid`,`team`),
  KEY `pid` (`pid`),
  KEY `gid` (`gid`)
) ENGINE=MyISAM AUTO_INCREMENT=615 ;
";

$create_table['uts_rank'] = "
CREATE TABLE `uts_rank` (
  `id` mediumint(10) NOT NULL auto_increment,
  `time` float unsigned NOT NULL default '0',
  `pid` int(10) unsigned NOT NULL default '0',
  `gid` tinyint(3) unsigned NOT NULL default '0',
  `rank` float NOT NULL default '0',
  `prevrank` float NOT NULL default '0',
  `matches` mediumint(5) NOT NULL default '0',
  PRIMARY KEY  (`id`),
  KEY `name` (`pid`,`gid`),
  KEY `rank` (`rank`),
  KEY `gamename` (`gid`,`rank`)
) ENGINE=MyISAM AUTO_INCREMENT=173 ;
";

$create_table['uts_weapons'] = "
CREATE TABLE `uts_weapons` (
  `id` tinyint(3) unsigned NOT NULL auto_increment,
  `name` varchar(100) NOT NULL default '',
  `image` varchar(50) NOT NULL default '',
  `sequence` tinyint(3) unsigned NOT NULL default '200',
  `hide` enum('N','Y') NOT NULL default 'N',
  PRIMARY KEY  (`id`),
  KEY `name` (`name`(20))
) ENGINE=MyISAM AUTO_INCREMENT=20 ;
";

$create_table['uts_weaponstats'] = "
CREATE TABLE `uts_weaponstats` (
  `matchid` mediumint(8) unsigned NOT NULL default '0',
  `pid` int(10) unsigned NOT NULL default '0',
  `weapon` tinyint(3) unsigned NOT NULL default '0',
  `kills` mediumint(8) unsigned NOT NULL default '0',
  `shots` int(10) unsigned NOT NULL default '0',
  `hits` int(10) unsigned NOT NULL default '0',
  `damage` int(10) unsigned NOT NULL default '0',
  `acc` float unsigned NOT NULL default '0',
  KEY `full` (`matchid`,`pid`)
) ENGINE=MyISAM;
";

$create_table['uts_chartdata'] = "
CREATE TABLE `uts_chartdata` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `mid` int(11) NOT NULL,
  `chartid` mediumint(9) NOT NULL,
  `title` varchar(50) NOT NULL,
  `data` blob NOT NULL,
  `labels` blob NOT NULL,
  `categories` blob NOT NULL,
  PRIMARY KEY (`id`),
  KEY `mid` (`mid`),
  KEY `mid_2` (`mid`)
) ENGINE=MyISAM;
";

$create_table['uts_charttypes'] = "
CREATE TABLE `uts_charttypes` (
  `id` mediumint(9) NOT NULL,
  `category` varchar(25) NOT NULL,
  `type` varchar(50) NOT NULL,
  `color` varchar(10) NOT NULL,
  `layout` text NOT NULL,
  `charttype` varchar(15) NOT NULL,
  `columns` int(11) NOT NULL DEFAULT '1',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM;
";

foreach ($create_table as $table => $query) {
	echo '
	<tr>
		<td class="smheading" align="left" width="350">', $table, '</td>';
	if (small_count("SHOW TABLES LIKE '$table'") == 1) {
		// database exists, check columns
		echo '
		      <td class="grey" align="left" width="200">OK</td></tr>';
	}
	else {
		if (mysql_query($query)) {
			echo '
		      <td class="grey" align="left" width="200">Created</td></tr>';
		}
		else {
			echo '
		      <td class="warn" align="left" width="200">Not found!</td></tr>';
		}
	}
}
echo '
	</tbody></table><br>';
	
// check graph types - have to use id since no other unique key except composite ones.
$create_charttype['90'] = "INSERT INTO `uts_charttypes` VALUES (90, 'Frags Team', 'teamScore', 'team', 'title: {\ntext: ''Battle for #1''\n},\ncredits: {\nenabled: false\n},			\nlegend: {\nenabled: false\n},			\nxAxis: {\ntitle: '''',\nmin: 0\n},		   \nyAxis: {\ntitle: '''',\nmin: 0\n},\nplotOptions : {\n	line : {\n		stickyTracking : false\n	}\n}', 'line', 1);";
$create_charttype['20'] = "INSERT INTO `uts_charttypes` VALUES (20, 'Domination', 'teamScore', 'team', 'title: {\ntext: ''Score''\n},\ncredits: {\nenabled: false\n},			\nlegend: {\nenabled: false\n},			\nxAxis: {\ntitle: '''',\nmin: 0\n},		   \nyAxis: {\ntitle: '''',\nmin: 0\n},\nplotOptions : {\n	line : {\n		stickyTracking : false\n	}\n}', 'line', 1);";
$create_charttype['21'] = "INSERT INTO `uts_charttypes` VALUES (21, 'Domination', 'teamDeriv', 'team', '			title : {\n					text : ''Score''\n				},\n				credits : {\n					enabled : false\n				},\n				legend : {\n					enabled : true\n				},\n				xAxis : {\n					title : '''',\n					min : 0\n				},\n				yAxis: [{\n					\n					title: {\n						text: ''Dom Score''\n						},\n					min: 0,\n					floor: 0,\n						opposite: true\n				   },{\n				   title: {\n						text: ''net dom points per min''\n						},\n					min: 0,\n					floor: 0\n				  }],\n				plotOptions : {\n					column : {\n						stacking : ''normal'',\npointPlacement: ''between'',									pointPadding : 0,\npointRange: 1,						groupPadding : 0.2,						borderWidth : 0,						shadow : false,\nenableMouseTracking: false\n					},line: { stickyTracking: false }\n				}', 'linecolumn', 1);";
$create_charttype['92'] = "INSERT INTO `uts_charttypes` VALUES (92, 'Frags Team', 'teamNormal', 'team', 'title: {\ntext: ''Spread vs #2 team''\n},\ncredits: {\nenabled: false\n},			\nlegend: {\nenabled: false\n},			\nxAxis: {\ntitle: '''',\nmin: 0\n},		   \nyAxis: {\ntitle: ''''	\n},\nplotOptions : {\n	line : {\n		stickyTracking : false\n	}\n}', 'line', 1);";
$create_charttype['91'] = "INSERT INTO `uts_charttypes` VALUES (91, 'Frags Team', 'teamDeriv', 'team', '			title : {\n					text : ''Battle for #1''\n				},\n				credits : {\n					enabled : false\n				},\n				legend : {\n					enabled : true\n				},\n				xAxis : {\n					title : '''',\n					min : 0\n				},\n				yAxis: [{\n					\n					title: {\n						text: ''score''\n						},\n					min: 0,\n					floor: 0,\n						opposite: true\n				   },{\n				   title: {\n						text: ''frags per min''\n						},\n					min: 0,\n					floor: 0\n				  }],\n				plotOptions : {\n					column : {\n						stacking : ''normal'',\n						pointPadding : 0,\n						groupPadding : 0,\n						borderWidth : 0,\n						shadow : false,\nenableMouseTracking: false\n					},line: { stickyTracking: false }\n				}', 'linecolumn', 1);";
$create_charttype['93'] = "INSERT INTO `uts_charttypes` VALUES (93, 'Top fraggers', 'playerScore', 'player', 'title: {\ntext: ''Battle for #1''\n},\ncredits: {\nenabled: false\n},			\nlegend: {\nenabled: true\n},			\nxAxis: {\ntitle: '''',\nmin: 0\n},		   \nyAxis: {\ntitle: '''',\nmin: 0\n},\nplotOptions : {\n	line : {\n		stickyTracking : false\n	}\n}', 'line', 1);";
$create_charttype['95'] = "INSERT INTO `uts_charttypes` VALUES (95, 'Top fraggers', 'playerNormal', 'player', 'title: {\ntext: ''Spread vs #2''\n},\ncredits: {\nenabled: false\n},			\nlegend: {\nenabled: true\n},			\nxAxis: {\ntitle: '''',\nmin: 0\n},		   \nyAxis: {\ntitle: ''''	\n},\nplotOptions : {\n	line : {\n		stickyTracking : false\n	}\n}', 'line', 1);";
$create_charttype['94'] = "INSERT INTO `uts_charttypes` VALUES (94, 'Top fraggers', 'playerDeriv', 'player', 'title : {\n					text : ''Battle for #1''\n				},\n				credits : {\n					enabled : false\n				},\n				legend : {\n					enabled : true\n				},\n				xAxis : {\n					title : '''',\n					min : 0\n				},\n				yAxis: [{\n					\n					title: {\n						text: ''score''\n						},allowDecimals : false,\n			 tickPositioner: function () {\n\n        var maxDeviation = round_up(Math.max(Math.abs(this.dataMax), Math.abs(this.dataMin)),3);\n        var thirdMaxDeviation = Math.ceil(maxDeviation / 3);\n\n        return [-thirdMaxDeviation , 0, thirdMaxDeviation , 2*thirdMaxDeviation , maxDeviation];\n    },\n			\n			\n							opposite: true\n				   },{\n				   title: {\n						text: ''frags per min''\n						},allowDecimals : false, tickPositioner: function () {\n\n\n        var maxDeviation = round_up(Math.max(Math.abs(this.dataMax), Math.abs(this.dataMin)),3);\n        var thirdMaxDeviation = Math.ceil(maxDeviation / 3);\n\n        return [-thirdMaxDeviation , 0, thirdMaxDeviation , 2*thirdMaxDeviation , maxDeviation];\n    }			\n				  }],\n				plotOptions : {\n					column : {\n						stacking : ''normal'',\n						pointPadding : 0,\n						groupPadding : 0,\n						borderWidth : 0,\n						shadow : false,\nenableMouseTracking: false\n					},line: { stickyTracking: false }\n				}', 'linecolumn', 1);";
$create_charttype['96'] = "INSERT INTO `uts_charttypes` VALUES (96, 'Frags #5-8', 'playerScore5', 'player', 'title: {\ntext: ''Battle for #5''\n},\ncredits: {\nenabled: false\n},			\nlegend: {\nenabled: true\n},			\nxAxis: {\ntitle: '''',\nmin: 0\n},		   \nyAxis: {\ntitle: '''',\nmin: 0\n},\nplotOptions : {\n	line : {\n		stickyTracking : false\n	}\n}', 'line', 1);";
$create_charttype['97'] = "INSERT INTO `uts_charttypes` VALUES (97, 'Frags #5-8', 'playerNormal5', 'player', 'title: {\ntext: ''Spread vs #5''\n},\ncredits: {\nenabled: false\n},			\nlegend: {\nenabled: true\n},			\nxAxis: {\ntitle: '''',\nmin: 0\n},		   \nyAxis: {\ntitle: ''''	\n}', 'line', 1);";
$create_charttype['10'] = "INSERT INTO `uts_charttypes` VALUES (10, 'CTF Score', 'teamScore', 'team', 'title : {\n					text : ''CTF Team Score''\n				},\n\ntooltip: {\n\n            positioner: function () {\n                return { x: 10, y: 10 };\n            },\nformatter:function() {\n\nif(this.point.tooltipdata === ''undefined'' || this.point.tooltipdata == null ) {\n                return Math.floor(this.x)+''min ''+Math.round((this.x-Math.floor(this.x))*60)+''sec<br><span style=\"color: '' + this.series.color + ''\"></span> ''+this.series.name+'' : ''+this.y;\n} else {\nreturn Math.floor(this.x)+''min ''+Math.round((this.x-Math.floor(this.x))*60)+''sec<br><span style=\"color: '' + this.series.color + ''\"></span> ''+this.series.name+'' : ''+this.y+''<br>''+this.point.tooltipdata;\n}\n}\n\n},\n				credits : {\n					enabled : false\n				},\n				legend : {\n					enabled : false\n				},\n				xAxis : {\n					title : '''',\n					min : 0\n				},\n				yAxis: [{\n					\n					title: {\n						text: ''score''\n						},\n					min: 0,\n					floor: 0,\n            allowDecimals: false,\n						opposite: true\n				   }, {\n				   title: {\n						text: ''grabs per min''\n						},\n					min: 0,\n            allowDecimals: false,\n					floor: 0\n				  }],\n				plotOptions : {\n           	column : {\n                pointPlacement: ''between'',\n									pointPadding : 0,\npointRange: 1,						groupPadding : 0.2,\n						borderWidth : 0,\n						shadow : false,\nenableMouseTracking: false\n					},line: { stickyTracking: false }\n				}', 'linestepcolumn', 2);";
$create_charttype['11'] = "INSERT INTO `uts_charttypes` VALUES (11, 'CTF Grabs', 'teamBreakdown', 'player', '\n		title : {\n					text : ''Grab conversion''\n				},\n				credits : {\n					enabled : false\n				},\n				legend : {\n					enabled : true\n				},\n				xAxis : {\n					title : '''',\ncategories: [''Red'',''Blue'']\n				},\n				yAxis: {\n					\n					   title: '''',allowDecimals: false,\n            reversedStacks: false,stackLabels: {\n                enabled: true }\n				  },\n				plotOptions : {\n					bar : { \n						stacking : ''normal'',				borderWidth : 0\n					}\n				}', 'bar', 2);";
$create_charttype['100'] = "INSERT INTO `uts_charttypes` VALUES (100, 'Items', 'Team Pickups', 'team', '\n			credits : {\n					enabled : false\n				},\n        title: {\n            text: ''''\n        },\n \n        pane: {\n            size: ''100%''\n        },\n\n         plotOptions: {\n            series: {\n                fillOpacity: 0.4\n            }\n        },\nxAxis: {\n							tickmarkPlacement: ''on'',\n						lineWidth: 0\n						},\n\n        yAxis: {\n            labels: {enabled:false},\n            tickPixelInterval: 25,\n           tickPositions: [0,25,50,75,100],\n            gridLineInterpolation: ''polygon'',lineWidth: 0\n        },\ntooltip: {            \nvalueSuffix: '' %''        }', 'radar', 1);";
$create_charttype['101'] = "INSERT INTO `uts_charttypes` VALUES (101, 'Items', 'Player Pickups', 'player', '			credits : {\n					enabled : false\n				},\n        title: {\n            text: ''''\n        },\n \n        pane: {\n            size: ''100%''\n        },\n\n         plotOptions: {\n            series: {\n                fillOpacity: 0.4\n            }\n        },\nxAxis: {\n							tickmarkPlacement: ''on'',\n						lineWidth: 0\n						},\n\n        yAxis: {\n            labels: {enabled:false},\n            tickPixelInterval: 25,\n            ickPositions: [0,25,50,75,100],\n            gridLineInterpolation: ''polygon'',lineWidth: 0\n        },\ntooltip: {            \nvalueSuffix: '' %''        }', 'radar', 1);";
$create_charttype['102'] = "INSERT INTO `uts_charttypes` VALUES (102, 'Items', 'ampRuns', 'team', 'title : {\n			text : ''Amp runs net points''\n		},\n		credits : {\n			enabled : false\n		},\n		legend : {\n			enabled : false\n		},\n		xAxis : {\n			title : '''',\n			tickPosition: ''inside'',\n			labels: {\n				align: ''left'',\n				overflow: false,\n				x:5,\n				y:3,\n				style: { whiteSpace: ''nowrap'' }\n			}\n		},\n		yAxis : {\n\n			title : '''',\n			allowDecimals : false,\n			reversedStacks : false,\n			 tickPositioner: function () {\nvar maxDeviation = round_up(Math.max(Math.abs(this.dataMax), Math.abs(this.dataMin)),6);\n        var thirdMaxDeviation = Math.ceil(maxDeviation / 3);\n\n        return [-maxDeviation,-2*thirdMaxDeviation,-thirdMaxDeviation , 0, thirdMaxDeviation , 2*thirdMaxDeviation , maxDeviation];\n    },\n			stackLabels : {\n				enabled : false\n			}\n		},\n		plotOptions : {\n			bar : {\n				stacking : ''normal'',\n				borderWidth : 0,\n			 pointPadding: 0.1,\n			 groupPadding: 0.1\n			}\n		},\n\ntooltip: {\nformatter:function() {\n                return this.x+''<br><span style=\"color: '' + this.point.color + ''\">●</span>''+this.series.name+ '' : ''+this.y;}\n\n}', 'bar', 1);";


echo '
	<table class="box" border="0" cellpadding="1" cellspacing="2">
	<tr>
		<td class="smheading" align="center" height="25" width="550" colspan="2">Checking all graphs types are in database</td>
	</tr>';
if (small_count("SHOW TABLES LIKE 'uts_charttypes'") != 1) {
	// database exists, check columns
	echo '
	      <td class="warn" align="left" width="550" colspan = "2">Table uts_charttypes does not exist!</td></tr>';
}
else {
	foreach ($create_charttype as $charttype => $query) {
		echo '
		<tr>
			<td class="smheading" align="left" width="350">', $charttype, '</td>';
		if (small_count("SELECT id FROM uts_charttypes WHERE id = '$charttype' LIMIT 0,1") == 1) {
			// database exists, check columns
			echo '
			      <td class="grey" align="left" width="200">OK</td></tr>';
		}
		else {
			if (mysql_query($query)) {
				echo '
			      <td class="grey" align="left" width="200">Added</td></tr>';
			}
			else {
				echo '
			      <td class="warn" align="left" width="200">Not found!</td></tr>';
			}
		}
	}
}
echo '
	</tbody></table><br>';

// Check the weapons
$create_weapon['Translocator'] = "INSERT INTO `uts_weapons` VALUES (1, 'Translocator', 'trans.jpg', 1, 'N');";
$create_weapon['Impact Hammer'] = "INSERT INTO `uts_weapons` VALUES (2, 'Impact Hammer', 'impact.jpg', 2, 'N');";
$create_weapon['Enforcer'] = "INSERT INTO `uts_weapons` VALUES (3, 'Enforcer', 'enforcer.jpg', 3, 'N');";
$create_weapon['Double Enforcer'] = "INSERT INTO `uts_weapons` VALUES (4, 'Double Enforcer', 'enforcer2.jpg', 4, 'N');";
$create_weapon['GES Bio Rifle'] = "INSERT INTO `uts_weapons` VALUES (5, 'GES Bio Rifle', 'bio.jpg', 5, 'N');";
$create_weapon['Ripper'] = "INSERT INTO `uts_weapons` VALUES (6, 'Ripper', 'ripper.jpg', 6, 'N');";
$create_weapon['Shock Rifle'] = "INSERT INTO `uts_weapons` VALUES (7, 'Shock Rifle', 'shock.jpg', 7, 'N');";
$create_weapon['Enhanced Shock Rifle'] = "INSERT INTO `uts_weapons` VALUES (8, 'Enhanced Shock Rifle', 'ishock.jpg', 8, 'N');";
$create_weapon['Pulse Gun'] = "INSERT INTO `uts_weapons` VALUES (9, 'Pulse Gun', 'pulse.jpg', 9, 'N');";
$create_weapon['Minigun'] = "INSERT INTO `uts_weapons` VALUES (10, 'Minigun', 'minigun.jpg', 10, 'N');";
$create_weapon['Flak Cannon'] = "INSERT INTO `uts_weapons` VALUES (11, 'Flak Cannon', 'flak.jpg', 11, 'N');";
$create_weapon['Rocket Launcher'] = "INSERT INTO `uts_weapons` VALUES (12, 'Rocket Launcher', 'rockets.jpg', 12, 'N');";
$create_weapon['Sniper Rifle'] = "INSERT INTO `uts_weapons` VALUES (13, 'Sniper Rifle', 'sniper.jpg', 13, 'N');";
$create_weapon['Redeemer'] = "INSERT INTO `uts_weapons` VALUES (14, 'Redeemer', 'deemer.jpg', 14, 'N');";
$create_weapon['None'] = "INSERT INTO `uts_weapons` VALUES (15, 'None', 'blank.jpg', 15, 'N');";
$create_weapon['Chainsaw'] = "INSERT INTO `uts_weapons` VALUES (16, 'Chainsaw', 'chainsaw.jpg', 16, 'N');";

echo '
	<table class="box" border="0" cellpadding="1" cellspacing="2">
	<tr>
		<td class="smheading" align="center" height="25" width="550" colspan="2">Checking supported weapons</td>
	</tr>';
if (small_count("SHOW TABLES LIKE 'uts_weapons'") != 1) {
	// database exists, check columns
	echo '
	      <td class="warn" align="left" width="550" colspan = "2">Table uts_weapons does not exist!</td></tr>';
}
else {
	foreach ($create_weapon as $weapon => $query) {
		echo '
		<tr>
			<td class="smheading" align="left" width="350">', $weapon, '</td>';
		if (small_count("SELECT id FROM uts_weapons WHERE name = '$weapon' LIMIT 0,1") == 1) {
			// database exists, check columns
			echo '
			      <td class="grey" align="left" width="200">OK</td></tr>';
		}
		else {
			if (mysql_query($query)) {
				echo '
			      <td class="grey" align="left" width="200">Added</td></tr>';
			}
			else {
				echo '
			      <td class="warn" align="left" width="200">Not found!</td></tr>';
			}
		}
	}
}
echo '
	</tbody></table><br>';

echo '
	<table class="box" border="0" cellpadding="1" cellspacing="2">
	<tr>
		<td class="smheading" align="center" height="25" width="550" colspan="2">Checking data</td>
	</tr>';

	echo '
	<tr>
		<td class="smheading" align="left" width="350">Fix BT cap times</td>';

	$q = mysql_query("UPDATE uts_events SET col3 = ROUND(CEILING((col3-1)*100/1.1) / 100, 2), col1 = 'btcap' WHERE col1 = 'cap'");
	$affected = mysql_affected_rows();

	if ($affected == -1) {
	echo '
	      <td class="error" align="left" width="200">Failed</td></tr>
	<table>';
	}
	else {
	echo '
	      <td class="grey" align="left" width="200">Updated ' . $affected . ' rows.</td></tr>
	<table><br>';
	}

echo '
	<table class = "box" border="0" cellpadding="1" cellspacing="2" width="720">
	<tbody>';
echo '<tr><td class="smheading" align="center" colspan="4"><a class="grey" href="./admin.php?key='.$_REQUEST[key].'">Go Back To Admin Page</a></td></tr>';
echo '</tbody></table>';

?>